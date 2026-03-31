<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class VacationController extends Controller
{
    private function notifyUser(int $userId, string $title, string $message, string $link): void
    {
        Notification::create([
            'user_id'    => $userId,
            'type'       => 'vacaciones',
            'title'      => $title,
            'message'    => $message,
            'link'       => $link,
            'is_read'    => false,
            'created_at' => now(),
        ]);
    }

    public function index(Request $request)
    {
        $user    = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        $query = Vacation::with('employee')
            ->when($request->search, fn($q) => $q->whereHas('employee', fn($e) => $e->where('nombre', 'like', "%{$request->search}%")))
            ->when($request->status, fn($q) => $q->where('status', $request->status));

        // Usuarios normales solo ven las suyas
        if ($user->rol === 'usuario' && $employee) {
            $query->where('employee_id', $employee->id);
        }

        $vacations = $query->orderBy('start_date', 'desc')->paginate(20)->withQueryString();

        // Para admins/gerencia: pasar lista de empleados con info de antigüedad
        $employeeStats = null;
        if (in_array($user->rol, ['administrador', 'gerencia'])) {
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
        $user     = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        // Admins/gerencia pueden solicitar para cualquier empleado
        $employees = in_array($user->rol, ['administrador', 'gerencia'])
            ? Employee::where('is_active', true)->orderBy('nombre')->get()
            : collect($employee ? [$employee] : []);

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
        $data = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'vacation_days' => 'required|integer|min:1',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'notes'         => 'nullable|string',
        ]);

        $employee = Employee::find($data['employee_id']);
        $data['remaining_days'] = max(0, $employee->vacationDaysRemaining() - $data['vacation_days']);
        $data['requested_by']   = auth()->id();
        $data['status']         = 'PENDIENTE';

        $vacation = Vacation::create($data);

        // Notificar a gerencia/admin
        User::whereIn('rol', ['gerencia', 'administrador'])->where('is_active', true)->each(
            fn($u) => $this->notifyUser($u->id, 'Solicitud de vacaciones',
                "{$employee->nombre} solicitó {$vacation->vacation_days} días de vacaciones.",
                route('vacations.show', $vacation))
        );

        return redirect()->route('vacations.index')->with('success', 'Solicitud enviada. Espera la aprobación del jefe directo.');
    }

    public function show(Vacation $vacation)
    {
        $vacation->load(['employee', 'requestedBy']);
        $employee = $vacation->employee;
        return view('vacations.show', compact('vacation', 'employee'));
    }

    public function edit(Vacation $vacation)
    {
        $employees = Employee::where('is_active', true)->orderBy('nombre')->get();
        return view('vacations.edit', compact('vacation', 'employees'));
    }

    public function update(Request $request, Vacation $vacation)
    {
        $data = $request->validate([
            'vacation_days' => 'required|integer|min:1',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'notes'         => 'nullable|string',
        ]);

        $employee = $vacation->employee;
        $data['remaining_days'] = max(0, $employee->vacationDaysRemaining() - $data['vacation_days']);
        $vacation->update($data);
        return redirect()->route('vacations.show', $vacation)->with('success', 'Solicitud actualizada.');
    }

    public function approve(Vacation $vacation)
    {
        $user = auth()->user();
        if (!in_array($user->rol, ['gerencia', 'administrador'])) abort(403);

        $vacation->update(['status' => 'APROBADO']);

        if ($vacation->employee?->user_id) {
            $this->notifyUser($vacation->employee->user_id, 'Vacaciones aprobadas',
                "Tu solicitud de {$vacation->vacation_days} días fue aprobada.",
                route('vacations.show', $vacation));
        }

        return back()->with('success', 'Vacaciones aprobadas.');
    }

    public function reject(Vacation $vacation)
    {
        $user = auth()->user();
        if (!in_array($user->rol, ['gerencia', 'administrador'])) abort(403);

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
        $vacation->delete();
        return redirect()->route('vacations.index')->with('success', 'Registro eliminado.');
    }
}
