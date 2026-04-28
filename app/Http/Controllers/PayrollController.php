<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\EmployeeCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $payrolls = Payroll::with('employee')
            ->when($request->search, fn($q) => $q->whereHas('employee', fn($e) => $e->where('nombre', 'like', "%{$request->search}%")))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('pay_day', 'desc')
            ->paginate(20)->withQueryString();

        return view('payrolls.index', compact('payrolls'));
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->orderBy('nombre')->get();
        return view('payrolls.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'salary'      => 'required|numeric|min:0',
            'pay_day'     => 'required|date',
            'bonus'       => 'nullable|numeric|min:0',
            'commission'  => 'nullable|numeric|min:0',
            'status'      => 'required|in:PENDIENTE,APROBADO,RECHAZADO,ACTIVO,PAGADO',
        ]);

        $data['bonus']     = $data['bonus'] ?? 0;
        $data['commission'] = $data['commission'] ?? 0;
        $employee = Employee::findOrFail($data['employee_id']);

        $data['credit_discount'] = $employee->currentCreditDiscount($data['pay_day']);
        $data['total_pay'] = $data['salary'] + $data['bonus'] + $data['commission'];
        $data['net_pay'] = max(0, $data['total_pay'] - $data['credit_discount']);

        DB::transaction(function () use ($data, $employee) {
            Payroll::create($data);
            $this->applyCreditPayment($employee, (float) $data['credit_discount']);
        });

        return redirect()->route('payrolls.index')->with('success', 'Nómina registrada.');
    }

    public function show(Payroll $payroll)
    {
        $payroll->load('employee');
        return view('payrolls.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        $employees = Employee::where('is_active', true)->orderBy('nombre')->get();
        return view('payrolls.edit', compact('payroll', 'employees'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $data = $request->validate([
            'salary'     => 'required|numeric|min:0',
            'pay_day'    => 'required|date',
            'bonus'      => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'status'     => 'required|in:PENDIENTE,APROBADO,RECHAZADO,ACTIVO,PAGADO',
        ]);

        $data['bonus']     = $data['bonus'] ?? 0;
        $data['commission'] = $data['commission'] ?? 0;
        $employee = $payroll->employee;

        $data['credit_discount'] = $employee ? $employee->currentCreditDiscount($data['pay_day']) : 0;
        $data['total_pay'] = $data['salary'] + $data['bonus'] + $data['commission'];
        $data['net_pay'] = max(0, $data['total_pay'] - $data['credit_discount']);

        $payroll->update($data);
        return redirect()->route('payrolls.show', $payroll)->with('success', 'Nómina actualizada.');
    }

    public function destroy(Payroll $payroll)
    {
        $payroll->delete();
        return redirect()->route('payrolls.index')->with('success', 'Nómina eliminada.');
    }

    /**
     * Aplica el descuento quincenal a los créditos autorizados del empleado.
     * Decrementa pending_amount y pending_biweeks, marca como LIQUIDADO al saldarse.
     */
    private function applyCreditPayment(Employee $employee, float $totalDiscount): void
    {
        if ($totalDiscount <= 0) return;

        $credits = $employee->credits()
            ->where('status', 'AUTORIZADO')
            ->where('pending_amount', '>', 0)
            ->where('pending_biweeks', '>', 0)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($credits as $credit) {
            $payment = (float) min($credit->biweekly_discount, $credit->pending_amount);
            $credit->pending_amount  = max(0, $credit->pending_amount - $payment);
            $credit->pending_biweeks = max(0, $credit->pending_biweeks - 1);
            if ($credit->pending_amount <= 0 || $credit->pending_biweeks <= 0) {
                $credit->status          = 'LIQUIDADO';
                $credit->pending_amount  = 0;
                $credit->pending_biweeks = 0;
            }
            $credit->save();
        }
    }
}
