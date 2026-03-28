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
            <input name="contract_number" value="{{ old('contract_number',$rent->contract_number) }}" class="form-input">
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
            <label class="form-label">Equipo *</label>
            <select name="item_id" class="form-select" required>
                @foreach($items as $i)
                <option value="{{ $i->id }}" @selected(old('item_id',$rent->item_id)==$i->id)>{{ $i->brand->name ?? '' }} {{ $i->model }} — {{ $i->serie }}</option>
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
</form>
</div>
@push('scripts')
<script>
document.getElementById('printCheck').addEventListener('change',function(){document.getElementById('printFields').classList.toggle('hidden',!this.checked);});
</script>
@endpush
@endsection
