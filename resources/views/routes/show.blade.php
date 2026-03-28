@extends('layouts.app')
@section('title','Ruta')
@section('page-title','Detalle de Ruta')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('routes.edit',$route) }}" class="btn-primary">Editar</a>
    <a href="{{ route('routes.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold">{{ $route->route_code }}</h3>
            @php $sc=['PROGRAMADA'=>'badge-blue','EN_RUTA'=>'badge-yellow','PAUSADA'=>'badge-gray','COMPLETADA'=>'badge-green','CANCELADA'=>'badge-red']; @endphp
            <span class="{{ $sc[$route->status]??'badge-gray' }}">{{ str_replace('_',' ',$route->status) }}</span>
        </div>
        <div class="card-body text-sm space-y-3">
            <div><p class="text-gray-500">Chofer</p><p class="font-medium">{{ $route->driver_name }}</p></div>
            <div><p class="text-gray-500">Vehículo</p><p>{{ $route->vehicle ?? '—' }}</p></div>
            <div><p class="text-gray-500">Fecha</p><p>{{ $route->scheduled_date->format('d/m/Y') }}</p></div>
            <div><p class="text-gray-500">Progreso</p>
                <div class="mt-1 bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width:{{ $route->total_stops>0 ? round($route->completed_stops/$route->total_stops*100) : 0 }}%"></div>
                </div>
                <p class="text-xs mt-0.5 text-gray-500">{{ $route->completed_stops }}/{{ $route->total_stops }} paradas</p>
            </div>
        </div>
    </div>
    <div class="card lg:col-span-2">
        <div class="card-header"><h3 class="font-semibold text-sm">Paradas</h3></div>
        <div class="table-wrap rounded-none border-0">
            <table class="table">
                <thead><tr><th>#</th><th>Cliente</th><th>Sucursal</th><th>Ciudad</th><th>Estado</th></tr></thead>
                <tbody>
                @forelse($route->stops->sortBy('stop_order') as $stop)
                <tr>
                    <td>{{ $stop->stop_order }}</td>
                    <td>{{ $stop->client?->name ?? '—' }}</td>
                    <td>{{ $stop->branch?->name ?? $stop->address ?? '—' }}</td>
                    <td>{{ $stop->city ?? '—' }}</td>
                    <td>
                        @if($stop->is_completed)<span class="badge-green">Completada</span>
                        @else<span class="badge-yellow">{{ str_replace('_',' ',$stop->visit_status) }}</span>@endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4 text-gray-400">Sin paradas</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
