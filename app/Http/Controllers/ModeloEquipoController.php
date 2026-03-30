<?php

namespace App\Http\Controllers;

use App\Models\ModeloEquipo;
use App\Models\CategoriaEquipo;
use App\Models\Brand;
use Illuminate\Http\Request;

class ModeloEquipoController extends Controller
{
    public function index(Request $request)
    {
        $modelos = ModeloEquipo::with('marca', 'categoria')
            ->withCount('equipos', 'consumibles')
            ->when($request->search, function ($q, $s) {
                $q->where('nombre_modelo', 'like', "%{$s}%")
                  ->orWhere('nombre_comercial', 'like', "%{$s}%");
            })
            ->when($request->categoria_id, fn($q, $v) => $q->where('categoria_id', $v))
            ->when($request->marca_id,     fn($q, $v) => $q->where('marca_id', $v))
            ->orderBy('nombre_modelo')
            ->paginate(20)
            ->withQueryString();

        $categorias = CategoriaEquipo::orderBy('nombre')->get();
        $marcas     = Brand::orderBy('name')->get();

        return view('modelos-equipo.index', compact('modelos', 'categorias', 'marcas'));
    }

    public function create()
    {
        $categorias = CategoriaEquipo::where('es_activo', true)->orderBy('nombre')->get();
        $marcas     = Brand::orderBy('name')->get();

        return view('modelos-equipo.create', compact('categorias', 'marcas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'marca_id'            => 'required|exists:brands,id',
            'categoria_id'        => 'required|exists:categorias_equipo,id',
            'nombre_modelo'       => 'required|string|max:100',
            'nombre_comercial'    => 'nullable|string|max:150',
            'tipo_color'          => 'required|in:MONOCROMO,COLOR,MONOCROMO_COLOR',
            'tecnologia'          => 'nullable|in:LASER,INKJET,MATRICIAL,LED,TERMICA',
            'formato_max'         => 'nullable|in:A4,A3,CARTA,OFICIO,A2,A1,A0',
            'velocidad_bn_ppm'    => 'nullable|integer|min:0',
            'velocidad_color_ppm' => 'nullable|integer|min:0',
            'vida_util_paginas'   => 'nullable|integer|min:0',
            'tiene_escaner'       => 'boolean',
            'tiene_fax'           => 'boolean',
            'tiene_duplex'        => 'boolean',
            'tiene_red'           => 'boolean',
            'tiene_wifi'          => 'boolean',
            'descripcion'         => 'nullable|string',
            'es_activo'           => 'boolean',
        ]);

        ModeloEquipo::create([
            'marca_id'            => $request->marca_id,
            'categoria_id'        => $request->categoria_id,
            'nombre_modelo'       => $request->nombre_modelo,
            'nombre_comercial'    => $request->nombre_comercial,
            'tipo_color'          => $request->tipo_color,
            'tecnologia'          => $request->tecnologia,
            'formato_max'         => $request->formato_max,
            'velocidad_bn_ppm'    => $request->velocidad_bn_ppm,
            'velocidad_color_ppm' => $request->velocidad_color_ppm,
            'vida_util_paginas'   => $request->vida_util_paginas,
            'tiene_escaner'       => $request->boolean('tiene_escaner'),
            'tiene_fax'           => $request->boolean('tiene_fax'),
            'tiene_duplex'        => $request->boolean('tiene_duplex'),
            'tiene_red'           => $request->boolean('tiene_red'),
            'tiene_wifi'          => $request->boolean('tiene_wifi'),
            'descripcion'         => $request->descripcion,
            'es_activo'           => $request->boolean('es_activo', true),
        ]);

        return redirect()->route('modelos-equipo.index')->with('success', 'Modelo registrado.');
    }

    public function show(ModeloEquipo $modelosEquipo)
    {
        $modelosEquipo->load('marca', 'categoria', 'equipos.brand', 'consumibles.tipo');
        return view('modelos-equipo.show', ['modelo' => $modelosEquipo]);
    }

    public function edit(ModeloEquipo $modelosEquipo)
    {
        $categorias = CategoriaEquipo::orderBy('nombre')->get();
        $marcas     = Brand::orderBy('name')->get();
        return view('modelos-equipo.edit', [
            'modelo'     => $modelosEquipo,
            'categorias' => $categorias,
            'marcas'     => $marcas,
        ]);
    }

    public function update(Request $request, ModeloEquipo $modelosEquipo)
    {
        $request->validate([
            'marca_id'            => 'required|exists:brands,id',
            'categoria_id'        => 'required|exists:categorias_equipo,id',
            'nombre_modelo'       => 'required|string|max:100',
            'nombre_comercial'    => 'nullable|string|max:150',
            'tipo_color'          => 'required|in:MONOCROMO,COLOR,MONOCROMO_COLOR',
            'tecnologia'          => 'nullable|in:LASER,INKJET,MATRICIAL,LED,TERMICA',
            'formato_max'         => 'nullable|in:A4,A3,CARTA,OFICIO,A2,A1,A0',
            'velocidad_bn_ppm'    => 'nullable|integer|min:0',
            'velocidad_color_ppm' => 'nullable|integer|min:0',
            'vida_util_paginas'   => 'nullable|integer|min:0',
            'tiene_escaner'       => 'boolean',
            'tiene_fax'           => 'boolean',
            'tiene_duplex'        => 'boolean',
            'tiene_red'           => 'boolean',
            'tiene_wifi'          => 'boolean',
            'descripcion'         => 'nullable|string',
            'es_activo'           => 'boolean',
        ]);

        $modelosEquipo->update([
            'marca_id'            => $request->marca_id,
            'categoria_id'        => $request->categoria_id,
            'nombre_modelo'       => $request->nombre_modelo,
            'nombre_comercial'    => $request->nombre_comercial,
            'tipo_color'          => $request->tipo_color,
            'tecnologia'          => $request->tecnologia,
            'formato_max'         => $request->formato_max,
            'velocidad_bn_ppm'    => $request->velocidad_bn_ppm,
            'velocidad_color_ppm' => $request->velocidad_color_ppm,
            'vida_util_paginas'   => $request->vida_util_paginas,
            'tiene_escaner'       => $request->boolean('tiene_escaner'),
            'tiene_fax'           => $request->boolean('tiene_fax'),
            'tiene_duplex'        => $request->boolean('tiene_duplex'),
            'tiene_red'           => $request->boolean('tiene_red'),
            'tiene_wifi'          => $request->boolean('tiene_wifi'),
            'descripcion'         => $request->descripcion,
            'es_activo'           => $request->boolean('es_activo'),
        ]);

        return redirect()->route('modelos-equipo.index')->with('success', 'Modelo actualizado.');
    }

    public function destroy(ModeloEquipo $modelosEquipo)
    {
        $modelosEquipo->delete();
        return redirect()->route('modelos-equipo.index')->with('success', 'Modelo eliminado.');
    }
}
