@extends('layouts.app')
@section('title','Nueva Cotización')
@section('page-title')
    Nueva Cotización
    <span id="quoteNumTitle" class="text-xs font-mono text-blue-700 bg-blue-50 border border-blue-200 rounded px-2 py-0.5 align-middle hidden"></span>
@endsection

@section('content')
<div class="w-full max-w-7xl mx-auto px-2 sm:px-4">
<form method="POST" action="{{ route('quotes.store') }}" id="quoteForm">
@csrf

{{-- DATOS GENERALES --}}
<div class="card mb-5">
    <div class="card-header">
        <h3 class="font-semibold text-sm">Datos generales</h3>
        <span class="text-xs font-mono text-blue-700 bg-blue-50 border border-blue-200 rounded px-2 py-0.5">{{ $nextNumber }}</span>
    </div>
    <div class="card-body grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="form-label">Cliente *</label>
            <input
                type="text"
                id="clientSearchInput"
                class="form-input"
                placeholder="Buscar cliente por nombre…"
                autocomplete="off"
            >
            <div id="clientSearchResults" class="mt-1 max-h-44 overflow-y-auto rounded-md border border-gray-200 bg-white shadow-sm hidden z-20 relative"></div>
            <select name="client_id" id="clientSelect" class="sr-only" required tabindex="-1" aria-hidden="true">
                <option value="">Seleccionar…</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('client_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Válida hasta</label>
            <input type="date" name="valid_until" value="{{ old('valid_until') }}" class="form-input" min="{{ date('Y-m-d') }}">
            @error('valid_until')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-3">
            <label class="form-label">Notas / Condiciones</label>
            <textarea name="notes" rows="2" class="form-input" placeholder="Condiciones de entrega, tiempo de vigencia, términos especiales…">{{ old('notes') }}</textarea>
        </div>
    </div>
</div>

{{-- SELECTOR DE PRODUCTOS --}}
<div class="card mb-5">
    <div class="card-header">
        <h3 class="font-semibold text-sm">Productos</h3>
        <p class="text-xs text-gray-500">Selecciona productos del catálogo o agrega líneas manuales</p>
    </div>
    <div class="card-body">
        @error('lines')<p class="form-error mb-3">{{ $message }}</p>@enderror

        {{-- Tabs de catálogo --}}
        <div class="border-b border-gray-200 mb-4">
            <div class="flex gap-0.5">
                <button type="button" class="cat-tab active px-4 py-2 text-sm font-medium border-b-2 border-blue-600 text-blue-600 transition-colors" data-tab="equipos">Equipos</button>
                <button type="button" class="cat-tab px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors" data-tab="refacciones">Refacciones</button>
                <button type="button" class="cat-tab px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors" data-tab="inventario">Toners/Inventario</button>
            </div>
        </div>

        {{-- EQUIPOS --}}
        <div class="cat-tab-content" data-tab="equipos">
            <div class="h-52 overflow-y-auto pr-1 mb-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($items as $i)
                    @php $available = $i->location_status === 'BODEGA'; @endphp
                    <button
                        type="button"
                        class="catalog-card h-24 flex flex-col justify-between text-left border border-gray-200 rounded-lg p-3 transition
                            {{ $available ? 'hover:border-blue-300 hover:bg-blue-50/40' : 'opacity-50 cursor-not-allowed bg-gray-50' }}"
                        data-type="item"
                        data-id="{{ $i->id }}"
                        data-label="{{ trim(($i->brand->name ?? '').' '.$i->model) }}"
                        data-price="{{ $i->cost ?? 0 }}"
                        {{ !$available ? 'disabled' : '' }}
                    >
                        <div class="text-sm font-semibold text-gray-900 truncate">{{ $i->brand->name ?? '—' }} {{ $i->model }}</div>
                        <div class="text-xs text-gray-500">Serie: {{ $i->serie ?: '—' }}</div>
                        <div class="mt-1 flex justify-between items-center">
                            <span class="text-[10px] px-1.5 py-0.5 rounded {{ $available ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' }}">{{ $i->location_status }}</span>
                            <span class="text-sm font-bold text-blue-700">{{ $i->cost ? '$'.number_format($i->cost,2) : 'Sin precio' }}</span>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- REFACCIONES --}}
        <div class="cat-tab-content hidden" data-tab="refacciones">
            <div class="h-52 overflow-y-auto pr-1 mb-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($spareparts as $sp)
                    <button
                        type="button"
                        class="catalog-card h-24 flex flex-col justify-between text-left border border-gray-200 rounded-lg p-3 hover:border-green-300 hover:bg-green-50/40 transition"
                        data-type="sparepart"
                        data-id="{{ $sp->id }}"
                        data-label="{{ $sp->name }}{{ $sp->code ? ' ('.$sp->code.')' : '' }}"
                        data-price="{{ $sp->unit_price ?? 0 }}"
                    >
                        <div class="text-sm font-semibold text-gray-900 truncate">{{ $sp->name }}</div>
                        <div class="text-xs text-gray-500">Código: {{ $sp->code ?: '—' }}</div>
                        <div class="mt-1 flex justify-between items-center">
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-green-100 text-green-700">DISPONIBLE</span>
                            <span class="text-sm font-bold text-green-700">{{ $sp->unit_price ? '$'.number_format($sp->unit_price,2) : 'Sin precio' }}</span>
                        </div>
                    </button>
                    @empty
                    <div class="col-span-3 py-8 text-center text-sm text-gray-400">Sin refacciones disponibles</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- INVENTARIO --}}
        <div class="cat-tab-content hidden" data-tab="inventario">
            <div class="h-52 overflow-y-auto pr-1 mb-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($inventory as $inv)
                    <button
                        type="button"
                        class="catalog-card h-24 flex flex-col justify-between text-left border border-gray-200 rounded-lg p-3 hover:border-purple-300 hover:bg-purple-50/40 transition"
                        data-type="inventory"
                        data-id="{{ $inv->id }}"
                        data-label="{{ $inv->catalog?->item_name ?? $inv->item_code }}"
                        data-price="{{ $inv->cost ?? 0 }}"
                    >
                        <div class="text-sm font-semibold text-gray-900 truncate">{{ $inv->catalog?->item_name ?? '—' }}</div>
                        <div class="text-xs text-gray-500">Cód: {{ $inv->item_code }}</div>
                        <div class="mt-1 flex justify-between items-center">
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-purple-100 text-purple-700">DISPONIBLE</span>
                            <span class="text-sm font-bold text-purple-700">{{ $inv->cost ? '$'.number_format($inv->cost,2) : 'Sin precio' }}</span>
                        </div>
                    </button>
                    @empty
                    <div class="col-span-3 py-8 text-center text-sm text-gray-400">Sin artículos en inventario</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Agregar línea manual --}}
        <div class="mt-2">
            <button type="button" onclick="addManualLine()" class="btn-secondary text-xs">
                + Agregar línea manual
            </button>
        </div>
    </div>
