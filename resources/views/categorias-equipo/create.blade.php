@extends('layouts.app')
@section('title','Nueva Categoría de Equipo')
@section('page-title','Nueva Categoría de Equipo')

@section('content')
<div class="max-w-lg">
<form method="POST" action="{{ route('categorias-equipo.store') }}">
@csrf
<div class="card">
    <div class="card-body space-y-4">
        <div>
            <label class="form-label">Nombre <span class="text-red-500">*</span></label>
            <input name="nombre" value="{{ old('nombre') }}"
                   class="form-input @error('nombre') border-red-400 @enderror"
                   required autofocus placeholder="Ej. Copiadora, Impresora…">
            @error('nombre')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Código <span class="text-red-500">*</span></label>
            <input name="codigo" value="{{ old('codigo') }}"
                   class="form-input @error('codigo') border-red-400 @enderror"
                   required maxlength="20" placeholder="Ej. MFP, IMP, COPI…"
                   style="text-transform:uppercase">
            @error('codigo')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" rows="3"
                      class="form-input @error('descripcion') border-red-400 @enderror">{{ old('descripcion') }}</textarea>
            @error('descripcion')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('categorias-equipo.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
