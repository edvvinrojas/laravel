<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Client;
use App\Models\Item;
use Illuminate\Http\Request;

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
        $items   = Item::where('is_active', true)->with('brand')->orderBy('model')->get();
        return view('sales.create', compact('clients', 'items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'branch_id'      => 'nullable|exists:branches,id',
            'area_id'        => 'nullable|exists:areas,id',
            'item_id'        => 'required|exists:items,id',
            'invoice_number' => 'nullable|string|max:50|unique:sales',
            'sale_status'    => 'required|in:PENDIENTE,CONFIRMADA,ENTREGADA,CANCELADA',
            'sale_price'     => 'required|numeric|min:0',
            'is_foreign'     => 'boolean',
        ]);

        $data['created_by'] = auth()->id();
        $data['is_foreign'] = $request->boolean('is_foreign');

        $sale = Sale::create($data);

        $sale->accesorios()->sync($request->input('accesorios', []));
        $sale->consumibles()->sync($request->input('consumibles', []));

        if ($sale->sale_status === 'ENTREGADA') {
            $sale->item->update(['location_status' => 'VENDIDO']);
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Venta registrada correctamente.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['client', 'branch', 'area', 'item.brand', 'creator', 'billings', 'accesorios', 'consumibles']);
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $items   = Item::where('is_active', true)->with('brand')->get();
        $sale->load('accesorios', 'consumibles');
        return view('sales.edit', compact('sale', 'clients', 'items'));
    }

    public function update(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'branch_id'      => 'nullable|exists:branches,id',
            'area_id'        => 'nullable|exists:areas,id',
            'item_id'        => 'required|exists:items,id',
            'invoice_number' => "nullable|string|max:50|unique:sales,invoice_number,{$sale->id}",
            'sale_status'    => 'required|in:PENDIENTE,CONFIRMADA,ENTREGADA,CANCELADA',
            'sale_price'     => 'required|numeric|min:0',
            'is_foreign'     => 'boolean',
        ]);

        $data['is_foreign'] = $request->boolean('is_foreign');
        $sale->update($data);

        $sale->accesorios()->sync($request->input('accesorios', []));
        $sale->consumibles()->sync($request->input('consumibles', []));

        return redirect()->route('sales.show', $sale)->with('success', 'Venta actualizada.');
    }

    public function destroy(Sale $sale)
    {
        $sale->update(['is_active' => false]);
        return redirect()->route('sales.index')->with('success', 'Venta cancelada.');
    }
}
