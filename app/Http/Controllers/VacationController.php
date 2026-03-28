<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use App\Models\Employee;
use Illuminate\Http\Request;

class VacationController extends Controller
{
    public function index(Request $request)
    {
        $vacations = Vacation::with('employee')
            ->when($request->search, fn($q) => $q->whereHas('employee', fn($e) => $e->where('nombre', 'like', "%{$request->search}%")))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('start_date', 'desc')
            ->paginate(20)->withQueryString();

        return view('vacations.index', compact('vacations'));
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->orderBy('nombre')->get();
        return view('vacations.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'vacation_days'  => 'required|integer|min:1',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after:start_date',
            'remaining_days' => 'required|integer|min:0',
            'notes'          => 'nullable|string',
        ]);

        $data['requested_by'] = auth()->id();
        $data['status'] = 'PENDIENTE';

        Vacation::create($data);
        return redirect()->route('vacations.index')->with('success', 'Solicitud de vacaciones registrada.');
    }

    public function show(Vacation $vacation)
    {
        $vacation->load(['employee', 'requestedBy']);
        return view('vacations.show', compact('vacation'));
    }

    public function edit(Vacation $vacation)
    {
        $employees = Employee::where('is_active', true)->orderBy('nombre')->get();
        return view('vacations.edit', compact('vacation', 'employees'));
    }

    public function update(Request $request, Vacation $vacation)
    {
        $data = $request->validate([
            'vacation_days'  => 'required|integer|min:1',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after:start_date',
            'remaining_days' => 'required|integer|min:0',
            'status'         => 'required|in:PENDIENTE,APROBADO,RECHAZADO,ACTIVO,PAGADO',
            'notes'          => 'nullable|string',
        ]);

        $vacation->update($data);
        return redirect()->route('vacations.show', $vacation)->with('success', 'Vacaciones actualizadas.');
    }

    public function destroy(Vacation $vacation)
    {
        $vacation->delete();
        return redirect()->route('vacations.index')->with('success', 'Registro eliminado.');
    }
}
