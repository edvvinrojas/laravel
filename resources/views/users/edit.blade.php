@extends('layouts.app')
@section('title','Editar Usuario')
@section('page-title','Editar Usuario')

@section('content')
<div class="max-w-xl">
<form method="POST" action="{{ route('users.update',$user) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2"><label class="form-label">Nombre completo *</label><input name="full_name" value="{{ old('full_name',$user->full_name) }}" class="form-input" required></div>
        <div><label class="form-label">Usuario *</label><input name="username" value="{{ old('username',$user->username) }}" class="form-input" required></div>
        <div><label class="form-label">Email *</label><input name="email" type="email" value="{{ old('email',$user->email) }}" class="form-input" required></div>
        <div><label class="form-label">Nueva contraseña</label><input name="password" type="password" class="form-input" placeholder="Dejar vacío para no cambiar"></div>
        <div><label class="form-label">Confirmar contraseña</label><input name="password_confirmation" type="password" class="form-input"></div>
        <div><label class="form-label">Rol *</label><select name="rol" class="form-select" required><option value="usuario" @selected(old('rol',$user->rol)==='usuario')>Usuario</option><option value="gerencia" @selected(old('rol',$user->rol)==='gerencia')>Gerencia</option><option value="administrador" @selected(old('rol',$user->rol)==='administrador')>Administrador</option></select></div>
        <div><label class="form-label">Departamento *</label><select name="department" class="form-select" required>@foreach(['rh'=>'RH','administracion'=>'Administración','comercial'=>'Comercial','operaciones'=>'Operaciones','ti'=>'TI'] as $v=>$l)<option value="{{ $v }}" @selected(old('department',$user->department)===$v)>{{ $l }}</option>@endforeach</select></div>
        <div class="flex items-center gap-2 pt-3"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$user->is_active))><label class="text-sm">Activo</label></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('users.show',$user) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
