<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'equipos');

        $equipment = Item::with('brand', 'supplier')
            ->when($request->q_eq, fn($q, $s) =>
                $q->where(fn($q2) => $q2->where('sku', 'like', "%$s%")
                    ->orWhere('model', 'like', "%$s%")
                    ->orWhere('serie', 'like', "%$s%"))
            )
            ->latest()
            ->paginate(15, ['*'], 'eq_page')
            ->withQueryString();

        $inventory = InventoryItem::with(['catalog', 'shelf'])
            ->when($request->q_inv, fn($q, $s) =>
                $q->where('item_code', 'like', "%$s%")
                  ->orWhereHas('catalog', fn($q2) => $q2->where('item_name', 'like', "%$s%"))
            )
            ->latest()
            ->paginate(15, ['*'], 'inv_page')
            ->withQueryString();

        return view('almacen.index', compact('equipment', 'inventory', 'tab'));
    }
}
