@extends('layouts.app')
@section('title','Nueva Renta')
@section('page-title','Nueva Renta')

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('rents.store') }}">
@csrf
<div class="card">
    <div class="card-header"><h3 class="font-semibold text-sm">Datos del contrato</h3></div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        @php
            $availableItems = $items->filter(fn($i) => $i->location_status === 'BODEGA');
            $unavailableItems = $items->reject(fn($i) => $i->location_status === 'BODEGA');
        @endphp

        <div>
            <label class="form-label">No. Contrato</label>
            <input name="contract_number" value="{{ $nextContract }}" class="form-input bg-gray-50" readonly>
            <p class="text-xs text-gray-400 mt-1">Se genera automáticamente</p>
        </div>

        <div>
            <label class="form-label">Cliente *</label>
            <select name="client_id" id="clientSelect" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('client_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Sucursal</label>
            <select name="branch_id" id="branchSelect" class="form-select">
                <option value="">Seleccione cliente primero…</option>
            </select>
        </div>

        <div>
            <label class="form-label">Área</label>
            <select name="area_id" id="areaSelect" class="form-select">
                <option value="">Seleccione sucursal primero…</option>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="form-label">Equipo *</label>
            <input type="hidden" name="item_id" id="item_id_input" value="{{ old('item_id') }}" required>
            <p class="text-xs text-gray-500 mb-2">Selecciona un equipo.</p>
            @error('item_id')<p class="form-error mb-2">{{ $message }}</p>@enderror

            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Equipos disponibles</p>
            <div class="h-[14.75rem] overflow-y-auto pr-1">
                <div class="grid grid-cols-3 gap-3">
                    @foreach($availableItems as $i)
                        @php $price = $i->cost; @endphp
                        <button
                            type="button"
                            class="equipment-card h-28 flex flex-col justify-between text-left border border-gray-200 rounded-lg p-3 hover:border-blue-300 hover:bg-blue-50/40 transition"
                            data-item-id="{{ $i->id }}"
                            data-item-price="{{ $price ?? '' }}"
                            data-selectable="1"
                        >
                            <div class="text-sm font-semibold text-gray-900 truncate">{{ $i->brand->name ?? '—' }} {{ $i->model }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">Serie: {{ $i->serie ?: '—' }}</div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-[11px] px-2 py-1 rounded bg-gray-100 text-gray-600">{{ $i->location_status ?? 'BODEGA' }}</span>
                                <span class="text-sm font-bold text-blue-700">{{ $price !== null ? '$'.number_format($price, 2) : 'Sin precio' }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>

                @if($unavailableItems->count())
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mt-4 mb-2">No disponibles</p>
                <div class="grid grid-cols-3 gap-3">
                    @foreach($unavailableItems as $i)
                        @php $price = $i->cost; @endphp
                        <button
                            type="button"
                            disabled
                            class="equipment-card h-28 flex flex-col justify-between text-left border border-gray-200 rounded-lg p-3 opacity-55 cursor-not-allowed bg-gray-50"
                            data-item-id="{{ $i->id }}"
                            data-item-price="{{ $price ?? '' }}"
                            data-selectable="0"
                        >
                            <div class="text-sm font-semibold text-gray-700 truncate">{{ $i->brand->name ?? '—' }} {{ $i->model }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">Serie: {{ $i->serie ?: '—' }}</div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-[11px] px-2 py-1 rounded bg-red-100 text-red-700">{{ $i->location_status ?? 'NO DISPONIBLE' }}</span>
                                <span class="text-sm font-bold text-gray-500">{{ $price !== null ? '$'.number_format($price, 2) : 'Sin precio' }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <div id="contadorSection" class="md:col-span-2 grid grid-cols-2 gap-4 hidden">
            <div>
                <label class="form-label">Contador inicial BN</label>
                <input name="contador_inicial_bn" id="contador_bn" type="number" min="0"
                    value="{{ old('contador_inicial_bn', 0) }}" class="form-input">
                <p class="text-xs text-gray-400 mt-1">Lectura actual del equipo al instalarlo</p>
            </div>
            <div>
                <label class="form-label">Contador inicial Color</label>
                <input name="contador_inicial_color" id="contador_color" type="number" min="0"
                    value="{{ old('contador_inicial_color', 0) }}" class="form-input">
                <p class="text-xs text-gray-400 mt-1">Lectura actual del equipo al instalarlo</p>
            </div>
        </div>

        <div>
            <label class="form-label">Renta mensual *</label>
            <input name="rent" type="number" step="0.01" min="0" value="{{ old('rent') }}" class="form-input" required>
            @error('rent')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Estatus *</label>
            <select name="contract_status" class="form-select" required>
                @foreach(['PENDIENTE','SIN_FIRMAR','VIGENTE','FINALIZADO','CANCELADO'] as $s)
                <option value="{{ $s }}" @selected(old('contract_status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Fecha inicio *</label>
            <input name="start_date" type="date" value="{{ old('start_date',date('Y-m-d')) }}" class="form-input" required>
        </div>

        <div>
            <label class="form-label">Fecha fin</label>
            <input name="end_date" type="date" value="{{ old('end_date') }}" class="form-input">
        </div>

        <div class="flex items-center gap-4 pt-5">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_foreign" value="1" @checked(old('is_foreign'))> Foráneo
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="has_print_service" value="1" id="printCheck" @checked(old('has_print_service'))> Servicio de impresión
            </label>
        </div>

        <div id="printFields" class="col-span-2 grid grid-cols-2 gap-4 {{ old('has_print_service') ? '' : 'hidden' }}">
            <div>
                <label class="form-label">Impresiones BN incluidas</label>
                <input name="bn_included" type="number" min="0" value="{{ old('bn_included',0) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Costo por exceso BN</label>
                <input name="bn_cost_per_excess" type="number" step="0.0001" min="0" value="{{ old('bn_cost_per_excess',0) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Impresiones Color incluidas</label>
                <input name="color_included" type="number" min="0" value="{{ old('color_included',0) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Costo por exceso Color</label>
                <input name="color_cost_per_excess" type="number" step="0.0001" min="0" value="{{ old('color_cost_per_excess',0) }}" class="form-input">
            </div>
            <div class="col-span-2">
                <label class="form-label">Notas impresión</label>
                <textarea name="print_notes" class="form-input" rows="2">{{ old('print_notes') }}</textarea>
            </div>
        </div>

    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('rents.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>

</form>
</div>

@push('scripts')
<script>
// Mostrar/ocultar campos de impresión
document.getElementById('printCheck').addEventListener('change', function() {
    document.getElementById('printFields').classList.toggle('hidden', !this.checked);
});

const itemInput = document.getElementById('item_id_input');
const salePriceHintCards = document.querySelectorAll('.equipment-card');

function updateEquipmentCardSelection() {
    const selectedId = itemInput.value;
    salePriceHintCards.forEach(card => {
        const active = card.dataset.itemId === selectedId;
        card.classList.toggle('border-blue-500', active);
        card.classList.toggle('bg-blue-50', active);
        card.classList.toggle('ring-1', active);
        card.classList.toggle('ring-blue-200', active);
    });

    const section = document.getElementById('contadorSection');
    if (!selectedId) {
        section.classList.add('hidden');
        return;
    }
    section.classList.remove('hidden');
}

salePriceHintCards.forEach(card => {
    card.addEventListener('click', function () {
        if (this.dataset.selectable !== '1') return;
        itemInput.value = this.dataset.itemId;
        updateEquipmentCardSelection();
    });
});

updateEquipmentCardSelection();

// Sucursales por cliente
document.getElementById('clientSelect').addEventListener('change', function() {
    const clientId = this.value;
    const branchSel = document.getElementById('branchSelect');
    const areaSel   = document.getElementById('areaSelect');
    branchSel.innerHTML = '<option value="">Cargando…</option>';
    areaSel.innerHTML   = '<option value="">Seleccione sucursal primero…</option>';
    if (!clientId) { branchSel.innerHTML = '<option value="">Seleccione cliente primero…</option>'; return; }
    fetch(`/api/clients/${clientId}/branches`)
        .then(r => r.json())
        .then(data => {
            branchSel.innerHTML = '<option value="">Sin sucursal</option>';
            data.forEach(b => branchSel.innerHTML += `<option value="${b.id}">${b.name}</option>`);
        });
});

// Áreas por sucursal
document.getElementById('branchSelect').addEventListener('change', function() {
    const branchId = this.value;
    const areaSel  = document.getElementById('areaSelect');
    areaSel.innerHTML = '<option value="">Cargando…</option>';
    if (!branchId) { areaSel.innerHTML = '<option value="">Sin área</option>'; return; }
    fetch(`/api/branches/${branchId}/areas`)
        .then(r => r.json())
        .then(data => {
            areaSel.innerHTML = '<option value="">Sin área</option>';
            data.forEach(a => areaSel.innerHTML += `<option value="${a.id}">${a.name}</option>`);
        });
});
</script>
@endpush
@endsection
