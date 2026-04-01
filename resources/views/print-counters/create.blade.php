@extends('layouts.app')
@section('title','Nuevo Contador')
@section('page-title','Registrar Contador de Impresión')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('print-counters.store') }}" enctype="multipart/form-data">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="form-label">Renta *</label>
            <select name="rent_id" id="rentSelect" class="form-select" required>
                <option value="">Seleccionar renta…</option>
                @foreach($rents as $r)
                <option value="{{ $r->id }}" @selected(old('rent_id')==$r->id||request('rent_id')==$r->id)>{{ $r->client->name }} — {{ $r->contract_number }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Al seleccionar la renta, los campos "anterior" se llenan automáticamente con la última lectura registrada.</p>
        </div>

        {{-- Info de servicio de impresión de la renta seleccionada --}}
        <div id="rentInfo" class="col-span-2 hidden bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm">
            <p class="font-medium text-blue-800 mb-1">Condiciones de impresión del contrato</p>
            <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-blue-700">
                <span>BN incluidas: <strong id="infobnIncluded">—</strong></span>
                <span>Color incluidas: <strong id="infoColorIncluded">—</strong></span>
                <span>Costo exceso BN: $<strong id="infoBnCosto">—</strong>/pág</span>
                <span>Costo exceso Color: $<strong id="infoColorCosto">—</strong>/pág</span>
            </div>
        </div>

        <div>
            <label class="form-label">Mes *</label>
            <select name="period_month" class="form-select" required>
                @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" @selected(old('period_month',date('n'))==$m)>{{ DateTime::createFromFormat('!m',$m)->format('F') }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="form-label">Año *</label>
            <input name="period_year" type="number" value="{{ old('period_year',date('Y')) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Contador BN anterior *</label>
            <input name="bn_previous" id="bn_previous" type="number" min="0" value="{{ old('bn_previous',0) }}" class="form-input" required>
            <p class="text-xs text-gray-400 mt-1">Lectura del equipo al inicio del período</p>
        </div>
        <div>
            <label class="form-label">Contador BN actual *</label>
            <input name="bn_current" id="bn_current" type="number" min="0" value="{{ old('bn_current',0) }}" class="form-input" required>
            <p class="text-xs text-gray-400 mt-1">Lectura del equipo al final del período</p>
        </div>
        <div>
            <label class="form-label">Contador Color anterior *</label>
            <input name="color_previous" id="color_previous" type="number" min="0" value="{{ old('color_previous',0) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Contador Color actual *</label>
            <input name="color_current" id="color_current" type="number" min="0" value="{{ old('color_current',0) }}" class="form-input" required>
        </div>

        {{-- Preview en tiempo real --}}
        <div id="excessPreview" class="col-span-2 hidden rounded-lg border text-sm overflow-hidden">
            <div class="px-4 py-2 bg-gray-50 border-b font-semibold text-gray-700 text-xs uppercase tracking-wide">Vista previa del exceso</div>
            <div class="px-4 py-3 grid grid-cols-2 gap-3 text-sm">
                <div class="space-y-1">
                    <p class="text-xs font-medium text-gray-500">BN</p>
                    <p>Impreso: <span id="pvBnPrinted" class="font-semibold font-mono">0</span> | Incluidas: <span id="pvBnIncluded" class="text-gray-500">—</span></p>
                    <p>Exceso: <span id="pvBnExcess" class="font-semibold">0</span> págs × $<span id="pvBnCosto">0</span> = <strong id="pvBnAmount">$0.00</strong></p>
                </div>
                <div class="space-y-1">
                    <p class="text-xs font-medium text-gray-500">Color</p>
                    <p>Impreso: <span id="pvColorPrinted" class="font-semibold font-mono">0</span> | Incluidas: <span id="pvColorIncluded" class="text-gray-500">—</span></p>
                    <p>Exceso: <span id="pvColorExcess" class="font-semibold">0</span> págs × $<span id="pvColorCosto">0</span> = <strong id="pvColorAmount">$0.00</strong></p>
                </div>
            </div>
            <div id="pvTotalBar" class="px-4 py-2 border-t text-sm font-bold"></div>
        </div>

        <div>
            <label class="form-label">Fecha de lectura *</label>
            <input name="reading_date" type="date" value="{{ old('reading_date',date('Y-m-d')) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Foto del contador</label>
            <input name="counter_photo" type="file" accept="image/*" class="form-input">
        </div>
        <div class="col-span-2">
            <label class="form-label">Notas</label>
            <textarea name="notes" class="form-input" rows="2">{{ old('notes') }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('print-counters.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>

@push('scripts')
<script>
const rentDefaults = @json($rentDefaults);

function applyRentDefaults(rentId) {
    const d = rentDefaults[rentId];
    if (!d) {
        document.getElementById('rentInfo').classList.add('hidden');
        return;
    }
    document.querySelector('[name="bn_previous"]').value    = d.bn;
    document.querySelector('[name="color_previous"]').value = d.color;

    // Panel info
    document.getElementById('infobnIncluded').textContent    = d.bn_included;
    document.getElementById('infoColorIncluded').textContent  = d.color_included;
    document.getElementById('infoBnCosto').textContent        = parseFloat(d.bn_costo).toFixed(4);
    document.getElementById('infoColorCosto').textContent     = parseFloat(d.color_costo).toFixed(4);
    document.getElementById('rentInfo').classList.remove('hidden');
}

document.getElementById('rentSelect').addEventListener('change', function () {
    applyRentDefaults(this.value);
    calcPreview();
});

// Preview en tiempo real al cambiar cualquier contador
['bn_previous','bn_current','color_previous','color_current'].forEach(id => {
    document.getElementById(id).addEventListener('input', calcPreview);
});

function calcPreview() {
    const rentId = document.getElementById('rentSelect').value;
    const d = rentDefaults[rentId];
    if (!d) { document.getElementById('excessPreview').classList.add('hidden'); return; }

    const bnPrev   = parseInt(document.getElementById('bn_previous').value)    || 0;
    const bnCur    = parseInt(document.getElementById('bn_current').value)      || 0;
    const colPrev  = parseInt(document.getElementById('color_previous').value)  || 0;
    const colCur   = parseInt(document.getElementById('color_current').value)   || 0;

    const bnPrinted    = Math.max(0, bnCur - bnPrev);
    const colPrinted   = Math.max(0, colCur - colPrev);
    const bnExcess     = Math.max(0, bnPrinted  - (d.bn_included    || 0));
    const colExcess    = Math.max(0, colPrinted - (d.color_included  || 0));
    const bnAmount     = bnExcess  * (parseFloat(d.bn_costo)    || 0);
    const colAmount    = colExcess * (parseFloat(d.color_costo)  || 0);
    const total        = bnAmount + colAmount;

    const fmt = n => '$' + parseFloat(n).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2});

    document.getElementById('pvBnPrinted').textContent    = bnPrinted;
    document.getElementById('pvBnIncluded').textContent   = d.bn_included ?? 0;
    document.getElementById('pvBnExcess').textContent     = bnExcess;
    document.getElementById('pvBnCosto').textContent      = parseFloat(d.bn_costo).toFixed(4);
    document.getElementById('pvBnAmount').textContent     = fmt(bnAmount);
    document.getElementById('pvColorPrinted').textContent = colPrinted;
    document.getElementById('pvColorIncluded').textContent= d.color_included ?? 0;
    document.getElementById('pvColorExcess').textContent  = colExcess;
    document.getElementById('pvColorCosto').textContent   = parseFloat(d.color_costo).toFixed(4);
    document.getElementById('pvColorAmount').textContent  = fmt(colAmount);

    const bar = document.getElementById('pvTotalBar');
    if (total > 0) {
        bar.className = 'px-4 py-2 border-t text-sm font-bold text-red-700 bg-red-50';
        bar.textContent = `⚠ Exceso a cobrar: ${fmt(total)}`;
    } else {
        bar.className = 'px-4 py-2 border-t text-sm font-bold text-green-700 bg-green-50';
        bar.textContent = `✓ Sin exceso — dentro del límite incluido`;
    }
    document.getElementById('excessPreview').classList.remove('hidden');
}

// Auto-fill al cargar si hay valor seleccionado (old input)
(function () {
    const sel = document.getElementById('rentSelect');
    if (sel.value) { applyRentDefaults(sel.value); calcPreview(); }
})();
</script>
@endpush
@endsection
