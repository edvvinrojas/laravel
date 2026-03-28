@extends('layouts.app')
@section('title','Nueva Compra')
@section('page-title','Nueva Compra')

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('purchases.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Nombre del artículo *</label>
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
            <textarea name="justification" class="form-input" rows="2">{{ old('justification') }}</textarea>
        </div>
        <div class="col-span-2 border-t pt-3">
            <p class="text-sm font-medium text-gray-700 mb-3">Cotizaciones (hasta 3 proveedores)</p>
            <div class="grid grid-cols-3 gap-3">
                @foreach([1,2,3] as $n)
                <div>
                    <label class="form-label">Proveedor {{ $n }}</label>
                    <input name="supplier{{ $n }}_name" value="{{ old('supplier'.$n.'_name') }}" class="form-input mb-2" placeholder="Nombre">
                    <input name="supplier{{ $n }}_cost" type="number" step="0.01" value="{{ old('supplier'.$n.'_cost') }}" class="form-input" placeholder="Costo">
                </div>
                @endforeach
            </div>
        </div>
        <div class="col-span-2">
            <label class="form-label">Comentarios</label>
            <textarea name="comments" class="form-input" rows="2">{{ old('comments') }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('purchases.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
