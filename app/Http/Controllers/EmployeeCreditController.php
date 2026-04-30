<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeCredit;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class EmployeeCreditController extends Controller
{
    private function canApproveCredit(User $user, EmployeeCredit $credit): bool
    {
        if ($user->hasFullRhAccess() || $user->department === 'rh') {
            return true;
        }

        return $credit->employee?->direct_manager_user_id === $user->id;
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
        $credits = EmployeeCredit::with(['employee', 'approvedBy'])
            ->when($request->search, fn($q) => $q->whereHas('employee', fn($e) => $e->where('nombre', 'like', "%{$request->search}%")))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('credits.index', compact('credits'));
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->orderBy('nombre')->get();
        return view('credits.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'credit_amount' => 'required|numeric|min:0.01',
            'credit_reason' => 'required|string',
            'biweekly_discount' => 'required|numeric|min:0.01',
            'payment_end_date' => 'nullable|date',
        ]);

        // pending_amount/biweeks se derivan automáticamente: nunca son editables manualmente.
        $data['pending_amount']  = $data['credit_amount'];
        $data['pending_biweeks'] = (int) ceil($data['credit_amount'] / max($data['biweekly_discount'], 0.01));
        $data['status'] = 'SOLICITADO';

        $credit = EmployeeCredit::create($data);

        $employee = Employee::with('user')->findOrFail($data['employee_id']);
        $link = route('credits.show', $credit);

        $this->managersForEmployee($employee)->each(
            fn($u) => Notification::create([
                'user_id'    => $u->id,
                'type'       => 'SISTEMA',
                'title'      => 'Solicitud de crédito pendiente',
                'message'    => "{$employee->nombre} solicitó un crédito por autorización de jefe directo.",
                'link'       => $link,
                'is_read'    => false,
                'created_at' => now(),
            ])
        );

        return redirect()->route('credits.index')->with('success', 'Crédito solicitado. Pendiente de autorización por jefe directo.');
    }

    public function show(EmployeeCredit $credit)
    {
        $credit->load(['employee', 'approvedBy']);
        return view('credits.show', compact('credit'));
    }

    public function edit(EmployeeCredit $credit)
    {
        $employees = Employee::where('is_active', true)->orderBy('nombre')->get();
        return view('credits.edit', compact('credit', 'employees'));
    }

    public function update(Request $request, EmployeeCredit $credit)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'credit_amount' => 'required|numeric|min:0.01',
            'credit_reason' => 'required|string',
            'biweekly_discount' => 'required|numeric|min:0.01',
            'approval_date' => 'nullable|date',
            'payment_end_date' => 'nullable|date|after_or_equal:approval_date',
            'status' => 'required|in:SOLICITADO,AUTORIZADO,LIQUIDADO,CANCELADO',
        ]);

        // pending_amount/biweeks: si cambia el monto del crédito y aún no se ha pagado nada,
        // los recalculamos. Si ya se aplicó alguna nómina, se respeta lo pendiente actual.
        $alreadyPaid = $credit->credit_amount - $credit->pending_amount;
        if (abs($alreadyPaid) < 0.01) {
            $data['pending_amount']  = $data['credit_amount'];
            $data['pending_biweeks'] = (int) ceil($data['credit_amount'] / max($data['biweekly_discount'], 0.01));
        }

        if ($data['status'] === 'AUTORIZADO' && !$credit->approved_by) {
            $data['approved_by'] = Auth::id();
            $data['approval_date'] = $data['approval_date'] ?? now()->toDateString();
        }

        if (in_array($data['status'], ['LIQUIDADO', 'CANCELADO'])) {
            $data['pending_amount'] = 0;
            $data['pending_biweeks'] = 0;
        }

        $credit->update($data);

        return redirect()->route('credits.show', $credit)->with('success', 'Crédito actualizado.');
    }

    public function destroy(EmployeeCredit $credit)
    {
        $credit->delete();
        return redirect()->route('credits.index')->with('success', 'Crédito eliminado.');
    }

    public function approve(EmployeeCredit $credit)
    {
        $user = Auth::user();
        if (!$this->canApproveCredit($user, $credit)) {
            abort(403);
        }

        if ($credit->status !== 'SOLICITADO') {
            return back()->with('error', 'Solo se pueden autorizar créditos en estado SOLICITADO.');
        }

        $credit->update([
            'status' => 'AUTORIZADO',
            'approved_by' => $user->id,
            'approval_date' => now()->toDateString(),
        ]);

        $link = route('credits.show', $credit);

        if ($credit->employee?->user_id) {
            Notification::create([
                'user_id'    => $credit->employee->user_id,
                'type'       => 'SISTEMA',
                'title'      => 'Crédito autorizado',
                'message'    => 'Tu solicitud de crédito fue autorizada por tu jefe directo.',
                'link'       => $link,
                'is_read'    => false,
                'created_at' => now(),
            ]);
        }

        $this->notifyRh(
            'Crédito autorizado por jefe directo',
            "La solicitud de crédito de {$credit->employee?->nombre} fue autorizada por su jefe directo.",
            $link
        );

        return back()->with('success', 'Crédito autorizado y RH notificado.');
    }

    public function reject(EmployeeCredit $credit)
    {
        $user = Auth::user();
        if (!$this->canApproveCredit($user, $credit)) {
            abort(403);
        }

        if ($credit->status !== 'SOLICITADO') {
            return back()->with('error', 'Solo se pueden rechazar créditos en estado SOLICITADO.');
        }

        $credit->update([
            'status' => 'CANCELADO',
            'approved_by' => $user->id,
            'approval_date' => now()->toDateString(),
            'pending_amount' => 0,
            'pending_biweeks' => 0,
        ]);

        if ($credit->employee?->user_id) {
            Notification::create([
                'user_id'    => $credit->employee->user_id,
                'type'       => 'SISTEMA',
                'title'      => 'Crédito no autorizado',
                'message'    => 'Tu solicitud de crédito no fue autorizada por tu jefe directo.',
                'link'       => route('credits.show', $credit),
                'is_read'    => false,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Solicitud marcada como no autorizada.');
    }
}
