@extends('layouts.app')
@section('title','Nueva Marca')
@section('page-title','Nueva Marca')

@section('content')
<div class="max-w-lg">
<form method="POST" action="{{ route('brands.store') }}">
@csrf
<div class="card">
    <div class="card-body space-y-4">
        <div>
            <label class="form-label">Nombre <span class="text-red-500">*</span></label>
            <input name="name" value="{{ old('name') }}" class="form-input @error('name') border-red-400 @enderror" required autofocus>
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Prefijo <span class="text-red-500">*</span></label>
            <input name="prefix" value="{{ old('prefix') }}" class="form-input @error('prefix') border-red-400 @enderror" maxlength="50" required placeholder="HP, CANON, KYOCERA…">
            @error('prefix')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('brands.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
