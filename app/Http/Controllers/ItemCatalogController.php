<?php

namespace App\Http\Controllers;

use App\Models\ItemCatalog;
use App\Models\Brand;
use Illuminate\Http\Request;

class ItemCatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = ItemCatalog::with('brand')
            ->withCount('inventoryItems')
            ->when($request->search, fn($q, $s) =>
                $q->where('item_name', 'like', "%{$s}%")->orWhere('item_type', 'like', "%{$s}%")
            )
            ->when($request->type, fn($q, $t) => $q->where('item_type', $t))
            ->orderBy('item_name')
            ->paginate(15)
            ->withQueryString();

        return view('item-catalog.index', compact('query'));
    }

    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        return view('item-catalog.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name'   => 'required|string|max:255|unique:item_catalog,item_name',
            'item_type'   => 'required|in:TONER,REFACCION',
            'brand_id'    => 'nullable|exists:brands,id',
            'color'       => 'nullable|in:K,C,M,Y',
            'description' => 'nullable|string',
            'usage'       => 'nullable|string',
        ]);

        ItemCatalog::create([
            'item_name'   => $request->item_name,
            'item_type'   => $request->item_type,
            'brand_id'    => $request->brand_id,
            'color'       => $request->color,
            'description' => $request->description,
            'usage'       => $request->usage,
            'is_active'   => true,
        ]);

        return redirect()->route('item-catalog.index')->with('success', 'Artículo de catálogo registrado correctamente.');
    }

    public function edit(ItemCatalog $itemCatalog)
    {
        $brands = Brand::orderBy('name')->get();
        return view('item-catalog.edit', compact('itemCatalog', 'brands'));
    }

    public function update(Request $request, ItemCatalog $itemCatalog)
    {
        $request->validate([
            'item_name'   => 'required|string|max:255|unique:item_catalog,item_name,'.$itemCatalog->id,
            'item_type'   => 'required|in:TONER,REFACCION',
            'brand_id'    => 'nullable|exists:brands,id',
            'color'       => 'nullable|in:K,C,M,Y',
            'description' => 'nullable|string',
            'usage'       => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $itemCatalog->update([
            'item_name'   => $request->item_name,
            'item_type'   => $request->item_type,
            'brand_id'    => $request->brand_id,
            'color'       => $request->color ?: null,
            'description' => $request->description,
            'usage'       => $request->usage,
            'is_active'   => $request->boolean('is_active'),
        ]);

        return redirect()->route('item-catalog.index')->with('success', 'Artículo actualizado correctamente.');
    }

    public function destroy(ItemCatalog $itemCatalog)
    {
        $itemCatalog->delete();
        return redirect()->route('item-catalog.index')->with('success', 'Artículo eliminado.');
    }
}
