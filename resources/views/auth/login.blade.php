@extends('layouts.guest')

@section('title', 'Iniciar sesión — CopyMart ERP')

@section('content')
<div class="w-full max-w-sm px-4">
    <div class="card">
        <div class="card-body">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <div class="mx-auto w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mb-3">
                    <span class="text-white font-bold text-lg">CM</span>
                </div>
                <h1 class="text-xl font-bold text-gray-900">CopyMart ERP</h1>
                <p class="text-sm text-gray-500 mt-1">Ingresa tus credenciales para continuar</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input
                        id="email" name="email" type="email"
                        value="{{ old('email') }}"
                        autocomplete="email" autofocus
                        class="form-input @error('email') border-red-400 @enderror"
                        placeholder="usuario@empresa.com"
                    >
                    @error('email')
                    <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="form-label">Contraseña</label>
                    <input
                        id="password" name="password" type="password"
                        autocomplete="current-password"
                        class="form-input @error('password') border-red-400 @enderror"
                        placeholder="••••••••"
                    >
                    @error('password')
                    <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input id="remember" name="remember" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600">
                    <label for="remember" class="text-sm text-gray-600">Recordarme</label>
                </div>

                <button type="submit" class="btn-primary w-full justify-center">
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>
    <p class="text-center text-xs text-gray-400 mt-4">CopyMart ERP &copy; {{ date('Y') }}</p>
</div>
@endsection
