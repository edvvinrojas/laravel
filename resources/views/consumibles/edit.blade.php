@extends('layouts.app')
@section('title','Editar Consumible')
@section('page-title','Editar Consumible')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('consumibles.update', $consumible) }}" class="space-y-4">
@csrf @method('PUT')
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Datos del consumible</h3></div>
    <div class="card-body space-y-4">
        <div>
            <label class="form-label">Nombre <span class="text-red-500">*</span></label>
            <input name="nombre" value="{{ old('nombre', $consumible->nombre) }}"
                   class="form-input @error('nombre') border-red-400 @enderror" required>
            @error('nombre')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Código OEM <span class="text-red-500">*</span></label>
                <input name="codigo_oem" value="{{ old('codigo_oem', $consumible->codigo_oem) }}"
                       class="form-input @error('codigo_oem') border-red-400 @enderror"
                       required style="text-transform:uppercase">
                @error('codigo_oem')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Código alternativo</label>
                <input name="codigo_alternativo" value="{{ old('codigo_alternativo', $consumible->codigo_alternativo) }}"
                       class="form-input" style="text-transform:uppercase">
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="form-label">Marca</label>
                <select name="brand_id" class="form-select">
                    <option value="">— Sin marca —</option>
                    @foreach($marcas as $m)
                        <option value="{{ $m->id }}" {{ old('brand_id', $consumible->brand_id) == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Tipo <span class="text-red-500">*</span></label>
                <select name="tipo" class="form-select @error('tipo') border-red-400 @enderror" required>
                    @foreach(['TONER' => 'Tóner', 'DRUM' => 'Tambor/Drum', 'KIT_MANTENIMIENTO' => 'Kit mantenimiento', 'FUSOR' => 'Fusor', 'RODILLO' => 'Rodillo', 'TINTA' => 'Tinta', 'OTRO' => 'Otro'] as $v => $l)
                        <option value="{{ $v }}" {{ old('tipo', $consumible->tipo) === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
                @error('tipo')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Color</label>
                <select name="color" class="form-select">
                    <option value="">— N/A —</option>
                    @foreach(['NEGRO','CYAN','MAGENTA','AMARILLO','TRICOLOR','NA'] as $col)
                        <option value="{{ $col }}" {{ old('color', $consumible->color) === $col ? 'selected' : '' }}>{{ $col }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Rendimiento (páginas)</label>
                <input type="number" name="rendimiento_paginas" value="{{ old('rendimiento_paginas', $consumible->rendimiento_paginas) }}"
                       class="form-input" min="0">
            </div>
            <div class="flex items-end gap-4 pb-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="es_original" value="1"
                           {{ old('es_original', $consumible->es_original) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-600">
                    <span class="text-sm text-gray-700">Original / OEM</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="es_activo" value="1"
                           {{ old('es_activo', $consumible->es_activo) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-600">
                    <span class="text-sm text-gray-700">Activo</span>
                </label>
            </div>
        </div>
        <div>
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" rows="2" class="form-input">{{ old('descripcion', $consumible->descripcion) }}</textarea>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Stock</h3></div>
    <div class="card-body grid grid-cols-2 gap-4">
        @php $st = $consumible->stock; @endphp
        <div>
            <label class="form-label">Cantidad disponible</label>
            <input type="number" name="stock_cantidad" value="{{ old('stock_cantidad', $st?->cantidad_disponible ?? 0) }}" class="form-input" min="0">
        </div>
        <div>
            <label class="form-label">Cantidad mínima</label>
            <input type="number" name="stock_minimo" value="{{ old('stock_minimo', $st?->cantidad_minima ?? 0) }}" class="form-input" min="0">
        </div>
        <div>
            <label class="form-label">Costo unitario</label>
            <input type="number" name="stock_costo" value="{{ old('stock_costo', $st?->costo) }}" class="form-input" min="0" step="0.01">
        </div>
        <div>
            <label class="form-label">Ubicación</label>
            <input type="text" name="stock_ubicacion" value="{{ old('stock_ubicacion', $st?->ubicacion) }}" class="form-input">
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('almacen.index', ['tab' => 'consumibles']) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>

</form>
</div>
@endsection
