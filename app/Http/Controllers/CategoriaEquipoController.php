<?php

namespace App\Http\Controllers;

use App\Models\CategoriaEquipo;
use Illuminate\Http\Request;

class CategoriaEquipoController extends Controller
{
    public function index()
    {
        $categorias = CategoriaEquipo::withCount('modelos', 'equipos')->orderBy('nombre')->get();
        return view('categorias-equipo.index', compact('categorias'));
    }

    public function create()
    {
        return view('categorias-equipo.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100|unique:categorias_equipo,nombre',
            'codigo'      => 'required|string|max:20|unique:categorias_equipo,codigo',
            'descripcion' => 'nullable|string',
        ]);

        CategoriaEquipo::create([
            'nombre'      => $request->nombre,
            'codigo'      => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'es_activo'   => true,
        ]);

        return redirect()->route('categorias-equipo.index')->with('success', 'Categoría creada.');
    }

    public function edit(CategoriaEquipo $categoriasEquipo)
    {
        return view('categorias-equipo.edit', ['categoria' => $categoriasEquipo]);
    }

    public function update(Request $request, CategoriaEquipo $categoriasEquipo)
    {
        $request->validate([
            'nombre'      => "required|string|max:100|unique:categorias_equipo,nombre,{$categoriasEquipo->id}",
            'codigo'      => "required|string|max:20|unique:categorias_equipo,codigo,{$categoriasEquipo->id}",
            'descripcion' => 'nullable|string',
            'es_activo'   => 'boolean',
        ]);

        $categoriasEquipo->update([
            'nombre'      => $request->nombre,
            'codigo'      => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'es_activo'   => $request->boolean('es_activo'),
        ]);

        return redirect()->route('categorias-equipo.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy(CategoriaEquipo $categoriasEquipo)
    {
        $categoriasEquipo->delete();
        return redirect()->route('categorias-equipo.index')->with('success', 'Categoría eliminada.');
    }
}
