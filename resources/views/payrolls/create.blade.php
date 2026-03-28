@extends('layouts.app')
@section('title','Nueva Nómina')
@section('page-title','Nueva Nómina')

@section('content')
<div class="max-w-xl">
<form method="POST" action="{{ route('payrolls.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2"><label class="form-label">Empleado *</label><select name="employee_id" class="form-select" required><option value="">Seleccionar…</option>@foreach($employees as $e)<option value="{{ $e->id }}" @selected(old('employee_id',request('employee_id'))==$e->id)>{{ $e->nombre }}</option>@endforeach</select></div>
        <div><label class="form-label">Salario *</label><input name="salary" type="number" step="0.01" min="0" value="{{ old('salary') }}" class="form-input" required></div>
        <div><label class="form-label">Fecha de pago *</label><input name="pay_day" type="date" value="{{ old('pay_day',date('Y-m-d')) }}" class="form-input" required></div>
        <div><label class="form-label">Bono</label><input name="bonus" type="number" step="0.01" min="0" value="{{ old('bonus',0) }}" class="form-input"></div>
        <div><label class="form-label">Comisión</label><input name="commission" type="number" step="0.01" min="0" value="{{ old('commission',0) }}" class="form-input"></div>
        <div><label class="form-label">Estado *</label><select name="status" class="form-select" required>@foreach(['PENDIENTE','APROBADO','RECHAZADO','ACTIVO','PAGADO'] as $s)<option value="{{ $s }}" @selected(old('status','PENDIENTE')===$s)>{{ $s }}</option>@endforeach</select></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('payrolls.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
