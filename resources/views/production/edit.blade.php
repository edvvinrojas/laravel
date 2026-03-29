@extends('layouts.app')
@section('title','Editar Plan')
@section('page-title','Editar Plan de Producción')

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('production.update', $production) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="form-label">Cliente *</label>
            <select name="client_id" id="client_id" class="form-select" required onchange="loadBranches(this.value)">
                <option value="">Seleccionar…</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(old('client_id',$production->client_id)==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Sucursal *</label>
            <select name="branch_id" id="branch_id" class="form-select" required>
                @if($production->branch)
                <option value="{{ $production->branch_id }}" selected>{{ $production->branch->name }}</option>
                @endif
            </select>
        </div>

        <div>
            <label class="form-label">Tipo de servicio *</label>
            <select name="service_type_id" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($serviceTypes as $s)
                <option value="{{ $s->id }}" @selected(old('service_type_id',$production->service_type_id)==$s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Fecha y hora de visita *</label>
            <input name="visit_date" type="datetime-local"
                   value="{{ old('visit_date', $production->visit_date->format('Y-m-d\TH:i')) }}"
                   class="form-input" required>
        </div>

        <div>
            <label class="form-label">Estado de asistencia *</label>
            <select name="attendance_status" class="form-select" required>
                <option value="PENDIENTE" @selected(old('attendance_status',$production->attendance_status)==='PENDIENTE')>Pendiente</option>
                <option value="VISITADO" @selected(old('attendance_status',$production->attendance_status)==='VISITADO')>Visitado</option>
                <option value="NO_QUEDO" @selected(old('attendance_status',$production->attendance_status)==='NO_QUEDO')>No quedó</option>
            </select>
        </div>

        <div class="col-span-2">
            <label class="form-label">Técnicos asignados</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 border border-gray-200 rounded-lg bg-gray-50">
                @php $assigned = old('user_ids', $production->users->pluck('id')->toArray()); @endphp
                @foreach($users as $u)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="user_ids[]" value="{{ $u->id }}"
                           class="form-checkbox" @checked(in_array($u->id, $assigned))>
                    {{ $u->full_name }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="col-span-2">
            <label class="form-label">Descripción / Notas</label>
            <textarea name="description" class="form-input" rows="3">{{ old('description', $production->description) }}</textarea>
        </div>

    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('production.show', $production) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@push('scripts')
<script>
function loadBranches(clientId) {
    const branchSel = document.getElementById('branch_id');
    if (!clientId) return;
    fetch(`/clients/${clientId}/branches`)
        .then(r => r.json())
        .then(data => {
            branchSel.innerHTML = '<option value="">Seleccionar…</option>';
            data.forEach(b => {
                branchSel.innerHTML += `<option value="${b.id}">${b.name}${b.is_main ? ' (Principal)' : ''}</option>`;
            });
        });
}
</script>
@endpush
@endsection
