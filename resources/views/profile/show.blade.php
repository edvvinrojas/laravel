@extends('layouts.app')
@section('title','Mi Perfil')
@section('page-title','Mi Perfil')
@section('breadcrumb','Configuración de cuenta')

@section('content')
<div class="max-w-2xl space-y-6">

    {{-- Avatar + info --}}
    <div class="card">
        <div class="card-body flex items-center gap-5">
            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
                {{ strtoupper(substr($user->full_name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-800">{{ $user->full_name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="{{ $user->rol === 'administrador' ? 'badge-red' : ($user->rol === 'gerencia' ? 'badge-blue' : 'badge-gray') }}">
                        {{ ucfirst($user->rol) }}
                    </span>
                    <span class="badge-purple">{{ ucfirst($user->department) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulario --}}
    <form method="POST" action="{{ route('profile.update') }}">
        @csrf @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-gray-800">Información personal</h3>
            </div>
            <div class="card-body space-y-4">

                <div>
                    <label class="form-label">Nombre completo <span class="text-red-500">*</span></label>
                    <input name="full_name" value="{{ old('full_name', $user->full_name) }}"
                           class="form-input @error('full_name') border-red-400 @enderror" required>
                    @error('full_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Usuario <span class="text-red-500">*</span></label>
                        <input name="username" value="{{ old('username', $user->username) }}"
                               class="form-input @error('username') border-red-400 @enderror" required>
                        @error('username')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Correo electrónico <span class="text-red-500">*</span></label>
                        <input name="email" type="email" value="{{ old('email', $user->email) }}"
                               class="form-input @error('email') border-red-400 @enderror" required>
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3 class="font-semibold text-gray-800">Cambiar contraseña</h3>
                <span class="text-xs text-gray-400">Deja en blanco para no cambiarla</span>
            </div>
            <div class="card-body space-y-4">

                <div>
                    <label class="form-label">Contraseña actual</label>
                    <input name="current_password" type="password"
                           class="form-input @error('current_password') border-red-400 @enderror"
                           autocomplete="current-password" placeholder="Requerida para cambiar contraseña">
                    @error('current_password')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nueva contraseña</label>
                        <input name="password" type="password"
                               class="form-input @error('password') border-red-400 @enderror"
                               autocomplete="new-password" placeholder="Mínimo 8 caracteres">
                        @error('password')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Confirmar contraseña</label>
                        <input name="password_confirmation" type="password"
                               class="form-input" autocomplete="new-password">
                    </div>
                </div>

            </div>
        </div>

        <div class="flex gap-3 mt-4">
            <button type="submit" class="btn-primary">Guardar cambios</button>
        </div>
    </form>

</div>
@endsection
