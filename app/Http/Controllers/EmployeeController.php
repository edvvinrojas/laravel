<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::with('user')
            ->when($request->search, fn($q) => $q->where('nombre', 'like', "%{$request->search}%")->orWhere('nss', 'like', "%{$request->search}%")->orWhere('rfc', 'like', "%{$request->search}%"))
            ->orderBy('nombre')
            ->paginate(20)->withQueryString();

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $users = User::where('is_active', true)->whereDoesntHave('employee')->orderBy('full_name')->get();
        $managers = User::where('is_active', true)
            ->whereIn('rol', ['gerencia', 'administrador'])
            ->orderBy('full_name')
            ->get();

        return view('employees.create', compact('users', 'managers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'           => 'nullable|exists:users,id|unique:employees',
            'direct_manager_user_id' => 'nullable|exists:users,id',
            'nombre'            => 'required|string|max:255',
            'departamento'      => 'nullable|string|max:100',
            'puesto'            => 'nullable|string|max:150',
            'sueldo'            => 'nullable|numeric|min:0',
            'nss'               => 'required|string|max:11|unique:employees',
            'rfc'               => 'required|string|max:13|unique:employees',
            'curp'              => 'required|string|max:18|unique:employees',
            'birthday'          => 'required|date',
            'hire_date'         => 'required|date',
            'termination_date'  => 'nullable|date|after_or_equal:hire_date',
            'phone_emergency'   => 'required|string|max:15',
            'contact_emergency' => 'required|string|max:255',
            'is_active'         => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        if ($data['is_active']) {
            $data['termination_date'] = null;
        }

        Employee::create($data);
        return redirect()->route('employees.index')->with('success', 'Empleado registrado.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'directManager', 'payrolls', 'vacations', 'absences', 'administrativeRecords', 'credits']);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $users = User::where('is_active', true)
            ->where(fn($q) => $q->whereDoesntHave('employee')->orWhere('id', $employee->user_id))
            ->orderBy('full_name')->get();

        $managers = User::where('is_active', true)
            ->whereIn('rol', ['gerencia', 'administrador'])
            ->orderBy('full_name')
            ->get();

        return view('employees.edit', compact('employee', 'users', 'managers'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'user_id'           => "nullable|exists:users,id|unique:employees,user_id,{$employee->id}",
            'direct_manager_user_id' => 'nullable|exists:users,id',
            'nombre'            => 'required|string|max:255',
            'departamento'      => 'nullable|string|max:100',
            'puesto'            => 'nullable|string|max:150',
            'sueldo'            => 'nullable|numeric|min:0',
            'nss'               => "required|string|max:11|unique:employees,nss,{$employee->id}",
            'rfc'               => "required|string|max:13|unique:employees,rfc,{$employee->id}",
            'curp'              => "required|string|max:18|unique:employees,curp,{$employee->id}",
            'birthday'          => 'required|date',
            'hire_date'         => 'required|date',
            'termination_date'  => 'nullable|date|after_or_equal:hire_date',
            'phone_emergency'   => 'required|string|max:15',
            'contact_emergency' => 'required|string|max:255',
            'is_active'         => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        if ($data['is_active']) {
            $data['termination_date'] = null;
        } elseif (empty($data['termination_date'])) {
            $data['termination_date'] = now()->toDateString();
        }

        $employee->update($data);
        return redirect()->route('employees.show', $employee)->with('success', 'Empleado actualizado.');
    }

    public function destroy(Employee $employee)
    {
        $employee->update(['is_active' => false]);
        return redirect()->route('employees.index')->with('success', 'Empleado desactivado.');
    }
}
