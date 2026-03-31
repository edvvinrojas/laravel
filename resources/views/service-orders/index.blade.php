@extends('layouts.app')
@section('title','Órdenes de Servicio')
@section('page-title','Órdenes de Servicio')

@section('content')
<div class="flex items-center justify-between mb-4">
    <form class="flex gap-2">
        <input name="search" value="{{ request('search') }}" class="form-input w-56" placeholder="Buscar cliente…">
        <select name="status" class="form-select w-36">
            <option value="">Todos</option>
            <option value="PENDIENTE" @selected(request('status')==='PENDIENTE')>Pendiente</option>
            <option value="COMPLETADO" @selected(request('status')==='COMPLETADO')>Completado</option>
        </select>
        <button class="btn-secondary">Filtrar</button>
    </form>
    <a href="{{ route('service-orders.create') }}" class="btn-primary">+ Nueva orden</a>
</div>

<div class="table-wrap">
    <table class="table">
        <thead><tr>
            <th>#</th><th>Ingeniero</th><th>Cliente</th><th>Sucursal</th><th>Tipo</th><th>Estatus</th><th>Fecha</th><th></th>
        </tr></thead>
        <tbody>
        @forelse($orders as $o)
        <tr>
            <td>{{ $o->id }}</td>
            <td>{{ $o->engineer->full_name }}</td>
            <td>{{ $o->client->name }}</td>
            <td>{{ $o->branch?->name ?? '—' }}</td>
            <td><span class="badge-blue">{{ str_replace('_',' ',$o->tipo_orden) }}</span></td>
            <td>
                @if($o->status==='COMPLETADO')
                    <span class="badge-green">Completado</span>
                @else
                    <span class="badge-yellow">Pendiente</span>
                @endif
            </td>
            <td>{{ $o->created_at->format('d/m/Y') }}</td>
            <td><a href="{{ route('service-orders.show',$o) }}" class="btn-secondary btn-sm">Ver</a></td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center py-10 text-gray-400">Sin órdenes de servicio.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
