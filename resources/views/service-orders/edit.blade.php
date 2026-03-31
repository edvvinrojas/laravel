@extends('layouts.app')
@section('title','Editar Orden de Servicio')
@section('page-title','Editar Orden de Servicio')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('service-orders.update', $serviceOrder) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-header"><h3 class="font-semibold text-sm">Actualizar orden #{{ $serviceOrder->id }}</h3></div>
    <div class="card-body grid grid-cols-1 gap-4">
        <div>
            <label class="form-label">Estatus</label>
            <select name="status" class="form-select">
                <option value="PENDIENTE" @selected($serviceOrder->status==='PENDIENTE')>Pendiente</option>
                <option value="COMPLETADO" @selected($serviceOrder->status==='COMPLETADO')>Completado</option>
            </select>
        </div>
        <div>
            <label class="form-label">Diagnóstico / Acción correctiva</label>
            <textarea name="diagnostico_accion" class="form-input" rows="4">{{ old('diagnostico_accion', $serviceOrder->diagnostico_accion) }}</textarea>
        </div>
        <div>
            <label class="form-label">Material pendiente</label>
            <textarea name="pendiente_material" class="form-input" rows="2">{{ old('pendiente_material', $serviceOrder->pendiente_material) }}</textarea>
        </div>
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 text-sm font-medium">
                <input type="checkbox" name="queda_pendiente" value="1" id="quedaPend" class="form-checkbox" @checked(old('queda_pendiente', $serviceOrder->queda_pendiente))>
                ¿Queda algo pendiente?
            </label>
        </div>
        <div id="descPend" class="{{ old('queda_pendiente', $serviceOrder->queda_pendiente) ? '' : 'hidden' }}">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion_pendiente" class="form-input" rows="2">{{ old('descripcion_pendiente', $serviceOrder->descripcion_pendiente) }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('service-orders.show', $serviceOrder) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@push('scripts')
<script>
document.getElementById('quedaPend').addEventListener('change', function() {
    document.getElementById('descPend').classList.toggle('hidden', !this.checked);
});
</script>
@endpush
@endsection
