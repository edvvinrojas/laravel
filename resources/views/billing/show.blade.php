@extends('layouts.app')
@section('title','Factura')
@section('page-title','Detalle de Factura')

@section('content')
<div class="flex gap-3 mb-4">
    @if($billing->status !== 'PAGADO')
    <a href="{{ route('billing.edit',$billing) }}" class="btn-primary">Editar</a>
    @endif
    <a href="{{ route('billing.index') }}" class="btn-secondary">← Volver</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold">Factura {{ $billing->invoice_number ?? 'Sin número' }}</h3>
            @php $c=['PENDIENTE'=>'badge-yellow','PAGADO'=>'badge-green','VENCIDO'=>'badge-red']; @endphp
            <span class="{{ $c[$billing->status]??'badge-gray' }}">{{ $billing->status }}</span>
        </div>
        <div class="card-body grid grid-cols-2 gap-4 text-sm">
            <div><p class="text-gray-500">Cliente</p><p class="font-medium">{{ $billing->client->name }}</p></div>
            <div><p class="text-gray-500">Tipo</p><p>{{ $billing->billing_type }}</p></div>
            <div><p class="text-gray-500">Monto</p><p class="text-xl font-bold">${{ number_format($billing->amount,2) }}</p></div>
            <div><p class="text-gray-500">Fecha objetivo</p><p>{{ $billing->target_date->format('d/m/Y') }}</p></div>
            <div><p class="text-gray-500">Vence</p><p class="{{ $billing->status==='VENCIDO'?'text-red-600 font-medium':'' }}">{{ $billing->due_date->format('d/m/Y') }}</p></div>
            <div><p class="text-gray-500">Pago recibido</p><p>{{ $billing->payment_date?->format('d/m/Y') ?? '—' }}</p></div>
            @if($billing->comment)
            <div class="col-span-2"><p class="text-gray-500">Comentario</p><p>{{ $billing->comment }}</p></div>
            @endif
        </div>
    </div>

    @if($billing->status !== 'PAGADO')
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-sm">Registrar pago</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('billing.pay',$billing) }}">
                @csrf @method('PATCH')
                <label class="form-label">Fecha de pago *</label>
                <input name="payment_date" type="date" value="{{ date('Y-m-d') }}" class="form-input mb-4" required>
                <button class="btn-success w-full justify-center">Marcar como pagado</button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
