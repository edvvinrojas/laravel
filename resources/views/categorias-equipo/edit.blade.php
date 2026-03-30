@extends('layouts.app')
@section('title','Editar Categoría')
@section('page-title','Editar Categoría')

@section('content')
<div class="max-w-lg">
<form method="POST" action="{{ route('categorias-equipo.update', $categoria) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body space-y-4">
        <div>
            <label class="form-label">Nombre <span class="text-red-500">*</span></label>
            <input name="nombre" value="{{ old('nombre', $categoria->nombre) }}"
                   class="form-input @error('nombre') border-red-400 @enderror"
                   required autofocus>
            @error('nombre')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Código <span class="text-red-500">*</span></label>
            <input name="codigo" value="{{ old('codigo', $categoria->codigo) }}"
                   class="form-input @error('codigo') border-red-400 @enderror"
                   required maxlength="20" style="text-transform:uppercase">
            @error('codigo')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" rows="3"
                      class="form-input @error('descripcion') border-red-400 @enderror">{{ old('descripcion', $categoria->descripcion) }}</textarea>
            @error('descripcion')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" id="es_activo" name="es_activo" value="1"
                   {{ old('es_activo', $categoria->es_activo) ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-gray-300 text-blue-600">
            <label for="es_activo" class="form-label mb-0">Categoría activa</label>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('categorias-equipo.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
