@extends('layouts.app')
@section('title','Nuevo Tipo de Servicio')
@section('page-title','Nuevo Tipo de Servicio')

@section('content')
<div class="max-w-lg">
<form method="POST" action="{{ route('service-types.store') }}">
@csrf
<div class="card">
    <div class="card-body space-y-4">
        <div>
            <label class="form-label">Nombre *</label>
            <input name="name" type="text" value="{{ old('name') }}" class="form-input" required>
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-input" rows="2">{{ old('description') }}</textarea>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" class="form-checkbox"
                   @checked(old('is_active', true))>
            <label for="is_active" class="text-sm text-gray-700">Activo</label>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('service-types.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
