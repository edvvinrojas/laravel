@extends('layouts.app')
@section('title','Editar Venta')
@section('page-title','Editar Venta')

@php
    $initialItemRows = old('item_rows');
    if ($initialItemRows === null) {
        $initialItemRows = $sale->items->map(function ($item) use ($sale) {
            return [
                'item_id'   => $item->id,
                'branch_id' => $item->pivot->branch_id ?? $sale->branch_id,
                'area_id'   => $item->pivot->area_id   ?? $sale->area_id,
            ];
        })->values()->all();
    }

    $initialSparepartRows = old('sparepart_rows');
    if ($initialSparepartRows === null) {
        $initialSparepartRows = $sale->spareparts->map(function ($sp) {
            return ['sparepart_id' => $sp->id];
        })->values()->all();
    }

    $initialInventoryRows = old('inventory_rows');
    if ($initialInventoryRows === null) {
        $initialInventoryRows = $sale->inventoryItems->map(function ($inv) {
            return ['inventory_id' => $inv->id];
        })->values()->all();
    }
@endphp

@section('content')
<div class="w-full max-w-7xl mx-auto px-2 sm:px-4">
<form method="POST" action="{{ route('sales.update',$sale) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        @php
            $selectedIds = $selectedIds ?? $sale->items->pluck('id');
            if ($selectedIds->isEmpty() && $sale->item_id) {
                $selectedIds = collect([$sale->item_id]);
            }
            $availableItems = $availableItems ?? $items->filter(fn($i) => $i->location_status === 'BODEGA' || $selectedIds->contains($i->id));
            $unavailableItems = $unavailableItems ?? $items->reject(fn($i) => $i->location_status === 'BODEGA' || $selectedIds->contains($i->id));
        @endphp
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
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(old('client_id',$sale->client_id)==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">No. Factura</label>
            <input name="invoice_number" value="{{ old('invoice_number',$sale->invoice_number) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Estado *</label>
            <select name="sale_status" class="form-select" required>
                @foreach(['PENDIENTE','CONFIRMADA','ENTREGADA','CANCELADA'] as $s)
                <option value="{{ $s }}" @selected(old('sale_status',$sale->sale_status)===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        {{-- Servicios incluidos --}}
        <div class="md:col-span-2 flex items-start gap-4 pt-1">
            <div class="flex items-center gap-2">
                <input type="checkbox" name="services_included" value="1" id="servicesCheck"
                    @checked(old('services_included', $sale->services_included))
                    onchange="document.getElementById('servicesQtyBox').classList.toggle('hidden', !this.checked)">
                <label for="servicesCheck" class="text-sm font-medium">Servicios incluidos</label>
            </div>
            <div id="servicesQtyBox" class="{{ old('services_included', $sale->services_included) ? '' : 'hidden' }} flex items-center gap-2">
                <label class="text-sm text-gray-600">Cantidad de servicios:</label>
                <input name="services_quantity" type="number" min="1"
                    value="{{ old('services_quantity', $sale->services_quantity) }}"
                    class="form-input w-24 text-sm" placeholder="0">
            </div>
        </div>

        <div class="md:col-span-2 pt-2">
            <label class="form-label">Productos *</label>
            <p class="text-xs text-gray-500 mb-2">Selecciona equipos, refacciones y/o artículos de inventario que deseas vender.</p>
            @error('item_rows')<p class="form-error mb-2">{{ $message }}</p>@enderror
            @error('sparepart_rows')<p class="form-error mb-2">{{ $message }}</p>@enderror
            @error('inventory_rows')<p class="form-error mb-2">{{ $message }}</p>@enderror

            {{-- TAB: EQUIPOS --}}
            <div class="border-b border-gray-200 mb-4">
                <div class="flex gap-0.5">
                    <button type="button" class="product-tab active px-4 py-2 text-sm font-medium border-b-2 border-blue-600 text-blue-600 transition-colors" data-tab="equipos">
                        Equipos
                    </button>
                    <button type="button" class="product-tab px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors" data-tab="refacciones">
                        Refacciones
                    </button>
                    <button type="button" class="product-tab px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors" data-tab="inventario">
                        Toners/Inventario
                    </button>
                </div>
            </div>

            {{-- EQUIPOS TAB CONTENT --}}
            <div class="product-tab-content active" data-tab="equipos">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Equipos disponibles</p>
                <div class="h-[14.75rem] overflow-y-auto pr-1 mb-4">
                    <div id="equipmentCards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($availableItems as $i)
                            @php $price = $i->cost; @endphp
                            <button
                                type="button"
                                class="equipment-card product-card h-28 flex flex-col justify-between text-left border border-gray-200 rounded-lg p-3 hover:border-blue-300 hover:bg-blue-50/40 transition"
                                data-item-id="{{ $i->id }}"
                                data-item-price="{{ $price ?? '' }}"
                                data-item-label="{{ trim(($i->brand->name ?? '').' '.$i->model.' — '.$i->serie) }}"
                                data-selectable="1"
                                data-product-type="equipment"
                            >
                                <div class="text-sm font-semibold text-gray-900 truncate">{{ $i->brand->name ?? '—' }} {{ $i->model }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">Serie: {{ $i->serie ?: '—' }}</div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-[11px] px-2 py-1 rounded {{ $selectedIds->contains($i->id) && $i->location_status !== 'BODEGA' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $selectedIds->contains($i->id) && $i->location_status !== 'BODEGA' ? 'ACTUAL: '.$i->location_status : ($i->location_status ?? 'BODEGA') }}
                                    </span>
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
                                class="equipment-card product-card h-28 flex flex-col justify-between text-left border border-gray-200 rounded-lg p-3 opacity-55 cursor-not-allowed bg-gray-50"
                                data-item-id="{{ $i->id }}"
                                data-item-price="{{ $price ?? '' }}"
                                data-selectable="0"
                                data-product-type="equipment"
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

            {{-- REFACCIONES TAB CONTENT --}}
            <div class="product-tab-content hidden" data-tab="refacciones">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Refacciones disponibles</p>
                <div class="h-[14.75rem] overflow-y-auto pr-1 mb-4">
                    <div id="sparepartsCards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @forelse($spareparts as $sp)
                            <button
                                type="button"
                                class="sparepart-card product-card h-28 flex flex-col justify-between text-left border border-gray-200 rounded-lg p-3 hover:border-green-300 hover:bg-green-50/40 transition"
                                data-sparepart-id="{{ $sp->id }}"
                                data-sparepart-price="{{ $sp->unit_price ?? 0 }}"
                                data-sparepart-label="{{ $sp->name }} {{ $sp->code ? '('.$sp->code.')' : '' }}"
                                data-product-type="sparepart"
                            >
                                <div class="text-sm font-semibold text-gray-900 truncate">{{ $sp->name }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">Código: {{ $sp->code ?: '—' }}</div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-[11px] px-2 py-1 rounded bg-green-100 text-green-700">DISPONIBLE</span>
                                    <span class="text-sm font-bold text-green-700">{{ $sp->unit_price !== null ? '$'.number_format($sp->unit_price, 2) : 'Sin precio' }}</span>
                                </div>
                            </button>
                        @empty
                            <div class="col-span-3 text-center py-8">
                                <p class="text-gray-500 text-sm">No hay refacciones disponibles.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- INVENTARIO TAB CONTENT --}}
            <div class="product-tab-content hidden" data-tab="inventario">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Toners y artículos disponibles</p>
                <div class="h-[14.75rem] overflow-y-auto pr-1 mb-4">
                    <div id="inventoryCards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @forelse($inventory as $inv)
                            <button
                                type="button"
                                class="inventory-card product-card h-28 flex flex-col justify-between text-left border border-gray-200 rounded-lg p-3 hover:border-purple-300 hover:bg-purple-50/40 transition"
                                data-inventory-id="{{ $inv->id }}"
                                data-inventory-price="{{ $inv->cost ?? 0 }}"
                                data-inventory-label="{{ $inv->catalog?->item_name ?? $inv->item_code }}"
                                data-product-type="inventory"
                            >
                                <div class="text-sm font-semibold text-gray-900 truncate">{{ $inv->catalog?->item_name ?? '—' }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">Código: {{ $inv->item_code }}</div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-[11px] px-2 py-1 rounded bg-purple-100 text-purple-700">DISPONIBLE</span>
                                    <span class="text-sm font-bold text-purple-700">{{ $inv->cost !== null ? '$'.number_format($inv->cost, 2) : 'Sin precio' }}</span>
                                </div>
                            </button>
                        @empty
                            <div class="col-span-3 text-center py-8">
                                <p class="text-gray-500 text-sm">No hay artículos disponibles.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-4 border border-gray-200 rounded-lg p-3 bg-gray-50/60">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Productos seleccionados</p>
                <div id="selectedProductsContainer" class="space-y-3"></div>
                <p id="selectedProductsEmpty" class="text-xs text-gray-500">No hay productos seleccionados.</p>
                <div class="mt-3 pt-3 border-t border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
                    <div>
                        <p class="text-xs text-gray-500">Suma automática de productos seleccionados</p>
                        <p id="saleTotalHint" class="text-sm font-semibold text-gray-700">$0.00</p>
                    </div>
                    <div>
                        <label class="form-label">Precio de venta general *</label>
                        <input name="sale_price" id="sale_price_total" type="number" step="0.01" min="0" value="{{ old('sale_price', $sale->sale_price) }}" class="form-input" required>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('sales.show',$sale) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>

</form>
</div>

@push('scripts')
<script>
const clientSelect = document.getElementById('clientSelect');
const clientSearchInput = document.getElementById('clientSearchInput');
const clientSearchResults = document.getElementById('clientSearchResults');
const salePriceInput = document.getElementById('sale_price_total');
const saleTotalHint = document.getElementById('saleTotalHint');
const equipmentCards = document.querySelectorAll('.equipment-card');
const sparepartCards = document.querySelectorAll('.sparepart-card');
const inventoryCards = document.querySelectorAll('.inventory-card');
const productTabButtons = document.querySelectorAll('.product-tab');
const selectedProductsContainer = document.getElementById('selectedProductsContainer');
const selectedProductsEmpty = document.getElementById('selectedProductsEmpty');
const branchesCache = new Map();
const areasCache = new Map();

// Almacenar productos seleccionados
const selectedProducts = {
    equipment: new Map(),
    sparepart: new Map(),
    inventory: new Map(),
};

let salePriceManuallyEdited = false;

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

// Cargar datos iniciales
const initialItemRows = @json($initialItemRows);
const initialSparepartRows = @json($initialSparepartRows);
const initialInventoryRows = @json($initialInventoryRows);

initialItemRows.forEach(row => {
    if (!row.item_id) return;
    selectedProducts.equipment.set(String(row.item_id), {
        item_id: String(row.item_id),
        branch_id: row.branch_id ? String(row.branch_id) : '',
        area_id: row.area_id ? String(row.area_id) : '',
    });
});

initialSparepartRows.forEach(row => {
    if (!row.sparepart_id) return;
    selectedProducts.sparepart.set(String(row.sparepart_id), {
        sparepart_id: String(row.sparepart_id),
    });
});

initialInventoryRows.forEach(row => {
    if (!row.inventory_id) return;
    selectedProducts.inventory.set(String(row.inventory_id), {
        inventory_id: String(row.inventory_id),
    });
});

function updateProductCardSelection() {
    equipmentCards.forEach(card => {
        const active = selectedProducts.equipment.has(card.dataset.itemId);
        card.classList.toggle('border-blue-500', active);
        card.classList.toggle('bg-blue-50', active);
        card.classList.toggle('ring-1', active);
        card.classList.toggle('ring-blue-200', active);
    });

    sparepartCards.forEach(card => {
        const active = selectedProducts.sparepart.has(card.dataset.sparepartId);
        card.classList.toggle('border-green-500', active);
        card.classList.toggle('bg-green-50', active);
        card.classList.toggle('ring-1', active);
        card.classList.toggle('ring-green-200', active);
    });

    inventoryCards.forEach(card => {
        const active = selectedProducts.inventory.has(card.dataset.inventoryId);
        card.classList.toggle('border-purple-500', active);
        card.classList.toggle('bg-purple-50', active);
        card.classList.toggle('ring-1', active);
        card.classList.toggle('ring-purple-200', active);
    });
}

function getProductPrice(productType, productId) {
    let price = 0;
    if (productType === 'equipment') {
        const card = document.querySelector(`.equipment-card[data-item-id="${productId}"]`);
        price = card?.dataset.itemPrice ? parseFloat(card.dataset.itemPrice) : 0;
    } else if (productType === 'sparepart') {
        const card = document.querySelector(`.sparepart-card[data-sparepart-id="${productId}"]`);
        price = card?.dataset.sparepartPrice ? parseFloat(card.dataset.sparepartPrice) : 0;
    } else if (productType === 'inventory') {
        const card = document.querySelector(`.inventory-card[data-inventory-id="${productId}"]`);
        price = card?.dataset.inventoryPrice ? parseFloat(card.dataset.inventoryPrice) : 0;
    }
    return isFinite(price) ? price : 0;
}

function getProductLabel(productType, productId) {
    if (productType === 'equipment') {
        const card = document.querySelector(`.equipment-card[data-item-id="${productId}"]`);
        return card?.dataset.itemLabel || `Equipo ${productId}`;
    } else if (productType === 'sparepart') {
        const card = document.querySelector(`.sparepart-card[data-sparepart-id="${productId}"]`);
        return card?.dataset.sparepartLabel || `Refacción ${productId}`;
    } else if (productType === 'inventory') {
        const card = document.querySelector(`.inventory-card[data-inventory-id="${productId}"]`);
        return card?.dataset.inventoryLabel || `Inventario ${productId}`;
    }
    return '';
}

function syncSaleTotal() {
    let total = 0;
    
    selectedProducts.equipment.forEach((row) => {
        total += getProductPrice('equipment', row.item_id);
    });
    selectedProducts.sparepart.forEach((row) => {
        total += getProductPrice('sparepart', row.sparepart_id);
    });
    selectedProducts.inventory.forEach((row) => {
        total += getProductPrice('inventory', row.inventory_id);
    });

    if (salePriceInput) {
        if (!salePriceManuallyEdited || salePriceInput.value === '' || salePriceInput.value === '0' || salePriceInput.value === '0.00') {
            salePriceInput.value = total.toFixed(2);
        }
    }
    if (saleTotalHint) {
        saleTotalHint.textContent = `$${total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }
}

if (salePriceInput) {
    salePriceInput.addEventListener('input', () => {
        salePriceManuallyEdited = true;
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

async function renderSelectedProducts() {
    const branches = await loadBranches(clientSelect.value);
    selectedProductsContainer.innerHTML = '';
    let index = 0;

    // Equipment rows
    selectedProducts.equipment.forEach((row, itemId) => {
        const label = getProductLabel('equipment', itemId);
        const rowEl = document.createElement('div');
        rowEl.className = 'grid grid-cols-1 md:grid-cols-4 gap-3 bg-white border border-blue-200 rounded-lg p-3';
        rowEl.innerHTML = `
            <input type="hidden" name="item_rows[${index}][item_id]" value="${itemId}">
            <div class="md:col-span-1">
                <label class="form-label text-xs">Equipo</label>
                <div class="text-sm font-medium text-gray-800">${label}</div>
                <p class="text-xs text-blue-700 font-semibold mt-1">$${getProductPrice('equipment', itemId).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
            </div>
            <div>
                <label class="form-label text-xs">Sucursal *</label>
                <select name="item_rows[${index}][branch_id]" class="form-select equipment-branch-select" data-item-id="${itemId}">
                    ${branchOptionsHtml(branches, row.branch_id)}
                </select>
            </div>
            <div>
                <label class="form-label text-xs">Área</label>
                <select name="item_rows[${index}][area_id]" class="form-select equipment-area-select" data-item-id="${itemId}">
                    <option value="">Cargando...</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="button" class="btn-danger btn-sm w-full remove-product" data-product-type="equipment" data-product-id="${itemId}">Quitar</button>
            </div>
        `;
        selectedProductsContainer.appendChild(rowEl);
        index++;
    });

    // Sparepart rows
    selectedProducts.sparepart.forEach((row, sparepartId) => {
        const label = getProductLabel('sparepart', sparepartId);
        const rowEl = document.createElement('div');
        rowEl.className = 'grid grid-cols-1 md:grid-cols-4 gap-3 bg-white border border-green-200 rounded-lg p-3';
        rowEl.innerHTML = `
            <input type="hidden" name="sparepart_rows[${index}][sparepart_id]" value="${sparepartId}">
            <div class="md:col-span-2">
                <label class="form-label text-xs">Refacción</label>
                <div class="text-sm font-medium text-gray-800">${label}</div>
                <p class="text-xs text-green-700 font-semibold mt-1">$${getProductPrice('sparepart', sparepartId).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
            </div>
            <div class="flex items-end">
                <button type="button" class="btn-danger btn-sm w-full remove-product" data-product-type="sparepart" data-product-id="${sparepartId}">Quitar</button>
            </div>
        `;
        selectedProductsContainer.appendChild(rowEl);
        index++;
    });

    // Inventory rows
    selectedProducts.inventory.forEach((row, inventoryId) => {
        const label = getProductLabel('inventory', inventoryId);
        const rowEl = document.createElement('div');
        rowEl.className = 'grid grid-cols-1 md:grid-cols-4 gap-3 bg-white border border-purple-200 rounded-lg p-3';
        rowEl.innerHTML = `
            <input type="hidden" name="inventory_rows[${index}][inventory_id]" value="${inventoryId}">
            <div class="md:col-span-2">
                <label class="form-label text-xs">Toner/Inventario</label>
                <div class="text-sm font-medium text-gray-800">${label}</div>
                <p class="text-xs text-purple-700 font-semibold mt-1">$${getProductPrice('inventory', inventoryId).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
            </div>
            <div class="flex items-end">
                <button type="button" class="btn-danger btn-sm w-full remove-product" data-product-type="inventory" data-product-id="${inventoryId}">Quitar</button>
            </div>
        `;
        selectedProductsContainer.appendChild(rowEl);
        index++;
    });

    // Setup event listeners
    selectedProductsContainer.querySelectorAll('.equipment-branch-select').forEach(select => {
        const itemId = select.dataset.itemId;
        const rowState = selectedProducts.equipment.get(itemId);
        const areaSelect = selectedProductsContainer.querySelector(`.equipment-area-select[data-item-id="${itemId}"]`);

        const fillAreas = async () => {
            const branchId = select.value;
            const areas = await loadAreas(branchId);
            const areaValue = rowState?.area_id || '';
            areaSelect.innerHTML = areaOptionsHtml(areas, areaValue);
            if (areaValue && !areas.some(a => String(a.id) === String(areaValue))) {
                rowState.area_id = '';
                areaSelect.value = '';
            }
        };

        select.addEventListener('change', async () => {
            rowState.branch_id = select.value || '';
            rowState.area_id = '';
            await fillAreas();
        });

        areaSelect.addEventListener('change', () => {
            rowState.area_id = areaSelect.value || '';
        });

        fillAreas();
    });

    selectedProductsContainer.querySelectorAll('.remove-product').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const productType = btn.dataset.productType;
            const productId = btn.dataset.productId;
            if (productType === 'equipment') {
                selectedProducts.equipment.delete(productId);
            } else if (productType === 'sparepart') {
                selectedProducts.sparepart.delete(productId);
            } else if (productType === 'inventory') {
                selectedProducts.inventory.delete(productId);
            }
            updateProductCardSelection();
            await renderSelectedProducts();
            syncSaleTotal();
        });
    });

    const hasProducts = selectedProducts.equipment.size > 0 || selectedProducts.sparepart.size > 0 || selectedProducts.inventory.size > 0;
    selectedProductsEmpty.classList.toggle('hidden', hasProducts);
    syncSaleTotal();
}

// Tab switching
productTabButtons.forEach(button => {
    button.addEventListener('click', () => {
        const tab = button.dataset.tab;
        productTabButtons.forEach(b => {
            b.classList.toggle('border-blue-600', b.dataset.tab === tab);
            b.classList.toggle('text-blue-600', b.dataset.tab === tab);
            b.classList.toggle('bg-blue-50', b.dataset.tab === tab);
            b.classList.toggle('border-transparent', b.dataset.tab !== tab);
            b.classList.toggle('text-gray-500', b.dataset.tab !== tab);
        });
        
        document.querySelectorAll('.product-tab-content').forEach(content => {
            content.classList.toggle('hidden', content.dataset.tab !== tab);
            content.classList.toggle('active', content.dataset.tab === tab);
        });
    });
});

// Equipment card click handlers
equipmentCards.forEach(card => {
    card.addEventListener('click', async function () {
        if (this.dataset.selectable !== '1') return;

        const itemId = this.dataset.itemId;
        if (selectedProducts.equipment.has(itemId)) {
            selectedProducts.equipment.delete(itemId);
        } else {
            selectedProducts.equipment.set(itemId, { item_id: itemId, branch_id: '', area_id: '' });
        }

        updateProductCardSelection();
        await renderSelectedProducts();
    });
});

// Sparepart card click handlers
sparepartCards.forEach(card => {
    card.addEventListener('click', async function () {
        const sparepartId = this.dataset.sparepartId;
        if (selectedProducts.sparepart.has(sparepartId)) {
            selectedProducts.sparepart.delete(sparepartId);
        } else {
            selectedProducts.sparepart.set(sparepartId, { sparepart_id: sparepartId });
        }

        updateProductCardSelection();
        await renderSelectedProducts();
    });
});

// Inventory card click handlers
inventoryCards.forEach(card => {
    card.addEventListener('click', async function () {
        const inventoryId = this.dataset.inventoryId;
        if (selectedProducts.inventory.has(inventoryId)) {
            selectedProducts.inventory.delete(inventoryId);
        } else {
            selectedProducts.inventory.set(inventoryId, { inventory_id: inventoryId });
        }

        updateProductCardSelection();
        await renderSelectedProducts();
    });
});

clientSelect.addEventListener('change', async () => {
    // Clear branches/areas for equipment rows
    selectedProducts.equipment.forEach(row => {
        row.branch_id = '';
        row.area_id = '';
    });
    await renderSelectedProducts();
});

setupClientSearch();
updateProductCardSelection();
renderSelectedProducts();
syncSaleTotal();
</script>
@endpush
@endsection
