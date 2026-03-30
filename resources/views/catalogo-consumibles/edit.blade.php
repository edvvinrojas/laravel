@extends('layouts.app')
@section('title','Editar Consumible')
@section('page-title','Editar Consumible')

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('catalogo-consumibles.update', $consumible) }}">
@csrf @method('PUT')
<div class="space-y-4">

{{-- Datos básicos --}}
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Datos del consumible</h3></div>
    <div class="card-body space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Tipo <span class="text-red-500">*</span></label>
                <select name="tipo_id" class="form-select @error('tipo_id') border-red-400 @enderror" required>
                    <option value="">— Seleccionar tipo —</option>
                    @foreach($tipos as $t)
                        <option value="{{ $t->id }}" {{ old('tipo_id', $consumible->tipo_id) == $t->id ? 'selected' : '' }}>{{ $t->nombre }}</option>
                    @endforeach
                </select>
                @error('tipo_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Marca</label>
                <select name="marca_id" class="form-select @error('marca_id') border-red-400 @enderror">
                    <option value="">— Sin marca —</option>
                    @foreach($marcas as $m)
                        <option value="{{ $m->id }}" {{ old('marca_id', $consumible->marca_id) == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
                @error('marca_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
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
                       class="form-input @error('codigo_alternativo') border-red-400 @enderror"
                       style="text-transform:uppercase">
                @error('codigo_alternativo')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="form-label">Color</label>
                <select name="color" class="form-select @error('color') border-red-400 @enderror">
                    <option value="">— N/A —</option>
                    @foreach(['NEGRO','CYAN','MAGENTA','AMARILLO','TRICOLOR'] as $col)
                        <option value="{{ $col }}" {{ old('color', $consumible->color) === $col ? 'selected' : '' }}>{{ $col }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Rendimiento (orig.)</label>
                <input type="number" name="rendimiento_paginas"
                       value="{{ old('rendimiento_paginas', $consumible->rendimiento_paginas) }}"
                       class="form-input" min="0">
            </div>
            <div>
                <label class="form-label">Rendimiento (alt.)</label>
                <input type="number" name="rendimiento_paginas_alt"
                       value="{{ old('rendimiento_paginas_alt', $consumible->rendimiento_paginas_alt) }}"
                       class="form-input" min="0">
            </div>
        </div>
        <div class="flex gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="es_original" value="1"
                       {{ old('es_original', $consumible->es_original) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-gray-300 text-blue-600">
                <span class="text-sm text-gray-700">Es consumible original / OEM</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="es_activo" value="1"
                       {{ old('es_activo', $consumible->es_activo) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-gray-300 text-blue-600">
                <span class="text-sm text-gray-700">Activo</span>
            </label>
        </div>
        <div>
            <label class="form-label">Descripción / notas</label>
            <textarea name="descripcion" rows="2"
                      class="form-input @error('descripcion') border-red-400 @enderror">{{ old('descripcion', $consumible->descripcion) }}</textarea>
        </div>
    </div>
</div>

{{-- Compatibilidad con modelos --}}
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Modelos compatibles</h3></div>
    <div class="card-body">
        @if($modelos->isEmpty())
            <p class="text-sm text-gray-400">No hay modelos registrados.</p>
        @else
        <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
            @foreach($modelos as $mod)
            @php
                $pivot   = $modelosCompatibles->get($mod->id);
                $checked = old('modelos_compatibles') !== null
                    ? in_array($mod->id, old('modelos_compatibles', []))
                    : $pivot !== null;
                $oficial = old('modelos_compatibles') !== null
                    ? isset(old('compatibilidad_oficial')[$mod->id])
                    : ($pivot?->pivot->es_oficial ?? false);
                $nota    = old('modelos_compatibles') !== null
                    ? (old("compatibilidad_notas.{$mod->id}") ?? '')
                    : ($pivot?->pivot->notas ?? '');
            @endphp
            <div class="flex items-start gap-3 p-2 rounded hover:bg-gray-50">
                <input type="checkbox"
                       name="modelos_compatibles[]"
                       value="{{ $mod->id }}"
                       id="mod_{{ $mod->id }}"
                       {{ $checked ? 'checked' : '' }}
                       class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-600">
                <div class="flex-1 min-w-0">
                    <label for="mod_{{ $mod->id }}" class="text-sm font-medium text-gray-800 cursor-pointer">
                        {{ $mod->marca->name ?? '' }} {{ $mod->nombre_modelo }}
                        <span class="text-xs text-gray-400">({{ $mod->categoria->nombre ?? '—' }})</span>
                    </label>
                    <div class="flex gap-3 mt-1">
                        <label class="flex items-center gap-1 text-xs text-gray-500">
                            <input type="checkbox"
                                   name="compatibilidad_oficial[{{ $mod->id }}]"
                                   value="1"
                                   {{ $oficial ? 'checked' : '' }}
                                   class="w-3 h-3 rounded border-gray-300 text-green-600">
                            Oficial
                        </label>
                        <input type="text"
                               name="compatibilidad_notas[{{ $mod->id }}]"
                               value="{{ $nota }}"
                               placeholder="Notas…"
                               class="form-input py-0.5 text-xs flex-1">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('catalogo-consumibles.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>

</div>
</form>
</div>
@endsection
