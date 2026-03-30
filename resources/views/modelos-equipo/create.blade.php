@extends('layouts.app')
@section('title','Nuevo Modelo de Equipo')
@section('page-title','Nuevo Modelo de Equipo')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('modelos-equipo.store') }}">
@csrf
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
                        <option value="{{ $m->id }}" {{ old('marca_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
                @error('marca_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Categoría <span class="text-red-500">*</span></label>
                <select name="categoria_id" class="form-select @error('categoria_id') border-red-400 @enderror" required>
                    <option value="">— Seleccionar —</option>
                    @foreach($categorias as $c)
                        <option value="{{ $c->id }}" {{ old('categoria_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                    @endforeach
                </select>
                @error('categoria_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Nombre del modelo <span class="text-red-500">*</span></label>
                <input name="nombre_modelo" value="{{ old('nombre_modelo') }}"
                       class="form-input @error('nombre_modelo') border-red-400 @enderror"
                       required placeholder="Ej. ECOSYS M2040dn">
                @error('nombre_modelo')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Nombre comercial</label>
                <input name="nombre_comercial" value="{{ old('nombre_comercial') }}"
                       class="form-input @error('nombre_comercial') border-red-400 @enderror"
                       placeholder="Ej. KYOCERA M2040dn">
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
                    <option value="MONOCROMO"       {{ old('tipo_color') === 'MONOCROMO'       ? 'selected' : '' }}>Monocromo</option>
                    <option value="COLOR"           {{ old('tipo_color') === 'COLOR'           ? 'selected' : '' }}>Color</option>
                    <option value="MONOCROMO_COLOR" {{ old('tipo_color') === 'MONOCROMO_COLOR' ? 'selected' : '' }}>Monocromo + Color</option>
                </select>
                @error('tipo_color')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Tecnología</label>
                <select name="tecnologia" class="form-select @error('tecnologia') border-red-400 @enderror">
                    <option value="">— Seleccionar —</option>
                    <option value="LASER"     {{ old('tecnologia') === 'LASER'     ? 'selected' : '' }}>Láser</option>
                    <option value="INKJET"    {{ old('tecnologia') === 'INKJET'    ? 'selected' : '' }}>Inkjet</option>
                    <option value="MATRICIAL" {{ old('tecnologia') === 'MATRICIAL' ? 'selected' : '' }}>Matricial</option>
                    <option value="LED"       {{ old('tecnologia') === 'LED'       ? 'selected' : '' }}>LED</option>
                    <option value="TERMICA"   {{ old('tecnologia') === 'TERMICA'   ? 'selected' : '' }}>Térmica</option>
                </select>
                @error('tecnologia')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Formato máximo</label>
                <select name="formato_max" class="form-select @error('formato_max') border-red-400 @enderror">
                    <option value="">— Seleccionar —</option>
                    @foreach(['A4','A3','CARTA','OFICIO','A2','A1','A0'] as $f)
                        <option value="{{ $f }}" {{ old('formato_max') === $f ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
                @error('formato_max')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="form-label">Vel. B/N (ppm)</label>
                <input type="number" name="velocidad_bn_ppm" value="{{ old('velocidad_bn_ppm') }}"
                       class="form-input @error('velocidad_bn_ppm') border-red-400 @enderror"
                       min="0" placeholder="0">
                @error('velocidad_bn_ppm')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Vel. Color (ppm)</label>
                <input type="number" name="velocidad_color_ppm" value="{{ old('velocidad_color_ppm') }}"
                       class="form-input @error('velocidad_color_ppm') border-red-400 @enderror"
                       min="0" placeholder="0">
                @error('velocidad_color_ppm')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Vida útil (páginas)</label>
                <input type="number" name="vida_util_paginas" value="{{ old('vida_util_paginas') }}"
                       class="form-input @error('vida_util_paginas') border-red-400 @enderror"
                       min="0" placeholder="0">
                @error('vida_util_paginas')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Capacidades --}}
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
                           {{ old($field) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-600">
                    <span class="text-sm text-gray-700">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Descripción y estado --}}
<div class="card">
    <div class="card-body space-y-4">
        <div>
            <label class="form-label">Descripción / notas</label>
            <textarea name="descripcion" rows="3"
                      class="form-input @error('descripcion') border-red-400 @enderror">{{ old('descripcion') }}</textarea>
            @error('descripcion')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" id="es_activo" name="es_activo" value="1"
                   {{ old('es_activo', '1') ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-gray-300 text-blue-600">
            <label for="es_activo" class="form-label mb-0">Modelo activo</label>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('modelos-equipo.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>

</div>
</form>
</div>
@endsection
