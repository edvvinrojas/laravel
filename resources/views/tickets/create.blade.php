@extends('layouts.app')
@section('title','Nuevo Ticket de Atención a Clientes')
@section('page-title','Nuevo Ticket — Atención a Clientes')

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
@csrf
<div class="card">
    <div class="card-header">
        <div>
            <p class="text-xs text-gray-500">Siguiente sugerido</p>
            <h3 class="font-semibold text-lg text-blue-700">{{ $nextCode }}</h3>
        </div>
        <span class="text-xs text-gray-400">Levantado el {{ now()->format('d/m/Y H:i') }}</span>
    </div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="form-label">Código de ticket</label>
            <input name="ticket_code" value="{{ old('ticket_code') }}" class="form-input font-mono" placeholder="Vacío = usar {{ $nextCode }}" list="ticket_codes_list">
            <datalist id="ticket_codes_list">
                @foreach($ticketCodes as $tc)
                    <option value="{{ $tc }}"></option>
                @endforeach
            </datalist>
            <p class="text-xs text-gray-400 mt-1">Puedes escribir un código manual o dejarlo vacío para usar el sugerido.</p>
            @error('ticket_code')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Cliente *</label>
            <select name="client_id" id="clientSel" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Sucursal *</label>
            <select name="branch_id" id="branchSel" class="form-select" required>
                <option value="">Seleccionar cliente primero…</option>
            </select>
        </div>
        <div>
            <label class="form-label">Área</label>
            <select name="area_id" id="areaSel" class="form-select">
                <option value="">— Sin área específica —</option>
            </select>
        </div>
        <div>
            <label class="form-label">Equipo</label>
            <select name="item_id" id="itemSel" class="form-select">
                <option value="">Seleccionar sucursal/área…</option>
            </select>
            <p class="text-xs text-gray-400 mt-1">Se carga automáticamente al elegir sucursal y área.</p>
        </div>

        <div>
            <label class="form-label">Tipo de falla *</label>
            <select name="report_type" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($reportTypes as $t)
                <option value="{{ $t }}" @selected(old('report_type')===$t)>{{ str_replace('_',' ',$t) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Prioridad *</label>
            <select name="priority" class="form-select" required>
                @foreach($priorities as $p)
                <option value="{{ $p }}" @selected(old('priority','NORMAL')===$p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Estado *</label>
            <select name="report_status" class="form-select" required>
                @foreach($statuses as $s)
                @if($s !== 'LISTO')
                <option value="{{ $s }}" @selected(old('report_status','PENDIENTE')===$s)>{{ str_replace('_',' ',$s) }}</option>
                @endif
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Evidencia — archivo</label>
            <input type="file" name="evidence_file" class="form-input"
                   accept="image/*,application/pdf,video/*">
            <p class="text-xs text-gray-400 mt-1">Imagen, PDF o video (máx. 10 MB).</p>
        </div>
        <div>
            <label class="form-label">Evidencia — URL (opcional)</label>
            <input name="evidence_url" value="{{ old('evidence_url') }}" class="form-input" maxlength="500" placeholder="O liga externa…">
        </div>

        <div class="col-span-2">
            <label class="form-label">Explicación del problema *</label>
            <textarea name="description" class="form-input" rows="4" required placeholder="Describe el problema reportado por el cliente…">{{ old('description') }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Levantar ticket</button>
        <a href="{{ route('tickets.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>

@push('scripts')
<script>
const clientSel = document.getElementById('clientSel');
const branchSel = document.getElementById('branchSel');
const areaSel   = document.getElementById('areaSel');
const itemSel   = document.getElementById('itemSel');

let serviceLocations = [];

clientSel.addEventListener('change', function() {
    const clientId = this.value;
    branchSel.innerHTML = '<option value="">Cargando…</option>';
    areaSel.innerHTML   = '<option value="">— Sin área específica —</option>';
    itemSel.innerHTML   = '<option value="">Seleccionar sucursal/área…</option>';
    serviceLocations    = [];

    if (!clientId) { branchSel.innerHTML = '<option value="">Seleccionar cliente primero…</option>'; return; }

    fetch(`/clients/${clientId}/branches`)
        .then(r => r.json())
        .then(branches => {
            branchSel.innerHTML = '<option value="">Seleccionar sucursal…</option>';
            branches.forEach(b => branchSel.innerHTML += `<option value="${b.id}">${b.name}</option>`);
        });
});

branchSel.addEventListener('change', function() {
    const branchId = this.value;
    areaSel.innerHTML = '<option value="">Cargando áreas…</option>';
    itemSel.innerHTML = '<option value="">Cargando equipos…</option>';
    serviceLocations  = [];

    if (!branchId) {
        areaSel.innerHTML = '<option value="">— Sin área específica —</option>';
        itemSel.innerHTML = '<option value="">Seleccionar sucursal…</option>';
        return;
    }

    // Áreas (independiente)
    fetch(`/api/branches/${branchId}/areas`)
        .then(r => r.ok ? r.json() : Promise.reject(r.status))
        .then(areas => {
            areaSel.innerHTML = '<option value="">— Sin área específica —</option>';
            areas.forEach(a => areaSel.innerHTML += `<option value="${a.id}">${a.name}</option>`);
        })
        .catch(() => { areaSel.innerHTML = '<option value="">Error al cargar áreas</option>'; });

    // Equipos (independiente)
    fetch(`/api/branches/${branchId}/service-locations`)
        .then(r => r.ok ? r.json() : Promise.reject(r.status))
        .then(locations => {
            serviceLocations = locations || [];
            refreshItems();
        })
        .catch(() => { itemSel.innerHTML = '<option value="">Error al cargar equipos</option>'; });
});

areaSel.addEventListener('change', refreshItems);

function refreshItems() {
    const areaId = areaSel.value;
    if (!serviceLocations.length) {
        itemSel.innerHTML = '<option value="">Sin equipos asignados a esta sucursal</option>';
        return;
    }

    const filtered = areaId
        ? serviceLocations.filter(l => String(l.area_id) === String(areaId))
        : serviceLocations;

    if (!filtered.length) {
        itemSel.innerHTML = '<option value="">Sin equipos en el área seleccionada</option>';
        return;
    }

    itemSel.innerHTML = '<option value="">Seleccionar equipo…</option>';
    filtered.forEach(l => {
        const label = `${l.brand || ''} ${l.model || ''} — ${l.serie || l.sku || ''}`.trim();
        itemSel.innerHTML += `<option value="${l.item_id}">${label}</option>`;
    });

    // Auto-seleccionar si solo hay un equipo
    if (filtered.length === 1) {
        itemSel.value = filtered[0].item_id;
    }
}
</script>
@endpush
@endsection
