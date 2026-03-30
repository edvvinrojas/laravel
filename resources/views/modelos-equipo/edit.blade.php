@extends('layouts.app')
@section('title','Editar Modelo')
@section('page-title','Editar Modelo')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('modelos-equipo.update', $modelo) }}">
@csrf @method('PUT')
<div class="space-y-4">

{{-- Identificación --}}
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Identificación</h3></div>
    <div class="card-body space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Marca <span class="text-red-500">*</span></label>
                <select name="marca_id" class="form-select @error('marca_id') border-red-400 @enderror" required>
                    <option value="">— Seleccionar —</option>
                    @foreach($marcas as $m)
                        <option value="{{ $m->id }}" {{ old('marca_id', $modelo->marca_id) == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
                @error('marca_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Categoría <span class="text-red-500">*</span></label>
                <select name="categoria_id" class="form-select @error('categoria_id') border-red-400 @enderror" required>
                    <option value="">— Seleccionar —</option>
                    @foreach($categorias as $c)
                        <option value="{{ $c->id }}" {{ old('categoria_id', $modelo->categoria_id) == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                    @endforeach
                </select>
                @error('categoria_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Nombre del modelo <span class="text-red-500">*</span></label>
                <input name="nombre_modelo" value="{{ old('nombre_modelo', $modelo->nombre_modelo) }}"
                       class="form-input @error('nombre_modelo') border-red-400 @enderror" required>
                @error('nombre_modelo')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Nombre comercial</label>
                <input name="nombre_comercial" value="{{ old('nombre_comercial', $modelo->nombre_comercial) }}"
                       class="form-input @error('nombre_comercial') border-red-400 @enderror">
                @error('nombre_comercial')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Especificaciones --}}
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Especificaciones técnicas</h3></div>
    <div class="card-body space-y-4">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="form-label">Tipo de color <span class="text-red-500">*</span></label>
                <select name="tipo_color" class="form-select @error('tipo_color') border-red-400 @enderror" required>
                    <option value="">— Seleccionar —</option>
                    <option value="MONOCROMO"       {{ old('tipo_color', $modelo->tipo_color) === 'MONOCROMO'       ? 'selected' : '' }}>Monocromo</option>
                    <option value="COLOR"           {{ old('tipo_color', $modelo->tipo_color) === 'COLOR'           ? 'selected' : '' }}>Color</option>
                    <option value="MONOCROMO_COLOR" {{ old('tipo_color', $modelo->tipo_color) === 'MONOCROMO_COLOR' ? 'selected' : '' }}>Monocromo + Color</option>
                </select>
                @error('tipo_color')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Tecnología</label>
                <select name="tecnologia" class="form-select @error('tecnologia') border-red-400 @enderror">
                    <option value="">— Seleccionar —</option>
                    @foreach(['LASER' => 'Láser', 'INKJET' => 'Inkjet', 'MATRICIAL' => 'Matricial', 'LED' => 'LED', 'TERMICA' => 'Térmica'] as $val => $lbl)
                        <option value="{{ $val }}" {{ old('tecnologia', $modelo->tecnologia) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('tecnologia')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Formato máximo</label>
                <select name="formato_max" class="form-select @error('formato_max') border-red-400 @enderror">
                    <option value="">— Seleccionar —</option>
                    @foreach(['A4','A3','CARTA','OFICIO','A2','A1','A0'] as $f)
                        <option value="{{ $f }}" {{ old('formato_max', $modelo->formato_max) === $f ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
                @error('formato_max')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="form-label">Vel. B/N (ppm)</label>
                <input type="number" name="velocidad_bn_ppm" value="{{ old('velocidad_bn_ppm', $modelo->velocidad_bn_ppm) }}"
                       class="form-input" min="0">
            </div>
            <div>
                <label class="form-label">Vel. Color (ppm)</label>
                <input type="number" name="velocidad_color_ppm" value="{{ old('velocidad_color_ppm', $modelo->velocidad_color_ppm) }}"
                       class="form-input" min="0">
            </div>
            <div>
                <label class="form-label">Vida útil (páginas)</label>
                <input type="number" name="vida_util_paginas" value="{{ old('vida_util_paginas', $modelo->vida_util_paginas) }}"
                       class="form-input" min="0">
            </div>
        </div>
        <div>
            <label class="form-label mb-2">Capacidades</label>
            <div class="flex flex-wrap gap-4">
                @foreach([
                    ['tiene_escaner', 'Escáner'],
                    ['tiene_fax',     'Fax'],
                    ['tiene_duplex',  'Dúplex automático'],
                    ['tiene_red',     'Red (Ethernet)'],
                    ['tiene_wifi',    'Wi-Fi'],
                ] as [$field, $label])
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="{{ $field }}" value="1"
                           {{ old($field, $modelo->$field) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-600">
                    <span class="text-sm text-gray-700">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body space-y-4">
        <div>
            <label class="form-label">Descripción / notas</label>
            <textarea name="descripcion" rows="3"
                      class="form-input @error('descripcion') border-red-400 @enderror">{{ old('descripcion', $modelo->descripcion) }}</textarea>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" id="es_activo" name="es_activo" value="1"
                   {{ old('es_activo', $modelo->es_activo) ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-gray-300 text-blue-600">
            <label for="es_activo" class="form-label mb-0">Modelo activo</label>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('modelos-equipo.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>

</div>
</form>
</div>
@endsection
