@extends('layouts.app')
@section('title','Compra')
@section('page-title','Detalle de Compra')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('purchases.edit',$purchase) }}" class="btn-primary">Editar</a>
    <a href="{{ route('purchases.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="card max-w-2xl">
    <div class="card-header">
        <h3 class="font-semibold">{{ $purchase->name }}</h3>
        <span class="badge-gray text-xs">{{ str_replace('_',' ',$purchase->status) }}</span>
    </div>
    <div class="card-body grid grid-cols-2 gap-4 text-sm">
        <div><p class="text-gray-500">Solicitante</p><p>{{ $purchase->user->full_name }}</p></div>
        <div><p class="text-gray-500">Tipo</p><p>{{ $purchase->type }}</p></div>
        <div><p class="text-gray-500">Cantidad</p><p>{{ $purchase->amount }}</p></div>
        <div><p class="text-gray-500">Autorizada</p><p>{{ $purchase->authorized_amount ?? '—' }}</p></div>
        @if($purchase->supplier1_name)
        <div><p class="text-gray-500">Cotización 1</p><p>{{ $purchase->supplier1_name }} — ${{ number_format($purchase->supplier1_cost,2) }}</p></div>
        @endif
        @if($purchase->supplier2_name)
        <div><p class="text-gray-500">Cotización 2</p><p>{{ $purchase->supplier2_name }} — ${{ number_format($purchase->supplier2_cost,2) }}</p></div>
        @endif
        @if($purchase->supplier3_name)
        <div><p class="text-gray-500">Cotización 3</p><p>{{ $purchase->supplier3_name }} — ${{ number_format($purchase->supplier3_cost,2) }}</p></div>
        @endif
        @if($purchase->comments)
        <div class="col-span-2"><p class="text-gray-500">Comentarios</p><p>{{ $purchase->comments }}</p></div>
        @endif
    </div>
</div>
@endsection
