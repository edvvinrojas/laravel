<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use Illuminate\Http\Request;

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
        $data['total_pay'] = $data['salary'] + $data['bonus'] + $data['commission'];

        Payroll::create($data);
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
        $data['total_pay'] = $data['salary'] + $data['bonus'] + $data['commission'];

        $payroll->update($data);
        return redirect()->route('payrolls.show', $payroll)->with('success', 'Nómina actualizada.');
    }

    public function destroy(Payroll $payroll)
    {
        $payroll->delete();
        return redirect()->route('payrolls.index')->with('success', 'Nómina eliminada.');
    }
}
