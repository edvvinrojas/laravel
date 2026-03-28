<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sparepart;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = Purchase::with(['sparepart', 'user'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(15)->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $spareparts = Sparepart::orderBy('name')->get();
        return view('purchases.create', compact('spareparts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sparepart_id'    => 'nullable|exists:spareparts,id',
            'name'            => 'required|string|max:255',
            'amount'          => 'required|integer|min:1',
            'quality'         => 'nullable|string|max:100',
            'justification'   => 'nullable|string',
            'type'            => 'required|in:INTERNA,VENTA',
            'supplier1_name'  => 'nullable|string|max:255',
            'supplier1_cost'  => 'nullable|numeric|min:0',
            'supplier2_name'  => 'nullable|string|max:255',
            'supplier2_cost'  => 'nullable|numeric|min:0',
            'supplier3_name'  => 'nullable|string|max:255',
            'supplier3_cost'  => 'nullable|numeric|min:0',
            'comments'        => 'nullable|string',
        ]);

        $data['user_id'] = auth()->id();
        $data['status']  = 'EN_CURSO';

        Purchase::create($data);

        return redirect()->route('purchases.index')->with('success', 'Compra registrada.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['sparepart', 'user', 'areaChief', 'admin']);
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $spareparts = Sparepart::orderBy('name')->get();
        return view('purchases.edit', compact('purchase', 'spareparts'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'amount'            => 'required|integer|min:1',
            'authorized_amount' => 'nullable|integer|min:0',
            'quality'           => 'nullable|string|max:100',
            'justification'     => 'nullable|string',
            'type'              => 'required|in:INTERNA,VENTA',
            'supplier1_name'    => 'nullable|string|max:255',
            'supplier1_cost'    => 'nullable|numeric|min:0',
            'supplier2_name'    => 'nullable|string|max:255',
            'supplier2_cost'    => 'nullable|numeric|min:0',
            'supplier3_name'    => 'nullable|string|max:255',
            'supplier3_cost'    => 'nullable|numeric|min:0',
            'status'            => 'required|string',
            'shipping_method'   => 'nullable|string|max:100',
            'shipping_cost'     => 'nullable|numeric|min:0',
            'shipping_code'     => 'nullable|string|max:100',
            'comments'          => 'nullable|string',
        ]);

        $purchase->update($data);

        return redirect()->route('purchases.show', $purchase)->with('success', 'Compra actualizada.');
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Compra eliminada.');
    }
}
