@extends('layouts.app')
@section('title','Editar Proveedor')
@section('page-title','Editar Proveedor')

@section('content')
<div class="max-w-lg">
<form method="POST" action="{{ route('suppliers.update', $supplier) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body">
        <label class="form-label">Nombre <span class="text-red-500">*</span></label>
        <input name="name" value="{{ old('name', $supplier->name) }}" class="form-input @error('name') border-red-400 @enderror" required>
        @error('name')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('suppliers.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
