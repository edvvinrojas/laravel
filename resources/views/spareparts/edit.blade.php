@extends('layouts.app')
@section('title','Editar Refacción')
@section('page-title','Editar Refacción')

@section('content')
<div class="mb-4">
    <a href="{{ route('spareparts.index') }}" class="btn-secondary">← Volver a refacciones</a>
</div>
<div class="max-w-xl">
<form method="POST" action="{{ route('spareparts.update', $sparepart) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div class="col-span-2">
            <label class="form-label">Nombre *</label>
            <input name="name" value="{{ old('name', $sparepart->name) }}" class="form-input" required>
        </div>

        <div>
            <label class="form-label">Código</label>
            <input name="code" value="{{ old('code', $sparepart->code) }}" class="form-input">
        </div>

        <div>
            <label class="form-label">Color</label>
            <input name="color" value="{{ old('color', $sparepart->color) }}" class="form-input">
        </div>

        <div>
            <label class="form-label">Marca</label>
            <input name="brand" value="{{ old('brand', $sparepart->brand) }}" class="form-input">
        </div>

        <div>
            <label class="form-label">Proveedor</label>
            <input name="supplier" value="{{ old('supplier', $sparepart->supplier) }}" class="form-input">
        </div>

        <div class="col-span-2">
            <label class="form-label">Equipo compatible</label>
            <input name="equipment" value="{{ old('equipment', $sparepart->equipment) }}" class="form-input">
        </div>

        <div class="col-span-2">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-input" rows="3">{{ old('description', $sparepart->description) }}</textarea>
        </div>

    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('spareparts.show', $sparepart) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>

@endsection
