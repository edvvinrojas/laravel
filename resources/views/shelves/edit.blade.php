@extends('layouts.app')
@section('title','Editar Estante')
@section('page-title','Editar Estante')

@section('content')
<div class="max-w-lg">
<form method="POST" action="{{ route('shelves.update', $shelf) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body space-y-4">
        <div>
            <label class="form-label">Nombre <span class="text-red-500">*</span></label>
            <input name="name" value="{{ old('name', $shelf->name) }}" class="form-input @error('name') border-red-400 @enderror" required>
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Sección <span class="text-red-500">*</span></label>
            <select name="section" class="form-select @error('section') border-red-400 @enderror" required>
                <option value="">Seleccionar…</option>
                @foreach($sections as $s)
                <option value="{{ $s }}" @selected(old('section',$shelf->section)==$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
            @error('section')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-input" rows="2">{{ old('description', $shelf->description) }}</textarea>
        </div>
        <div class="flex items-center gap-2">
            <input name="is_active" type="checkbox" id="is_active" value="1" class="form-checkbox" @checked(old('is_active',$shelf->is_active))>
            <label for="is_active" class="form-label mb-0">Activo</label>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('shelves.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
