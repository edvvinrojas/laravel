@extends('layouts.app')
@section('title','Detalle de Crédito')
@section('page-title','Detalle de Crédito')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('credits.edit',$credit) }}" class="btn-primary">Editar</a>
    <a href="{{ route('credits.index') }}" class="btn-secondary">&larr; Volver</a>
</div>

<div class="card max-w-2xl">
    <div class="card-header">
        <h3 class="font-semibold">{{ $credit->employee->nombre }}</h3>
        @php $sc=['SOLICITADO'=>'badge-yellow','AUTORIZADO'=>'badge-blue','LIQUIDADO'=>'badge-green','CANCELADO'=>'badge-red']; @endphp
        <span class="{{ $sc[$credit->status] ?? 'badge-gray' }}">{{ $credit->status }}</span>
    </div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
        <div><p class="text-gray-500">Cantidad del crédito</p><p class="font-semibold">${{ number_format($credit->credit_amount,2) }}</p></div>
        <div><p class="text-gray-500">Descuento quincenal</p><p class="font-semibold">${{ number_format($credit->biweekly_discount,2) }}</p></div>
        <div><p class="text-gray-500">Monto pendiente</p><p class="font-semibold">${{ number_format($credit->pending_amount,2) }}</p></div>
        <div><p class="text-gray-500">Quincenas pendientes</p><p class="font-semibold">{{ $credit->pending_biweeks }}</p></div>
        <div><p class="text-gray-500">Fecha de aprobación</p><p>{{ $credit->approval_date?->format('d/m/Y') ?? 'N/A' }}</p></div>
        <div><p class="text-gray-500">Fecha término de pago</p><p>{{ $credit->payment_end_date?->format('d/m/Y') ?? 'N/A' }}</p></div>
        <div class="md:col-span-2"><p class="text-gray-500">Motivo del crédito</p><p>{{ $credit->credit_reason }}</p></div>
        @if($credit->approvedBy)
        <div class="md:col-span-2"><p class="text-gray-500">Autorizado por</p><p>{{ $credit->approvedBy->full_name }}</p></div>
        @endif
    </div>
</div>
@endsection
