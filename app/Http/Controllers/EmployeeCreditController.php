<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeCreditController extends Controller
{
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
            'approval_date' => 'nullable|date',
            'payment_end_date' => 'nullable|date|after_or_equal:approval_date',
            'status' => 'required|in:SOLICITADO,AUTORIZADO,LIQUIDADO,CANCELADO',
        ]);

        // pending_amount/biweeks se derivan automáticamente: nunca son editables manualmente.
        $data['pending_amount']  = $data['credit_amount'];
        $data['pending_biweeks'] = (int) ceil($data['credit_amount'] / max($data['biweekly_discount'], 0.01));

        if ($data['status'] === 'AUTORIZADO') {
            $data['approved_by'] = Auth::id();
            $data['approval_date'] = $data['approval_date'] ?? now()->toDateString();
        }

        EmployeeCredit::create($data);

        return redirect()->route('credits.index')->with('success', 'Crédito registrado.');
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
}
