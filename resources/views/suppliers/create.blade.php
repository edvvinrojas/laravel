@extends('layouts.app')
@section('title','Nuevo Proveedor')
@section('page-title','Nuevo Proveedor')

@section('content')
<div class="max-w-lg">
<form method="POST" action="{{ route('suppliers.store') }}">
@csrf
<div class="card">
    <div class="card-body">
        <label class="form-label">Nombre <span class="text-red-500">*</span></label>
        <input name="name" value="{{ old('name') }}" class="form-input @error('name') border-red-400 @enderror" required autofocus>
        @error('name')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('suppliers.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
