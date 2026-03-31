<?php

namespace App\Http\Controllers;

use App\Models\Accesorio;
use App\Models\Stock;
use Illuminate\Http\Request;

class AccesorioController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:200',
            'codigo'          => 'required|string|max:50|unique:accesorios,codigo',
            'descripcion'     => 'nullable|string',
            'precio'          => 'nullable|numeric|min:0',
            'es_activo'       => 'boolean',
            'stock_cantidad'  => 'nullable|integer|min:0',
            'stock_minimo'    => 'nullable|integer|min:0',
            'stock_costo'     => 'nullable|numeric|min:0',
            'stock_ubicacion' => 'nullable|string|max:200',
        ]);

        $accesorio = Accesorio::create([
            'nombre'      => $request->nombre,
            'codigo'      => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'precio'      => $request->precio,
            'es_activo'   => $request->boolean('es_activo', true),
        ]);

        Stock::create([
            'tipo'                => 'ACCESORIO',
            'referencia_id'       => $accesorio->id,
            'cantidad_disponible' => $request->input('stock_cantidad', 0),
            'cantidad_minima'     => $request->input('stock_minimo', 0),
            'costo'               => $request->stock_costo,
            'ubicacion'           => $request->stock_ubicacion,
        ]);

        return redirect()->route('almacen.index', ['tab' => 'accesorios'])
            ->with('success', 'Accesorio registrado.');
    }

    public function create()
    {
        return view('accesorios.create');
    }

    public function edit(Accesorio $accesorio)
    {
        $accesorio->load('stock');
        return view('accesorios.edit', compact('accesorio'));
    }

    public function update(Request $request, Accesorio $accesorio)
    {
        $request->validate([
            'nombre'          => 'required|string|max:200',
            'codigo'          => "required|string|max:50|unique:accesorios,codigo,{$accesorio->id}",
            'descripcion'     => 'nullable|string',
            'precio'          => 'nullable|numeric|min:0',
            'es_activo'       => 'boolean',
            'stock_cantidad'  => 'nullable|integer|min:0',
            'stock_minimo'    => 'nullable|integer|min:0',
            'stock_costo'     => 'nullable|numeric|min:0',
            'stock_ubicacion' => 'nullable|string|max:200',
        ]);

        $accesorio->update([
            'nombre'      => $request->nombre,
            'codigo'      => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'precio'      => $request->precio,
            'es_activo'   => $request->boolean('es_activo'),
        ]);

        $accesorio->stock()->updateOrCreate(
            ['tipo' => 'ACCESORIO', 'referencia_id' => $accesorio->id],
            [
                'cantidad_disponible' => $request->input('stock_cantidad', 0),
                'cantidad_minima'     => $request->input('stock_minimo', 0),
                'costo'               => $request->stock_costo,
                'ubicacion'           => $request->stock_ubicacion,
            ]
        );

        return redirect()->route('almacen.index', ['tab' => 'accesorios'])
            ->with('success', 'Accesorio actualizado.');
    }

    public function destroy(Accesorio $accesorio)
    {
        $accesorio->stock()->delete();
        $accesorio->productos()->detach();
        $accesorio->delete();

        return redirect()->route('almacen.index', ['tab' => 'accesorios'])
            ->with('success', 'Accesorio eliminado.');
    }
}
