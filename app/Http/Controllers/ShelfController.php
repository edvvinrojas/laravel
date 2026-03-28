<?php

namespace App\Http\Controllers;

use App\Models\Shelf;
use Illuminate\Http\Request;

class ShelfController extends Controller
{
    public function index(Request $request)
    {
        $query = Shelf::withCount('inventoryItems')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('shelves.index', compact('query'));
    }

    public function create()
    {
        $sections = ['SECCION_1','SECCION_2','SECCION_3','SECCION_4','SECCION_5','SECCION_6'];
        return view('shelves.create', compact('sections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:shelves,name',
            'section'     => 'required|in:SECCION_1,SECCION_2,SECCION_3,SECCION_4,SECCION_5,SECCION_6',
            'description' => 'nullable|string',
        ]);

        Shelf::create([
            'name'        => $request->name,
            'section'     => $request->section,
            'description' => $request->description,
            'is_active'   => true,
        ]);

        return redirect()->route('shelves.index')->with('success', 'Estante registrado correctamente.');
    }

    public function edit(Shelf $shelf)
    {
        $sections = ['SECCION_1','SECCION_2','SECCION_3','SECCION_4','SECCION_5','SECCION_6'];
        return view('shelves.edit', compact('shelf', 'sections'));
    }

    public function update(Request $request, Shelf $shelf)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:shelves,name,'.$shelf->id,
            'section'     => 'required|in:SECCION_1,SECCION_2,SECCION_3,SECCION_4,SECCION_5,SECCION_6',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $shelf->update([
            'name'        => $request->name,
            'section'     => $request->section,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active'),
        ]);

        return redirect()->route('shelves.index')->with('success', 'Estante actualizado correctamente.');
    }

    public function destroy(Shelf $shelf)
    {
        $shelf->delete();
        return redirect()->route('shelves.index')->with('success', 'Estante eliminado.');
    }
}
