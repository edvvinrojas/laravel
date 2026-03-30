<?php
// app/Http/Controllers/EquipmentController.php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\CategoriaEquipo;
use App\Models\ModeloEquipo;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with('brand', 'supplier', 'categoria', 'modelo')
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
            'brands'     => Brand::orderBy('name')->get(),
            'suppliers'  => Supplier::orderBy('name')->get(),
            'categorias' => CategoriaEquipo::where('es_activo', true)->orderBy('nombre')->get(),
            'modelos'    => ModeloEquipo::with('marca', 'categoria')->where('es_activo', true)->orderBy('nombre_modelo')->get(),
        ];
    }

    public function create()
    {
        return view('equipment.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'                   => 'nullable|string|max:100',
            'brand_id'              => 'required|exists:brands,id',
            'categoria_id'          => 'nullable|exists:categorias_equipo,id',
            'modelo_id'             => 'nullable|exists:modelos_equipo,id',
            'model'                 => 'required|string|max:255',
            'serie'                 => 'required|string|max:255|unique:items,serie',
            'model_toner'           => 'required|string|max:255',
            'type'                  => 'required|in:MONOCROMO,COLOR',
            'tipo_equipo'           => 'nullable|in:COPIADORA,IMPRESORA,MFP,ESCANER,FAX,PLOTTER',
            'formato_max'           => 'nullable|in:A4,A3,CARTA,OFICIO,A2,A1,A0',
            'supplier_id'           => 'nullable|exists:suppliers,id',
            'invoice'               => 'nullable|string|max:100',
            'cost'                  => 'nullable|numeric|min:0',
            'fecha_compra'          => 'nullable|date',
            'fecha_instalacion'     => 'nullable|date',
            'fecha_garantia_fin'    => 'nullable|date',
            'location_status'       => 'nullable|in:BODEGA,ASIGNADO,VENDIDO,TALLER,DESCONOCIDO',
            'ubicacion_fisica'      => 'nullable|string|max:200',
            'contador_inicial_bn'   => 'nullable|integer|min:0',
            'contador_inicial_color'=> 'nullable|integer|min:0',
            'contador_inicial_scan' => 'nullable|integer|min:0',
            'direccion_ip'          => 'nullable|string|max:45',
            'mac_address'           => 'nullable|string|max:20',
            'comments'              => 'nullable|string',
            'is_active'             => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Item::create($validated);

        return redirect()->route('equipment.index')
            ->with('success', 'Equipo registrado correctamente.');
    }

    public function show(Item $equipment)
    {
        $equipment->load('brand', 'supplier', 'categoria', 'modelo', 'rents.client', 'sales.client');

        return view('equipment.show', compact('equipment'));
    }

    public function edit(Item $equipment)
    {
        return view('equipment.edit', ['equipment' => $equipment] + $this->formData());
    }

    public function update(Request $request, Item $equipment)
    {
        $validated = $request->validate([
            'sku'                   => 'nullable|string|max:100',
            'brand_id'              => 'required|exists:brands,id',
            'categoria_id'          => 'nullable|exists:categorias_equipo,id',
            'modelo_id'             => 'nullable|exists:modelos_equipo,id',
            'model'                 => 'required|string|max:255',
            'serie'                 => 'required|string|max:255|unique:items,serie,'.$equipment->id,
            'model_toner'           => 'required|string|max:255',
            'type'                  => 'required|in:MONOCROMO,COLOR',
            'tipo_equipo'           => 'nullable|in:COPIADORA,IMPRESORA,MFP,ESCANER,FAX,PLOTTER',
            'formato_max'           => 'nullable|in:A4,A3,CARTA,OFICIO,A2,A1,A0',
            'supplier_id'           => 'nullable|exists:suppliers,id',
            'invoice'               => 'nullable|string|max:100',
            'cost'                  => 'nullable|numeric|min:0',
            'fecha_compra'          => 'nullable|date',
            'fecha_instalacion'     => 'nullable|date',
            'fecha_garantia_fin'    => 'nullable|date',
            'location_status'       => 'nullable|in:BODEGA,ASIGNADO,VENDIDO,TALLER,DESCONOCIDO',
            'ubicacion_fisica'      => 'nullable|string|max:200',
            'contador_inicial_bn'   => 'nullable|integer|min:0',
            'contador_inicial_color'=> 'nullable|integer|min:0',
            'contador_inicial_scan' => 'nullable|integer|min:0',
            'direccion_ip'          => 'nullable|string|max:45',
            'mac_address'           => 'nullable|string|max:20',
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
        $equipment->delete();

        return redirect()->route('equipment.index')
            ->with('success', 'Equipo eliminado correctamente.');
    }
}
