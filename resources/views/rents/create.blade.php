@extends('layouts.app')
@section('title','Nueva Renta')
@section('page-title','Nueva Renta')

@section('content')
<div class="w-full max-w-7xl mx-auto px-2 sm:px-4">
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
            <input
                type="text"
                id="clientSearchInput"
                class="form-input"
                placeholder="Buscar cliente por nombre..."
                autocomplete="off"
            >
            <div id="clientSearchResults" class="mt-1 max-h-44 overflow-y-auto rounded-md border border-gray-200 bg-white shadow-sm hidden"></div>
            <select name="client_id" id="clientSelect" class="sr-only" required tabindex="-1" aria-hidden="true">
                <option value="">Seleccionar…</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('client_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label class="form-label">Equipos *</label>
            <p class="text-xs text-gray-500 mb-2">Selecciona uno o varios equipos y asigna su sucursal/area.</p>
            @error('item_rows')<p class="form-error mb-2">{{ $message }}</p>@enderror

            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Equipos disponibles</p>
            <div class="h-[14.75rem] overflow-y-auto pr-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($availableItems as $i)
                        @php $price = $i->cost; @endphp
                        <button
                            type="button"
                            class="equipment-card h-28 flex flex-col justify-between text-left border border-gray-200 rounded-lg p-3 hover:border-blue-300 hover:bg-blue-50/40 transition"
                            data-item-id="{{ $i->id }}"
                            data-item-price="{{ $price ?? '' }}"
                            data-item-label="{{ trim(($i->brand->name ?? '').' '.$i->model.' | Serie: '.($i->serie ?: 'N/A')) }}"
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
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
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

            <div class="mt-4 border border-gray-200 rounded-lg p-3 bg-gray-50/60">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Asignacion por equipo</p>
                <div id="itemRowsContainer" class="space-y-3"></div>
                <p id="itemRowsEmpty" class="text-xs text-gray-500">No hay equipos seleccionados.</p>
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

const clientSelect = document.getElementById('clientSelect');
const clientSearchInput = document.getElementById('clientSearchInput');
const clientSearchResults = document.getElementById('clientSearchResults');
const equipmentCards = document.querySelectorAll('.equipment-card');
const itemRowsContainer = document.getElementById('itemRowsContainer');
const itemRowsEmpty = document.getElementById('itemRowsEmpty');
const selectedRows = new Map();
const branchesCache = new Map();
const areasCache = new Map();

function normalizeText(value) {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

function setupClientSearch() {
    if (!clientSelect || !clientSearchInput || !clientSearchResults) return;

    const baseOptions = Array.from(clientSelect.options).map((option) => ({
        value: option.value,
        text: option.text,
    }));
    const selectableOptions = baseOptions.filter((option) => option.value !== '');

    const escapeHtml = (value) => String(value || '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

    const hideResults = () => {
        clientSearchResults.classList.add('hidden');
    };

    const applySelection = (value, text) => {
        const previousValue = String(clientSelect.value || '');
        const nextValue = String(value || '');
        clientSelect.value = nextValue;
        clientSearchInput.value = text || '';
        hideResults();
        if (previousValue !== nextValue) {
            clientSelect.dispatchEvent(new Event('change'));
        }
    };

    const renderClientOptions = (query = '') => {
        const normalizedQuery = normalizeText(query.trim());
        const matches = selectableOptions.filter((option) => (
            !normalizedQuery || normalizeText(option.text).includes(normalizedQuery)
        ));

        if (matches.length === 0) {
            clientSearchResults.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">Sin coincidencias</div>';
            return;
        }

        clientSearchResults.innerHTML = matches.map((option) => (
            `<button type="button" class="block w-full text-left px-3 py-2 text-sm hover:bg-blue-50" data-client-value="${escapeHtml(option.value)}" data-client-text="${escapeHtml(option.text)}">${escapeHtml(option.text)}</button>`
        )).join('');

        clientSearchResults.querySelectorAll('[data-client-value]').forEach((item) => {
            item.addEventListener('click', () => {
                applySelection(item.dataset.clientValue, item.dataset.clientText);
            });
        });
    };

    const selectedOption = selectableOptions.find((option) => String(option.value) === String(clientSelect.value));
    if (selectedOption) {
        clientSearchInput.value = selectedOption.text;
    }

    clientSearchInput.addEventListener('focus', () => {
        renderClientOptions(clientSearchInput.value || '');
        clientSearchResults.classList.remove('hidden');
    });

    clientSearchInput.addEventListener('click', () => {
        renderClientOptions(clientSearchInput.value || '');
        clientSearchResults.classList.remove('hidden');
    });

    clientSearchInput.addEventListener('input', (event) => {
        const value = event.target.value || '';
        const currentOption = selectableOptions.find((option) => String(option.value) === String(clientSelect.value));
        if (!value.trim()) {
            applySelection('', '');
        } else if (currentOption && normalizeText(value) !== normalizeText(currentOption.text)) {
            clientSelect.value = '';
        }
        renderClientOptions(value);
        clientSearchResults.classList.remove('hidden');
    });

    document.addEventListener('click', (event) => {
        if (!clientSearchInput.contains(event.target) && !clientSearchResults.contains(event.target)) {
            hideResults();
        }
    });

    hideResults();
}

const initialRows = @json(old('item_rows', []));
initialRows.forEach(row => {
    if (!row.item_id) return;
    selectedRows.set(String(row.item_id), {
        item_id: String(row.item_id),
        branch_id: row.branch_id ? String(row.branch_id) : '',
        area_id: row.area_id ? String(row.area_id) : '',
        contador_inicial_bn: row.contador_inicial_bn ? String(row.contador_inicial_bn) : '0',
        contador_inicial_color: row.contador_inicial_color ? String(row.contador_inicial_color) : '0',
        has_print_service: !!row.has_print_service,
        bn_included: row.bn_included ? String(row.bn_included) : '0',
        bn_cost_per_excess: row.bn_cost_per_excess ? String(row.bn_cost_per_excess) : '0',
        color_included: row.color_included ? String(row.color_included) : '0',
        color_cost_per_excess: row.color_cost_per_excess ? String(row.color_cost_per_excess) : '0',
    });
});

function updateEquipmentCardSelection() {
    equipmentCards.forEach(card => {
        const active = selectedRows.has(card.dataset.itemId);
        card.classList.toggle('border-blue-500', active);
        card.classList.toggle('bg-blue-50', active);
        card.classList.toggle('ring-1', active);
        card.classList.toggle('ring-blue-200', active);
    });

}

function branchOptionsHtml(branches, selectedValue = '') {
    let html = '<option value="">Seleccione sucursal</option>';
    branches.forEach(branch => {
        const selected = selectedValue && String(branch.id) === String(selectedValue) ? ' selected' : '';
        html += `<option value="${branch.id}"${selected}>${branch.name}</option>`;
    });
    return html;
}

function areaOptionsHtml(areas, selectedValue = '') {
    let html = '<option value="">Sin area</option>';
    areas.forEach(area => {
        const selected = selectedValue && String(area.id) === String(selectedValue) ? ' selected' : '';
        html += `<option value="${area.id}"${selected}>${area.name}</option>`;
    });
    return html;
}

async function loadBranches(clientId) {
    if (!clientId) return [];
    const key = String(clientId);
    if (branchesCache.has(key)) return branchesCache.get(key);
    const data = await fetch(`/api/clients/${clientId}/branches`).then(r => r.json());
    branchesCache.set(key, data);
    return data;
}

async function loadAreas(branchId) {
    if (!branchId) return [];
    const key = String(branchId);
    if (areasCache.has(key)) return areasCache.get(key);
    const data = await fetch(`/api/branches/${branchId}/areas`).then(r => r.json());
    areasCache.set(key, data);
    return data;
}

async function renderItemRows() {
    const clientId = clientSelect.value;
    const branches = await loadBranches(clientId);
    itemRowsContainer.innerHTML = '';

    const rows = Array.from(selectedRows.values());
    itemRowsEmpty.classList.toggle('hidden', rows.length > 0);

    rows.forEach((row, index) => {
        const card = document.querySelector(`.equipment-card[data-item-id="${row.item_id}"]`);
        const label = card?.dataset.itemLabel || `Equipo ${row.item_id}`;

        const rowEl = document.createElement('div');
        rowEl.className = 'grid grid-cols-1 md:grid-cols-2 gap-3 bg-white border border-gray-200 rounded-lg p-3';
        rowEl.innerHTML = `
            <input type="hidden" name="item_rows[${index}][item_id]" value="${row.item_id}">
            <div class="md:col-span-2">
                <label class="form-label text-xs">Equipo</label>
                <div class="text-sm font-medium text-gray-800">${label}</div>
            </div>
            <div>
                <label class="form-label text-xs">Sucursal *</label>
                <select name="item_rows[${index}][branch_id]" class="form-select branch-select" data-item-id="${row.item_id}">
                    ${branchOptionsHtml(branches, row.branch_id)}
                </select>
            </div>
            <div>
                <label class="form-label text-xs">Area</label>
                <select name="item_rows[${index}][area_id]" class="form-select area-select" data-item-id="${row.item_id}">
                    <option value="">Cargando...</option>
                </select>
            </div>
            <div>
                <label class="form-label text-xs">Contador inicial BN</label>
                <input type="number" min="0" name="item_rows[${index}][contador_inicial_bn]" class="form-input counter-bn" data-item-id="${row.item_id}" value="${row.contador_inicial_bn || '0'}">
            </div>
            <div>
                <label class="form-label text-xs">Contador inicial Color</label>
                <input type="number" min="0" name="item_rows[${index}][contador_inicial_color]" class="form-input counter-color" data-item-id="${row.item_id}" value="${row.contador_inicial_color || '0'}">
            </div>
            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="item_rows[${index}][has_print_service]" class="row-print-check" data-item-id="${row.item_id}" ${row.has_print_service ? 'checked' : ''}>
                    Servicio de impresion en este equipo
                </label>
            </div>
            <div class="md:col-span-2 row-print-accordion" data-item-id="${row.item_id}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 border border-gray-200 rounded-lg p-3 bg-gray-50/50">
                    <div>
                        <label class="form-label text-xs">Impresiones BN incluidas</label>
                        <input type="number" min="0" name="item_rows[${index}][bn_included]" class="form-input row-print-field row-bn-included" data-item-id="${row.item_id}" value="${row.bn_included || '0'}">
                    </div>
                    <div>
                        <label class="form-label text-xs">Costo exceso BN</label>
                        <input type="number" step="0.0001" min="0" name="item_rows[${index}][bn_cost_per_excess]" class="form-input row-print-field row-bn-cost" data-item-id="${row.item_id}" value="${row.bn_cost_per_excess || '0'}">
                    </div>
                    <div>
                        <label class="form-label text-xs">Impresiones Color incluidas</label>
                        <input type="number" min="0" name="item_rows[${index}][color_included]" class="form-input row-print-field row-color-included" data-item-id="${row.item_id}" value="${row.color_included || '0'}">
                    </div>
                    <div>
                        <label class="form-label text-xs">Costo exceso Color</label>
                        <input type="number" step="0.0001" min="0" name="item_rows[${index}][color_cost_per_excess]" class="form-input row-print-field row-color-cost" data-item-id="${row.item_id}" value="${row.color_cost_per_excess || '0'}">
                    </div>
                </div>
            </div>
        `;
        itemRowsContainer.appendChild(rowEl);
    });

    const branchSelects = itemRowsContainer.querySelectorAll('.branch-select');
    for (const branchSelect of branchSelects) {
        const itemId = branchSelect.dataset.itemId;
        const rowState = selectedRows.get(itemId);
        const areaSelect = itemRowsContainer.querySelector(`.area-select[data-item-id="${itemId}"]`);

        const fillAreas = async () => {
            const branchId = branchSelect.value;
            const areas = await loadAreas(branchId);
            const areaValue = rowState?.area_id || '';
            areaSelect.innerHTML = areaOptionsHtml(areas, areaValue);
            if (areaValue && !areas.some(a => String(a.id) === String(areaValue))) {
                rowState.area_id = '';
                areaSelect.value = '';
            }
        };

        branchSelect.addEventListener('change', async () => {
            const state = selectedRows.get(itemId);
            state.branch_id = branchSelect.value || '';
            state.area_id = '';
            await fillAreas();
        });

        areaSelect.addEventListener('change', () => {
            const state = selectedRows.get(itemId);
            state.area_id = areaSelect.value || '';
        });

        await fillAreas();
    }

    itemRowsContainer.querySelectorAll('.counter-bn').forEach((input) => {
        input.addEventListener('input', () => {
            const state = selectedRows.get(input.dataset.itemId);
            state.contador_inicial_bn = input.value || '0';
        });
    });

    itemRowsContainer.querySelectorAll('.counter-color').forEach((input) => {
        input.addEventListener('input', () => {
            const state = selectedRows.get(input.dataset.itemId);
            state.contador_inicial_color = input.value || '0';
        });
    });

    const togglePrintFields = (itemId) => {
        const state = selectedRows.get(itemId);
        const enabled = !!state?.has_print_service;
        const accordion = itemRowsContainer.querySelector(`.row-print-accordion[data-item-id="${itemId}"]`);
        const fields = itemRowsContainer.querySelectorAll(`.row-print-field[data-item-id="${itemId}"]`);
        if (accordion) {
            accordion.classList.toggle('hidden', !enabled);
        }
        fields.forEach((field) => {
            field.disabled = !enabled;
            if (!enabled) {
                field.value = field.classList.contains('row-bn-cost') || field.classList.contains('row-color-cost') ? '0.0000' : '0';
            }
        });
    };

    itemRowsContainer.querySelectorAll('.row-print-check').forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            const state = selectedRows.get(checkbox.dataset.itemId);
            state.has_print_service = checkbox.checked;
            if (!checkbox.checked) {
                state.bn_included = '0';
                state.bn_cost_per_excess = '0';
                state.color_included = '0';
                state.color_cost_per_excess = '0';
            }
            togglePrintFields(checkbox.dataset.itemId);
        });
        togglePrintFields(checkbox.dataset.itemId);
    });

    itemRowsContainer.querySelectorAll('.row-bn-included').forEach((input) => {
        input.addEventListener('input', () => {
            const state = selectedRows.get(input.dataset.itemId);
            state.bn_included = input.value || '0';
        });
    });

    itemRowsContainer.querySelectorAll('.row-bn-cost').forEach((input) => {
        input.addEventListener('input', () => {
            const state = selectedRows.get(input.dataset.itemId);
            state.bn_cost_per_excess = input.value || '0';
        });
    });

    itemRowsContainer.querySelectorAll('.row-color-included').forEach((input) => {
        input.addEventListener('input', () => {
            const state = selectedRows.get(input.dataset.itemId);
            state.color_included = input.value || '0';
        });
    });

    itemRowsContainer.querySelectorAll('.row-color-cost').forEach((input) => {
        input.addEventListener('input', () => {
            const state = selectedRows.get(input.dataset.itemId);
            state.color_cost_per_excess = input.value || '0';
        });
    });
}

equipmentCards.forEach(card => {
    card.addEventListener('click', async function () {
        if (this.dataset.selectable !== '1') return;
        const itemId = this.dataset.itemId;

        if (selectedRows.has(itemId)) {
            selectedRows.delete(itemId);
        } else {
            selectedRows.set(itemId, {
                item_id: itemId,
                branch_id: '',
                area_id: '',
                contador_inicial_bn: '0',
                contador_inicial_color: '0',
                has_print_service: false,
                bn_included: '0',
                bn_cost_per_excess: '0',
                color_included: '0',
                color_cost_per_excess: '0',
            });
        }

        updateEquipmentCardSelection();
        await renderItemRows();
    });
});

clientSelect.addEventListener('change', async () => {
    const rows = Array.from(selectedRows.values());
    rows.forEach(row => {
        row.branch_id = '';
        row.area_id = '';
    });
    await renderItemRows();
});

setupClientSearch();
updateEquipmentCardSelection();
renderItemRows();
</script>
@endpush
@endsection
