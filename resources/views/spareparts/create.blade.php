@extends('layouts.app')
@section('title','Nueva Refacción')
@section('page-title','Nueva Refacción')

@section('content')
<div class="mb-4">
    <a href="{{ route('spareparts.index') }}" class="btn-secondary">← Volver a refacciones</a>
</div>

<div class="max-w-2xl">
<form method="POST" action="{{ route('spareparts.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div class="col-span-2">
            <label class="form-label">Nombre *</label>
            <input name="name" value="{{ old('name') }}" class="form-input" required>
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <!-- Código de pieza con preview de consecutivo -->
        <div>
            <label class="form-label">Código de pieza</label>
            <div class="flex gap-2">
                <input name="code" id="codeField" value="{{ old('code') }}" class="form-input flex-1"
                    placeholder="Ej: DV-512C" list="code_prefixes_list" autocomplete="off">
                @if(count($codePrefixes) > 0)
                <button type="button" id="btnAutoCode"
                    class="btn-secondary btn-sm whitespace-nowrap"
                    title="Generar código automático">
                    ↻ Auto
                </button>
                @endif
            </div>
            <datalist id="code_prefixes_list">
                @foreach($codePrefixes as $prefix)
                    <option value="{{ $prefix }}"></option>
                @endforeach
            </datalist>
            <p class="text-xs text-gray-400 mt-1">Prefijos existentes: {{ implode(', ', $codePrefixes) ?: 'ninguno aún' }}</p>
            <div id="nextSequential" class="mt-1 text-xs">
                <span class="text-gray-600">Siguiente: <span class="font-semibold text-blue-600">—</span></span>
            </div>
            @error('code')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <!-- Cantidad a dar de alta -->
        <div>
            <label class="form-label">Cantidad a dar de alta *</label>
            <input name="quantity" type="number" min="1" max="500" value="{{ old('quantity', 1) }}" class="form-input" required>
            <p class="text-xs text-gray-400 mt-1">Replica N veces auto-incrementando código.</p>
            @error('quantity')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <!-- Color con selector -->
        <div>
            <label class="form-label">Color</label>
            <select name="color" class="form-select">
                <option value="">— Sin color —</option>
                @foreach($colors as $color)
                    <option value="{{ $color }}" @selected(old('color')===$color)>{{ $color }}</option>
                @endforeach
            </select>
        </div>

        <!-- Marca con selector -->
        <div>
            <label class="form-label">Marca</label>
            <select name="brand" id="brandSelect" class="form-select">
                <option value="">Sin marca</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand }}" @selected(old('brand')===$brand)>{{ $brand }}</option>
                @endforeach
                <option value="__add_new__" @selected(old('brand_new'))>＋ Agregar nueva marca...</option>
            </select>
            <div id="brandNewWrap" class="{{ old('brand_new') ? '' : 'hidden' }} mt-2">
                <input type="text" name="brand_new" id="brand_new" value="{{ old('brand_new') }}" placeholder="Escribe el nombre de la marca" class="form-input">
                @error('brand_new')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="col-span-2">
            <label class="form-label">Proveedor</label>
            <select name="supplier" class="form-select">
                <option value="">— Sin proveedor —</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->name }}" @selected(old('supplier')===$s->name)>{{ $s->name }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">¿Falta uno? Da de alta el proveedor en el catálogo.</p>
        </div>

        <div class="col-span-2">
            <label class="form-label">Equipo compatible</label>
            <input name="equipment" value="{{ old('equipment') }}" class="form-input">
        </div>

        {{-- Precio y factura --}}
        <div>
            <label class="form-label">Precio unitario</label>
            <input name="unit_price" type="number" step="0.01" min="0" value="{{ old('unit_price') }}" class="form-input" placeholder="0.00">
            @error('unit_price')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Precio total</label>
            <input name="total_price" type="number" step="0.01" min="0" value="{{ old('total_price') }}" class="form-input" placeholder="0.00">
            @error('total_price')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="col-span-2">
            <label class="form-label">No. de factura</label>
            <input name="invoice_number" value="{{ old('invoice_number') }}" class="form-input" placeholder="Ej: FAC-2026-0001">
            @error('invoice_number')<p class="form-error">{{ $message }}</p>@enderror
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

<script>
const codePrefixes = @json($codePrefixes);

document.addEventListener('DOMContentLoaded', function() {
    const codeField = document.getElementById('codeField');
    const nextSequentialDiv = document.getElementById('nextSequential');
    const btnAuto = document.getElementById('btnAutoCode');
    const apiUrl = '{{ route('spareparts.api.next-sequential') }}';

    function updateNextSequential(callback) {
        const code = codeField.value.trim();
        if (!code) {
            nextSequentialDiv.innerHTML = '<span class="text-gray-600">Siguiente: <span class="font-semibold text-blue-600">—</span></span>';
            if (callback) callback(null);
            return;
        }
        fetch(`${apiUrl}?code=${encodeURIComponent(code)}`)
            .then(r => r.json())
            .then(data => {
                const next = data.next || '—';
                nextSequentialDiv.innerHTML = `<span class="text-gray-600">Siguiente: <span class="font-semibold text-blue-600">${next}</span></span>`;
                if (callback) callback(data.next || null);
            })
            .catch(() => {
                nextSequentialDiv.innerHTML = '<span class="text-gray-600">Siguiente: <span class="font-semibold text-blue-600">—</span></span>';
                if (callback) callback(null);
            });
    }

    // Al cargar: si hay prefijos y el campo está vacío, auto-rellenar con el primero
    if (codePrefixes.length > 0 && !codeField.value.trim()) {
        codeField.value = codePrefixes[0];
        updateNextSequential();
    }

    codeField.addEventListener('input', () => updateNextSequential());

    // Botón Auto: pide el siguiente código completo y lo mete en el campo
    if (btnAuto) {
        btnAuto.addEventListener('click', function() {
            const prefix = codeField.value.trim() || codePrefixes[0] || '';
            if (!prefix) return;
            codeField.value = prefix;
            updateNextSequential(function(next) {
                if (next) {
                    codeField.value = next;
                    updateNextSequential();
                }
            });
        });
    }

    // Mostrar/ocultar input nueva marca según selección
    const brandSelect = document.getElementById('brandSelect');
    const brandNewWrap = document.getElementById('brandNewWrap');
    const brandNewInput = document.getElementById('brand_new');
    if (brandSelect && brandNewWrap) {
        brandSelect.addEventListener('change', function () {
            if (this.value === '__add_new__') {
                brandNewWrap.classList.remove('hidden');
                brandNewInput.focus();
            } else {
                brandNewWrap.classList.add('hidden');
                brandNewInput.value = '';
            }
        });
    }
});
</script>
@endsection
