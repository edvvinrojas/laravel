@extends('layouts.app')
@section('title','Nueva Refacción')
@section('page-title','Nueva Refacción')

@section('content')
<div class="max-w-xl">
<form method="POST" action="{{ route('spareparts.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2"><label class="form-label">Nombre *</label><input name="name" value="{{ old('name') }}" class="form-input" required>@error('name')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><label class="form-label">Código</label><input name="code" value="{{ old('code') }}" class="form-input">@error('code')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><label class="form-label">Marca</label><input name="brand" value="{{ old('brand') }}" class="form-input"></div>
        <div><label class="form-label">Color</label><input name="color" value="{{ old('color') }}" class="form-input"></div>
        <div><label class="form-label">Proveedor</label><input name="supplier" value="{{ old('supplier') }}" class="form-input"></div>
        <div class="col-span-2"><label class="form-label">Equipo compatible</label><input name="equipment" value="{{ old('equipment') }}" class="form-input"></div>
        <div class="col-span-2"><label class="form-label">Descripción</label><textarea name="description" class="form-input" rows="3">{{ old('description') }}</textarea></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('spareparts.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
