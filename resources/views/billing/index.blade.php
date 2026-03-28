@extends('layouts.app')
@section('title','Cobranza')
@section('page-title','Facturación / Cobranza')

@section('content')
{{-- Totales --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-yellow-600">${{ number_format($totals['pending'],2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Pendiente de cobro</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-red-600">${{ number_format($totals['overdue'],2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Vencido</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-green-600">${{ number_format($totals['paid'],2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Cobrado este mes</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2 flex-wrap">
            <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Cliente / factura…">
            <select name="status" class="form-select w-36">
                <option value="">Estado</option>
                @foreach(['PENDIENTE','PAGADO','VENCIDO'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select w-32">
                <option value="">Tipo</option>
                <option value="RENTA" @selected(request('type')==='RENTA')>RENTA</option>
                <option value="VENTA" @selected(request('type')==='VENTA')>VENTA</option>
            </select>
            <button class="btn-secondary">Filtrar</button>
            @if(request()->anyFilled(['search','status','type']))
            <a href="{{ route('billing.index') }}" class="btn-secondary">Limpiar</a>
            @endif
        </form>
        <a href="{{ route('billing.create') }}" class="btn-primary">+ Nueva factura</a>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Factura</th><th>Cliente</th><th>Tipo</th><th>Monto</th><th>Vence</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($billings as $b)
            <tr>
                <td class="font-mono text-xs">{{ $b->invoice_number ?? '—' }}</td>
                <td class="font-medium">{{ $b->client->name }}</td>
                <td><span class="{{ $b->billing_type==='RENTA'?'badge-blue':'badge-purple' }}">{{ $b->billing_type }}</span></td>
                <td class="font-medium">${{ number_format($b->amount,2) }}</td>
                <td class="{{ $b->status==='VENCIDO'?'text-red-600 font-medium':'' }}">{{ $b->due_date->format('d/m/Y') }}</td>
                <td>
                    @php $c=['PENDIENTE'=>'badge-yellow','PAGADO'=>'badge-green','VENCIDO'=>'badge-red']; @endphp
                    <span class="{{ $c[$b->status]??'badge-gray' }}">{{ $b->status }}</span>
                </td>
                <td class="flex gap-1">
                    <a href="{{ route('billing.show',$b) }}" class="btn btn-sm btn-secondary">Ver</a>
                    @if($b->status !== 'PAGADO')
                    <a href="{{ route('billing.edit',$b) }}" class="btn btn-sm btn-primary">Editar</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-8 text-gray-400">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $billings->links() }}</div>
</div>
@endsection
