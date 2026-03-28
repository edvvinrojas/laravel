<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::when($request->search, fn($q) => $q->where('full_name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->rol, fn($q) => $q->where('rol', $request->rol))
            ->orderBy('full_name')
            ->paginate(20)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
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
            'is_active'  => 'boolean',
        ]);

        $data['password']  = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['permissions'] = [];

        User::create($data);
        return redirect()->route('users.index')->with('success', 'Usuario creado.');
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
