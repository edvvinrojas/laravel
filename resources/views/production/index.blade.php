@extends('layouts.app')
@section('title','Producción')
@section('page-title','Producción — Planes de Visita')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <form method="GET" action="{{ route('production.index') }}" class="flex flex-wrap gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar cliente…" class="form-input w-48">
            <select name="status" class="form-select w-36">
                <option value="">Todos</option>
                <option value="PENDIENTE" @selected(request('status')=='PENDIENTE')>Pendiente</option>
                <option value="VISITADO" @selected(request('status')=='VISITADO')>Visitado</option>
                <option value="NO_QUEDO" @selected(request('status')=='NO_QUEDO')>No quedó</option>
            </select>
            <select name="month" class="form-select w-32">
                <option value="">Mes</option>
                @foreach(range(1,12) as $m)
                <option value="{{ $m }}" @selected(request('month')==$m)>{{ \Carbon\Carbon::create(null,$m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary btn-sm">Filtrar</button>
            @if(request()->anyFilled(['search','status','month']))<a href="{{ route('production.index') }}" class="btn-secondary btn-sm">Limpiar</a>@endif
        </form>
        <a href="{{ route('production.create') }}" class="btn-primary">+ Nuevo plan</a>
    </div>

    <div class="card">
        <div class="card-header"><span class="text-sm text-gray-500">{{ $plans->total() }} plan(es)</span></div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha visita</th>
                            <th>Cliente / Sucursal</th>
                            <th>Tipo servicio</th>
                            <th>Técnicos</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $plan)
                        @php
                            $badge = match($plan->attendance_status) {
                                'VISITADO'  => 'badge-green',
                                'NO_QUEDO'  => 'badge-red',
                                default     => 'badge-yellow',
                            };
                            $label = match($plan->attendance_status) {
                                'VISITADO'  => 'Visitado',
                                'NO_QUEDO'  => 'No quedó',
                                default     => 'Pendiente',
                            };
                        @endphp
                        <tr>
                            <td class="text-sm">
                                <p class="font-medium">{{ $plan->visit_date->format('d/m/Y') }}</p>
                                <p class="text-gray-400 text-xs">{{ $plan->visit_date->format('H:i') }}</p>
                            </td>
                            <td>
                                <p class="font-medium text-gray-900">{{ $plan->client?->name }}</p>
                                <p class="text-xs text-gray-500">{{ $plan->branch?->name }}</p>
                            </td>
                            <td class="text-gray-700">{{ $plan->serviceType?->name ?? '—' }}</td>
                            <td class="text-gray-500 text-xs">
                                {{ $plan->users->pluck('full_name')->join(', ') ?: '—' }}
                            </td>
                            <td><span class="{{ $badge }}">{{ $label }}</span></td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('production.show', $plan) }}" class="btn-secondary btn-sm">Ver</a>
                                    <a href="{{ route('production.edit', $plan) }}" class="btn-secondary btn-sm">Editar</a>
                                    <form action="{{ route('production.destroy', $plan) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar plan?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-gray-400 py-10">No hay planes registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($plans->hasPages())<div class="flex justify-end">{{ $plans->links() }}</div>@endif
</div>
@endsection
