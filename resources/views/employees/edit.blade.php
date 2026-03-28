@extends('layouts.app')
@section('title','Editar Empleado')
@section('page-title','Editar Empleado')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('employees.update',$employee) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2"><label class="form-label">Nombre *</label><input name="nombre" value="{{ old('nombre',$employee->nombre) }}" class="form-input" required></div>
        <div><label class="form-label">Usuario del sistema</label><select name="user_id" class="form-select"><option value="">Ninguno</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected(old('user_id',$employee->user_id)==$u->id)>{{ $u->full_name }}</option>@endforeach</select></div>
        <div><label class="form-label">NSS *</label><input name="nss" maxlength="11" value="{{ old('nss',$employee->nss) }}" class="form-input" required></div>
        <div><label class="form-label">RFC *</label><input name="rfc" maxlength="13" value="{{ old('rfc',$employee->rfc) }}" class="form-input" required></div>
        <div><label class="form-label">CURP *</label><input name="curp" maxlength="18" value="{{ old('curp',$employee->curp) }}" class="form-input" required></div>
        <div><label class="form-label">Fecha nacimiento *</label><input name="birthday" type="date" value="{{ old('birthday',$employee->birthday?->format('Y-m-d')) }}" class="form-input" required></div>
        <div><label class="form-label">Fecha ingreso *</label><input name="hire_date" type="date" value="{{ old('hire_date',$employee->hire_date?->format('Y-m-d')) }}" class="form-input" required></div>
        <div><label class="form-label">Tel. emergencia *</label><input name="phone_emergency" value="{{ old('phone_emergency',$employee->phone_emergency) }}" class="form-input" required></div>
        <div><label class="form-label">Contacto emergencia *</label><input name="contact_emergency" value="{{ old('contact_emergency',$employee->contact_emergency) }}" class="form-input" required></div>
        <div class="flex items-center gap-2 pt-3"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$employee->is_active))><label class="text-sm">Activo</label></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('employees.show',$employee) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
