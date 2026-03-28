@extends('layouts.app')
@section('title','Editar Factura')
@section('page-title','Editar Factura')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('billing.update',$billing) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">No. Factura</label>
            <input name="invoice_number" value="{{ old('invoice_number',$billing->invoice_number) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Monto *</label>
            <input name="amount" type="number" step="0.01" value="{{ old('amount',$billing->amount) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Fecha objetivo *</label>
            <input name="target_date" type="date" value="{{ old('target_date',$billing->target_date->format('Y-m-d')) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Fecha vencimiento *</label>
            <input name="due_date" type="date" value="{{ old('due_date',$billing->due_date->format('Y-m-d')) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Plazo (días)</label>
            <input name="payment_term" type="number" min="0" value="{{ old('payment_term',$billing->payment_term) }}" class="form-input">
        </div>
        <div class="flex items-center gap-2 pt-5">
            <input type="checkbox" name="follow_up" value="1" @checked(old('follow_up',$billing->follow_up))>
            <label class="text-sm">Dar seguimiento</label>
        </div>
        <div class="col-span-2">
            <label class="form-label">Comentarios</label>
            <textarea name="comment" class="form-input" rows="2">{{ old('comment',$billing->comment) }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('billing.show',$billing) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
