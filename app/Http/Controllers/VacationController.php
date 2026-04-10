<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class VacationController extends Controller
{
    private function notifyUser(int $userId, string $title, string $message, string $link): void
    {
        Notification::create([
            'user_id'    => $userId,
            'type'       => 'VACACION_PENDIENTE',
            'title'      => $title,
            'message'    => $message,
            'link'       => $link,
            'is_read'    => false,
            'created_at' => now(),
        ]);
    }

    private function userEmployee(User $user): ?Employee
    {
        return Employee::where('user_id', $user->id)->first();
    }

    private function scopeVisibleVacations($query, User $user, ?Employee $employee): void
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

    private function canViewVacation(User $user, Vacation $vacation, ?Employee $employee): bool
    {
        if ($user->hasFullRhAccess()) {
            return true;
        }

        if ($user->rol === 'gerencia') {
            if ($employee && $vacation->employee_id === $employee->id) {
                return true;
            }

            return $vacation->employee?->user?->department === $user->department;
        }

        return $employee && $vacation->employee_id === $employee->id;
    }

    private function canApproveVacation(User $user, Vacation $vacation): bool
    {
        if ($user->hasFullRhAccess() || $user->department === 'rh') {
            return true;
        }

        return $vacation->employee?->direct_manager_user_id === $user->id;
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
            fn($u) => $this->notifyUser($u->id, $title, $message, $link)
        );
    }

    public function index(Request $request)
    {
        $user     = Auth::user();
        $employee = $this->userEmployee($user);

        $query = Vacation::with('employee')
            ->when($request->search, fn($q) => $q->whereHas('employee', fn($e) => $e->where('nombre', 'like', "%{$request->search}%")))
            ->when($request->status, fn($q) => $q->where('status', $request->status));

        $this->scopeVisibleVacations($query, $user, $employee);

        $vacations = $query->orderBy('start_date', 'desc')->paginate(20)->withQueryString();

        // Solo usuarios con acceso completo ven el resumen global de todos los empleados.
        $employeeStats = null;
        if ($user->hasFullRhAccess()) {
            $employeeStats = Employee::where('is_active', true)->with('user')->orderBy('nombre')->get()
                ->map(fn($e) => [
                    'id'          => $e->id,
                    'nombre'      => $e->nombre,
                    'years'       => $e->yearsOfService(),
                    'entitlement' => $e->vacationDaysEntitlement(),
                    'used'        => $e->vacationDaysUsed(),
                    'remaining'   => $e->vacationDaysRemaining(),
                ]);
        }

        return view('vacations.index', compact('vacations', 'employeeStats'));
    }

    public function create()
    {
        $user     = Auth::user();
        $employee = $this->userEmployee($user);

        $employees = $this->selectableEmployees($user, $employee);

        $employeeData = $employees->keyBy('id')->map(fn($e) => [
            'entitlement' => $e->vacationDaysEntitlement(),
            'used'        => $e->vacationDaysUsed(),
            'remaining'   => $e->vacationDaysRemaining(),
            'years'       => $e->yearsOfService(),
        ]);

        return view('vacations.create', compact('employees', 'employeeData'));
    }

    public function store(Request $request)
    {
        $user     = Auth::user();
        $employee = $this->userEmployee($user);

        $data = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'vacation_days' => 'required|integer|min:1',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'notes'         => 'nullable|string',
        ]);

        $allowedEmployeeIds = $this->selectableEmployees($user, $employee)->pluck('id');
        if (!$allowedEmployeeIds->contains((int) $data['employee_id'])) {
            abort(403, 'No puedes solicitar vacaciones para este empleado.');
        }

        $employee = Employee::findOrFail($data['employee_id']);
        $data['remaining_days'] = max(0, $employee->vacationDaysRemaining() - $data['vacation_days']);
        $data['requested_by']   = $user->id;
        $data['status']         = 'PENDIENTE';

        $vacation = Vacation::create($data);

        $managers = $this->managersForEmployee($employee);
        $managers->each(fn($manager) => $this->notifyUser(
            $manager->id,
            'Solicitud de vacaciones',
            "{$employee->nombre} solicitó {$vacation->vacation_days} días de vacaciones para autorización.",
            route('vacations.show', $vacation)
        ));

        return redirect()->route('vacations.index')->with('success', 'Solicitud enviada. Se notificó al jefe directo para autorización.');
    }

    public function show(Vacation $vacation)
    {
        $user     = Auth::user();
        $employee = $this->userEmployee($user);
        if (!$this->canViewVacation($user, $vacation, $employee)) {
            abort(403);
        }

        $vacation->load(['employee', 'requestedBy']);
        $targetEmployee = $vacation->employee;
        return view('vacations.show', ['vacation' => $vacation, 'employee' => $targetEmployee]);
    }

    public function edit(Vacation $vacation)
    {
        $user     = Auth::user();
        $employee = $this->userEmployee($user);
        if (!$this->canViewVacation($user, $vacation, $employee)) {
            abort(403);
        }

        if ($vacation->status !== 'PENDIENTE') {
            return redirect()->route('vacations.show', $vacation)
                ->with('error', 'Solo se pueden editar solicitudes de vacaciones en estado PENDIENTE.');
        }

        $employees = Employee::where('is_active', true)->orderBy('nombre')->get();
        return view('vacations.edit', compact('vacation', 'employees'));
    }

    public function update(Request $request, Vacation $vacation)
    {
        $user     = Auth::user();
        $employee = $this->userEmployee($user);
        if (!$this->canViewVacation($user, $vacation, $employee)) {
            abort(403);
        }

        if ($vacation->status !== 'PENDIENTE') {
            return redirect()->route('vacations.show', $vacation)
                ->with('error', 'La solicitud ya fue procesada y no se puede editar.');
        }

        $data = $request->validate([
            'vacation_days' => 'required|integer|min:1',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'notes'         => 'nullable|string',
        ]);

        $employee = $vacation->employee;
        $data['remaining_days'] = max(0, $employee->vacationDaysRemaining() - $data['vacation_days']);
        $vacation->update($data);
        return redirect()->route('vacations.show', $vacation)->with('success', 'Solicitud actualizada.');
    }

    public function approve(Vacation $vacation)
    {
        $user = Auth::user();
        if (!$this->canApproveVacation($user, $vacation)) abort(403);

        $vacation->update(['status' => 'APROBADO']);

        $link = route('vacations.show', $vacation);

        if ($vacation->employee?->user_id) {
            $this->notifyUser($vacation->employee->user_id, 'Vacaciones aprobadas',
                "Tu solicitud de {$vacation->vacation_days} días fue aprobada.",
                $link);
        }

        $this->notifyRh(
            'Vacaciones autorizadas por jefe directo',
            "La solicitud de {$vacation->employee?->nombre} fue autorizada por su jefe directo.",
            $link
        );

        return back()->with('success', 'Vacaciones aprobadas y RH notificado.');
    }

    public function reject(Vacation $vacation)
    {
        $user = Auth::user();
        if (!$this->canApproveVacation($user, $vacation)) abort(403);

        $vacation->update(['status' => 'RECHAZADO']);

        if ($vacation->employee?->user_id) {
            $this->notifyUser($vacation->employee->user_id, 'Vacaciones rechazadas',
                "Tu solicitud de {$vacation->vacation_days} días fue rechazada.",
                route('vacations.show', $vacation));
        }

        return back()->with('success', 'Solicitud rechazada.');
    }

    public function destroy(Vacation $vacation)
    {
        $user     = Auth::user();
        $employee = $this->userEmployee($user);
        if (!$this->canViewVacation($user, $vacation, $employee)) {
            abort(403);
        }

        if ($vacation->status !== 'PENDIENTE') {
            return redirect()->route('vacations.show', $vacation)
                ->with('error', 'La solicitud ya fue procesada y no se puede eliminar.');
        }

        $vacation->delete();
        return redirect()->route('vacations.index')->with('success', 'Registro eliminado.');
    }
}
