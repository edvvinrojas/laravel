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
            <select name="item_id" id="item_id_select" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($items as $i)
                    @php $asignado = $i->location_status === 'ASIGNADO'; @endphp
                    <option value="{{ $i->id }}"
                        @selected(old('item_id')==$i->id)
                        @if($asignado) disabled class="text-gray-400 bg-gray-100" @endif
                        data-status="{{ $i->location_status }}"
                        data-bn="{{ $i->contador_inicial_bn ?? 0 }}"
                        data-color="{{ $i->contador_inicial_color ?? 0 }}">
                        {{ $i->brand->name ?? '' }} {{ $i->model }} — {{ $i->serie }}
                        @if($asignado) [RENTADO - NO DISPONIBLE] @else [{{ $i->location_status ?? 'BODEGA' }}] @endif
                    </option>
                @endforeach
            </select>
            @error('item_id')<p class="form-error">{{ $message }}</p>@enderror
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

@include('components.accesorios-consumibles-selector', [
    'itemSelectId' => 'item_id_select',
])

</form>
</div>

@push('scripts')
<script>
// Mostrar/ocultar campos de impresión
document.getElementById('printCheck').addEventListener('change', function() {
    document.getElementById('printFields').classList.toggle('hidden', !this.checked);
});

// Al seleccionar equipo: mostrar contadores con valores sugeridos
document.getElementById('item_id_select').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const section  = document.getElementById('contadorSection');
    if (!this.value) { section.classList.add('hidden'); return; }
    section.classList.remove('hidden');
    document.getElementById('contador_bn').value    = selected.dataset.bn    || 0;
    document.getElementById('contador_color').value = selected.dataset.color || 0;
});

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
