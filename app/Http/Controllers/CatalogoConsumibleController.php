<?php

namespace App\Http\Controllers;

use App\Models\CatalogoConsumible;
use App\Models\TipoConsumible;
use App\Models\ModeloEquipo;
use App\Models\Brand;
use Illuminate\Http\Request;

class CatalogoConsumibleController extends Controller
{
    public function index(Request $request)
    {
        $consumibles = CatalogoConsumible::with('tipo', 'marca')
            ->when($request->search, function ($q, $s) {
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('codigo_oem', 'like', "%{$s}%")
                  ->orWhere('codigo_alternativo', 'like', "%{$s}%");
            })
            ->when($request->tipo_id,  fn($q, $v) => $q->where('tipo_id', $v))
            ->when($request->marca_id, fn($q, $v) => $q->where('marca_id', $v))
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $tipos  = TipoConsumible::orderBy('nombre')->get();
        $marcas = Brand::orderBy('name')->get();

        return view('catalogo-consumibles.index', compact('consumibles', 'tipos', 'marcas'));
    }

    public function create()
    {
        $tipos   = TipoConsumible::orderBy('nombre')->get();
        $marcas  = Brand::orderBy('name')->get();
        $modelos = ModeloEquipo::with('marca', 'categoria')
            ->where('es_activo', true)
            ->orderBy('nombre_modelo')
            ->get();

        return view('catalogo-consumibles.create', compact('tipos', 'marcas', 'modelos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_id'                  => 'required|exists:tipos_consumible,id',
            'marca_id'                 => 'nullable|exists:brands,id',
            'codigo_oem'               => 'required|string|max:100',
            'codigo_alternativo'       => 'nullable|string|max:100',
            'nombre'                   => 'required|string|max:200',
            'color'                    => 'nullable|in:NEGRO,CYAN,MAGENTA,AMARILLO,TRICOLOR',
            'rendimiento_paginas'      => 'nullable|integer|min:0',
            'rendimiento_paginas_alt'  => 'nullable|integer|min:0',
            'es_original'              => 'boolean',
            'descripcion'              => 'nullable|string',
            'es_activo'                => 'boolean',
            'modelos_compatibles'      => 'nullable|array',
            'modelos_compatibles.*'    => 'exists:modelos_equipo,id',
            'compatibilidad_oficial.*' => 'nullable|boolean',
            'compatibilidad_notas.*'   => 'nullable|string|max:255',
        ]);

        $consumible = CatalogoConsumible::create([
            'tipo_id'                 => $request->tipo_id,
            'marca_id'                => $request->marca_id,
            'codigo_oem'              => strtoupper($request->codigo_oem),
            'codigo_alternativo'      => $request->codigo_alternativo ? strtoupper($request->codigo_alternativo) : null,
            'nombre'                  => $request->nombre,
            'color'                   => $request->color,
            'rendimiento_paginas'     => $request->rendimiento_paginas,
            'rendimiento_paginas_alt' => $request->rendimiento_paginas_alt,
            'es_original'             => $request->boolean('es_original'),
            'descripcion'             => $request->descripcion,
            'es_activo'               => $request->boolean('es_activo', true),
        ]);

        // Sync modelos compatibles con pivot data
        if ($request->has('modelos_compatibles')) {
            $pivot = [];
            foreach ($request->modelos_compatibles as $modeloId) {
                $pivot[$modeloId] = [
                    'es_oficial' => isset($request->compatibilidad_oficial[$modeloId]),
                    'notas'      => $request->compatibilidad_notas[$modeloId] ?? null,
                ];
            }
            $consumible->modelos()->sync($pivot);
        }

        return redirect()->route('catalogo-consumibles.index')->with('success', 'Consumible registrado.');
    }

    public function show(CatalogoConsumible $catalogoConsumible)
    {
        $catalogoConsumible->load('tipo', 'marca', 'modelos.marca', 'modelos.categoria');
        return view('catalogo-consumibles.show', ['consumible' => $catalogoConsumible]);
    }

    public function edit(CatalogoConsumible $catalogoConsumible)
    {
        $tipos   = TipoConsumible::orderBy('nombre')->get();
        $marcas  = Brand::orderBy('name')->get();
        $modelos = ModeloEquipo::with('marca', 'categoria')
            ->orderBy('nombre_modelo')
            ->get();

        $modelosCompatibles = $catalogoConsumible->modelos->keyBy('id');

        return view('catalogo-consumibles.edit', compact('tipos', 'marcas', 'modelos', 'modelosCompatibles') + ['consumible' => $catalogoConsumible]);
    }

    public function update(Request $request, CatalogoConsumible $catalogoConsumible)
    {
        $request->validate([
            'tipo_id'                  => 'required|exists:tipos_consumible,id',
            'marca_id'                 => 'nullable|exists:brands,id',
            'codigo_oem'               => 'required|string|max:100',
            'codigo_alternativo'       => 'nullable|string|max:100',
            'nombre'                   => 'required|string|max:200',
            'color'                    => 'nullable|in:NEGRO,CYAN,MAGENTA,AMARILLO,TRICOLOR',
            'rendimiento_paginas'      => 'nullable|integer|min:0',
            'rendimiento_paginas_alt'  => 'nullable|integer|min:0',
            'es_original'              => 'boolean',
            'descripcion'              => 'nullable|string',
            'es_activo'                => 'boolean',
            'modelos_compatibles'      => 'nullable|array',
            'modelos_compatibles.*'    => 'exists:modelos_equipo,id',
            'compatibilidad_oficial.*' => 'nullable|boolean',
            'compatibilidad_notas.*'   => 'nullable|string|max:255',
        ]);

        $catalogoConsumible->update([
            'tipo_id'                 => $request->tipo_id,
            'marca_id'                => $request->marca_id,
            'codigo_oem'              => strtoupper($request->codigo_oem),
            'codigo_alternativo'      => $request->codigo_alternativo ? strtoupper($request->codigo_alternativo) : null,
            'nombre'                  => $request->nombre,
            'color'                   => $request->color,
            'rendimiento_paginas'     => $request->rendimiento_paginas,
            'rendimiento_paginas_alt' => $request->rendimiento_paginas_alt,
            'es_original'             => $request->boolean('es_original'),
            'descripcion'             => $request->descripcion,
            'es_activo'               => $request->boolean('es_activo'),
        ]);

        $pivot = [];
        foreach ($request->input('modelos_compatibles', []) as $modeloId) {
            $pivot[$modeloId] = [
                'es_oficial' => isset($request->compatibilidad_oficial[$modeloId]),
                'notas'      => $request->compatibilidad_notas[$modeloId] ?? null,
            ];
        }
        $catalogoConsumible->modelos()->sync($pivot);

        return redirect()->route('catalogo-consumibles.index')->with('success', 'Consumible actualizado.');
    }

    public function destroy(CatalogoConsumible $catalogoConsumible)
    {
        $catalogoConsumible->modelos()->detach();
        $catalogoConsumible->delete();
        return redirect()->route('catalogo-consumibles.index')->with('success', 'Consumible eliminado.');
    }
}
