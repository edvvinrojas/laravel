@extends('layouts.app')
@section('title','Nueva Venta')
@section('page-title','Nueva Venta')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('sales.store') }}">
@csrf
<div class="card">
    <div class="card-header"><h3 class="font-semibold text-sm">Datos de la venta</h3></div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
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
            <label class="form-label">Equipo *</label>
            <select name="item_id" id="item_id_select" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($items as $i)
                    @php
                        $noDisponible = in_array($i->location_status, ['ASIGNADO', 'TALLER']);
                        $statusLabel  = match($i->location_status) {
                            'ASIGNADO' => 'RENTADO - NO DISPONIBLE',
                            'TALLER'   => 'EN TALLER - NO DISPONIBLE',
                            default    => $i->location_status ?? 'BODEGA',
                        };
                    @endphp
                    <option value="{{ $i->id }}"
                        @selected(old('item_id')==$i->id)
                        @if($noDisponible) disabled class="text-gray-400 bg-gray-100" @endif>
                        {{ $i->brand->name ?? '' }} {{ $i->model }} — {{ $i->serie }} [{{ $statusLabel }}]
                    </option>
                @endforeach
            </select>
            @error('item_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Sucursal</label>
            <select name="branch_id" id="branchSelect" class="form-select">
                <option value="">Sin sucursal / Seleccione cliente primero</option>
            </select>
        </div>
        <div>
            <label class="form-label">Área</label>
            <select name="area_id" id="areaSelect" class="form-select">
                <option value="">Sin área / Seleccione sucursal primero</option>
            </select>
        </div>

        <div>
            <label class="form-label">No. Factura</label>
            <input name="invoice_number" value="{{ old('invoice_number') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Precio venta *</label>
            <input name="sale_price" type="number" step="0.01" min="0" value="{{ old('sale_price') }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Estado *</label>
            <select name="sale_status" class="form-select" required>
                @foreach(['PENDIENTE','CONFIRMADA','ENTREGADA','CANCELADA'] as $s)
                <option value="{{ $s }}" @selected(old('sale_status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 pt-5">
            <input type="checkbox" name="is_foreign" value="1" id="foreign" @checked(old('is_foreign'))>
            <label for="foreign" class="text-sm">Venta foránea</label>
        </div>

        {{-- Servicios incluidos --}}
        <div class="md:col-span-2 flex items-start gap-4 pt-1">
            <div class="flex items-center gap-2">
                <input type="checkbox" name="services_included" value="1" id="servicesCheck"
                    @checked(old('services_included'))
                    onchange="document.getElementById('servicesQtyBox').classList.toggle('hidden', !this.checked)">
                <label for="servicesCheck" class="text-sm font-medium">Servicios incluidos</label>
            </div>
            <div id="servicesQtyBox" class="{{ old('services_included') ? '' : 'hidden' }} flex items-center gap-2">
                <label class="text-sm text-gray-600">Cantidad de servicios:</label>
                <input name="services_quantity" type="number" min="1" value="{{ old('services_quantity') }}"
                    class="form-input w-24 text-sm" placeholder="0">
            </div>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('sales.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>

</form>
</div>

@push('scripts')
<script>
const clientSelect = document.getElementById('clientSelect');
const branchSelect = document.getElementById('branchSelect');
const areaSelect   = document.getElementById('areaSelect');

clientSelect.addEventListener('change', function () {
    branchSelect.innerHTML = '<option value="">Cargando…</option>';
    areaSelect.innerHTML   = '<option value="">Sin área</option>';
    if (!this.value) {
        branchSelect.innerHTML = '<option value="">Sin sucursal</option>';
        return;
    }
    fetch(`/api/clients/${this.value}/branches`)
        .then(r => r.json())
        .then(data => {
            branchSelect.innerHTML = '<option value="">Sin sucursal</option>';
            data.forEach(b => {
                branchSelect.innerHTML += `<option value="${b.id}">${b.name}</option>`;
            });
        });
});

branchSelect.addEventListener('change', function () {
    areaSelect.innerHTML = '<option value="">Cargando…</option>';
    if (!this.value) {
        areaSelect.innerHTML = '<option value="">Sin área</option>';
        return;
    }
    fetch(`/api/branches/${this.value}/areas`)
        .then(r => r.json())
        .then(data => {
            areaSelect.innerHTML = '<option value="">Sin área</option>';
            data.forEach(a => {
                areaSelect.innerHTML += `<option value="${a.id}">${a.name}</option>`;
            });
        });
});
</script>
@endpush
@endsection
