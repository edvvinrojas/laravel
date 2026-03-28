@extends('layouts.app')
@section('title','Editar Nómina')
@section('page-title','Editar Nómina')

@section('content')
<div class="max-w-xl">
<form method="POST" action="{{ route('payrolls.update',$payroll) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="form-label">Salario *</label><input name="salary" type="number" step="0.01" value="{{ old('salary',$payroll->salary) }}" class="form-input" required></div>
        <div><label class="form-label">Fecha pago *</label><input name="pay_day" type="date" value="{{ old('pay_day',$payroll->pay_day?->format('Y-m-d')) }}" class="form-input" required></div>
        <div><label class="form-label">Bono</label><input name="bonus" type="number" step="0.01" value="{{ old('bonus',$payroll->bonus) }}" class="form-input"></div>
        <div><label class="form-label">Comisión</label><input name="commission" type="number" step="0.01" value="{{ old('commission',$payroll->commission) }}" class="form-input"></div>
        <div><label class="form-label">Estado *</label><select name="status" class="form-select" required>@foreach(['PENDIENTE','APROBADO','RECHAZADO','ACTIVO','PAGADO'] as $s)<option value="{{ $s }}" @selected(old('status',$payroll->status)===$s)>{{ $s }}</option>@endforeach</select></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('payrolls.show',$payroll) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
