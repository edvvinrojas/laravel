@extends('layouts.app')
@section('title','Editar Renta')
@section('page-title','Editar Renta')

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('rents.update',$rent) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-header"><h3 class="font-semibold text-sm">Editar contrato {{ $rent->contract_number }}</h3></div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="form-label">No. Contrato</label>
            <input name="contract_number" value="{{ old('contract_number',$rent->contract_number) }}" class="form-input bg-gray-50" readonly>
        </div>

        <div>
            <label class="form-label">Cliente *</label>
            <select name="client_id" class="form-select" required>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(old('client_id',$rent->client_id)==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Sucursal</label>
            <select name="branch_id" id="branchSelect" class="form-select">
                <option value="">Sin sucursal</option>
                @foreach($branches as $b)
                <option value="{{ $b->id }}" @selected(old('branch_id',$rent->branch_id)==$b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Área</label>
            <select name="area_id" id="areaSelect" class="form-select">
                <option value="">Sin área</option>
                @foreach($areas as $a)
                <option value="{{ $a->id }}" @selected(old('area_id',$rent->area_id)==$a->id)>{{ $a->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Equipo *</label>
            <select name="item_id" id="item_id_select" class="form-select" required>
                @foreach($items as $i)
                    @php $asignado = $i->location_status === 'ASIGNADO' && $i->id !== $rent->item_id; @endphp
                    <option value="{{ $i->id }}"
                        @selected(old('item_id',$rent->item_id)==$i->id)
                        @if($asignado) disabled class="text-gray-400 bg-gray-100" @endif>
                        {{ $i->brand->name ?? '' }} {{ $i->model }} — {{ $i->serie }}
                        @if($asignado) [RENTADO] @else [{{ $i->location_status ?? 'BODEGA' }}] @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Renta mensual *</label>
            <input name="rent" type="number" step="0.01" min="0" value="{{ old('rent',$rent->rent) }}" class="form-input" required>
        </div>

        <div>
            <label class="form-label">Estatus *</label>
            <select name="contract_status" class="form-select" required>
                @foreach(['PENDIENTE','SIN_FIRMAR','VIGENTE','FINALIZADO','CANCELADO'] as $s)
                <option value="{{ $s }}" @selected(old('contract_status',$rent->contract_status)===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Fecha inicio *</label>
            <input name="start_date" type="date" value="{{ old('start_date',$rent->start_date?->format('Y-m-d')) }}" class="form-input" required>
        </div>

        <div>
            <label class="form-label">Fecha fin</label>
            <input name="end_date" type="date" value="{{ old('end_date',$rent->end_date?->format('Y-m-d')) }}" class="form-input">
        </div>

        <div class="flex items-center gap-4 pt-5">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_foreign" value="1" @checked(old('is_foreign',$rent->is_foreign))> Foráneo
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="has_print_service" value="1" id="printCheck" @checked(old('has_print_service',$rent->has_print_service))> Servicio impresión
            </label>
        </div>

        <div id="printFields" class="col-span-2 grid grid-cols-2 gap-4 {{ (old('has_print_service',$rent->has_print_service)) ? '' : 'hidden' }}">
            <div>
                <label class="form-label">BN incluidas</label>
                <input name="bn_included" type="number" min="0" value="{{ old('bn_included',$rent->bn_included) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Costo exceso BN</label>
                <input name="bn_cost_per_excess" type="number" step="0.0001" value="{{ old('bn_cost_per_excess',$rent->bn_cost_per_excess) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Color incluidas</label>
                <input name="color_included" type="number" min="0" value="{{ old('color_included',$rent->color_included) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Costo exceso Color</label>
                <input name="color_cost_per_excess" type="number" step="0.0001" value="{{ old('color_cost_per_excess',$rent->color_cost_per_excess) }}" class="form-input">
            </div>
        </div>

    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('rents.show',$rent) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>

@include('components.accesorios-consumibles-selector', [
    'itemSelectId'        => 'item_id_select',
    'selectedAccesorios'  => $rent->accesorios,
    'selectedConsumibles' => $rent->consumibles,
])

</form>
</div>

@push('scripts')
<script>
document.getElementById('printCheck').addEventListener('change', function() {
    document.getElementById('printFields').classList.toggle('hidden', !this.checked);
});

// Recargar sucursales al cambiar cliente
document.querySelector('[name="client_id"]').addEventListener('change', function() {
    const clientId  = this.value;
    const branchSel = document.getElementById('branchSelect');
    const areaSel   = document.getElementById('areaSelect');
    branchSel.innerHTML = '<option value="">Cargando…</option>';
    areaSel.innerHTML   = '<option value="">Sin área</option>';
    if (!clientId) { branchSel.innerHTML = '<option value="">Sin sucursal</option>'; return; }
    fetch(`/api/clients/${clientId}/branches`)
        .then(r => r.json())
        .then(data => {
            branchSel.innerHTML = '<option value="">Sin sucursal</option>';
            data.forEach(b => branchSel.innerHTML += `<option value="${b.id}">${b.name}</option>`);
        });
});

// Recargar áreas al cambiar sucursal
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
