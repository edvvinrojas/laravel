@extends('layouts.app')
@section('title','Editar Equipo TI')
@section('page-title','Editar Equipo TI')

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('ti-equipment.update', $tiEquipment) }}">
@csrf @method('PUT')
<div class="card mb-4">
    <div class="card-header font-semibold">Datos del equipo <span class="font-mono text-blue-700">{{ $tiEquipment->codigo_interno }}</span></div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Tipo *</label>
            <select name="tipo" class="form-select" required>
                @foreach(['PC','LAPTOP','SERVIDOR','IMPRESORA','TELEFONO','TABLET','SWITCH','ROUTER','OTRO'] as $t)
                <option value="{{ $t }}" @selected(old('tipo',$tiEquipment->tipo)===$t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Marca *</label>
            <input name="marca" type="text" value="{{ old('marca',$tiEquipment->marca) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Modelo *</label>
            <input name="modelo" type="text" value="{{ old('modelo',$tiEquipment->modelo) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">No. serie</label>
            <input name="numero_serie" type="text" value="{{ old('numero_serie',$tiEquipment->numero_serie) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Estatus *</label>
            <select name="status" class="form-select" required>
                @foreach(['ACTIVO','BAJA','REPARACION','BODEGA'] as $s)
                <option value="{{ $s }}" @selected(old('status',$tiEquipment->status)===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Asignado a</label>
            <select name="assigned_user_id" class="form-select">
                <option value="">Sin asignar</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(old('assigned_user_id',$tiEquipment->assigned_user_id)==$u->id)>{{ $u->full_name ?: $u->username }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Procesador</label>
            <input name="procesador" type="text" value="{{ old('procesador',$tiEquipment->procesador) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">RAM</label>
            <input name="ram" type="text" value="{{ old('ram',$tiEquipment->ram) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Almacenamiento</label>
            <input name="almacenamiento" type="text" value="{{ old('almacenamiento',$tiEquipment->almacenamiento) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Sistema operativo</label>
            <input name="sistema_operativo" type="text" value="{{ old('sistema_operativo',$tiEquipment->sistema_operativo) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Ubicación</label>
            <input name="ubicacion" type="text" value="{{ old('ubicacion',$tiEquipment->ubicacion) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Fecha de compra</label>
            <input name="fecha_compra" type="date" value="{{ old('fecha_compra', $tiEquipment->fecha_compra?->format('Y-m-d')) }}" class="form-input">
        </div>
        <div class="col-span-2">
            <label class="form-label">Notas</label>
            <textarea name="notas" class="form-input" rows="2">{{ old('notas',$tiEquipment->notas) }}</textarea>
        </div>
    </div>
</div>

{{-- Licencias --}}
@if($licenses->count())
<div class="card mb-4">
    <div class="card-header font-semibold">Licencias asociadas</div>
    <div class="card-body grid grid-cols-2 md:grid-cols-3 gap-2">
        @foreach($licenses as $lic)
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="licenses[]" value="{{ $lic->id }}"
                   @checked(in_array($lic->id, old('licenses', $tiEquipment->licenses->pluck('id')->toArray())))>
            {{ $lic->software }}
            <span class="text-xs text-gray-400">({{ $lic->tipo }})</span>
        </label>
        @endforeach
    </div>
</div>
@endif

<div class="flex gap-3">
    <button type="submit" class="btn-primary">Guardar cambios</button>
    <a href="{{ route('ti-equipment.show', $tiEquipment) }}" class="btn-secondary">Cancelar</a>
</div>
</form>
</div>
@endsection
