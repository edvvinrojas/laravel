@extends('layouts.app')
@section('title','Compras')
@section('page-title','Compras')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Buscar…">
            <select name="status" class="form-select w-48">
                <option value="">Estado</option>
                @foreach(['EN_CURSO','CONCLUIDO','RECHAZADO','EN_TRANSITO','FALTA_AUTORIZACION','FALTA_FACTURA','FALTA_PAGO_PROVEEDOR','PAUSADO_BACK_ORDERS','POR_REVISAR'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Buscar</button>
        </form>
        @if(auth()->user()->hasPermission('compras.create'))
            <a href="{{ route('purchases.create') }}" class="btn-primary">+ Nueva compra</a>
        @endif
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Artículo</th><th>Solicitante</th><th>Cant.</th><th>Tipo</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($purchases as $p)
            <tr>
                <td class="font-medium">{{ $p->name }}</td>
                <td>{{ $p->user->full_name }}</td>
                <td>{{ $p->amount }}</td>
                <td><span class="{{ $p->type==='INTERNA'?'badge-blue':'badge-purple' }}">{{ $p->type }}</span></td>
                <td><span class="badge-gray text-xs">{{ str_replace('_',' ',$p->status) }}</span></td>
                <td class="text-xs text-gray-500">{{ $p->created_at->format('d/m/Y') }}</td>
                <td class="flex gap-1">
                    @if(auth()->user()->hasPermission('compras.view'))
                        <a href="{{ route('purchases.show',$p) }}" class="btn btn-sm btn-secondary">Ver</a>
                    @endif
                    @if(auth()->user()->hasPermission('compras.edit'))
                        <a href="{{ route('purchases.edit',$p) }}" class="btn btn-sm btn-primary">Editar</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-8 text-gray-400">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $purchases->links() }}</div>
</div>
@endsection
