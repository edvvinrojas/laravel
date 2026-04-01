@extends('layouts.app')
@section('title','Editar Venta')
@section('page-title','Editar Venta')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('sales.update',$sale) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Cliente *</label>
            <select name="client_id" class="form-select" required>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(old('client_id',$sale->client_id)==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Equipo *</label>
            <select name="item_id" id="item_id_select" class="form-select" required>
                @foreach($items as $i)
                <option value="{{ $i->id }}" @selected(old('item_id',$sale->item_id)==$i->id)>{{ $i->brand->name ?? '' }} {{ $i->model }} — {{ $i->serie }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">No. Factura</label>
            <input name="invoice_number" value="{{ old('invoice_number',$sale->invoice_number) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Precio venta *</label>
            <input name="sale_price" type="number" step="0.01" value="{{ old('sale_price',$sale->sale_price) }}" class="form-input" required>
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
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('sales.show',$sale) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>

@include('components.accesorios-consumibles-selector', [
    'itemSelectId'        => 'item_id_select',
    'selectedAccesorios'  => $sale->accesorios,
    'selectedConsumibles' => $sale->consumibles,
])

</form>
</div>
@endsection
