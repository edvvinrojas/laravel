@extends('layouts.app')
@section('title','Editar Periférico')
@section('page-title','Editar Periférico')

@section('content')
<div class="mb-4">
    <a href="{{ route('ti-equipment.show', $tiEquipment) }}" class="btn-secondary">← Volver al equipo</a>
</div>

<div class="max-w-2xl">
<form method="POST" action="{{ route('ti-equipment.peripherals.update', [$tiEquipment, $peripheral]) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Código *</label>
            <input name="codigo" type="text" value="{{ old('codigo', $peripheral->codigo) }}" class="form-input font-mono" required>
        </div>
        <div>
            <label class="form-label">Tipo *</label>
            <select name="tipo" class="form-select" required>
                @foreach(['MONITOR','TECLADO','MOUSE','CARGADOR','DOCKING','HEADSET','CAMARA','ELIMINADOR','OTRO'] as $t)
                    <option value="{{ $t }}" @selected(old('tipo', $peripheral->tipo) === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Marca</label>
            <input name="marca" type="text" value="{{ old('marca', $peripheral->marca) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Modelo</label>
            <input name="modelo" type="text" value="{{ old('modelo', $peripheral->modelo) }}" class="form-input">
        </div>
        <div class="col-span-2">
            <label class="form-label">Número de serie</label>
            <input name="numero_serie" type="text" value="{{ old('numero_serie', $peripheral->numero_serie) }}" class="form-input">
        </div>
        <div class="col-span-2">
            <label class="form-label">Notas</label>
            <textarea name="notas" class="form-input" rows="2">{{ old('notas', $peripheral->notas) }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('ti-equipment.show', $tiEquipment) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
