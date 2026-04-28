@extends('layouts.app')
@section('title','Editar Ticket')
@section('page-title','Editar Ticket — '.$ticket->ticket_code)

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('tickets.update',$ticket) }}" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="card">
    <div class="card-header">
        <div>
            <p class="text-xs text-gray-500">ID</p>
            <h3 class="font-semibold text-lg text-blue-700">{{ $ticket->ticket_code }}</h3>
        </div>
        <span class="text-xs text-gray-400">Levantado el {{ $ticket->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Cliente *</label>
            <select name="client_id" id="clientSel" class="form-select" required>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(old('client_id',$ticket->client_id)==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Sucursal *</label>
            <select name="branch_id" id="branchSel" class="form-select" required>
                @foreach($branches as $b)
                <option value="{{ $b->id }}" @selected(old('branch_id',$ticket->branch_id)==$b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Área</label>
            <select name="area_id" id="areaSel" class="form-select">
                <option value="">— Sin área específica —</option>
                @foreach($areas as $a)
                <option value="{{ $a->id }}" @selected(old('area_id',$ticket->area_id)==$a->id)>{{ $a->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Equipo</label>
            <select name="item_id" id="itemSel" class="form-select" data-current="{{ $ticket->item_id }}">
                <option value="">— Sin equipo asignado —</option>
                @if($ticket->item)
                <option value="{{ $ticket->item->id }}" selected>
                    {{ $ticket->item->brand?->name }} {{ $ticket->item->model }} — {{ $ticket->item->serie ?? $ticket->item->sku }}
                </option>
                @endif
            </select>
        </div>

        <div>
            <label class="form-label">Tipo de falla *</label>
            <select name="report_type" class="form-select" required>
                @foreach($reportTypes as $t)
                <option value="{{ $t }}" @selected(old('report_type',$ticket->report_type)===$t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Prioridad *</label>
            <select name="priority" class="form-select" required>
                @foreach($priorities as $p)
                <option value="{{ $p }}" @selected(old('priority',$ticket->priority ?? 'NORMAL')===$p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Estado *</label>
            <select name="report_status" class="form-select" required>
                @foreach($statuses as $s)
                <option value="{{ $s }}" @selected(old('report_status',$ticket->report_status)===$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Evidencia actual</label>
                @php $isFile = $ticket->evidence && !str_starts_with($ticket->evidence, 'http'); @endphp
                @if($ticket->evidence)
                    @if($isFile)
                        <a href="{{ Storage::url($ticket->evidence) }}" target="_blank" class="text-blue-600 underline text-sm break-all">
                            Ver archivo actual
                        </a>
                    @else
                        <a href="{{ $ticket->evidence }}" target="_blank" class="text-blue-600 underline text-sm break-all">
                            {{ $ticket->evidence }}
                        </a>
                    @endif
                    <label class="flex items-center gap-2 text-xs text-gray-600 mt-2">
                        <input type="checkbox" name="evidence_remove" value="1"> Eliminar evidencia actual
                    </label>
                @else
                    <p class="text-sm text-gray-400">Sin evidencia.</p>
                @endif
            </div>
            <div>
                <label class="form-label">Subir archivo nuevo</label>
                <input type="file" name="evidence_file" class="form-input"
                       accept="image/*,application/pdf,video/*">
                <label class="form-label mt-2">O URL externa</label>
                <input name="evidence_url" value="{{ old('evidence_url', $isFile ? '' : $ticket->evidence) }}" class="form-input" maxlength="500" placeholder="https://…">
            </div>
        </div>

        <div class="col-span-2">
            <label class="form-label">Explicación del problema *</label>
            <textarea name="description" class="form-input" rows="3" required>{{ old('description',$ticket->description) }}</textarea>
        </div>
        <div class="col-span-2">
            <label class="form-label">Acción correctiva</label>
            <textarea name="corrective_action" class="form-input" rows="2">{{ old('corrective_action',$ticket->corrective_action) }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('tickets.show',$ticket) }}" class="btn-secondary">Cancelar</a>
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
    fetch(`/clients/${clientId}/branches`)
        .then(r => r.json())
        .then(branches => {
            branchSel.innerHTML = '';
            branches.forEach(b => branchSel.innerHTML += `<option value="${b.id}">${b.name}</option>`);
            branchSel.dispatchEvent(new Event('change'));
        });
});

branchSel.addEventListener('change', function() {
    const branchId = this.value;
    if (!branchId) return;

    fetch(`/api/branches/${branchId}/areas`)
        .then(r => r.ok ? r.json() : Promise.reject(r.status))
        .then(areas => {
            areaSel.innerHTML = '<option value="">— Sin área específica —</option>';
            areas.forEach(a => areaSel.innerHTML += `<option value="${a.id}">${a.name}</option>`);
        })
        .catch(() => { areaSel.innerHTML = '<option value="">Error al cargar áreas</option>'; });

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
    const areaId  = areaSel.value;
    const current = itemSel.dataset.current;

    const filtered = areaId
        ? serviceLocations.filter(l => String(l.area_id) === String(areaId))
        : serviceLocations;

    itemSel.innerHTML = '<option value="">— Sin equipo asignado —</option>';
    filtered.forEach(l => {
        const label = `${l.brand || ''} ${l.model || ''} — ${l.serie || l.sku || ''}`.trim();
        const sel = String(l.item_id) === String(current) ? 'selected' : '';
        itemSel.innerHTML += `<option value="${l.item_id}" ${sel}>${label}</option>`;
    });
}
</script>
@endpush
@endsection
