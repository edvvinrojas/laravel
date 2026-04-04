<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Client;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
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
            ->whereNotIn('location_status', ['ASIGNADO', 'TALLER'])
            ->with('brand')
            ->orderBy('model')
            ->get();
        return view('sales.create', compact('clients', 'items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'branch_id'          => 'nullable|exists:branches,id',
            'area_id'            => 'nullable|exists:areas,id',
            'item_id'            => 'required|exists:items,id',
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

        $sale = Sale::create($data);

        if ($sale->sale_status === 'ENTREGADA') {
            $sale->item->update(['location_status' => 'VENDIDO']);
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Venta registrada correctamente.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['client', 'branch', 'area', 'item.brand', 'creator', 'billings']);
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $items   = Item::where('is_active', true)
            ->where(fn($q) => $q
                ->whereNotIn('location_status', ['ASIGNADO', 'TALLER'])
                ->orWhere('id', $sale->item_id)  // siempre incluir el equipo actual de la venta
            )
            ->with('brand')
            ->orderBy('model')
            ->get();
        return view('sales.edit', compact('sale', 'clients', 'items'));
    }

    public function update(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'client_id'         => 'required|exists:clients,id',
            'branch_id'         => 'nullable|exists:branches,id',
            'area_id'           => 'nullable|exists:areas,id',
            'item_id'           => 'required|exists:items,id',
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
        $sale->update($data);

        return redirect()->route('sales.show', $sale)->with('success', 'Venta actualizada.');
    }

    public function destroy(Sale $sale)
    {
        $sale->update(['is_active' => false]);
        return redirect()->route('sales.index')->with('success', 'Venta cancelada.');
    }
}
