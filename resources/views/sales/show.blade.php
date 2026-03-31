@extends('layouts.app')
@section('title','Venta')
@section('page-title','Detalle de Venta')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('sales.edit',$sale) }}" class="btn-primary">Editar</a>
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

    {{-- Accesorios y consumibles incluidos --}}
    @if($sale->accesorios->count() || $sale->consumibles->count())
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-sm">Accesorios y consumibles incluidos</h3>
        </div>
        <div class="card-body space-y-4">

            @if($sale->accesorios->count())
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Accesorios</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($sale->accesorios as $acc)
                    <div class="flex items-center gap-2 text-sm border border-gray-200 rounded px-3 py-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="font-medium">{{ $acc->nombre }}</span>
                        @if($acc->codigo)<span class="text-gray-400 text-xs">{{ $acc->codigo }}</span>@endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($sale->consumibles->count())
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Consumibles / Tóner</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($sale->consumibles as $con)
                    <div class="flex items-center gap-2 text-sm border border-gray-200 rounded px-3 py-2">
                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>
                            <span class="font-medium">{{ $con->nombre }}</span>
                            <span class="text-gray-400 text-xs ml-1">
                                {{ $con->tipo }}{{ $con->color ? ' · '.$con->color : '' }}
                            </span>
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
    @endif

</div>
@endsection
