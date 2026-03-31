@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')
@section('title','Mi Perfil')
@section('page-title','Mi Perfil')
@section('breadcrumb','Configuración de cuenta')

@section('content')
<div class="max-w-2xl space-y-6">

    {{-- Avatar + info --}}
    <div class="card">
        <div class="card-body flex items-center gap-6">

            {{-- Foto + controles --}}
            <div class="flex flex-col items-center gap-2 flex-shrink-0">
                <div class="relative group w-24 h-24">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}"
                             alt="Foto de perfil"
                             class="w-24 h-24 rounded-full object-cover border-2 border-gray-200">
                    @else
                        <div class="w-24 h-24 bg-blue-600 rounded-full flex items-center justify-center text-white text-3xl font-bold">
                            {{ strtoupper(substr($user->full_name, 0, 1)) }}
                        </div>
                    @endif
                    {{-- overlay al hover --}}
                    <label for="avatarInput"
                           class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover:opacity-100 transition cursor-pointer flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>
                </div>

                {{-- Upload form oculto --}}
                <form id="avatarForm" method="POST" action="{{ route('profile.avatar') }}"
                      enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input id="avatarInput" name="avatar" type="file"
                           accept="image/jpeg,image/png,image/webp"
                           onchange="document.getElementById('avatarForm').submit()">
                </form>

                @if($user->avatar)
                <form method="POST" action="{{ route('profile.avatar.delete') }}">
                    @csrf @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('¿Eliminar foto de perfil?')"
                            class="text-xs text-red-500 hover:underline">
                        Eliminar foto
                    </button>
                </form>
                @else
                <label for="avatarInput" class="text-xs text-blue-600 hover:underline cursor-pointer">
                    Subir foto
                </label>
                @endif
            </div>

            {{-- Info --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-800">{{ $user->full_name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="{{ $user->rol === 'administrador' ? 'badge-red' : ($user->rol === 'gerencia' ? 'badge-blue' : 'badge-gray') }}">
                        {{ ucfirst($user->rol) }}
                    </span>
                    <span class="badge-purple">{{ ucfirst($user->department) }}</span>
                </div>
                <p class="text-xs text-gray-400 mt-2">Pasa el cursor sobre la foto para cambiarla</p>
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
