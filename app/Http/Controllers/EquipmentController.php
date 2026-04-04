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

    private function formData(): array
    {
        return [
            'brands'    => Brand::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ];
    }

    public function create()
    {
        $skus = \App\Models\Sku::where('category', 'EQUIPO')->orderBy('code')->get();
        return view('equipment.create', ['skus' => $skus] + $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'                   => 'nullable|string|max:100',
            'brand_id'              => 'required|exists:brands,id',
            'model'                 => 'required|string|max:255',
            'serie'                 => 'required|string|max:255|unique:items,serie',
            'model_toner'           => 'required|string|max:255',
            'type'                  => 'required|in:MONOCROMO,COLOR',
            'supplier_id'           => 'nullable|exists:suppliers,id',
            'invoice'               => 'nullable|string|max:100',
            'cost'                  => 'nullable|numeric|min:0',
            'location_status'       => 'nullable|in:BODEGA,ASIGNADO,VENDIDO,TALLER,DESCONOCIDO',
            'comments'              => 'nullable|string',
            'is_active'             => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        if (empty($validated['sku'])) {
            $brand = Brand::findOrFail($validated['brand_id']);
            $prefix = strtoupper($brand->name) . '-';
            $lastItem = Item::where('sku', 'like', $prefix . '%')->orderByDesc('sku')->first();
            $nextNumber = 1;
            if ($lastItem) {
                $num = intval(str_replace($prefix, '', $lastItem->sku));
                $nextNumber = $num + 1;
            }
            $format = \App\Models\SkuFormat::where('category', 'EQUIPO')->first();
            $pad = $format->pad ?? 3;
            $validated['sku'] = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);
        }

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
        $skus = \App\Models\Sku::where('category', 'EQUIPO')->orderBy('code')->get();
        return view('equipment.edit', ['equipment' => $equipment, 'skus' => $skus] + $this->formData());
    }

    public function update(Request $request, Item $equipment)
    {
        $validated = $request->validate([
            'sku'                   => 'nullable|string|max:100',
            'brand_id'              => 'required|exists:brands,id',
            'model'                 => 'required|string|max:255',
            'serie'                 => 'required|string|max:255|unique:items,serie,'.$equipment->id,
            'model_toner'           => 'required|string|max:255',
            'type'                  => 'required|in:MONOCROMO,COLOR',
            'supplier_id'           => 'nullable|exists:suppliers,id',
            'invoice'               => 'nullable|string|max:100',
            'cost'                  => 'nullable|numeric|min:0',
            'location_status'       => 'nullable|in:BODEGA,ASIGNADO,VENDIDO,TALLER,DESCONOCIDO',
            'comments'              => 'nullable|string',
            'is_active'             => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $equipment->update($validated);

        return redirect()->route('equipment.index')
            ->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy(Item $equipment)
    {
        if ($equipment->rents()->exists() || $equipment->sales()->exists() || $equipment->repairs()->exists()) {
            return redirect()->route('equipment.index')
                ->with('error', 'No se puede eliminar el equipo porque tiene rentas, ventas o reparaciones asociadas.');
        }

        $equipment->delete();

        return redirect()->route('equipment.index')
            ->with('success', 'Equipo eliminado correctamente.');
    }
}
