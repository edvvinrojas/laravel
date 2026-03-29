<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Vacation;
use App\Models\Absence;

class RhController extends Controller
{
    public function index()
    {
        $stats = [
            'employees_active'   => Employee::where('is_active', true)->count(),
            'employees_inactive' => Employee::where('is_active', false)->count(),
            'payrolls_pending'   => Payroll::where('status', 'PENDIENTE')->count(),
            'payroll_month'      => Payroll::where('status', 'PAGADO')->whereMonth('pay_day', now()->month)->sum('total_pay'),
            'vacations_pending'  => Vacation::where('status', 'PENDIENTE')->count(),
            'absences_month'     => Absence::whereMonth('start_date', now()->month)->count(),
        ];

        $recent_employees = Employee::with('user')->where('is_active', true)->latest()->take(5)->get();

        return view('rh.index', compact('stats', 'recent_employees'));
    }
}
