@extends('layouts.app')
@section('title','Nueva Refacción')
@section('page-title','Nueva Refacción')

@section('content')
{{-- Tabs: individual / masivo --}}
<div class="flex gap-1 mb-4">
    <button onclick="showTab('single')" id="tab-single"
        class="tab-btn px-4 py-2 text-sm font-medium rounded-t border border-b-0 bg-white border-gray-300 text-blue-700">Individual</button>
    <button onclick="showTab('bulk')" id="tab-bulk"
        class="tab-btn px-4 py-2 text-sm font-medium rounded-t border border-b-0 bg-gray-50 border-gray-300 text-gray-600">Alta masiva</button>
</div>

{{-- INDIVIDUAL --}}
<div id="pane-single" class="max-w-xl">
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
            <label class="form-label">Código de pieza</label>
            <input name="code" id="codeField" value="{{ old('code') }}" class="form-input"
                placeholder="Ej: DV-512C">
            <p class="text-xs text-gray-400 mt-1">Se generará código interno automáticamente (DV-512C-01)</p>
            @error('code')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Color</label>
            <input name="color" value="{{ old('color') }}" class="form-input">
        </div>

        <div>
            <label class="form-label">Anaquel</label>
            <select name="shelf_id" class="form-select">
                <option value="">Sin anaquel</option>
                @foreach($shelves as $sh)
                <option value="{{ $sh->id }}" @selected(old('shelf_id')==$sh->id)>{{ $sh->name }}{{ $sh->section ? ' — '.$sh->section : '' }}</option>
                @endforeach
            </select>
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

{{-- ALTA MASIVA --}}
<div id="pane-bulk" class="hidden">
<form method="POST" action="{{ route('spareparts.store') }}">
@csrf
<input type="hidden" name="bulk" value="1">
<div class="card">
    <div class="card-header flex items-center justify-between">
        <h3 class="font-semibold text-sm">Alta masiva de refacciones</h3>
        <button type="button" onclick="addBulkRow()" class="btn-secondary btn-sm">+ Agregar fila</button>
    </div>
    <div class="overflow-x-auto">
        <table class="table text-sm" id="bulkTable">
            <thead>
                <tr>
                    <th>Nombre *</th>
                    <th>Código pieza</th>
                    <th>Equipo</th>
                    <th>Marca</th>
                    <th>Proveedor</th>
                    <th>Descripción</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="bulkBody">
                @for($i=0;$i<5;$i++)
                <tr>
                    <td><input name="items[{{ $i }}][name]" class="form-input text-xs" placeholder="Nombre"></td>
                    <td><input name="items[{{ $i }}][code]" class="form-input text-xs" placeholder="DV-512C"></td>
                    <td><input name="items[{{ $i }}][equipment]" class="form-input text-xs" placeholder="Modelo impresora"></td>
                    <td><input name="items[{{ $i }}][brand]" class="form-input text-xs" placeholder="Marca"></td>
                    <td><input name="items[{{ $i }}][supplier]" class="form-input text-xs" placeholder="Proveedor"></td>
                    <td><input name="items[{{ $i }}][description]" class="form-input text-xs" placeholder="Descripción"></td>
                    <td><button type="button" onclick="this.closest('tr').remove()" class="text-red-500 text-xs px-2">✕</button></td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Importar todas</button>
        <a href="{{ route('spareparts.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>

@push('scripts')
<script>
let bulkRowIdx = 5;

function showTab(tab) {
    document.getElementById('pane-single').classList.toggle('hidden', tab !== 'single');
    document.getElementById('pane-bulk').classList.toggle('hidden', tab !== 'bulk');
    document.getElementById('tab-single').classList.toggle('text-blue-700', tab === 'single');
    document.getElementById('tab-single').classList.toggle('bg-white', tab === 'single');
    document.getElementById('tab-single').classList.toggle('text-gray-600', tab !== 'single');
    document.getElementById('tab-single').classList.toggle('bg-gray-50', tab !== 'single');
    document.getElementById('tab-bulk').classList.toggle('text-blue-700', tab === 'bulk');
    document.getElementById('tab-bulk').classList.toggle('bg-white', tab === 'bulk');
    document.getElementById('tab-bulk').classList.toggle('text-gray-600', tab !== 'bulk');
    document.getElementById('tab-bulk').classList.toggle('bg-gray-50', tab !== 'bulk');
}

function addBulkRow() {
    const i = bulkRowIdx++;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input name="items[${i}][name]" class="form-input text-xs" placeholder="Nombre"></td>
        <td><input name="items[${i}][code]" class="form-input text-xs" placeholder="DV-512C"></td>
        <td><input name="items[${i}][equipment]" class="form-input text-xs" placeholder="Modelo impresora"></td>
        <td><input name="items[${i}][brand]" class="form-input text-xs" placeholder="Marca"></td>
        <td><input name="items[${i}][supplier]" class="form-input text-xs" placeholder="Proveedor"></td>
        <td><input name="items[${i}][description]" class="form-input text-xs" placeholder="Descripción"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="text-red-500 text-xs px-2">✕</button></td>
    `;
    document.getElementById('bulkBody').appendChild(tr);
}

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
