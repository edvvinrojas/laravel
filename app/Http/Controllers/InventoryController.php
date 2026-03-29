<?php
// app/Http/Controllers/InventoryController.php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ItemCatalog;
use App\Models\Shelf;
use App\Models\Supplier;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::with('catalog', 'shelf', 'supplier')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('item_code', 'like', "%{$search}%")
                       ->orWhereHas('catalog', function ($q3) use ($search) {
                           $q3->where('item_name', 'like', "%{$search}%")
                              ->orWhere('item_type', 'like', "%{$search}%");
                       });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('inventory.index', compact('query'));
    }

    public function create()
    {
        $catalogs  = ItemCatalog::where('is_active', true)->orderBy('item_name')->get();
        $shelves   = Shelf::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('inventory.create', compact('catalogs', 'shelves', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code'    => 'required|string|max:100',
            'catalog_id'   => 'required|exists:item_catalog,id',
            'section'      => 'required|in:SECCION_1,SECCION_2,SECCION_3,SECCION_4,SECCION_5,SECCION_6',
            'shelf_id'     => 'nullable|exists:shelves,id',
            'quality'      => 'required|in:ORIGINAL,GENERICO,REPARADO,NUEVA,USADO,NA',
            'entry_date'   => 'nullable|date',
            'supplier_id'  => 'nullable|exists:suppliers,id',
            'invoice'      => 'nullable|string|max:100',
            'cost'         => 'nullable|numeric|min:0',
            'is_available' => 'nullable|boolean',
            'comments'     => 'nullable|string',
        ]);

        $validated['is_available'] = $request->boolean('is_available', true);
        $validated['is_active']    = true;

        InventoryItem::create($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Artículo de inventario registrado correctamente.');
    }

    public function show(InventoryItem $inventory)
    {
        $inventory->load('catalog', 'shelf', 'supplier', 'items');

        return view('inventory.show', compact('inventory'));
    }

    public function edit(InventoryItem $inventory)
    {
        $catalogs  = ItemCatalog::where('is_active', true)->orderBy('item_name')->get();
        $shelves   = Shelf::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('inventory.edit', compact('inventory', 'catalogs', 'shelves', 'suppliers'));
    }

    public function update(Request $request, InventoryItem $inventory)
    {
        $validated = $request->validate([
            'item_code'    => 'required|string|max:100',
            'catalog_id'   => 'required|exists:item_catalog,id',
            'section'      => 'required|in:SECCION_1,SECCION_2,SECCION_3,SECCION_4,SECCION_5,SECCION_6',
            'shelf_id'     => 'nullable|exists:shelves,id',
            'quality'      => 'required|in:ORIGINAL,GENERICO,REPARADO,NUEVA,USADO,NA',
            'entry_date'   => 'nullable|date',
            'supplier_id'  => 'nullable|exists:suppliers,id',
            'invoice'      => 'nullable|string|max:100',
            'cost'         => 'nullable|numeric|min:0',
            'is_available' => 'nullable|boolean',
            'comments'     => 'nullable|string',
        ]);

        $validated['is_available'] = $request->boolean('is_available');

        $inventory->update($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Artículo de inventario actualizado correctamente.');
    }

    public function destroy(InventoryItem $inventory)
    {
        $inventory->delete();

        return redirect()->route('inventory.index')
            ->with('success', 'Artículo de inventario eliminado correctamente.');
    }
}
