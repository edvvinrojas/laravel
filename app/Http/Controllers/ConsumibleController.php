<?php

namespace App\Http\Controllers;

use App\Models\Consumible;
use App\Models\Stock;
use App\Models\Brand;
use Illuminate\Http\Request;

class ConsumibleController extends Controller
{
    private function nextTonerCode(): string
    {
        $last = Consumible::where('codigo_alternativo', 'like', 'TON-%')
            ->orderByDesc('codigo_alternativo')->value('codigo_alternativo');
        $seq  = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'TON-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    private function rules(int $ignoreId = 0): array
    {
        return [
            'nombre'          => 'required|string|max:200',
            'codigo_oem'      => "required|string|max:100|unique:consumibles,codigo_oem,{$ignoreId}",
            'codigo_alternativo' => 'nullable|string|max:100',
            'brand_id'        => 'nullable|exists:brands,id',
            'tipo'            => 'required|in:TONER,DRUM,KIT_MANTENIMIENTO,FUSOR,RODILLO,TINTA,OTRO',
            'color'           => 'nullable|in:NEGRO,CYAN,MAGENTA,AMARILLO,TRICOLOR,NA',
            'rendimiento_paginas' => 'nullable|integer|min:0',
            'es_original'     => 'boolean',
            'descripcion'     => 'nullable|string',
            'es_activo'       => 'boolean',
            'stock_cantidad'  => 'nullable|integer|min:0',
            'stock_minimo'    => 'nullable|integer|min:0',
            'stock_costo'     => 'nullable|numeric|min:0',
            'stock_ubicacion' => 'nullable|string|max:200',
        ];
    }

    public function create()
    {
        $marcas = Brand::orderBy('name')->get();
        return view('consumibles.create', compact('marcas'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        $codigoAlt = $request->codigo_alternativo
            ? strtoupper($request->codigo_alternativo)
            : ($request->tipo === 'TONER' ? $this->nextTonerCode() : null);

        $consumible = Consumible::create([
            'nombre'               => $request->nombre,
            'codigo_oem'           => strtoupper($request->codigo_oem),
            'codigo_alternativo'   => $codigoAlt,
            'brand_id'             => $request->brand_id,
            'tipo'                 => $request->tipo,
            'color'                => $request->color,
            'rendimiento_paginas'  => $request->rendimiento_paginas,
            'es_original'          => $request->boolean('es_original', true),
            'descripcion'          => $request->descripcion,
            'es_activo'            => $request->boolean('es_activo', true),
        ]);

        Stock::create([
            'tipo'                => 'CONSUMIBLE',
            'referencia_id'       => $consumible->id,
            'cantidad_disponible' => $request->input('stock_cantidad', 0),
            'cantidad_minima'     => $request->input('stock_minimo', 0),
            'costo'               => $request->stock_costo,
            'ubicacion'           => $request->stock_ubicacion,
        ]);

        return redirect()->route('almacen.index', ['tab' => 'consumibles'])
            ->with('success', 'Consumible registrado.');
    }

    public function edit(Consumible $consumible)
    {
        $marcas = Brand::orderBy('name')->get();
        $consumible->load('stock');
        return view('consumibles.edit', compact('consumible', 'marcas'));
    }

    public function update(Request $request, Consumible $consumible)
    {
        $request->validate($this->rules($consumible->id));

        $consumible->update([
            'nombre'               => $request->nombre,
            'codigo_oem'           => strtoupper($request->codigo_oem),
            'codigo_alternativo'   => $request->codigo_alternativo ? strtoupper($request->codigo_alternativo) : null,
            'brand_id'             => $request->brand_id,
            'tipo'                 => $request->tipo,
            'color'                => $request->color,
            'rendimiento_paginas'  => $request->rendimiento_paginas,
            'es_original'          => $request->boolean('es_original'),
            'descripcion'          => $request->descripcion,
            'es_activo'            => $request->boolean('es_activo'),
        ]);

        $consumible->stock()->updateOrCreate(
            ['tipo' => 'CONSUMIBLE', 'referencia_id' => $consumible->id],
            [
                'cantidad_disponible' => $request->input('stock_cantidad', 0),
                'cantidad_minima'     => $request->input('stock_minimo', 0),
                'costo'               => $request->stock_costo,
                'ubicacion'           => $request->stock_ubicacion,
            ]
        );

        return redirect()->route('almacen.index', ['tab' => 'consumibles'])
            ->with('success', 'Consumible actualizado.');
    }

    public function destroy(Consumible $consumible)
    {
        $consumible->stock()->delete();
        $consumible->productos()->detach();
        $consumible->delete();

        return redirect()->route('almacen.index', ['tab' => 'consumibles'])
            ->with('success', 'Consumible eliminado.');
    }
}
