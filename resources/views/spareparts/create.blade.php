@extends('layouts.app')
@section('title','Nueva Refacción')
@section('page-title','Nueva Refacción')

@section('content')
<div class="max-w-xl">
<form method="POST" action="{{ route('spareparts.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div class="col-span-2">
            <label class="form-label">Nombre *</label>
            <input name="name" value="{{ old('name') }}" class="form-input" required>
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Código</label>
            <input name="code" value="{{ old('code') }}" class="form-input">
            @error('code')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Color</label>
            <input name="color" value="{{ old('color') }}" class="form-input">
        </div>

        <div>
            <label class="form-label">Marca (catálogo)</label>
            <select name="brand_id" id="brandSel" class="form-select" onchange="toggleFallback('brand',this.value)">
                <option value="">— Seleccionar —</option>
                @foreach($brands as $b)
                <option value="{{ $b->id }}" @selected(old('brand_id')==$b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div id="brandFallback">
            <label class="form-label">Marca (texto libre)</label>
            <input name="brand" id="brandText" value="{{ old('brand') }}" class="form-input" placeholder="Solo si no está en catálogo">
        </div>

        <div>
            <label class="form-label">Proveedor (catálogo)</label>
            <select name="supplier_id" id="supplierSel" class="form-select" onchange="toggleFallback('supplier',this.value)">
                <option value="">— Seleccionar —</option>
                @foreach($suppliers as $s)
                <option value="{{ $s->id }}" @selected(old('supplier_id')==$s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div id="supplierFallback">
            <label class="form-label">Proveedor (texto libre)</label>
            <input name="supplier" id="supplierText" value="{{ old('supplier') }}" class="form-input" placeholder="Solo si no está en catálogo">
        </div>

        <div class="col-span-2">
            <label class="form-label">Equipo compatible</label>
            <input name="equipment" value="{{ old('equipment') }}" class="form-input">
        </div>

        <div class="col-span-2">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-input" rows="3">{{ old('description') }}</textarea>
        </div>

    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('spareparts.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>

@push('scripts')
<script>
function toggleFallback(field, val) {
    const wrap = document.getElementById(field + 'Fallback');
    const input = document.getElementById(field + 'Text');
    wrap.style.opacity = val ? '0.4' : '1';
    if (val) input.value = '';
}
toggleFallback('brand',    document.getElementById('brandSel').value);
toggleFallback('supplier', document.getElementById('supplierSel').value);
</script>
@endpush
@endsection
