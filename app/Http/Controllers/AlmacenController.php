<?php

namespace App\Http\Controllers;

use App\Models\AlmacenMovement;
use App\Models\Item;
use App\Models\InventoryItem;
use App\Models\Sparepart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

        $spareparts = Sparepart::when($request->q_sp, fn($q, $s) =>
                $q->where('name', 'like', "%$s%")
                  ->orWhere('code', 'like', "%$s%")
                  ->orWhere('brand', 'like', "%$s%")
            )
            ->latest()
            ->paginate(15, ['*'], 'sp_page')
            ->withQueryString();

        $equipmentOptions = Item::query()
            ->select('id', 'sku', 'model', 'serie', 'location_status')
            ->orderBy('model')
            ->orderBy('serie')
            ->get();

        $inventoryOptions = InventoryItem::query()
            ->select('id', 'item_code', 'catalog_id', 'is_available')
            ->with('catalog:id,item_name')
            ->orderBy('item_code')
            ->get();

        $movements = AlmacenMovement::query()
            ->with(['equipment:id,sku,model,serie', 'inventory:id,item_code,catalog_id', 'inventory.catalog:id,item_name'])
            ->latest()
            ->paginate(15, ['*'], 'mv_page')
            ->withQueryString();

        return view('almacen.index', compact(
            'equipment', 'inventory', 'spareparts', 'tab',
            'equipmentOptions', 'inventoryOptions', 'movements'
        ));
    }

    public function storeMovement(Request $request)
    {
        $data = $request->validate([
            'movement_type' => 'required|in:SALIDA,ENTRADA',
            'equipment_id'  => 'nullable|exists:items,id',
            'inventory_id'  => 'nullable|exists:inventory,id',
            'person_name'   => 'required|string|max:120',
            'reason'        => 'required|string|max:1000',
        ]);

        if (!$data['equipment_id'] && !$data['inventory_id']) {
            return back()
                ->withInput()
                ->withErrors(['equipment_id' => 'Selecciona al menos un equipo o un tóner.']);
        }

        DB::transaction(function () use ($data) {
            $equipment = null;
            $inventory = null;

            if (!empty($data['equipment_id'])) {
                $equipment = Item::query()->lockForUpdate()->findOrFail($data['equipment_id']);

                if ($data['movement_type'] === 'SALIDA') {
                    $equipment->update(['location_status' => 'ASIGNADO']);
                } else {
                    $equipment->update(['location_status' => 'BODEGA']);
                }
            }

            if (!empty($data['inventory_id'])) {
                $inventory = InventoryItem::query()->lockForUpdate()->findOrFail($data['inventory_id']);

                if ($data['movement_type'] === 'SALIDA') {
                    if (!$inventory->is_available) {
                        throw ValidationException::withMessages([
                            'inventory_id' => 'El tóner seleccionado ya no está disponible.',
                        ]);
                    }
                    $inventory->update(['is_available' => false]);
                } else {
                    $inventory->update(['is_available' => true]);
                }
            }

            AlmacenMovement::create([
                'movement_type' => $data['movement_type'],
                'equipment_id'  => $equipment?->id,
                'inventory_id'  => $inventory?->id,
                'person_name'   => $data['person_name'],
                'reason'        => $data['reason'],
                'created_by'    => Auth::id(),
            ]);
        });

        return redirect()
            ->route('almacen.index', ['tab' => 'movimientos'])
            ->with('success', 'Movimiento de almacén registrado correctamente.');
    }
}
