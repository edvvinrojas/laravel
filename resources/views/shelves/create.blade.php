@extends('layouts.app')
@section('title','Nuevo Estante')
@section('page-title','Nuevo Estante')

@section('content')
<div class="mb-4">
    <a href="{{ route('shelves.index') }}" class="btn-secondary">← Volver a estantes</a>
</div>
<div class="max-w-lg">
<form method="POST" action="{{ route('shelves.store') }}">
@csrf
<div class="card">
    <div class="card-body space-y-4">
        <div>
            <label class="form-label">Nombre <span class="text-red-500">*</span></label>
            <input name="name" value="{{ old('name') }}" class="form-input @error('name') border-red-400 @enderror" required autofocus placeholder="Ej: Repisa A">
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Sección <span class="text-red-500">*</span></label>
            <select name="section" class="form-select @error('section') border-red-400 @enderror" required>
                <option value="">Seleccionar…</option>
                @foreach($sections as $s)
                <option value="{{ $s }}" @selected(old('section')==$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
            @error('section')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-input" rows="2">{{ old('description') }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('shelves.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
