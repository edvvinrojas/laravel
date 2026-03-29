<?php
// app/Http/Controllers/EquipmentController.php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Brand;
use App\Models\Supplier;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with('brand', 'supplier')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('sku', 'like', "%{$search}%")
                       ->orWhere('model', 'like', "%{$search}%")
                       ->orWhere('serie', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('equipment.index', compact('query'));
    }

    public function create()
    {
        $brands    = Brand::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('equipment.create', compact('brands', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'             => 'nullable|string|max:100',
            'brand_id'        => 'required|exists:brands,id',
            'model'           => 'required|string|max:255',
            'serie'           => 'required|string|max:255|unique:items,serie',
            'model_toner'     => 'required|string|max:255',
            'type'            => 'required|in:MONOCROMO,COLOR',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'invoice'         => 'nullable|string|max:100',
            'cost'            => 'nullable|numeric|min:0',
            'location_status' => 'nullable|in:BODEGA,ASIGNADO,VENDIDO,TALLER,DESCONOCIDO',
            'comments'        => 'nullable|string',
            'is_active'       => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Item::create($validated);

        return redirect()->route('equipment.index')
            ->with('success', 'Equipo registrado correctamente.');
    }

    public function show(Item $equipment)
    {
        $equipment->load('brand', 'supplier', 'rents.client', 'sales.client');

        return view('equipment.show', compact('equipment'));
    }

    public function edit(Item $equipment)
    {
        $brands    = Brand::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('equipment.edit', compact('equipment', 'brands', 'suppliers'));
    }

    public function update(Request $request, Item $equipment)
    {
        $validated = $request->validate([
            'sku'             => 'nullable|string|max:100',
            'brand_id'        => 'required|exists:brands,id',
            'model'           => 'required|string|max:255',
            'serie'           => 'required|string|max:255|unique:items,serie,'.$equipment->id,
            'model_toner'     => 'required|string|max:255',
            'type'            => 'required|in:MONOCROMO,COLOR',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'invoice'         => 'nullable|string|max:100',
            'cost'            => 'nullable|numeric|min:0',
            'location_status' => 'nullable|in:BODEGA,ASIGNADO,VENDIDO,TALLER,DESCONOCIDO',
            'comments'        => 'nullable|string',
            'is_active'       => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $equipment->update($validated);

        return redirect()->route('equipment.index')
            ->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy(Item $equipment)
    {
        $equipment->delete();

        return redirect()->route('equipment.index')
            ->with('success', 'Equipo eliminado correctamente.');
    }
}
