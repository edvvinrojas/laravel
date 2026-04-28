@extends('layouts.app')
@section('title','Editar Licencia')
@section('page-title','Editar Licencia')

@section('content')
<div class="mb-4">
    <a href="{{ route('ti-equipment.licenses') }}" class="btn-secondary">← Volver a licencias</a>
</div>

<div class="max-w-3xl">
<form method="POST" action="{{ route('ti-equipment.licenses.update', $license) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="form-label">Software *</label>
            <input name="software" type="text" value="{{ old('software', $license->software) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Tipo *</label>
            <select name="tipo" class="form-select" required>
                @foreach(['OFFICE','ANTIVIRUS','OS','OTRO'] as $t)
                    <option value="{{ $t }}" @selected(old('tipo', $license->tipo) === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Cantidad de licencias *</label>
            <input name="cantidad_licencias" type="number" min="1" value="{{ old('cantidad_licencias', $license->cantidad_licencias) }}" class="form-input" required>
            <p class="text-xs text-gray-500 mt-1">Asignadas: {{ $license->equipment()->count() }}. No puedes bajar este número por debajo de las asignadas.</p>
        </div>
        <div>
            <label class="form-label">Clave / Product Key</label>
            <input name="clave_licencia" type="text" value="{{ old('clave_licencia', $license->clave_licencia) }}" class="form-input font-mono">
        </div>
        <div>
            <label class="form-label">Proveedor</label>
            <input name="proveedor" type="text" value="{{ old('proveedor', $license->proveedor) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Fecha de vencimiento</label>
            <input name="fecha_vencimiento" type="date" value="{{ old('fecha_vencimiento', $license->fecha_vencimiento?->format('Y-m-d')) }}" class="form-input">
        </div>
        <div class="col-span-3">
            <label class="form-label">Notas</label>
            <textarea name="notas" class="form-input" rows="2">{{ old('notas', $license->notas) }}</textarea>
        </div>
        <div class="flex items-center gap-2 pt-3">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $license->is_active))>
            <label class="text-sm">Activa</label>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('ti-equipment.licenses') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
