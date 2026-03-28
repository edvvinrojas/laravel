@extends('layouts.app')
@section('title','Nueva Venta')
@section('page-title','Nueva Venta')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('sales.store') }}">
@csrf
<div class="card">
    <div class="card-header"><h3 class="font-semibold text-sm">Datos de la venta</h3></div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Cliente *</label>
            <select name="client_id" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('client_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Equipo *</label>
            <select name="item_id" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($items as $i)
                <option value="{{ $i->id }}" @selected(old('item_id')==$i->id)>{{ $i->brand->name ?? '' }} {{ $i->model }} — {{ $i->serie }}</option>
                @endforeach
            </select>
            @error('item_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">No. Factura</label>
            <input name="invoice_number" value="{{ old('invoice_number') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Precio venta *</label>
            <input name="sale_price" type="number" step="0.01" min="0" value="{{ old('sale_price') }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Estado *</label>
            <select name="sale_status" class="form-select" required>
                @foreach(['PENDIENTE','CONFIRMADA','ENTREGADA','CANCELADA'] as $s)
                <option value="{{ $s }}" @selected(old('sale_status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 pt-5">
            <input type="checkbox" name="is_foreign" value="1" id="foreign" @checked(old('is_foreign'))>
            <label for="foreign" class="text-sm">Venta foránea</label>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('sales.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