</div>

{{-- LÍNEAS DE COTIZACIÓN --}}
<div class="card mb-5">
    <div class="card-header">
        <h3 class="font-semibold text-sm">Líneas de cotización</h3>
        <span id="linesCount" class="badge-gray text-xs">0 líneas</span>
    </div>
    <div class="card-body p-0">
        <div class="table-wrap rounded-none border-0">
            <table class="table" id="linesTable">
                <thead>
                    <tr>
                        <th class="w-8">#</th>
                        <th>Descripción</th>
                        <th class="w-20 text-center">Cant.</th>
                        <th class="w-36 text-right">Precio unitario</th>
                        <th class="w-36 text-right">Total</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody id="linesTbody">
                    <tr id="emptyRow">
                        <td colspan="6" class="text-center py-8 text-gray-400 text-sm">
                            Selecciona productos del catálogo o agrega líneas manuales
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50">
                        <td colspan="4" class="px-4 py-3 text-right font-semibold text-gray-700">Total cotización</td>
                        <td class="px-4 py-3 text-right font-bold text-blue-700 text-base" id="grandTotal">$0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ACCIONES --}}
<div class="flex justify-end gap-3">
    <a href="{{ route('quotes.index') }}" class="btn-secondary">Cancelar</a>
    <button type="submit" class="btn-primary">Guardar cotización</button>
</div>

</form>
</div>
@endsection

@push('scripts')
<script>
// ── Autocomplete de cliente ──────────────────────────────────────────────────
const clientInput = document.getElementById('clientSearchInput');
const clientSelect = document.getElementById('clientSelect');
const clientResults = document.getElementById('clientSearchResults');

