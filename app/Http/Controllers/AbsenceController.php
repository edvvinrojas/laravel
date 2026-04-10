<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AbsenceController extends Controller
{
    private function userEmployee(User $user): ?Employee
    {
        return Employee::where('user_id', $user->id)->first();
    }

    private function scopeVisibleAbsences($query, User $user, ?Employee $employee): void
    {
        if ($user->hasFullRhAccess()) {
            return;
        }

        if ($user->rol === 'gerencia') {
            $query->where(function ($q) use ($user, $employee) {
                if ($employee) {
                    $q->where('employee_id', $employee->id);
                }
                $q->orWhereHas('employee.user', fn($uq) => $uq->where('department', $user->department));
            });
            return;
        }

        if ($employee) {
            $query->where('employee_id', $employee->id);
            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function selectableEmployees(User $user, ?Employee $employee)
    {
        if ($user->hasFullRhAccess()) {
            return Employee::where('is_active', true)->orderBy('nombre')->get();
        }

        if ($user->rol === 'gerencia') {
            return Employee::where('is_active', true)
                ->where(function ($q) use ($user, $employee) {
                    if ($employee) {
                        $q->where('id', $employee->id);
                    }
                    $q->orWhereHas('user', fn($uq) => $uq->where('department', $user->department));
                })
                ->orderBy('nombre')
                ->get();
        }

        return collect($employee ? [$employee] : []);
    }

    private function canViewAbsence(User $user, Absence $absence, ?Employee $employee): bool
    {
        if ($user->hasFullRhAccess()) {
            return true;
        }

        if ($user->rol === 'gerencia') {
            if ($employee && $absence->employee_id === $employee->id) {
                return true;
            }
            return $absence->employee?->user?->department === $user->department;
        }

        return $employee && $absence->employee_id === $employee->id;
    }

    private function canApproveAbsence(User $user, Absence $absence): bool
    {
        if ($user->hasFullRhAccess() || $user->department === 'rh') {
            return true;
        }

        return $absence->employee?->direct_manager_user_id === $user->id;
    }

    private function managersForEmployee(Employee $employee): Collection
    {
        $managerIds = collect([$employee->direct_manager_user_id])->filter()->values();

        if ($managerIds->isEmpty()) {
            return User::where('rol', 'gerencia')
                ->where('department', $employee->user?->department)
                ->where('is_active', true)
                ->get();
        }

        return User::whereIn('id', $managerIds)
            ->where('is_active', true)
            ->get();
    }

    private function notifyRh(string $title, string $message, string $link): void
    {
        User::where(function ($q) {
            $q->where('department', 'rh')->orWhere('rol', 'administrador');
        })->where('is_active', true)->each(
            fn($u) => Notification::create([
                'user_id'    => $u->id,
                'type'       => 'SISTEMA',
                'title'      => $title,
                'message'    => $message,
                'link'       => $link,
                'is_read'    => false,
                'created_at' => now(),
            ])
        );
    }

    public function index(Request $request)
    {
        $user     = Auth::user();
        $employee = $this->userEmployee($user);

        $absences = Absence::with('employee')
            ->when($request->search, fn($q) => $q->whereHas('employee', fn($e) => $e->where('nombre', 'like', "%{$request->search}%")))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('start_date', 'desc');

        $this->scopeVisibleAbsences($absences, $user, $employee);
        $absences = $absences->paginate(20)->withQueryString();

        return view('absences.index', compact('absences'));
    }

    public function create()
    {
        $user     = Auth::user();
        $employee = $this->userEmployee($user);
        $employees = $this->selectableEmployees($user, $employee);
        return view('absences.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $user     = Auth::user();
        $employee = $this->userEmployee($user);

        $data = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'absence_type'  => 'required|in:ENFERMEDAD,AUSENTISMO,PERMISO_PERSONAL,SALIDA_TEMPRANA,LLEGADA_TARDE,OTRO',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'is_justified'  => 'boolean',
            'justification' => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);

        $allowedEmployeeIds = $this->selectableEmployees($user, $employee)->pluck('id');
        if (!$allowedEmployeeIds->contains((int) $data['employee_id'])) {
            abort(403, 'No puedes registrar ausentismo para este empleado.');
        }

        $data['is_justified'] = $request->boolean('is_justified');
        $data['status'] = 'PENDIENTE';

        $absence  = Absence::create($data);
        $employee = Employee::findOrFail($data['employee_id']);
        $link     = route('absences.show', $absence);

        $this->managersForEmployee($employee)->each(
            fn($u) => Notification::create([
                'user_id'    => $u->id,
                'type'       => 'SISTEMA',
                'title'      => 'Nueva solicitud de ausentismo',
                'message'    => "{$employee->nombre} registró una solicitud para autorización de jefe directo.",
                'link'       => $link,
                'is_read'    => false,
                'created_at' => now(),
            ])
        );

        return redirect()->route('absences.index')->with('success', 'Ausentismo registrado. Pendiente de aprobación.');
    }

    public function show(Absence $absence)
    {
        $user     = Auth::user();
        $employee = $this->userEmployee($user);
        if (!$this->canViewAbsence($user, $absence, $employee)) {
            abort(403);
        }

        $absence->load(['employee', 'reviewedBy']);
        return view('absences.show', compact('absence'));
    }

    public function edit(Absence $absence)
    {
        $user     = Auth::user();
        $employee = $this->userEmployee($user);
        if (!$this->canViewAbsence($user, $absence, $employee)) {
            abort(403);
        }

        if ($absence->status !== 'PENDIENTE') {
            return redirect()->route('absences.show', $absence)
                ->with('error', 'Solo se pueden editar solicitudes de ausentismo en estado PENDIENTE.');
        }

        $employees = Employee::where('is_active', true)->orderBy('nombre')->get();
        return view('absences.edit', compact('absence', 'employees'));
    }

    public function update(Request $request, Absence $absence)
    {
        $user     = Auth::user();
        $employee = $this->userEmployee($user);
        if (!$this->canViewAbsence($user, $absence, $employee)) {
            abort(403);
        }

        if ($absence->status !== 'PENDIENTE') {
            return redirect()->route('absences.show', $absence)
                ->with('error', 'La solicitud ya fue procesada y no se puede editar.');
        }

        $data = $request->validate([
            'absence_type'  => 'required|in:ENFERMEDAD,AUSENTISMO,PERMISO_PERSONAL,SALIDA_TEMPRANA,LLEGADA_TARDE,OTRO',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'is_justified'  => 'boolean',
            'justification' => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);

        $data['is_justified'] = $request->boolean('is_justified');

        $absence->update($data);
        return redirect()->route('absences.show', $absence)->with('success', 'Ausentismo actualizado.');
    }

    public function approve(Absence $absence)
    {
        $user = Auth::user();
        if (!$this->canApproveAbsence($user, $absence)) abort(403);

        $absence->update(['status' => 'APROBADO', 'reviewed_by' => $user->id]);

        $link = route('absences.show', $absence);

        if ($absence->employee?->user_id) {
            Notification::create([
                'user_id'    => $absence->employee->user_id,
                'type'       => 'SISTEMA',
                'title'      => 'Ausentismo aprobado',
                'message'    => 'Tu solicitud de ausentismo fue aprobada.',
                'link'       => $link,
                'is_read'    => false,
                'created_at' => now(),
            ]);
        }

        $this->notifyRh(
            'Ausentismo autorizado por jefe directo',
            "La solicitud de {$absence->employee?->nombre} fue autorizada por su jefe directo.",
            $link
        );

        return back()->with('success', 'Ausentismo aprobado y RH notificado.');
    }

    public function reject(Absence $absence)
    {
        $user = Auth::user();
        if (!$this->canApproveAbsence($user, $absence)) abort(403);

        $absence->update(['status' => 'RECHAZADO', 'reviewed_by' => $user->id]);

        if ($absence->employee?->user_id) {
            Notification::create([
                'user_id'    => $absence->employee->user_id,
                'type'       => 'SISTEMA',
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
        $user     = Auth::user();
        $employee = $this->userEmployee($user);
        if (!$this->canViewAbsence($user, $absence, $employee)) {
            abort(403);
        }

        if ($absence->status !== 'PENDIENTE') {
            return redirect()->route('absences.show', $absence)
                ->with('error', 'La solicitud ya fue procesada y no se puede eliminar.');
        }

        $absence->delete();
        return redirect()->route('absences.index')->with('success', 'Registro eliminado.');
    }
}
