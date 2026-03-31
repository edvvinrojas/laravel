@extends('layouts.app')
@section('title','Nueva Solicitud de Compra')
@section('page-title','Nueva Solicitud de Compra')

@section('content')
<div class="max-w-2xl">
<div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-800">
    <strong>Flujo:</strong> Tu solicitud llegará al gerente para autorización. Una vez autorizada, el departamento de compras gestionará los proveedores y actualizará el estatus.
</div>
<form method="POST" action="{{ route('purchases.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Artículo / Descripción *</label>
            <input name="name" value="{{ old('name') }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Refacción relacionada</label>
            <select name="sparepart_id" class="form-select">
                <option value="">Ninguna</option>
                @foreach($spareparts as $s)
                <option value="{{ $s->id }}" @selected(old('sparepart_id')==$s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Cantidad *</label>
            <input name="amount" type="number" min="1" value="{{ old('amount',1) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Tipo *</label>
            <select name="type" class="form-select" required>
                <option value="INTERNA" @selected(old('type')==='INTERNA')>INTERNA</option>
                <option value="VENTA" @selected(old('type')==='VENTA')>VENTA</option>
            </select>
        </div>
        <div>
            <label class="form-label">Calidad</label>
            <input name="quality" value="{{ old('quality') }}" class="form-input" placeholder="Original, Genérico…">
        </div>
        <div class="col-span-2">
            <label class="form-label">Justificación</label>
            <textarea name="justification" class="form-input" rows="3">{{ old('justification') }}</textarea>
        </div>
        <div class="col-span-2">
            <label class="form-label">Comentarios adicionales</label>
            <textarea name="comments" class="form-input" rows="2">{{ old('comments') }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Enviar solicitud</button>
        <a href="{{ route('purchases.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