function normalizeText(value) {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

function setupClientSearch() {
    if (!clientSelect || !clientInput || !clientResults) return;

    const baseOptions = Array.from(clientSelect.options).map(o => ({ value: o.value, text: o.text }));
    const selectableOptions = baseOptions.filter(o => o.value !== '');

    const escapeHtml = v => String(v || '')
        .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;').replaceAll("'", '&#39;');

    const hideResults = () => clientResults.classList.add('hidden');

    const applySelection = (value, text) => {
        const prev = String(clientSelect.value || '');
        clientSelect.value = String(value || '');
        clientInput.value = text || '';
        hideResults();
        if (prev !== String(value || '')) clientSelect.dispatchEvent(new Event('change'));
    };

    const renderOptions = (query = '') => {
        const nq = normalizeText(query.trim());
        const matches = selectableOptions.filter(o => !nq || normalizeText(o.text).includes(nq));
        if (!matches.length) {
            clientResults.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">Sin coincidencias</div>';
            return;
        }
        clientResults.innerHTML = matches.map(o =>
            `<button type="button" class="block w-full text-left px-3 py-2 text-sm hover:bg-blue-50" data-v="${escapeHtml(o.value)}" data-t="${escapeHtml(o.text)}">${escapeHtml(o.text)}</button>`
        ).join('');
        clientResults.querySelectorAll('[data-v]').forEach(btn => {
            btn.addEventListener('click', () => applySelection(btn.dataset.v, btn.dataset.t));
        });
    };

    // Pre-seleccionar si ya tiene valor
    const preselected = selectableOptions.find(o => String(o.value) === String(clientSelect.value));
    if (preselected) clientInput.value = preselected.text;

    clientInput.addEventListener('focus', () => { renderOptions(clientInput.value); clientResults.classList.remove('hidden'); });
    clientInput.addEventListener('click', () => { renderOptions(clientInput.value); clientResults.classList.remove('hidden'); });
    clientInput.addEventListener('input', e => {
        const val = e.target.value || '';
        const current = selectableOptions.find(o => String(o.value) === String(clientSelect.value));
        if (!val.trim()) applySelection('', '');
        else if (current && normalizeText(val) !== normalizeText(current.text)) clientSelect.value = '';
        renderOptions(val);
        clientResults.classList.remove('hidden');
    });
    document.addEventListener('click', e => {
        if (!clientInput.contains(e.target) && !clientResults.contains(e.target)) hideResults();
    });
    hideResults();
}

setupClientSearch();

// ── Tabs del catálogo ────────────────────────────────────────────────────────
document.querySelectorAll('.cat-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.cat-tab').forEach(t => {
            t.classList.remove('border-blue-600', 'text-blue-600');
            t.classList.add('border-transparent', 'text-gray-500');
        });
        this.classList.add('border-blue-600', 'text-blue-600');
        this.classList.remove('border-transparent', 'text-gray-500');
        const target = this.dataset.tab;
        document.querySelectorAll('.cat-tab-content').forEach(c => c.classList.add('hidden'));
        document.querySelector(`.cat-tab-content[data-tab="${target}"]`).classList.remove('hidden');
    });
});

// ── Líneas ───────────────────────────────────────────────────────────────────
let lineIndex = 0;
const linesTbody = document.getElementById('linesTbody');
const emptyRow = document.getElementById('emptyRow');
const grandTotal = document.getElementById('grandTotal');
const linesCount = document.getElementById('linesCount');

function formatMoney(n) {
    return '$' + Number(n).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function recalc() {
    let total = 0;
    linesTbody.querySelectorAll('tr[data-line]').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const lineTotal = qty * price;
        total += lineTotal;
        row.querySelector('.line-total').textContent = formatMoney(lineTotal);
    });
    grandTotal.textContent = formatMoney(total);
    const count = linesTbody.querySelectorAll('tr[data-line]').length;
    linesCount.textContent = count + (count === 1 ? ' línea' : ' líneas');
    emptyRow.style.display = count === 0 ? '' : 'none';
}

function addLine(type, id, label, price) {
    const idx = lineIndex++;
    emptyRow.style.display = 'none';
    const tr = document.createElement('tr');
    tr.dataset.line = idx;
    tr.innerHTML = `
        <td class="text-xs text-gray-400">${linesTbody.querySelectorAll('tr[data-line]').length + 1}</td>
        <td>
            <input type="hidden" name="lines[${idx}][product_type]" value="${type}">
            <input type="hidden" name="lines[${idx}][product_id]" value="${id ?? ''}">
            <input type="text" name="lines[${idx}][description]" value="${label}" required
                class="form-input text-sm py-1.5" placeholder="Descripción">
        </td>
        <td>
            <input type="number" name="lines[${idx}][quantity]" value="1" min="1" required
                class="form-input text-sm py-1.5 text-center qty-input w-full">
        </td>
        <td>
            <input type="number" name="lines[${idx}][unit_price]" value="${price}" step="0.01" min="0" required
                class="form-input text-sm py-1.5 text-right price-input w-full">
        </td>
        <td class="text-right font-semibold text-sm line-total pr-4">${formatMoney(price)}</td>
        <td class="text-center">
            <button type="button" class="text-red-400 hover:text-red-600 transition" onclick="removeLine(this)">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </td>
    `;
    tr.querySelectorAll('.qty-input, .price-input').forEach(inp => inp.addEventListener('input', recalc));
    linesTbody.appendChild(tr);
    recalc();
}

function addManualLine() {
    addLine('manual', null, '', 0);
}

function removeLine(btn) {
    btn.closest('tr[data-line]').remove();
    recalc();
    // Re-numerar
    linesTbody.querySelectorAll('tr[data-line]').forEach((row, i) => {
        row.cells[0].textContent = i + 1;
    });
}

// Click en tarjetas de catálogo
document.querySelectorAll('.catalog-card:not([disabled])').forEach(card => {
    card.addEventListener('click', function () {
        addLine(this.dataset.type, this.dataset.id, this.dataset.label, parseFloat(this.dataset.price) || 0);
    });
});

// Validación antes de enviar
document.getElementById('quoteForm').addEventListener('submit', function (e) {
    const count = linesTbody.querySelectorAll('tr[data-line]').length;
    if (count === 0) {
        e.preventDefault();
        alert('Debes agregar al menos una línea a la cotización.');
    }
});
</script>
@endpush
