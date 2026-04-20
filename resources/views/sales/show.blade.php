@extends('layouts.app')
@section('title','Venta')
@section('page-title','Detalle de Venta')

@section('content')
<div class="flex gap-3 mb-4">
    @if(auth()->user()->hasPermission('ventas.edit'))
        <a href="{{ route('sales.edit',$sale) }}" class="btn-primary">Editar</a>
    @endif
    @if(auth()->user()->hasPermission('ventas.view'))
        <a href="{{ route('sales.pdf',$sale) }}" target="_blank" class="btn-secondary">PDF</a>
    @endif
    <a href="{{ route('sales.index') }}" class="btn-secondary">← Volver</a>
</div>

<div class="space-y-4 max-w-2xl">

    {{-- Datos principales --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold">Factura {{ $sale->invoice_number ?? 'Sin número' }}</h3>
            @php $c=['PENDIENTE'=>'badge-yellow','CONFIRMADA'=>'badge-blue','ENTREGADA'=>'badge-green','CANCELADA'=>'badge-red']; @endphp
            <span class="{{ $c[$sale->sale_status]??'badge-gray' }}">{{ $sale->sale_status }}</span>
        </div>
        <div class="card-body grid grid-cols-2 gap-4 text-sm">
            <div><p class="text-gray-500">Cliente</p><p class="font-medium">{{ $sale->client->name }}</p></div>
            <div><p class="text-gray-500">Equipos</p><p class="font-medium">{{ $sale->items->count() ?: 1 }}</p></div>
            <div><p class="text-gray-500">Precio</p><p class="font-bold text-lg">${{ number_format($sale->sale_price,2) }}</p></div>
            <div><p class="text-gray-500">Registrado por</p><p>{{ $sale->creator?->full_name ?? '—' }}</p></div>
            <div><p class="text-gray-500">Fecha</p><p>{{ $sale->created_at->format('d/m/Y') }}</p></div>
            <div class="col-span-2 border-t pt-3">
                <p class="text-gray-500 mb-2">Asignacion por equipo</p>
                <div class="space-y-2">
                    @foreach(($sale->items->count() ? $sale->items : collect([$sale->item])) as $eq)
                        @php
                            $branchId = $eq->pivot->branch_id ?? $sale->branch_id;
                            $areaId = $eq->pivot->area_id ?? $sale->area_id;
                            $branchName = optional($sale->client->branches->firstWhere('id', $branchId))->name;
                            $branchModel = $sale->client->branches->firstWhere('id', $branchId);
                            $areaName = $branchModel ? optional($branchModel->areas->firstWhere('id', $areaId))->name : null;
                        @endphp
                        <div class="rounded border border-gray-200 p-2">
                            <p class="font-medium">{{ $eq->brand->name ?? '' }} {{ $eq->model }} <span class="font-mono text-xs text-gray-500">{{ $eq->serie }}</span></p>
                            <p class="text-xs text-gray-600">Sucursal: {{ $branchName ?: 'Sin sucursal' }} | Area: {{ $areaName ?: 'Sin area' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
