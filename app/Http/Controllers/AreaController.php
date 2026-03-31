<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Branch;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function store(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $branch->areas()->create(['name' => $request->name]);

        return redirect()->route('clients.show', $branch->client_id)
            ->with('success', "Área agregada a {$branch->name}.");
    }

    public function destroy(Branch $branch, Area $area)
    {
        $clientId = $branch->client_id;
        $area->delete();

        return redirect()->route('clients.show', $clientId)
            ->with('success', 'Área eliminada.');
    }
}
