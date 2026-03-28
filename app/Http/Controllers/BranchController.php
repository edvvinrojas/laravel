<?php
// app/Http/Controllers/BranchController.php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Client;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Client $client)
    {
        $branches = $client->branches()->with('areas')->get();

        return response()->json($branches);
    }

    public function store(Client $client, Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'address'   => 'nullable|string|max:255',
            'colonia'   => 'nullable|string|max:255',
            'zip_code'  => 'nullable|string|max:10',
            'city'      => 'nullable|string|max:100',
            'is_main'   => 'nullable|boolean',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $validated['client_id'] = $client->id;
        $validated['is_main']   = $request->boolean('is_main', false);

        $branch = Branch::create($validated);

        if ($request->expectsJson()) {
            return response()->json($branch->load('areas'), 201);
        }

        return redirect()->route('clients.show', $client)
            ->with('success', 'Sucursal agregada correctamente.');
    }

    public function destroy(Branch $branch)
    {
        $clientId = $branch->client_id;
        $branch->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Sucursal eliminada correctamente.']);
        }

        return redirect()->route('clients.show', $clientId)
            ->with('success', 'Sucursal eliminada correctamente.');
    }
}
