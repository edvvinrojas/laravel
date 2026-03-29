<?php

namespace App\Http\Controllers;

use App\Models\MonthlyPlan;
use App\Models\ServiceType;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class MonthlyPlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = MonthlyPlan::with(['client', 'branch', 'serviceType', 'creator', 'users'])
            ->when($request->search, fn($q, $s) =>
                $q->whereHas('client', fn($q2) => $q2->where('name', 'like', "%{$s}%"))
            )
            ->when($request->status, fn($q, $s) => $q->where('attendance_status', $s))
            ->when($request->month, fn($q, $m) => $q->whereMonth('visit_date', $m))
            ->orderBy('visit_date', 'desc')
            ->paginate(20)->withQueryString();

        return view('production.index', compact('plans'));
    }

    public function create()
    {
        $clients      = Client::where('is_active', true)->orderBy('name')->get();
        $serviceTypes = ServiceType::where('is_active', true)->orderBy('name')->get();
        $users        = User::where('is_active', true)->orderBy('full_name')->get();

        return view('production.create', compact('clients', 'serviceTypes', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'branch_id'       => 'required|exists:branches,id',
            'area_id'         => 'nullable|exists:areas,id',
            'ticket_id'       => 'nullable|exists:tickets,id',
            'service_type_id' => 'required|exists:service_types,id',
            'visit_date'      => 'required|date',
            'attendance_status' => 'required|in:VISITADO,NO_QUEDO,PENDIENTE',
            'description'     => 'nullable|string',
            'user_ids'        => 'nullable|array',
            'user_ids.*'      => 'exists:users,id',
        ]);

        $data['created_by'] = auth()->id();

        $plan = MonthlyPlan::create($data);

        if (!empty($data['user_ids'])) {
            $plan->users()->sync($data['user_ids']);
        }

        return redirect()->route('production.index')->with('success', 'Plan de producción creado correctamente.');
    }

    public function show(MonthlyPlan $production)
    {
        $production->load(['client', 'branch', 'area', 'serviceType', 'creator', 'users', 'ticket']);
        return view('production.show', compact('production'));
    }

    public function edit(MonthlyPlan $production)
    {
        $clients      = Client::where('is_active', true)->orderBy('name')->get();
        $serviceTypes = ServiceType::where('is_active', true)->orderBy('name')->get();
        $users        = User::where('is_active', true)->orderBy('full_name')->get();

        return view('production.edit', compact('production', 'clients', 'serviceTypes', 'users'));
    }

    public function update(Request $request, MonthlyPlan $production)
    {
        $data = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'branch_id'       => 'required|exists:branches,id',
            'area_id'         => 'nullable|exists:areas,id',
            'ticket_id'       => 'nullable|exists:tickets,id',
            'service_type_id' => 'required|exists:service_types,id',
            'visit_date'      => 'required|date',
            'attendance_status' => 'required|in:VISITADO,NO_QUEDO,PENDIENTE',
            'description'     => 'nullable|string',
            'user_ids'        => 'nullable|array',
            'user_ids.*'      => 'exists:users,id',
        ]);

        $production->update($data);
        $production->users()->sync($data['user_ids'] ?? []);

        return redirect()->route('production.show', $production)->with('success', 'Plan actualizado correctamente.');
    }

    public function destroy(MonthlyPlan $production)
    {
        $production->users()->detach();
        $production->delete();
        return redirect()->route('production.index')->with('success', 'Plan eliminado.');
    }
}
