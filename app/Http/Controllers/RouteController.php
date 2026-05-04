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

    private function nextRouteCode(): string
    {
        $last = Route::orderByDesc('id')->value('route_code');
        if ($last && preg_match('/RUT-(\d+)$/', $last, $m)) {
            return 'RUT-' . str_pad((int)$m[1] + 1, 4, '0', STR_PAD_LEFT);
        }
        return 'RUT-0001';
    }

    public function create()
    {
        $clients   = Client::where('is_active', true)->with('branches')->orderBy('name')->get();
        $nextCode  = $this->nextRouteCode();
        $routeCodes = Route::whereNotNull('route_code')->distinct()->orderBy('route_code')->pluck('route_code')->toArray();
        return view('routes.create', compact('clients', 'nextCode', 'routeCodes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'route_code'     => 'nullable|string|max:50|unique:routes,route_code',
            'driver_name'    => 'required|string|max:200',
            'vehicle'        => 'nullable|string|max:100',
            'status'         => 'required|in:PROGRAMADA,EN_RUTA,PAUSADA,COMPLETADA,CANCELADA',
            'scheduled_date' => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        $data['route_code'] = trim((string) ($data['route_code'] ?? ''));
        if ($data['route_code'] === '') {
            $data['route_code'] = $this->nextRouteCode();
        }

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
        $completedCount = $route->stops()->where('is_completed', true)->count();
        $totalCount     = $route->stops()->count();
        $routeData      = ['completed_stops' => $completedCount];
        if ($completedCount >= $totalCount && $totalCount > 0) {
            $routeData['status'] = 'COMPLETADA';
        }
        $route->update($routeData);
        return back()->with('success', 'Parada marcada como completada.');
    }

    public function postponeStop(Request $request, Route $route, RouteStop $stop)
    {
        $request->validate(['no_visit_reason' => 'required|string|max:500']);
        $stop->update([
            'visit_status'   => 'pospuesto',
            'no_visit_reason' => $request->no_visit_reason,
        ]);
        return back()->with('success', 'Parada pospuesta.');
    }

    public function completeRoute(Route $route)
    {
        $route->update(['status' => 'COMPLETADA']);
        return back()->with('success', 'Ruta marcada como completada.');
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
