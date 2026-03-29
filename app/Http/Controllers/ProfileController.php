<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        return view('profile.show', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'full_name'        => 'required|string|max:255',
            'username'         => "required|string|max:150|unique:users,username,{$user->id}",
            'email'            => "required|email|unique:users,email,{$user->id}",
            'current_password' => 'nullable|string',
            'password'         => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($data['current_password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.'])->withInput();
            }
            if (empty($data['password'])) {
                return back()->withErrors(['password' => 'Ingresa la nueva contraseña.'])->withInput();
            }
        }

        $user->full_name = $data['full_name'];
        $user->username  = $data['username'];
        $user->email     = $data['email'];

        if (!empty($data['password']) && !empty($data['current_password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', 'Perfil actualizado correctamente.');
    }
}
