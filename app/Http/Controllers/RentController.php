<?php

namespace App\Http\Controllers;

use App\Models\Rent;
use App\Models\Client;
use App\Models\Branch;
use App\Models\Area;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentController extends Controller
{
    private function generateContractNumber(): string
    {
        $year = date('Y');
        $last = Rent::whereYear('created_at', $year)
            ->whereNotNull('contract_number')
            ->where('contract_number', 'like', "CON-{$year}-%")
            ->orderByDesc('contract_number')
            ->value('contract_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'CON-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $rents = Rent::with(['client', 'item', 'item.brand'])
            ->where('is_active', true)
            ->when($request->search, fn($q) => $q->where(fn($sub) =>
                $sub->whereHas('client', fn($c) => $c->where('name', 'like', "%{$request->search}%"))
                    ->orWhere('contract_number', 'like', "%{$request->search}%")
            ))
            ->when($request->status, fn($q) => $q->where('contract_status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(15)->withQueryString();

        return view('rents.index', compact('rents'));
    }

    public function create()
    {
        $clients   = Client::where('is_active', true)->orderBy('name')->get();
        $items     = Item::where('is_active', true)
            ->with('brand')
            ->orderByRaw("CASE WHEN location_status = 'BODEGA' THEN 0 ELSE 1 END")
            ->orderBy('model')
            ->get();
        $nextContract = $this->generateContractNumber();
        return view('rents.create', compact('clients', 'items', 'nextContract'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'              => 'required|exists:clients,id',
            'item_rows'              => 'required|array|min:1',
            'item_rows.*.item_id'    => 'required|integer|exists:items,id|distinct',
            'item_rows.*.branch_id'  => 'required|integer|exists:branches,id',
            'item_rows.*.area_id'    => 'nullable|integer|exists:areas,id',
            'item_rows.*.rent'       => 'required|numeric|min:0',
            'item_rows.*.contador_inicial_bn' => 'nullable|integer|min:0',
            'item_rows.*.contador_inicial_color' => 'nullable|integer|min:0',
            'item_rows.*.has_print_service' => 'nullable|boolean',
            'item_rows.*.bn_included' => 'nullable|integer|min:0',
            'item_rows.*.bn_cost_per_excess' => 'nullable|numeric|min:0',
            'item_rows.*.color_included' => 'nullable|integer|min:0',
            'item_rows.*.color_cost_per_excess' => 'nullable|numeric|min:0',
            'contract_status'        => 'required|in:PENDIENTE,SIN_FIRMAR,VIGENTE,FINALIZADO,CANCELADO',
            'start_date'             => 'required|date',
            'end_date'               => 'nullable|date|after:start_date',
            'is_foreign'             => 'boolean',
            'has_print_service'      => 'boolean',
            'bn_included'            => 'nullable|integer|min:0',
            'bn_cost_per_excess'     => 'nullable|numeric|min:0',
            'color_included'         => 'nullable|integer|min:0',
            'color_cost_per_excess'  => 'nullable|numeric|min:0',
            'print_notes'            => 'nullable|string',
        ]);

        $data['contract_number']     = $this->generateContractNumber();
        $data['created_by']          = Auth::id();
        $data['is_foreign']          = $request->boolean('is_foreign');
        $data['has_print_service']   = $request->boolean('has_print_service');

        $itemRows = collect($data['item_rows'])
            ->map(function (array $row) {
                return [
                    'item_id' => (int) $row['item_id'],
                    'branch_id' => (int) $row['branch_id'],
                    'area_id' => !empty($row['area_id']) ? (int) $row['area_id'] : null,
                    'rent' => max(0, (float) ($row['rent'] ?? 0)),
                    'contador_inicial_bn' => max(0, (int) ($row['contador_inicial_bn'] ?? 0)),
                    'contador_inicial_color' => max(0, (int) ($row['contador_inicial_color'] ?? 0)),
                    'has_print_service' => !empty($row['has_print_service']),
                    'bn_included' => max(0, (int) ($row['bn_included'] ?? 0)),
                    'bn_cost_per_excess' => max(0, (float) ($row['bn_cost_per_excess'] ?? 0)),
                    'color_included' => max(0, (int) ($row['color_included'] ?? 0)),
                    'color_cost_per_excess' => max(0, (float) ($row['color_cost_per_excess'] ?? 0)),
                ];
            })
            ->values();

        $itemRows = $itemRows->map(function (array $row) {
            if (!$row['has_print_service']) {
                $row['bn_included'] = 0;
                $row['bn_cost_per_excess'] = 0;
                $row['color_included'] = 0;
                $row['color_cost_per_excess'] = 0;
            }
            return $row;
        })->values();

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
        $data['rent'] = $itemRows->sum('rent');  // total = suma de rentas por equipo
        $data['item_id'] = $firstRow['item_id'];
        $data['branch_id'] = $firstRow['branch_id'];
        $data['area_id'] = $firstRow['area_id'];
        $data['contador_inicial_bn'] = $firstRow['contador_inicial_bn'];
        $data['contador_inicial_color'] = $firstRow['contador_inicial_color'];
        $data['has_print_service'] = $firstRow['has_print_service'];
        $data['bn_included'] = $firstRow['bn_included'];
        $data['bn_cost_per_excess'] = $firstRow['bn_cost_per_excess'];
        $data['color_included'] = $firstRow['color_included'];
        $data['color_cost_per_excess'] = $firstRow['color_cost_per_excess'];
        unset($data['item_rows']);

        $rent = Rent::create($data);
        $syncData = [];
        foreach ($itemRows as $row) {
            $syncData[$row['item_id']] = [
                'branch_id' => $row['branch_id'],
                'area_id' => $row['area_id'],
                'rent' => $row['rent'],
                'contador_inicial_bn' => $row['contador_inicial_bn'],
                'contador_inicial_color' => $row['contador_inicial_color'],
                'has_print_service' => $row['has_print_service'],
                'bn_included' => $row['bn_included'],
                'bn_cost_per_excess' => $row['bn_cost_per_excess'],
                'color_included' => $row['color_included'],
                'color_cost_per_excess' => $row['color_cost_per_excess'],
            ];
        }
        $rent->items()->sync($syncData);

        if ($rent->contract_status === 'VIGENTE') {
            Item::whereIn('id', $selectedItemIds)->update(['location_status' => 'ASIGNADO']);
        }

        return redirect()->route('rents.show', $rent)->with('success', 'Renta creada correctamente.');
    }

    public function show(Rent $rent)
    {
        $rent->load(['client.branches.areas', 'branch', 'area', 'item.brand', 'items.brand', 'creator', 'billings', 'printCounters.rent']);
        return view('rents.show', compact('rent'));
    }

    public function pdf(Rent $rent)
    {
        $rent->load(['client.branches.areas', 'branch', 'area', 'item.brand', 'items.brand', 'creator', 'billings', 'printCounters']);
        return view('pdf.rent', compact('rent'));
    }

    public function edit(Rent $rent)
    {
        $clients  = Client::where('is_active', true)->orderBy('name')->get();
        $items    = Item::where('is_active', true)
            ->with('brand')
            ->orderByRaw("CASE WHEN location_status = 'BODEGA' THEN 0 ELSE 1 END")
            ->orderBy('model')
            ->get();
        $rent->load('items');

        return view('rents.edit', compact('rent', 'clients', 'items'));
    }

    public function update(Request $request, Rent $rent)
    {
        $data = $request->validate([
            'client_id'              => 'required|exists:clients,id',
            'item_rows'              => 'required|array|min:1',
            'item_rows.*.item_id'    => 'required|integer|exists:items,id|distinct',
            'item_rows.*.branch_id'  => 'required|integer|exists:branches,id',
            'item_rows.*.area_id'    => 'nullable|integer|exists:areas,id',
            'item_rows.*.contador_inicial_bn' => 'nullable|integer|min:0',
            'item_rows.*.contador_inicial_color' => 'nullable|integer|min:0',
            'item_rows.*.has_print_service' => 'nullable|boolean',
            'item_rows.*.bn_included' => 'nullable|integer|min:0',
            'item_rows.*.bn_cost_per_excess' => 'nullable|numeric|min:0',
            'item_rows.*.color_included' => 'nullable|integer|min:0',
            'item_rows.*.color_cost_per_excess' => 'nullable|numeric|min:0',
            'item_rows.*.rent'       => 'required|numeric|min:0',
            'contract_status'        => 'required|in:PENDIENTE,SIN_FIRMAR,VIGENTE,FINALIZADO,CANCELADO',
            'start_date'             => 'required|date',
            'end_date'               => 'nullable|date',
            'is_foreign'             => 'boolean',
            'has_print_service'      => 'boolean',
            'bn_included'            => 'nullable|integer|min:0',
            'bn_cost_per_excess'     => 'nullable|numeric|min:0',
            'color_included'         => 'nullable|integer|min:0',
            'color_cost_per_excess'  => 'nullable|numeric|min:0',
            'print_notes'            => 'nullable|string',
        ]);

        $data['is_foreign']        = $request->boolean('is_foreign');
        $data['has_print_service'] = $request->boolean('has_print_service');

        $currentItemIds = $rent->items()->pluck('items.id');
        if ($currentItemIds->isEmpty() && $rent->item_id) {
            $currentItemIds = collect([$rent->item_id]);
        }

        $itemRows = collect($data['item_rows'])
            ->map(function (array $row) {
                return [
                    'item_id' => (int) $row['item_id'],
                    'branch_id' => (int) $row['branch_id'],
                    'area_id' => !empty($row['area_id']) ? (int) $row['area_id'] : null,
                    'rent' => max(0, (float) ($row['rent'] ?? 0)),
                    'contador_inicial_bn' => max(0, (int) ($row['contador_inicial_bn'] ?? 0)),
                    'contador_inicial_color' => max(0, (int) ($row['contador_inicial_color'] ?? 0)),
                    'has_print_service' => !empty($row['has_print_service']),
                    'bn_included' => max(0, (int) ($row['bn_included'] ?? 0)),
                    'bn_cost_per_excess' => max(0, (float) ($row['bn_cost_per_excess'] ?? 0)),
                    'color_included' => max(0, (int) ($row['color_included'] ?? 0)),
                    'color_cost_per_excess' => max(0, (float) ($row['color_cost_per_excess'] ?? 0)),
                ];
            })
            ->values();

        $itemRows = $itemRows->map(function (array $row) {
            if (!$row['has_print_service']) {
                $row['bn_included'] = 0;
                $row['bn_cost_per_excess'] = 0;
                $row['color_included'] = 0;
                $row['color_cost_per_excess'] = 0;
            }
            return $row;
        })->values();

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
        $data['rent'] = $itemRows->sum('rent');  // total = suma de rentas por equipo
        $data['item_id'] = $firstRow['item_id'];
        $data['branch_id'] = $firstRow['branch_id'];
        $data['area_id'] = $firstRow['area_id'];
        $data['contador_inicial_bn'] = $firstRow['contador_inicial_bn'];
        $data['contador_inicial_color'] = $firstRow['contador_inicial_color'];
        $data['has_print_service'] = $firstRow['has_print_service'];
        $data['bn_included'] = $firstRow['bn_included'];
        $data['bn_cost_per_excess'] = $firstRow['bn_cost_per_excess'];
        $data['color_included'] = $firstRow['color_included'];
        $data['color_cost_per_excess'] = $firstRow['color_cost_per_excess'];
        unset($data['item_rows']);

        $previousStatus = $rent->contract_status;
        $rent->update($data);
        $syncData = [];
        foreach ($itemRows as $row) {
            $syncData[$row['item_id']] = [
                'branch_id' => $row['branch_id'],
                'area_id' => $row['area_id'],
                'rent' => $row['rent'],
                'contador_inicial_bn' => $row['contador_inicial_bn'],
                'contador_inicial_color' => $row['contador_inicial_color'],
                'has_print_service' => $row['has_print_service'],
                'bn_included' => $row['bn_included'],
                'bn_cost_per_excess' => $row['bn_cost_per_excess'],
                'color_included' => $row['color_included'],
                'color_cost_per_excess' => $row['color_cost_per_excess'],
            ];
        }
        $rent->items()->sync($syncData);

        // Actualizar location_status de equipos según cambio de estatus
        if ($data['contract_status'] === 'VIGENTE') {
            Item::whereIn('id', $selectedItemIds)->update(['location_status' => 'ASIGNADO']);
        } elseif (in_array($data['contract_status'], ['FINALIZADO', 'CANCELADO'], true) && $previousStatus === 'VIGENTE') {
            // Liberar equipos que ya no estén en otra renta vigente
            foreach ($selectedItemIds as $itemId) {
                $hasOtherActive = Rent::where('id', '!=', $rent->id)
                    ->where('contract_status', 'VIGENTE')
                    ->where('is_active', true)
                    ->whereHas('items', fn($q) => $q->where('items.id', $itemId))
                    ->exists();
                if (!$hasOtherActive) {
                    Item::where('id', $itemId)->update(['location_status' => 'BODEGA']);
                }
            }
        }

        return redirect()->route('rents.show', $rent)->with('success', 'Renta actualizada.');
    }

    public function destroy(Rent $rent)
    {
        // Liberar equipos si la renta estaba vigente
        if ($rent->contract_status === 'VIGENTE') {
            $itemIds = $rent->items()->pluck('items.id');
            if ($itemIds->isEmpty() && $rent->item_id) {
                $itemIds = collect([$rent->item_id]);
            }
            foreach ($itemIds as $itemId) {
                $hasOtherActive = Rent::where('id', '!=', $rent->id)
                    ->where('contract_status', 'VIGENTE')
                    ->where('is_active', true)
                    ->whereHas('items', fn($q) => $q->where('items.id', $itemId))
                    ->exists();
                if (!$hasOtherActive) {
                    Item::where('id', $itemId)->update(['location_status' => 'BODEGA']);
                }
            }
        }

        $rent->update(['is_active' => false]);
        return redirect()->route('rents.index')->with('success', 'Renta desactivada.');
    }
}
