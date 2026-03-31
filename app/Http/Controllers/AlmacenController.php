<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\InventoryItem;
use App\Models\Sparepart;
use App\Models\Producto;
use App\Models\Accesorio;
use App\Models\Consumible;
use App\Models\Stock;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'equipos');

        $equipment = Item::with('brand', 'supplier', 'producto')
            ->when($request->q_eq, fn($q, $s) =>
                $q->where(fn($q2) => $q2->where('sku', 'like', "%$s%")
                    ->orWhere('model', 'like', "%$s%")
                    ->orWhere('serie', 'like', "%$s%"))
            )
            ->latest()
            ->paginate(15, ['*'], 'eq_page')
            ->withQueryString();

        $productos = Producto::with('marca', 'stock')
            ->withCount('accesorios', 'consumibles', 'equipos')
            ->when($request->q_pr, fn($q, $s) =>
                $q->where('nombre', 'like', "%$s%")
                  ->orWhere('codigo', 'like', "%$s%")
            )
            ->orderBy('nombre')
            ->paginate(15, ['*'], 'pr_page')
            ->withQueryString();

        $accesorios = Accesorio::with('stock')
            ->when($request->q_ac, fn($q, $s) =>
                $q->where('nombre', 'like', "%$s%")
                  ->orWhere('codigo', 'like', "%$s%")
            )
            ->orderBy('nombre')
            ->paginate(15, ['*'], 'ac_page')
            ->withQueryString();

        $consumibles = Consumible::with('marca', 'stock')
            ->when($request->q_co, fn($q, $s) =>
                $q->where('nombre', 'like', "%$s%")
                  ->orWhere('codigo_oem', 'like', "%$s%")
            )
            ->orderBy('nombre')
            ->paginate(15, ['*'], 'co_page')
            ->withQueryString();

        $inventory = InventoryItem::with(['catalog', 'shelf'])
            ->when($request->q_inv, fn($q, $s) =>
                $q->where('item_code', 'like', "%$s%")
                  ->orWhereHas('catalog', fn($q2) => $q2->where('item_name', 'like', "%$s%"))
            )
            ->latest()
            ->paginate(15, ['*'], 'inv_page')
            ->withQueryString();

        $spareparts = Sparepart::when($request->q_sp, fn($q, $s) =>
                $q->where('name', 'like', "%$s%")
                  ->orWhere('code', 'like', "%$s%")
                  ->orWhere('brand', 'like', "%$s%")
            )
            ->latest()
            ->paginate(15, ['*'], 'sp_page')
            ->withQueryString();

        return view('almacen.index', compact(
            'equipment', 'productos', 'accesorios', 'consumibles',
            'inventory', 'spareparts', 'tab'
        ));
    }
}
