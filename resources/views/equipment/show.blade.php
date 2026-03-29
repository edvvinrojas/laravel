@extends('layouts.app')
@section('title','Equipo')
@section('page-title','Detalle de Equipo')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('equipment.edit',$equipment) }}" class="btn-primary">Editar</a>
    <a href="{{ route('equipment.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="card max-w-2xl mb-6">
    <div class="card-header">
        <h3 class="font-semibold">{{ $equipment->brand?->name }} {{ $equipment->model }}</h3>
        @php
            $badge = match($equipment->location_status) {
                'BODEGA'      => 'badge-green',
                'ASIGNADO'    => 'badge-blue',
                'VENDIDO'     => 'badge-yellow',
                'TALLER'      => 'badge-red',
                default       => 'badge-gray',
            };
            $statusLabel = match($equipment->location_status) {
                'BODEGA'      => 'Bodega',
                'ASIGNADO'    => 'Asignado',
                'VENDIDO'     => 'Vendido',
                'TALLER'      => 'Taller',
                'DESCONOCIDO' => 'Desconocido',
                default       => $equipment->location_status ?? '—',
            };
        @endphp
        @if($equipment->location_status)
        <span class="{{ $badge }}">{{ $statusLabel }}</span>
        @endif
    </div>
    <div class="card-body grid grid-cols-2 gap-4 text-sm">
        <div><p class="text-gray-500">SKU</p><p class="font-mono">{{ $equipment->sku ?? '—' }}</p></div>
        <div><p class="text-gray-500">Tipo</p>
            @if($equipment->type === 'COLOR')
            <span class="badge-blue">COLOR</span>
            @else
            <span class="badge-gray">MONOCROMO</span>
            @endif
        </div>
        <div><p class="text-gray-500">Serie</p><p class="font-mono">{{ $equipment->serie }}</p></div>
        <div><p class="text-gray-500">Tóner / Drum</p><p>{{ $equipment->model_toner }}</p></div>
        <div><p class="text-gray-500">Proveedor</p><p>{{ $equipment->supplier?->name ?? '—' }}</p></div>
        <div><p class="text-gray-500">Factura</p><p>{{ $equipment->invoice ?? '—' }}</p></div>
        <div><p class="text-gray-500">Costo</p><p>${{ $equipment->cost ? number_format($equipment->cost,2) : '—' }}</p></div>
        <div><p class="text-gray-500">Activo</p>
            @if($equipment->is_active)<span class="badge-green">Sí</span>@else<span class="badge-gray">No</span>@endif
        </div>
        @if($equipment->comments)
        <div class="col-span-2"><p class="text-gray-500">Comentarios</p><p>{{ $equipment->comments }}</p></div>
        @endif
    </div>
</div>

{{-- Rents --}}
@if($equipment->rents->count())
<div class="card max-w-2xl mb-6">
    <div class="card-header"><h3 class="font-semibold text-sm">Rentas ({{ $equipment->rents->count() }})</h3></div>
    <div class="card-body p-0">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Cliente</th><th>Estado</th><th>Fecha inicio</th></tr></thead>
                <tbody>
                @foreach($equipment->rents as $rent)
                <tr>
                    <td><a href="{{ route('rents.show',$rent) }}" class="text-blue-600 hover:underline">{{ $rent->client?->name }}</a></td>
                    <td><span class="badge-gray text-xs">{{ $rent->status }}</span></td>
                    <td class="text-xs text-gray-500">{{ $rent->start_date?->format('d/m/Y') ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Sales --}}
@if($equipment->sales->count())
<div class="card max-w-2xl">
    <div class="card-header"><h3 class="font-semibold text-sm">Ventas ({{ $equipment->sales->count() }})</h3></div>
    <div class="card-body p-0">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Cliente</th><th>Estado</th><th>Fecha</th></tr></thead>
                <tbody>
                @foreach($equipment->sales as $sale)
                <tr>
                    <td><a href="{{ route('sales.show',$sale) }}" class="text-blue-600 hover:underline">{{ $sale->client?->name }}</a></td>
                    <td><span class="badge-gray text-xs">{{ $sale->status }}</span></td>
                    <td class="text-xs text-gray-500">{{ $sale->created_at->format('d/m/Y') }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
