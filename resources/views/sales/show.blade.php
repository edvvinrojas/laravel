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
            <div><p class="text-gray-500">Equipo</p><p class="font-medium">{{ $sale->item->brand->name ?? '' }} {{ $sale->item->model }}</p></div>
            <div><p class="text-gray-500">Serie</p><p class="font-mono">{{ $sale->item->serie }}</p></div>
            <div><p class="text-gray-500">Precio</p><p class="font-bold text-lg">${{ number_format($sale->sale_price,2) }}</p></div>
            <div><p class="text-gray-500">Registrado por</p><p>{{ $sale->creator?->full_name ?? '—' }}</p></div>
            <div><p class="text-gray-500">Fecha</p><p>{{ $sale->created_at->format('d/m/Y') }}</p></div>
        </div>
    </div>

</div>
@endsection
