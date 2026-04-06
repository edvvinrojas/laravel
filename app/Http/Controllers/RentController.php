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
            ->when($request->search, fn($q) => $q->whereHas('client', fn($c) => $c->where('name', 'like', "%{$request->search}%"))
                ->orWhere('contract_number', 'like', "%{$request->search}%"))
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
            'branch_id'              => 'nullable|exists:branches,id',
            'area_id'                => 'nullable|exists:areas,id',
            'item_id'                => 'required|exists:items,id',
            'rent'                   => 'required|numeric|min:0',
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
            'contador_inicial_bn'    => 'nullable|integer|min:0',
            'contador_inicial_color' => 'nullable|integer|min:0',
        ]);

        $data['contract_number']     = $this->generateContractNumber();
        $data['created_by']          = Auth::id();
        $data['is_foreign']          = $request->boolean('is_foreign');
        $data['has_print_service']   = $request->boolean('has_print_service');
        $data['contador_inicial_bn']    = $request->input('contador_inicial_bn', 0);
        $data['contador_inicial_color'] = $request->input('contador_inicial_color', 0);

        $item = Item::findOrFail($data['item_id']);
        if ($item->location_status !== 'BODEGA') {
            return back()
                ->withInput()
                ->withErrors(['item_id' => 'Solo puedes seleccionar equipos con estado BODEGA.']);
        }

        $rent = Rent::create($data);

        if ($rent->contract_status === 'VIGENTE') {
            $rent->item->update(['location_status' => 'ASIGNADO']);
        }

        return redirect()->route('rents.show', $rent)->with('success', 'Renta creada correctamente.');
    }

    public function show(Rent $rent)
    {
        $rent->load(['client', 'branch', 'area', 'item.brand', 'creator', 'billings', 'printCounters.rent']);
        return view('rents.show', compact('rent'));
    }

    public function edit(Rent $rent)
    {
        $clients  = Client::where('is_active', true)->orderBy('name')->get();
        $items    = Item::where('is_active', true)
            ->with('brand')
            ->orderByRaw("CASE WHEN location_status = 'BODEGA' THEN 0 ELSE 1 END")
            ->orderBy('model')
            ->get();
        $branches = $rent->client_id ? Branch::where('client_id', $rent->client_id)->get() : collect();
        $areas    = $rent->branch_id  ? Area::where('branch_id', $rent->branch_id)->get()   : collect();
        return view('rents.edit', compact('rent', 'clients', 'items', 'branches', 'areas'));
    }

    public function update(Request $request, Rent $rent)
    {
        $data = $request->validate([
            'client_id'              => 'required|exists:clients,id',
            'branch_id'              => 'nullable|exists:branches,id',
            'area_id'                => 'nullable|exists:areas,id',
            'item_id'                => 'required|exists:items,id',
            'rent'                   => 'required|numeric|min:0',
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

        $item = Item::findOrFail($data['item_id']);
        if ($item->location_status !== 'BODEGA' && $item->id !== $rent->item_id) {
            return back()
                ->withInput()
                ->withErrors(['item_id' => 'Solo puedes seleccionar equipos con estado BODEGA.']);
        }

        $rent->update($data);

        return redirect()->route('rents.show', $rent)->with('success', 'Renta actualizada.');
    }

    public function destroy(Rent $rent)
    {
        $rent->update(['is_active' => false]);
        return redirect()->route('rents.index')->with('success', 'Renta desactivada.');
    }
}
