<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Employee;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        $absences = Absence::with('employee')
            ->when($request->search, fn($q) => $q->whereHas('employee', fn($e) => $e->where('nombre', 'like', "%{$request->search}%")))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('start_date', 'desc')
            ->paginate(20)->withQueryString();

        return view('absences.index', compact('absences'));
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->orderBy('nombre')->get();
        return view('absences.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'absence_type'  => 'required|in:ENFERMEDAD,AUSENTISMO,PERMISO_PERSONAL,OTRO',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'is_justified'  => 'boolean',
            'justification' => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);

        $data['is_justified'] = $request->boolean('is_justified');
        $data['status'] = 'PENDIENTE';

        Absence::create($data);
        return redirect()->route('absences.index')->with('success', 'Ausentismo registrado.');
    }

    public function show(Absence $absence)
    {
        $absence->load(['employee', 'reviewedBy']);
        return view('absences.show', compact('absence'));
    }

    public function edit(Absence $absence)
    {
        $employees = Employee::where('is_active', true)->orderBy('nombre')->get();
        return view('absences.edit', compact('absence', 'employees'));
    }

    public function update(Request $request, Absence $absence)
    {
        $data = $request->validate([
            'absence_type'  => 'required|in:ENFERMEDAD,AUSENTISMO,PERMISO_PERSONAL,OTRO',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'is_justified'  => 'boolean',
            'justification' => 'nullable|string',
            'status'        => 'required|in:PENDIENTE,APROBADO,RECHAZADO,ACTIVO,PAGADO',
            'notes'         => 'nullable|string',
        ]);

        $data['is_justified'] = $request->boolean('is_justified');
        if (in_array($data['status'], ['APROBADO', 'RECHAZADO'])) {
            $data['reviewed_by'] = auth()->id();
        }

        $absence->update($data);
        return redirect()->route('absences.show', $absence)->with('success', 'Ausentismo actualizado.');
    }

    public function destroy(Absence $absence)
    {
        $absence->delete();
        return redirect()->route('absences.index')->with('success', 'Registro eliminado.');
    }
}
