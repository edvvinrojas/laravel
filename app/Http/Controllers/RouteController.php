<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Client;
use App\Models\Branch;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index(Request $request)
    {
        $routes = Route::when($request->search, fn($q) => $q->where('route_code', 'like', "%{$request->search}%")->orWhere('driver_name', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->where('is_active', true)
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20)->withQueryString();

        return view('routes.index', compact('routes'));
    }

    public function create()
    {
        $clients = Client::where('is_active', true)->with('branches')->orderBy('name')->get();
        return view('routes.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'route_code'     => 'required|string|max:50|unique:routes',
            'driver_name'    => 'required|string|max:200',
            'vehicle'        => 'nullable|string|max:100',
            'status'         => 'required|in:PROGRAMADA,EN_RUTA,PAUSADA,COMPLETADA,CANCELADA',
            'scheduled_date' => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        $route = Route::create($data);

        // Create stops from request
        if ($request->has('stops')) {
            foreach ($request->stops as $i => $stop) {
                RouteStop::create([
                    'route_id'   => $route->id,
                    'client_id'  => $stop['client_id'] ?? null,
                    'branch_id'  => $stop['branch_id'] ?? null,
                    'stop_order' => $i + 1,
                    'address'    => $stop['address'] ?? null,
                    'city'       => $stop['city'] ?? null,
                    'notes'      => $stop['notes'] ?? null,
                ]);
            }
            $route->update(['total_stops' => count($request->stops)]);
        }

        return redirect()->route('routes.show', $route)->with('success', 'Ruta creada.');
    }

    public function show(Route $route)
    {
        $route->load(['stops.client', 'stops.branch']);
        return view('routes.show', compact('route'));
    }

    public function edit(Route $route)
    {
        $route->load(['stops.client', 'stops.branch']);
        $clients = Client::where('is_active', true)->with('branches')->orderBy('name')->get();
        return view('routes.edit', compact('route', 'clients'));
    }

    public function update(Request $request, Route $route)
    {
        $data = $request->validate([
            'driver_name'    => 'required|string|max:200',
            'vehicle'        => 'nullable|string|max:100',
            'status'         => 'required|in:PROGRAMADA,EN_RUTA,PAUSADA,COMPLETADA,CANCELADA',
            'scheduled_date' => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        $route->update($data);
        return redirect()->route('routes.show', $route)->with('success', 'Ruta actualizada.');
    }

    public function destroy(Route $route)
    {
        $route->update(['is_active' => false]);
        return redirect()->route('routes.index')->with('success', 'Ruta eliminada.');
    }
}
