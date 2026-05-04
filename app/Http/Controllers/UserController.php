<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('is_hidden', false)
            ->when($request->search, fn($q) => $q->where('full_name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->rol, fn($q) => $q->where('rol', $request->rol))
            ->orderBy('full_name')
            ->paginate(20)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $employeesWithoutAccount = Employee::query()
            ->where('is_active', '=', true, 'and')
            ->where('user_id', '=', null, 'and')
            ->orderBy('nombre', 'asc')
            ->get(['id', 'nombre', 'departamento', 'puesto']);

        return view('users.create', compact('employeesWithoutAccount'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username'   => 'required|string|max:150|unique:users',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|string|min:8|confirmed',
            'full_name'  => 'required|string|max:255',
            'rol'        => 'required|in:administrador,gerencia,usuario',
            'department' => 'required|in:rh,administracion,comercial,operaciones,ti',
            'employee_id' => 'nullable|exists:employees,id',
            'is_active'  => 'boolean',
        ]);

        if (!empty($data['employee_id'])) {
            $employee = Employee::query()->findOrFail($data['employee_id']);
            if (!empty($employee->user_id)) {
                return back()->withInput()->withErrors([
                    'employee_id' => 'El empleado seleccionado ya tiene una cuenta de usuario asignada.',
                ]);
            }
        }

        $data['password']  = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['permissions'] = [];
        unset($data['employee_id']);

        $user = User::create($data);

        if ($request->filled('employee_id')) {
            Employee::where('id', '=', $request->integer('employee_id'), 'and')
                ->where('user_id', '=', null, 'and')
                ->update(['user_id' => $user->id]);
        }

        return redirect()->route('users.edit', $user)->with('success', 'Usuario creado. Ahora configura sus permisos.');
    }

    public function show(User $user)
    {
        $user->load('employee');
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'username'   => "required|string|max:150|unique:users,username,{$user->id}",
            'email'      => "required|email|unique:users,email,{$user->id}",
            'full_name'  => 'required|string|max:255',
            'rol'        => 'required|in:administrador,gerencia,usuario',
            'department' => 'required|in:rh,administracion,comercial,operaciones,ti',
            'is_active'  => 'boolean',
            'password'   => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');

        // Construir permisos granulares desde los checkboxes
        $areas = [
            'ventas','rentas','clientes','produccion','compras','almacen',
            'cobranza','facturacion','inventario','rutas','ordenes_servicio',
            'taller','recursos_humanos','ti','usuarios','reportes','auditoria',
            'configuracion','migraciones',
        ];
        $permissions = [];
        $submitted = $request->input('permissions', []);
        foreach ($areas as $area) {
            if (!empty($submitted[$area])) {
                $permissions[$area] = [
                    'view'   => !empty($submitted[$area]['view']),
                    'create' => !empty($submitted[$area]['create']),
                    'edit'   => !empty($submitted[$area]['edit']),
                    'delete' => !empty($submitted[$area]['delete']),
                ];
            }
        }
        $data['permissions'] = $permissions;

        $user->update($data);

        return redirect()->route('users.show', $user)->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }
        $user->update(['is_active' => false]);
        return redirect()->route('users.index')->with('success', 'Usuario desactivado.');
    }
}
