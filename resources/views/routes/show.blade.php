@extends('layouts.app')
@section('title','Ruta')
@section('page-title','Detalle de Ruta')

@section('content')
<div class="flex gap-3 mb-4 flex-wrap">
    <a href="{{ route('routes.edit',$route) }}" class="btn-primary">Editar</a>
    <a href="{{ route('routes.index') }}" class="btn-secondary">← Volver</a>
    @if($route->status !== 'COMPLETADA' && $route->status !== 'CANCELADA')
    <form action="{{ route('routes.complete', $route) }}" method="POST"
          onsubmit="return confirm('¿Marcar la ruta como completada?')">
        @csrf @method('PATCH')
        <button class="btn-success">Completar Ruta</button>
    </form>
    @endif
</div>

{{-- Mapa de la ruta --}}
@php
    $stopsWithCoords = $route->stops->filter(fn($s) => $s->branch?->latitude && $s->branch?->longitude)->sortBy('stop_order');
    $stopsForMap = $route->stops->sortBy('stop_order')->map(fn($s) => [
        'order'   => $s->stop_order,
        'client'  => $s->client?->name ?? 'Sin cliente',
        'branch'  => $s->branch?->name ?? '',
        'address' => implode(', ', array_filter([$s->branch?->address ?? $s->address, $s->branch?->city ?? $s->city])),
        'lat'     => $s->branch?->latitude,
        'lng'     => $s->branch?->longitude,
        'done'    => $s->is_completed,
    ]);
@endphp
@if($stopsWithCoords->isNotEmpty())
<div class="card mb-5">
    <div class="card-header">
        <h3 class="font-semibold text-sm">Mapa de la ruta</h3>
    </div>
    <div id="routeMap" class="w-full rounded-b-lg" style="height:380px;"></div>
