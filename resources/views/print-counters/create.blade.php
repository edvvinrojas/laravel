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
            <input name="bn_previous" id="bn_previous" type="number" min="0" value="{{ old('bn_previous',0) }}" class="form-input bg-gray-50" readonly>
            <p class="text-xs text-gray-400 mt-1">Lectura del equipo al inicio del período (automático)</p>
        </div>
        <div>
            <label class="form-label">Contador BN actual *</label>
            <input name="bn_current" id="bn_current" type="number" min="0" value="{{ old('bn_current',0) }}" class="form-input" required>
            <p class="text-xs text-gray-400 mt-1">Lectura del equipo al final del período</p>
            @error('bn_current')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
        </div>
        <div>
            <label class="form-label">Contador Color anterior *</label>
            <input name="color_previous" id="color_previous" type="number" min="0" value="{{ old('color_previous',0) }}" class="form-input bg-gray-50" readonly>
            <p class="text-xs text-gray-400 mt-1">Lectura del equipo al inicio del período (automático)</p>
        </div>
        <div>
            <label class="form-label">Contador Color actual *</label>
            <input name="color_current" id="color_current" type="number" min="0" value="{{ old('color_current',0) }}" class="form-input" required>
            <p class="text-xs text-gray-400 mt-1">Lectura del equipo al final del período</p>
            @error('color_current')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
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

    {{-- Mostrar errores de validación --}}
    @if($errors->any())
    <div class="px-5 py-4 border-t border-gray-100 bg-red-50 border-red-200">
        <p class="text-red-700 font-medium text-sm mb-2">Errores en el formulario:</p>
        <ul class="list-disc list-inside text-red-600 text-sm">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('print-counters.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>

<script>
const rentDefaults = {!! json_encode($rentDefaults) !!};

document.getElementById('rentSelect').addEventListener('change', function() {
    const rentId = this.value;
    if (!rentId || !rentDefaults[rentId]) {
        document.getElementById('rentInfo').classList.add('hidden');
        return;
    }
    
    const defaults = rentDefaults[rentId];
    document.getElementById('bn_previous').value = defaults.bn || 0;
    document.getElementById('color_previous').value = defaults.color || 0;
    
    // Mostrar info
    const rentInfo = document.getElementById('rentInfo');
    document.getElementById('infobnIncluded').textContent = defaults.bn_included || 0;
    document.getElementById('infoColorIncluded').textContent = defaults.color_included || 0;
    document.getElementById('infoBnCosto').textContent = (defaults.bn_costo || 0).toFixed(2);
    document.getElementById('infoColorCosto').textContent = (defaults.color_costo || 0).toFixed(2);
    rentInfo.classList.remove('hidden');
});

// Validación: contador actual >= anterior
document.getElementById('bn_current').addEventListener('blur', function() {
    const prev = parseInt(document.getElementById('bn_previous').value) || 0;
    const curr = parseInt(this.value) || 0;
    if (curr < prev) {
        alert(`El contador BN actual (${curr}) no puede ser menor que el anterior (${prev})`);
        this.value = prev;
    }
});

document.getElementById('color_current').addEventListener('blur', function() {
    const prev = parseInt(document.getElementById('color_previous').value) || 0;
    const curr = parseInt(this.value) || 0;
    if (curr < prev) {
        alert(`El contador Color actual (${curr}) no puede ser menor que el anterior (${prev})`);
        this.value = prev;
    }
});

// Si ya hay una renta seleccionada, llenar valores
if (document.getElementById('rentSelect').value) {
    document.getElementById('rentSelect').dispatchEvent(new Event('change'));
}
</script>
@endsection
