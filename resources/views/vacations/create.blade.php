@extends('layouts.app')
@section('title','Solicitar Vacaciones')
@section('page-title','Solicitar Vacaciones')

@section('content')
<div class="max-w-xl">
<form method="POST" action="{{ route('vacations.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2"><label class="form-label">Empleado *</label><select name="employee_id" class="form-select" required><option value="">Seleccionar…</option>@foreach($employees as $e)<option value="{{ $e->id }}" @selected(old('employee_id',request('employee_id'))==$e->id)>{{ $e->nombre }}</option>@endforeach</select></div>
        <div><label class="form-label">Días de vacaciones *</label><input name="vacation_days" type="number" min="1" value="{{ old('vacation_days') }}" class="form-input" required></div>
        <div><label class="form-label">Días restantes *</label><input name="remaining_days" type="number" min="0" value="{{ old('remaining_days') }}" class="form-input" required></div>
        <div><label class="form-label">Inicio *</label><input name="start_date" type="date" value="{{ old('start_date') }}" class="form-input" required></div>
        <div><label class="form-label">Fin *</label><input name="end_date" type="date" value="{{ old('end_date') }}" class="form-input" required></div>
        <div class="col-span-2"><label class="form-label">Notas</label><textarea name="notes" class="form-input" rows="2">{{ old('notes') }}</textarea></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('vacations.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
