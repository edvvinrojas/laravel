@extends('layouts.app')
@section('title','Rutas')
@section('page-title','Rutas')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Código / chofer…">
            <select name="status" class="form-select w-40">
                <option value="">Estado</option>
                @foreach(['PROGRAMADA','EN_RUTA','PAUSADA','COMPLETADA','CANCELADA'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Buscar</button>
        </form>
        <a href="{{ route('routes.create') }}" class="btn-primary">+ Nueva ruta</a>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Código</th><th>Chofer</th><th>Vehículo</th><th>Fecha</th><th>Paradas</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($routes as $r)
            @php $sc=['PROGRAMADA'=>'badge-blue','EN_RUTA'=>'badge-yellow','PAUSADA'=>'badge-gray','COMPLETADA'=>'badge-green','CANCELADA'=>'badge-red']; @endphp
            <tr>
                <td class="font-mono text-xs">{{ $r->route_code }}</td>
                <td class="font-medium">{{ $r->driver_name }}</td>
                <td>{{ $r->vehicle ?? '—' }}</td>
                <td>{{ $r->scheduled_date->format('d/m/Y') }}</td>
                <td>{{ $r->completed_stops }}/{{ $r->total_stops }}</td>
                <td><span class="{{ $sc[$r->status]??'badge-gray' }}">{{ str_replace('_',' ',$r->status) }}</span></td>
                <td class="flex gap-1">
                    <a href="{{ route('routes.show',$r) }}" class="btn btn-sm btn-secondary">Ver</a>
                    <a href="{{ route('routes.edit',$r) }}" class="btn btn-sm btn-primary">Editar</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-8 text-gray-400">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $routes->links() }}</div>
</div>
@endsection