</div>
@endif

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
                        <select name="branch_id" id="stopBranch" class="form-select" onchange="fillBranchAddress(this)">
                            <option value="">Sin sucursal</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Dirección</label>
                        <input name="address" id="stopAddress" class="form-input" placeholder="Se llena automáticamente al seleccionar sucursal">
                    </div>
                    <div>
                        <label class="form-label">Ciudad</label>
                        <input name="city" id="stopCity" class="form-input" placeholder="Se llena automáticamente al seleccionar sucursal">
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

        {{-- Lista de paradas con acordeón --}}
        <div class="divide-y divide-gray-100">
            @forelse($route->stops->sortBy('stop_order') as $stop)
            @php
                $branch  = $stop->branch;
                $address = $branch?->address ?? $stop->address ?? null;
                $city    = $branch?->city ?? $stop->city ?? null;
                $colonia = $branch?->colonia ?? null;
                $lat     = $branch?->latitude ?? null;
                $lng     = $branch?->longitude ?? null;

                // Construir link a Google Maps
                if ($lat && $lng) {
                    $mapsUrl = "https://www.google.com/maps?q={$lat},{$lng}";
                } elseif ($address) {
                    $query   = urlencode(implode(', ', array_filter([$address, $colonia, $city])));
                    $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$query}";
                } else {
                    $mapsUrl = null;
                }
            @endphp

            <div class="px-5 py-3">
                {{-- Fila principal --}}
                <div class="flex items-center gap-3">
                    {{-- Número + estado --}}
                    <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                                {{ $stop->is_completed ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $stop->stop_order }}
                    </div>

                    {{-- Contenido principal --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-sm text-gray-900 truncate">
                                {{ $stop->client?->name ?? '—' }}
                            </span>
                            @if($stop->is_completed)
                                <span class="badge-green text-xs">Completada</span>
                            @else
                                <span class="badge-yellow text-xs">Pendiente</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 truncate">
                            {{ $branch?->name ?? ($address ?? 'Sin dirección') }}
                            @if($city) · {{ $city }} @endif
                        </div>
                    </div>

                    {{-- Botón ubicación (acordeón) --}}
                    @if($mapsUrl || $address)
                    <button type="button"
                            onclick="toggleUbicacion('ubic-{{ $stop->id }}')"
                            class="flex-shrink-0 flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 border border-blue-200 rounded px-2 py-1 hover:bg-blue-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Ubicación
                    </button>
                    @endif

                    {{-- Acciones --}}
                    <div class="flex-shrink-0 flex items-center gap-1">
                        @if(!$stop->is_completed && $stop->visit_status !== 'pospuesto')
                        <form action="{{ route('routes.stops.complete', [$route, $stop]) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn-success btn-sm" title="Marcar completada">✓</button>
                        </form>
                        <button type="button" class="btn-secondary btn-sm"
                            onclick="togglePostpone('pp-{{ $stop->id }}')">Posponer</button>
                        @endif
                        @if($stop->visit_status === 'pospuesto')
                            <span class="badge-yellow text-xs">Pospuesta</span>
                        @endif
                        <form action="{{ route('routes.stops.destroy', [$route, $stop]) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar parada?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm">✕</button>
                        </form>
                    </div>
                </div>

                {{-- Acordeón: ubicación detallada --}}
                @if($mapsUrl || $address)
                <div id="ubic-{{ $stop->id }}" class="hidden mt-3 ml-10">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm space-y-1.5">
                        @if($branch?->name)
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 w-20 flex-shrink-0">Sucursal</span>
                            <span class="font-medium">{{ $branch->name }}</span>
                        </div>
                        @endif
                        @if($address)
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 w-20 flex-shrink-0">Dirección</span>
                            <span>{{ $address }}</span>
                        </div>
                        @endif
                        @if($colonia)
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 w-20 flex-shrink-0">Colonia</span>
                            <span>{{ $colonia }}</span>
                        </div>
                        @endif
                        @if($city)
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 w-20 flex-shrink-0">Ciudad</span>
                            <span>{{ $city }}</span>
                        </div>
                        @endif
                        @if($stop->notes)
                        <div class="flex items-start gap-2">
                            <span class="text-gray-500 w-20 flex-shrink-0">Notas</span>
                            <span class="text-gray-600 text-xs">{{ $stop->notes }}</span>
                        </div>
                        @endif
                        @if($mapsUrl)
                        <div class="pt-1.5 border-t border-gray-200">
                            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 text-white bg-blue-600 hover:bg-blue-700 text-xs font-medium px-3 py-1.5 rounded transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Abrir en Google Maps
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
                {{-- Razón de posposición si aplica --}}
                @if($stop->visit_status === 'pospuesto' && $stop->no_visit_reason)
                <div class="ml-10 mt-2 text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 rounded px-3 py-2">
                    <span class="font-semibold">Motivo de posposición:</span> {{ $stop->no_visit_reason }}
                </div>
                @endif

                {{-- Formulario posponer (oculto) --}}
                @if(!$stop->is_completed && $stop->visit_status !== 'pospuesto')
                <div id="pp-{{ $stop->id }}" class="hidden ml-10 mt-2">
                    <form action="{{ route('routes.stops.postpone', [$route, $stop]) }}" method="POST" class="flex gap-2 items-end">
                        @csrf @method('PATCH')
                        <div class="flex-1">
                            <label class="form-label text-xs">Motivo de posposición *</label>
                            <input name="no_visit_reason" class="form-input text-sm" required placeholder="Ej: cliente no disponible, acceso bloqueado…">
                        </div>
                        <button type="submit" class="btn-primary btn-sm">Guardar</button>
                        <button type="button" onclick="togglePostpone('pp-{{ $stop->id }}')" class="btn-secondary btn-sm">Cancelar</button>
                    </form>
                </div>
                @endif
            </div>
            @empty
            <div class="px-5 py-10 text-center text-gray-400 text-sm">
                Sin paradas. Agrega la primera usando el botón de arriba.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@push('scripts')
<script>
// Mapa Leaflet
const stopsForMap = @json($stopsForMap);
const mapEl = document.getElementById('routeMap');
if (mapEl) {
    const coordStops = stopsForMap.filter(s => s.lat && s.lng);
    if (coordStops.length) {
        const map = L.map('routeMap');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        const bounds = [];
        coordStops.forEach(s => {
            const color  = s.done ? '#16a34a' : '#2563eb';
            const icon   = L.divIcon({
                className: '',
                html: `<div style="background:${color};color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:12px;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,.4)">${s.order}</div>`,
                iconSize: [28, 28], iconAnchor: [14, 14],
            });
            L.marker([s.lat, s.lng], {icon})
                .addTo(map)
                .bindPopup(`<strong>#${s.order} ${s.client}</strong><br>${s.branch}<br><small>${s.address}</small>`);
            bounds.push([s.lat, s.lng]);
        });

        // Línea conectando paradas en orden
        if (bounds.length > 1) {
            L.polyline(bounds, {color:'#3b82f6', weight:3, dashArray:'6 4'}).addTo(map);
        }
        map.fitBounds(bounds, {padding:[30,30]});
    }
}

let branchesData = {};

function loadStopBranches(clientId) {
    const sel = document.getElementById('stopBranch');
    sel.innerHTML = '<option value="">Cargando…</option>';
    document.getElementById('stopAddress').value = '';
    document.getElementById('stopCity').value    = '';
    branchesData = {};
    if (!clientId) { sel.innerHTML = '<option value="">Sin sucursal</option>'; return; }
    fetch(`/api/clients/${clientId}/branches`)
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">Sin sucursal</option>';
            data.forEach(b => {
                branchesData[b.id] = b;
                sel.innerHTML += `<option value="${b.id}">${b.name}</option>`;
            });
        });
}

function fillBranchAddress(sel) {
    const branch = branchesData[sel.value];
    if (!branch) {
        document.getElementById('stopAddress').value = '';
        document.getElementById('stopCity').value    = '';
        return;
    }
    const address = [branch.address, branch.colonia].filter(Boolean).join(', ');
    document.getElementById('stopAddress').value = address || '';
    document.getElementById('stopCity').value    = branch.city || '';
}

function toggleUbicacion(id) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
}

function togglePostpone(id) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
}
</script>
@endpush
