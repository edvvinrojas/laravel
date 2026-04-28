@extends('layouts.app')
@section('title','Editar Refacción')
@section('page-title','Editar Refacción')

@section('content')
<div class="mb-4">
    <a href="{{ route('spareparts.index') }}" class="btn-secondary">← Volver a refacciones</a>
</div>

<div class="max-w-2xl">
<form method="POST" action="{{ route('spareparts.update', $sparepart) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div class="col-span-2">
            <label class="form-label">Nombre *</label>
            <input name="name" value="{{ old('name', $sparepart->name) }}" class="form-input" required>
        </div>

        <!-- Código actual y consecutivos existentes -->
        <div>
            <label class="form-label">Código</label>
            <div class="form-input bg-gray-50 text-gray-700 font-mono text-sm cursor-default">
                {{ $sparepart->code ?? '—' }}
            </div>
            <p class="text-xs text-gray-400 mt-1">Inmutable para auditoría.</p>
            @if(count($existingSequential) > 1)
            <div class="mt-3">
                <p class="text-xs font-semibold text-gray-600 mb-2">Otros consecutivos:</p>
                <div class="space-y-1">
                    @foreach($existingSequential as $code)
                    <span class="inline-block px-2 py-1 bg-blue-50 border border-blue-200 text-blue-700 text-xs rounded">
                        {{ $code }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div>
            <label class="form-label">Color</label>
            <select name="color" class="form-select">
                <option value="">— Sin color —</option>
                @foreach($colors as $color)
                    <option value="{{ $color }}" @selected(old('color', $sparepart->color)===$color)>{{ $color }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Marca</label>
            <select name="brand" class="form-select">
                <option value="">— Sin marca —</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand }}" @selected(old('brand', $sparepart->brand)===$brand)>{{ $brand }}</option>
                @endforeach
                <option value="" disabled>───</option>
                <option value="__add_new__">+ Agregar nueva marca…</option>
            </select>
            <input type="text" id="newBrandInput" placeholder="Nueva marca" class="form-input mt-2 hidden">
        </div>

        <div>
            <label class="form-label">Proveedor</label>
            <select name="supplier" class="form-select">
                <option value="">— Sin proveedor —</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->name }}" @selected(old('supplier', $sparepart->supplier)===$s->name)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-span-2">
            <label class="form-label">Equipo compatible</label>
            <input name="equipment" value="{{ old('equipment', $sparepart->equipment) }}" class="form-input">
        </div>

        <div class="col-span-2">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-input" rows="3">{{ old('description', $sparepart->description) }}</textarea>
        </div>

    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('spareparts.show', $sparepart) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const brandSelect = document.querySelector('select[name="brand"]');
    const newBrandInput = document.getElementById('newBrandInput');

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
