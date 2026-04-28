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
            <input name="code" id="codeField" value="{{ old('code') }}" class="form-input"
                placeholder="Ej: DV-512C">
            <p class="text-xs text-gray-400 mt-1">Si capturas un código base, al replicar se generan DV-512C-01, DV-512C-02, …</p>
            <div id="nextSequential" class="mt-3 text-xs">
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
            <select name="brand" class="form-select">
                <option value="">— Sin marca —</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand }}" @selected(old('brand')===$brand)>{{ $brand }}</option>
                @endforeach
                <option value="" disabled>───</option>
                <option value="__add_new__">+ Agregar nueva marca…</option>
            </select>
            <input type="text" id="newBrandInput" placeholder="Nueva marca" class="form-input mt-2 hidden">
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
document.addEventListener('DOMContentLoaded', function() {
    const codeField = document.getElementById('codeField');
    const nextSequentialDiv = document.getElementById('nextSequential');
    const brandSelect = document.querySelector('select[name="brand"]');
    const newBrandInput = document.getElementById('newBrandInput');

    // Actualizar preview del siguiente consecutivo
    function updateNextSequential() {
        const code = codeField.value.trim();
        if (!code) {
            nextSequentialDiv.innerHTML = '<span class="text-gray-600">Siguiente: <span class="font-semibold text-blue-600">—</span></span>';
            return;
        }

        // Enviar petición AJAX para obtener el siguiente consecutivo
        fetch(`{{ route('spareparts.api.next-sequential') }}?code=${encodeURIComponent(code)}`)
            .then(r => r.json())
            .then(data => {
                const next = data.next || '—';
                nextSequentialDiv.innerHTML = `<span class="text-gray-600">Siguiente: <span class="font-semibold text-blue-600">${next}</span></span>`;
            })
            .catch(() => {
                nextSequentialDiv.innerHTML = '<span class="text-gray-600">Siguiente: <span class="font-semibold text-blue-600">—</span></span>';
            });
    }

    codeField.addEventListener('input', updateNextSequential);
    updateNextSequential();

    // Manejar agregar nueva marca
    brandSelect.addEventListener('change', function() {
        if (this.value === '__add_new__') {
            newBrandInput.classList.remove('hidden');
            newBrandInput.focus();
        } else {
            newBrandInput.classList.add('hidden');
            newBrandInput.value = '';
        }
    });

    // Interceptar el submit para usar la nueva marca si se capturó
    document.querySelector('form').addEventListener('submit', function(e) {
        if (brandSelect.value === '__add_new__' && newBrandInput.value.trim()) {
            brandSelect.value = newBrandInput.value.trim();
        }
    });
});
</script>
@endsection
