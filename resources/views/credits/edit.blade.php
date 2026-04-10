@extends('layouts.app')
@section('title','Editar Crédito')
@section('page-title','Editar Crédito')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('credits.update',$credit) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2"><label class="form-label">Empleado *</label><select name="employee_id" class="form-select" required>@foreach($employees as $e)<option value="{{ $e->id }}" @selected(old('employee_id',$credit->employee_id)==$e->id)>{{ $e->nombre }}</option>@endforeach</select></div>
        <div><label class="form-label">Cantidad del crédito *</label><input name="credit_amount" type="number" step="0.01" min="0" value="{{ old('credit_amount',$credit->credit_amount) }}" class="form-input" required></div>
        <div><label class="form-label">Descuento quincenal *</label><input name="biweekly_discount" type="number" step="0.01" min="0" value="{{ old('biweekly_discount',$credit->biweekly_discount) }}" class="form-input" required></div>
        <div><label class="form-label">Monto pendiente *</label><input name="pending_amount" type="number" step="0.01" min="0" value="{{ old('pending_amount',$credit->pending_amount) }}" class="form-input" required></div>
        <div><label class="form-label">Quincenas pendientes *</label><input name="pending_biweeks" type="number" min="0" value="{{ old('pending_biweeks',$credit->pending_biweeks) }}" class="form-input" required></div>
        <div><label class="form-label">Fecha de aprobación</label><input name="approval_date" type="date" value="{{ old('approval_date',$credit->approval_date?->format('Y-m-d')) }}" class="form-input"></div>
        <div><label class="form-label">Fecha fin de pago</label><input name="payment_end_date" type="date" value="{{ old('payment_end_date',$credit->payment_end_date?->format('Y-m-d')) }}" class="form-input"></div>
        <div><label class="form-label">Estado *</label><select name="status" class="form-select" required>@foreach(['SOLICITADO','AUTORIZADO','LIQUIDADO','CANCELADO'] as $s)<option value="{{ $s }}" @selected(old('status',$credit->status)===$s)>{{ $s }}</option>@endforeach</select></div>
        <div class="col-span-2"><label class="form-label">Motivo del crédito *</label><textarea name="credit_reason" class="form-input" rows="3" required>{{ old('credit_reason',$credit->credit_reason) }}</textarea></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('credits.show',$credit) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
