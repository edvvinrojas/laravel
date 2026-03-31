<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\User;
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

        $absence  = Absence::create($data);
        $employee = Employee::find($data['employee_id']);
        $link     = route('absences.show', $absence);

        User::whereIn('rol', ['gerencia', 'administrador'])->where('is_active', true)->each(
            fn($u) => Notification::create([
                'user_id'    => $u->id,
                'type'       => 'ausentismo',
                'title'      => 'Nueva solicitud de ausentismo',
                'message'    => "{$employee->nombre} registró una ausencia.",
                'link'       => $link,
                'is_read'    => false,
                'created_at' => now(),
            ])
        );

        return redirect()->route('absences.index')->with('success', 'Ausentismo registrado. Pendiente de aprobación.');
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

    public function approve(Absence $absence)
    {
        if (!in_array(auth()->user()->rol, ['gerencia', 'administrador'])) abort(403);
        $absence->update(['status' => 'APROBADO', 'reviewed_by' => auth()->id()]);

        if ($absence->employee?->user_id) {
            Notification::create([
                'user_id'    => $absence->employee->user_id,
                'type'       => 'ausentismo',
                'title'      => 'Ausentismo aprobado',
                'message'    => 'Tu solicitud de ausentismo fue aprobada.',
                'link'       => route('absences.show', $absence),
                'is_read'    => false,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Ausentismo aprobado.');
    }

    public function reject(Absence $absence)
    {
        if (!in_array(auth()->user()->rol, ['gerencia', 'administrador'])) abort(403);
        $absence->update(['status' => 'RECHAZADO', 'reviewed_by' => auth()->id()]);

        if ($absence->employee?->user_id) {
            Notification::create([
                'user_id'    => $absence->employee->user_id,
                'type'       => 'ausentismo',
                'title'      => 'Ausentismo rechazado',
                'message'    => 'Tu solicitud de ausentismo fue rechazada.',
                'link'       => route('absences.show', $absence),
                'is_read'    => false,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Solicitud rechazada.');
    }

    public function destroy(Absence $absence)
    {
        $absence->delete();
        return redirect()->route('absences.index')->with('success', 'Registro eliminado.');
    }
}
