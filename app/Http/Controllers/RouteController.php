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
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        return view('routes.show', compact('route', 'clients'));
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

    public function storeStop(Request $request, Route $route)
    {
        $request->validate([
            'client_id'    => 'nullable|exists:clients,id',
            'branch_id'    => 'nullable|exists:branches,id',
            'address'      => 'nullable|string|max:500',
            'city'         => 'nullable|string|max:100',
            'notes'        => 'nullable|string',
        ]);

        $order = $route->stops()->max('stop_order') + 1;

        $route->stops()->create([
            'client_id'    => $request->client_id ?: null,
            'branch_id'    => $request->branch_id ?: null,
            'stop_order'   => $order,
            'address'      => $request->address,
            'city'         => $request->city,
            'notes'        => $request->notes,
            'visit_status' => 'pendiente',
        ]);

        $route->update(['total_stops' => $route->stops()->count()]);

        return back()->with('success', 'Parada agregada.');
    }

    public function completeStop(Route $route, RouteStop $stop)
    {
        $stop->update(['is_completed' => true, 'visit_status' => 'completado']);
        $route->update(['completed_stops' => $route->stops()->where('is_completed', true)->count()]);
        return back()->with('success', 'Parada marcada como completada.');
    }

    public function destroyStop(Route $route, RouteStop $stop)
    {
        $stop->delete();
        $route->update([
            'total_stops'     => $route->stops()->count(),
            'completed_stops' => $route->stops()->where('is_completed', true)->count(),
        ]);
        return back()->with('success', 'Parada eliminada.');
    }
}
