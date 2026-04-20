<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Client;
use App\Models\Branch;
use App\Models\Area;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $last = Sale::whereYear('created_at', $year)
            ->whereNotNull('invoice_number')
            ->where('invoice_number', 'like', "VTA-{$year}-%")
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'VTA-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $sales = Sale::with(['client', 'item.brand'])
            ->when($request->search, fn($q) => $q->whereHas('client', fn($c) => $c->where('name', 'like', "%{$request->search}%"))
                ->orWhere('invoice_number', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('sale_status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(15)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $items   = Item::where('is_active', true)
            ->with('brand')
            ->orderByRaw("CASE WHEN location_status = 'BODEGA' THEN 0 ELSE 1 END")
            ->orderBy('model')
            ->get();
        return view('sales.create', compact('clients', 'items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'item_rows'          => 'required|array|min:1',
            'item_rows.*.item_id' => 'required|integer|exists:items,id|distinct',
            'item_rows.*.branch_id' => 'required|integer|exists:branches,id',
            'item_rows.*.area_id' => 'nullable|integer|exists:areas,id',
            'invoice_number'     => 'nullable|string|max:50|unique:sales',
            'sale_status'        => 'required|in:PENDIENTE,CONFIRMADA,ENTREGADA,CANCELADA',
            'sale_price'         => 'required|numeric|min:0',
            'is_foreign'         => 'boolean',
            'services_included'  => 'boolean',
            'services_quantity'  => 'nullable|integer|min:1',
        ]);

        $data['created_by']         = Auth::id();
        $data['is_foreign']         = $request->boolean('is_foreign');
        $data['services_included']  = $request->boolean('services_included');
        $data['services_quantity']  = $data['services_included'] ? ($data['services_quantity'] ?? null) : null;

        if (empty($data['invoice_number'])) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
        }

        $itemRows = collect($data['item_rows'])
            ->map(function (array $row) {
                return [
                    'item_id' => (int) $row['item_id'],
                    'branch_id' => (int) $row['branch_id'],
                    'area_id' => !empty($row['area_id']) ? (int) $row['area_id'] : null,
                ];
            })
            ->values();

        $branchIds = $itemRows->pluck('branch_id')->unique()->values();
        $validBranchIds = Branch::where('client_id', $data['client_id'])
            ->whereIn('id', $branchIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($validBranchIds) !== $branchIds->count()) {
            return back()->withInput()->withErrors([
                'item_rows' => 'Cada equipo debe apuntar a una sucursal del cliente seleccionado.',
            ]);
        }

        $areaIds = $itemRows->pluck('area_id')->filter()->unique()->values();
        $areasById = Area::whereIn('id', $areaIds)->get()->keyBy('id');
        foreach ($itemRows as $row) {
            if (!$row['area_id']) {
                continue;
            }

            $area = $areasById->get($row['area_id']);
            if (!$area || (int) $area->branch_id !== $row['branch_id']) {
                return back()->withInput()->withErrors([
                    'item_rows' => 'El area seleccionada debe pertenecer a la sucursal indicada en cada equipo.',
                ]);
            }
        }

        $selectedItemIds = $itemRows->pluck('item_id')->unique()->values();
        $items = Item::whereIn('id', $selectedItemIds)->get();
        $nonBodega = $items->first(fn ($item) => $item->location_status !== 'BODEGA');
        if ($nonBodega) {
            return back()
                ->withInput()
                ->withErrors(['item_rows' => 'Solo puedes seleccionar equipos con estado BODEGA.']);
        }

        $firstRow = $itemRows->first();
        $data['item_id'] = $firstRow['item_id'];
        $data['branch_id'] = $firstRow['branch_id'];
        $data['area_id'] = $firstRow['area_id'];
        unset($data['item_rows']);

        $sale = Sale::create($data);
        $syncData = [];
        foreach ($itemRows as $row) {
            $syncData[$row['item_id']] = [
                'branch_id' => $row['branch_id'],
                'area_id' => $row['area_id'],
            ];
        }
        $sale->items()->sync($syncData);

        if ($sale->sale_status === 'ENTREGADA') {
            Item::whereIn('id', $selectedItemIds)->update(['location_status' => 'VENDIDO']);
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Venta registrada correctamente.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['client.branches.areas', 'branch', 'area', 'item.brand', 'items.brand', 'creator', 'billings']);
        return view('sales.show', compact('sale'));
    }

    public function pdf(Sale $sale)
    {
        $sale->load(['client.branches.areas', 'branch', 'area', 'item.brand', 'items.brand', 'creator', 'billings']);
        return view('pdf.sale', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $items   = Item::where('is_active', true)
            ->with('brand')
            ->orderByRaw("CASE WHEN location_status = 'BODEGA' THEN 0 ELSE 1 END")
            ->orderBy('model')
            ->get();
        $sale->load('items');

        return view('sales.edit', compact('sale', 'clients', 'items'));
    }

    public function update(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'client_id'         => 'required|exists:clients,id',
            'item_rows'         => 'required|array|min:1',
            'item_rows.*.item_id' => 'required|integer|exists:items,id|distinct',
            'item_rows.*.branch_id' => 'required|integer|exists:branches,id',
            'item_rows.*.area_id' => 'nullable|integer|exists:areas,id',
            'invoice_number'    => "nullable|string|max:50|unique:sales,invoice_number,{$sale->id}",
            'sale_status'       => 'required|in:PENDIENTE,CONFIRMADA,ENTREGADA,CANCELADA',
            'sale_price'        => 'required|numeric|min:0',
            'is_foreign'        => 'boolean',
            'services_included' => 'boolean',
            'services_quantity' => 'nullable|integer|min:1',
        ]);

        $data['is_foreign']        = $request->boolean('is_foreign');
        $data['services_included'] = $request->boolean('services_included');
        $data['services_quantity'] = $data['services_included'] ? ($data['services_quantity'] ?? null) : null;

        $currentItemIds = $sale->items()->pluck('items.id');
        if ($currentItemIds->isEmpty() && $sale->item_id) {
            $currentItemIds = collect([$sale->item_id]);
        }

        $itemRows = collect($data['item_rows'])
            ->map(function (array $row) {
                return [
                    'item_id' => (int) $row['item_id'],
                    'branch_id' => (int) $row['branch_id'],
                    'area_id' => !empty($row['area_id']) ? (int) $row['area_id'] : null,
                ];
            })
            ->values();

        $branchIds = $itemRows->pluck('branch_id')->unique()->values();
        $validBranchIds = Branch::where('client_id', $data['client_id'])
            ->whereIn('id', $branchIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($validBranchIds) !== $branchIds->count()) {
            return back()->withInput()->withErrors([
                'item_rows' => 'Cada equipo debe apuntar a una sucursal del cliente seleccionado.',
            ]);
        }

        $areaIds = $itemRows->pluck('area_id')->filter()->unique()->values();
        $areasById = Area::whereIn('id', $areaIds)->get()->keyBy('id');
        foreach ($itemRows as $row) {
            if (!$row['area_id']) {
                continue;
            }

            $area = $areasById->get($row['area_id']);
            if (!$area || (int) $area->branch_id !== $row['branch_id']) {
                return back()->withInput()->withErrors([
                    'item_rows' => 'El area seleccionada debe pertenecer a la sucursal indicada en cada equipo.',
                ]);
            }
        }

        $selectedItemIds = $itemRows->pluck('item_id')->unique()->values();
        $items = Item::whereIn('id', $selectedItemIds)->get();
        $currentLookup = $currentItemIds->map(fn ($id) => (int) $id)->all();
        $invalid = $items->first(function ($item) use ($currentLookup) {
            return $item->location_status !== 'BODEGA' && !in_array((int) $item->id, $currentLookup, true);
        });

        if ($invalid) {
            return back()
                ->withInput()
                ->withErrors(['item_rows' => 'Solo puedes seleccionar equipos con estado BODEGA.']);
        }

        $firstRow = $itemRows->first();
        $data['item_id'] = $firstRow['item_id'];
        $data['branch_id'] = $firstRow['branch_id'];
        $data['area_id'] = $firstRow['area_id'];
        unset($data['item_rows']);

        $sale->update($data);
        $syncData = [];
        foreach ($itemRows as $row) {
            $syncData[$row['item_id']] = [
                'branch_id' => $row['branch_id'],
                'area_id' => $row['area_id'],
            ];
        }
        $sale->items()->sync($syncData);

        return redirect()->route('sales.show', $sale)->with('success', 'Venta actualizada.');
    }

    public function destroy(Sale $sale)
    {
        $sale->update(['is_active' => false]);
        return redirect()->route('sales.index')->with('success', 'Venta cancelada.');
    }
}
