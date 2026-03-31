@extends('layouts.app')
@section('title','Detalle de Compra')
@section('page-title','Detalle de Compra')

@section('content')
@php
    $user      = auth()->user();
    $isCompras = $user->rol === 'administrador' || $user->department === 'administracion';
    $isGerencia= in_array($user->rol, ['gerencia','administrador']);
    $statusColors = [
        'SOLICITADO' => 'bg-yellow-100 text-yellow-800',
        'AUTORIZADO' => 'bg-blue-100 text-blue-800',
        'PEDIDO'     => 'bg-purple-100 text-purple-800',
        'LLEGO'      => 'bg-indigo-100 text-indigo-800',
        'ENTREGADO'  => 'bg-green-100 text-green-800',
        'EN_CURSO'   => 'bg-gray-100 text-gray-700',
    ];
    $statusColor = $statusColors[$purchase->status] ?? 'bg-gray-100 text-gray-700';
@endphp

<div class="flex gap-3 mb-4 flex-wrap">
    @if($isGerencia && $purchase->status === 'SOLICITADO')
        <form action="{{ route('purchases.approve', $purchase) }}" method="POST"
              onsubmit="return confirm('¿Autorizar esta solicitud de compra?')">
            @csrf @method('PATCH')
            <button type="submit" class="btn-primary">✓ Autorizar</button>
        </form>
    @endif

    @if($isCompras && $purchase->status === 'AUTORIZADO')
        <form action="{{ route('purchases.status', $purchase) }}" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="PEDIDO">
            <button type="submit" class="btn-primary">Marcar como Pedido</button>
        </form>
    @endif

    @if($isCompras && $purchase->status === 'PEDIDO')
        <form action="{{ route('purchases.status', $purchase) }}" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="LLEGO">
            <button type="submit" class="btn-primary">Marcar como Llegó</button>
        </form>
    @endif

    @if($isCompras && $purchase->status === 'LLEGO')
        <form action="{{ route('purchases.status', $purchase) }}" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="ENTREGADO">
            <button type="submit" class="btn-primary">Marcar como Entregado</button>
        </form>
    @endif

    @if($isCompras || $purchase->user_id === $user->id)
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn-secondary">Editar</a>
    @endif
    <a href="{{ route('purchases.index') }}" class="btn-secondary">← Volver</a>
</div>

<div class="card max-w-2xl">
    <div class="card-header flex items-center justify-between">
        <h3 class="font-semibold">{{ $purchase->name }}</h3>
        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
            {{ str_replace('_',' ', $purchase->status) }}
        </span>
    </div>
    <div class="card-body grid grid-cols-2 gap-4 text-sm">
        <div><p class="text-gray-500">Solicitante</p><p>{{ $purchase->user->full_name }}</p></div>
        <div><p class="text-gray-500">Tipo</p><p>{{ $purchase->type }}</p></div>
        <div><p class="text-gray-500">Cantidad solicitada</p><p>{{ $purchase->amount }}</p></div>
        <div><p class="text-gray-500">Cantidad autorizada</p><p>{{ $purchase->authorized_amount ?? '—' }}</p></div>
        @if($purchase->quality)
        <div><p class="text-gray-500">Calidad</p><p>{{ $purchase->quality }}</p></div>
        @endif
        @if($purchase->justification)
        <div class="col-span-2"><p class="text-gray-500">Justificación</p><p>{{ $purchase->justification }}</p></div>
        @endif
        @if($purchase->comments)
        <div class="col-span-2"><p class="text-gray-500">Comentarios</p><p>{{ $purchase->comments }}</p></div>
        @endif

        @if($purchase->authorized_by_area_chief_id)
        <div><p class="text-gray-500">Autorizado por</p><p>{{ $purchase->areaChief->full_name }}</p></div>
        <div><p class="text-gray-500">Fecha autorización</p><p>{{ $purchase->authorized_by_area_chief_date?->format('d/m/Y') }}</p></div>
        @endif

        {{-- Cotizaciones: solo visibles para compras/admin --}}
        @if($isCompras)
        <div class="col-span-2 border-t pt-3">
            <p class="font-medium text-gray-700 mb-2">Cotizaciones de proveedores</p>
            <div class="grid grid-cols-3 gap-3">
                @foreach([1,2,3] as $n)
                @php $sn = "supplier{$n}_name"; $sc = "supplier{$n}_cost"; @endphp
                <div class="bg-gray-50 rounded p-3">
                    <p class="text-xs text-gray-500 mb-1">Proveedor {{ $n }}</p>
                    @if($purchase->$sn)
                        <p class="font-medium text-sm">{{ $purchase->$sn }}</p>
                        <p class="text-green-700 font-semibold">${{ number_format($purchase->$sc, 2) }}</p>
                    @else
                        <p class="text-gray-400 text-xs">Sin cotización</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        @if($purchase->shipping_method || $purchase->shipping_code)
        <div class="col-span-2 border-t pt-3">
            <p class="font-medium text-gray-700 mb-2">Envío</p>
            <div class="grid grid-cols-3 gap-3 text-sm">
                <div><p class="text-gray-500">Método</p><p>{{ $purchase->shipping_method ?? '—' }}</p></div>
                <div><p class="text-gray-500">Costo</p><p>${{ number_format($purchase->shipping_cost ?? 0, 2) }}</p></div>
                <div><p class="text-gray-500">Código</p><p>{{ $purchase->shipping_code ?? '—' }}</p></div>
            </div>
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
