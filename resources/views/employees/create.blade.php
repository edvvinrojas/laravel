@extends('layouts.app')
@section('title','Nuevo Empleado')
@section('page-title','Nuevo Empleado')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('employees.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2"><label class="form-label">Nombre completo *</label><input name="nombre" value="{{ old('nombre') }}" class="form-input" required>@error('nombre')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><label class="form-label">Departamento</label><input name="departamento" value="{{ old('departamento') }}" class="form-input" placeholder="Ej: Ventas, Operaciones…"></div>
        <div><label class="form-label">Puesto</label><input name="puesto" value="{{ old('puesto') }}" class="form-input" placeholder="Ej: Vendedor, Técnico…"></div>
        <div><label class="form-label">Sueldo mensual</label><input name="sueldo" type="number" step="0.01" min="0" value="{{ old('sueldo') }}" class="form-input" placeholder="0.00"></div>
        <div><label class="form-label">Usuario del sistema</label><select name="user_id" class="form-select"><option value="">Ninguno</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected(old('user_id')==$u->id)>{{ $u->full_name }}</option>@endforeach</select></div>
        <div class="col-span-2"><label class="form-label">Jefe directo</label><select name="direct_manager_user_id" class="form-select"><option value="">Sin asignar</option>@foreach($managers as $m)<option value="{{ $m->id }}" @selected(old('direct_manager_user_id')==$m->id)>{{ $m->full_name }} ({{ strtoupper($m->department) }})</option>@endforeach</select></div>
        <div><label class="form-label">NSS *</label><input name="nss" maxlength="11" value="{{ old('nss') }}" class="form-input" required>@error('nss')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><label class="form-label">RFC *</label><input name="rfc" maxlength="13" value="{{ old('rfc') }}" class="form-input" required>@error('rfc')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><label class="form-label">CURP *</label><input name="curp" maxlength="18" value="{{ old('curp') }}" class="form-input" required>@error('curp')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><label class="form-label">Fecha de nacimiento *</label><input name="birthday" type="date" value="{{ old('birthday') }}" class="form-input" required></div>
        <div><label class="form-label">Fecha de ingreso *</label><input name="hire_date" type="date" value="{{ old('hire_date',date('Y-m-d')) }}" class="form-input" required></div>
        <div><label class="form-label">Fecha de baja</label><input name="termination_date" type="date" value="{{ old('termination_date') }}" class="form-input"></div>
        <div><label class="form-label">Teléfono emergencia *</label><input name="phone_emergency" value="{{ old('phone_emergency') }}" class="form-input" required></div>
        <div><label class="form-label">Contacto emergencia *</label><input name="contact_emergency" value="{{ old('contact_emergency') }}" class="form-input" required></div>
        <div class="flex items-center gap-2 pt-3"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))><label class="text-sm">Activo</label></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('employees.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
