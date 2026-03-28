{{-- resources/views/equipment/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Equipos')
@section('page-title', 'Equipos')
@section('breadcrumb', 'Administración / Equipos')

@section('content')
<div class="space-y-4">

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <form method="GET" action="{{ route('equipment.index') }}" class="flex gap-2 flex-1 max-w-md">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Buscar por SKU, modelo o serie…"
                class="form-input flex-1"
            >
            <button type="submit" class="btn-secondary btn-sm">Buscar</button>
            @if(request('search'))
                <a href="{{ route('equipment.index') }}" class="btn-secondary btn-sm">Limpiar</a>
            @endif
        </form>
        <a href="{{ route('equipment.create') }}" class="btn-primary">
            + Nuevo equipo
        </a>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <span class="text-sm text-gray-500">
                {{ $query->total() }} {{ Str::plural('equipo', $query->total()) }} encontrado{{ $query->total() === 1 ? '' : 's' }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Serie</th>
                            <th>Tipo</th>
                            <th>Estado / Ubicación</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($query as $item)
                        <tr>
                            <td class="font-mono text-sm text-gray-700">{{ $item->sku ?? '—' }}</td>
                            <td class="text-gray-700">{{ $item->brand?->name ?? '—' }}</td>
                            <td class="font-medium text-gray-900">{{ $item->model }}</td>
                            <td class="font-mono text-sm text-gray-700">{{ $item->serie }}</td>
                            <td>
                                @if($item->type === 'COLOR')
                                    <span class="badge-blue">COLOR</span>
                                @else
                                    <span class="badge-gray">MONO</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $status = $item->location_status;
                                    $badge  = match(strtolower($status ?? '')) {
                                        'disponible'  => 'badge-green',
                                        'rentado'     => 'badge-blue',
                                        'vendido'     => 'badge-yellow',
                                        'taller'      => 'badge-red',
                                        default       => 'badge-gray',
                                    };
                                @endphp
                                @if($status)
                                    <span class="{{ $badge }}">{{ $status }}</span>
                                @else
                                    <span class="text-gray-400 text-sm">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('equipment.show', $item) }}"
                                       class="btn-secondary btn-sm">Ver</a>
                                    <a href="{{ route('equipment.edit', $item) }}"
                                       class="btn-secondary btn-sm">Editar</a>
                                    <form action="{{ route('equipment.destroy', $item) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Eliminar equipo «{{ addslashes($item->model) }}»?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-400 py-10">
                                No se encontraron equipos.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if($query->hasPages())
    <div class="flex justify-end">
        {{ $query->links() }}
    </div>
    @endif

</div>
@endsection
