@extends('layouts.app')
@section('title','Ruta')
@section('page-title','Detalle de Ruta')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('routes.edit',$route) }}" class="btn-primary">Editar</a>
    <a href="{{ route('routes.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Info general --}}
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
            @if($route->notes)
            <div><p class="text-gray-500">Notas</p><p class="text-xs text-gray-600">{{ $route->notes }}</p></div>
            @endif
            <div><p class="text-gray-500">Progreso</p>
                <div class="mt-1 bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full"
                         style="width:{{ $route->total_stops>0 ? round($route->completed_stops/$route->total_stops*100) : 0 }}%"></div>
                </div>
                <p class="text-xs mt-0.5 text-gray-500">{{ $route->completed_stops }}/{{ $route->total_stops }} paradas</p>
            </div>
        </div>
    </div>

    {{-- Paradas --}}
    <div class="card lg:col-span-2">
        <div class="card-header flex items-center justify-between">
            <h3 class="font-semibold text-sm">Paradas</h3>
            <button onclick="document.getElementById('stop-form').classList.toggle('hidden')"
                    class="btn-primary btn-sm">+ Agregar parada</button>
        </div>

        {{-- Formulario nueva parada --}}
        <div id="stop-form" class="hidden border-b border-gray-100 bg-gray-50 px-5 py-4">
            <form action="{{ route('routes.stops.store', $route) }}" method="POST">
                @csrf
                <p class="text-sm font-medium text-gray-700 mb-3">Nueva parada</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Cliente</label>
                        <select name="client_id" id="stopClient" class="form-select" onchange="loadStopBranches(this.value)">
                            <option value="">Sin cliente</option>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Sucursal</label>
                        <select name="branch_id" id="stopBranch" class="form-select">
                            <option value="">Sin sucursal</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Dirección (si no hay sucursal)</label>
                        <input name="address" class="form-input" placeholder="Calle y número">
                    </div>
                    <div>
                        <label class="form-label">Ciudad</label>
                        <input name="city" class="form-input" placeholder="Ciudad">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Notas</label>
                        <input name="notes" class="form-input" placeholder="Instrucciones opcionales">
                    </div>
                </div>
                <div class="flex gap-2 mt-3">
                    <button type="submit" class="btn-primary btn-sm">Guardar parada</button>
                    <button type="button"
                            onclick="document.getElementById('stop-form').classList.add('hidden')"
                            class="btn-secondary btn-sm">Cancelar</button>
                </div>
            </form>
        </div>

        <div class="table-wrap rounded-none border-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Sucursal / Dirección</th>
                        <th>Ciudad</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($route->stops->sortBy('stop_order') as $stop)
                <tr>
                    <td class="font-medium text-gray-500">{{ $stop->stop_order }}</td>
                    <td>{{ $stop->client?->name ?? '—' }}</td>
                    <td class="text-sm text-gray-600">{{ $stop->branch?->name ?? $stop->address ?? '—' }}</td>
                    <td class="text-sm text-gray-600">{{ $stop->city ?? '—' }}</td>
                    <td>
                        @if($stop->is_completed)
                            <span class="badge-green">Completada</span>
                        @else
                            <span class="badge-yellow">Pendiente</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            @if(!$stop->is_completed)
                            <form action="{{ route('routes.stops.complete', [$route, $stop]) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-success btn-sm">✓</button>
                            </form>
                            @endif
                            <form action="{{ route('routes.stops.destroy', [$route, $stop]) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar parada?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-6 text-gray-400">Sin paradas. Agrega la primera usando el botón de arriba.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadStopBranches(clientId) {
    const sel = document.getElementById('stopBranch');
    sel.innerHTML = '<option value="">Cargando…</option>';
    if (!clientId) { sel.innerHTML = '<option value="">Sin sucursal</option>'; return; }
    fetch(`/clients/${clientId}/branches`)
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">Sin sucursal</option>';
            data.forEach(b => sel.innerHTML += `<option value="${b.id}">${b.name}</option>`);
        });
}
</script>
@endpush
