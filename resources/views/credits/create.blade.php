@extends('layouts.app')
@section('title','Nuevo Crédito')
@section('page-title','Nuevo Crédito')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('credits.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2"><label class="form-label">Empleado *</label><select name="employee_id" class="form-select" required><option value="">Seleccionar...</option>@foreach($employees as $e)<option value="{{ $e->id }}" @selected(old('employee_id',request('employee_id'))==$e->id)>{{ $e->nombre }}</option>@endforeach</select></div>
        <div><label class="form-label">Cantidad del crédito *</label><input name="credit_amount" type="number" step="0.01" min="0" value="{{ old('credit_amount') }}" class="form-input" required></div>
        <div><label class="form-label">Descuento quincenal *</label><input name="biweekly_discount" type="number" step="0.01" min="0" value="{{ old('biweekly_discount') }}" class="form-input" required></div>
        <div class="col-span-2 text-xs text-gray-600 bg-blue-50 border border-blue-200 rounded px-3 py-2">El <strong>monto pendiente</strong> y las <strong>quincenas pendientes</strong> se calculan automáticamente y se descuentan al procesar cada nómina.</div>
        <div><label class="form-label">Fecha fin de pago</label><input name="payment_end_date" type="date" value="{{ old('payment_end_date') }}" class="form-input"></div>
        <div class="md:col-span-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-2">El crédito se registra como <strong>SOLICITADO</strong> y debe ser autorizado por el jefe directo.</div>
        <div class="col-span-2"><label class="form-label">Motivo del crédito *</label><textarea name="credit_reason" class="form-input" rows="3" required>{{ old('credit_reason') }}</textarea></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('credits.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
