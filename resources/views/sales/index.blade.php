@extends('layouts.app')
@section('title','Ventas')
@section('page-title','Ventas')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ request('search') }}" class="form-input w-56" placeholder="Cliente / factura…">
            <select name="status" class="form-select w-40">
                <option value="">Todos</option>
                @foreach(['PENDIENTE','CONFIRMADA','ENTREGADA','CANCELADA'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Buscar</button>
        </form>
        <a href="{{ route('sales.create') }}" class="btn-primary">+ Nueva venta</a>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Factura</th><th>Cliente</th><th>Equipo</th><th>Precio</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($sales as $s)
            <tr>
                <td class="font-mono text-xs">{{ $s->invoice_number ?? '—' }}</td>
                <td>{{ $s->client->name }}</td>
                <td>{{ $s->item->brand->name ?? '' }} {{ $s->item->model }}</td>
                <td>${{ number_format($s->sale_price,2) }}</td>
                <td>
                    @php $c=['PENDIENTE'=>'badge-yellow','CONFIRMADA'=>'badge-blue','ENTREGADA'=>'badge-green','CANCELADA'=>'badge-red']; @endphp
                    <span class="{{ $c[$s->sale_status]??'badge-gray' }}">{{ $s->sale_status }}</span>
                </td>
                <td class="flex gap-1">
                    <a href="{{ route('sales.show',$s) }}" class="btn btn-sm btn-secondary">Ver</a>
                    <a href="{{ route('sales.edit',$s) }}" class="btn btn-sm btn-primary">Editar</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-8 text-gray-400">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $sales->links() }}</div>
</div>
@endsection
