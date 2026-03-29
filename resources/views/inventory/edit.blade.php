@extends('layouts.app')
@section('title','Editar Artículo')
@section('page-title','Editar Artículo de Inventario')

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('inventory.update',$inventory) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Código *</label>
            <input name="item_code" value="{{ old('item_code',$inventory->item_code) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Catálogo / Tipo *</label>
            <select name="catalog_id" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($catalogs as $c)
                <option value="{{ $c->id }}" @selected(old('catalog_id',$inventory->catalog_id)==$c->id)>{{ $c->item_name }} — {{ $c->item_type }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Repisa / Ubicación</label>
            <select name="shelf_id" class="form-select">
                <option value="">Sin asignar</option>
                @foreach($shelves as $s)
                <option value="{{ $s->id }}" @selected(old('shelf_id',$inventory->shelf_id)==$s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Sección <span class="text-red-500">*</span></label>
            <select name="section" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach(['SECCION_1','SECCION_2','SECCION_3','SECCION_4','SECCION_5','SECCION_6'] as $sec)
                <option value="{{ $sec }}" @selected(old('section',$inventory->section)==$sec)>{{ str_replace('_',' ',$sec) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Calidad <span class="text-red-500">*</span></label>
            <select name="quality" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach(['ORIGINAL','GENERICO','REPARADO','NUEVA','USADO','NA'] as $q)
                <option value="{{ $q }}" @selected(old('quality',$inventory->quality)==$q)>{{ ucfirst(strtolower($q)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Proveedor</label>
            <select name="supplier_id" class="form-select">
                <option value="">Ninguno</option>
                @foreach($suppliers as $s)
                <option value="{{ $s->id }}" @selected(old('supplier_id',$inventory->supplier_id)==$s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Factura</label>
            <input name="invoice" value="{{ old('invoice',$inventory->invoice) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Fecha de entrada</label>
            <input name="entry_date" type="date" value="{{ old('entry_date',$inventory->entry_date?->format('Y-m-d')) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Costo</label>
            <input name="cost" type="number" step="0.01" min="0" value="{{ old('cost',$inventory->cost) }}" class="form-input">
        </div>
        <div class="flex items-center gap-2 mt-5">
            <input name="is_available" type="checkbox" id="is_available" value="1" class="form-checkbox" @checked(old('is_available',$inventory->is_available))>
            <label for="is_available" class="form-label mb-0">Disponible</label>
        </div>
        <div class="col-span-2">
            <label class="form-label">Comentarios</label>
            <textarea name="comments" class="form-input" rows="2">{{ old('comments',$inventory->comments) }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('inventory.show',$inventory) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
