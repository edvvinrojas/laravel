@extends('layouts.app')
@section('title','Nueva Factura')
@section('page-title','Nueva Factura')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('billing.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Tipo *</label>
            <select name="billing_type" id="billingType" class="form-select" required>
                <option value="RENTA" @selected(old('billing_type')==='RENTA')>RENTA</option>
                <option value="VENTA" @selected(old('billing_type')==='VENTA')>VENTA</option>
            </select>
        </div>
        <div id="rentField">
            <label class="form-label">Renta relacionada</label>
            <select name="rent_id" class="form-select">
                <option value="">Ninguna</option>
                @foreach($rents as $r)
                <option value="{{ $r->id }}" @selected(old('rent_id')==$r->id||request('rent_id')==$r->id)>{{ $r->client->name }} — {{ $r->contract_number }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Cliente *</label>
            <select name="client_id" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">No. Factura</label>
            <input name="invoice_number" value="{{ old('invoice_number') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Monto *</label>
            <input name="amount" type="number" step="0.01" min="0" value="{{ old('amount') }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Fecha objetivo *</label>
            <input name="target_date" type="date" value="{{ old('target_date',date('Y-m-d')) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Fecha vencimiento *</label>
            <input name="due_date" type="date" value="{{ old('due_date') }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Plazo (días)</label>
            <input name="payment_term" type="number" min="0" value="{{ old('payment_term') }}" class="form-input">
        </div>
        <div class="col-span-2">
            <label class="form-label">Comentarios</label>
            <textarea name="comment" class="form-input" rows="2">{{ old('comment') }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('billing.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
