@extends('layouts.app')
@section('title','Almacén')
@section('page-title','Almacén')

@section('content')
<div class="space-y-4">

    {{-- Pestañas --}}
    <div class="flex border-b border-gray-200 gap-1">
        <a href="{{ route('almacen.index', ['tab' => 'equipos']) }}"
           class="px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 transition-colors
                  {{ $tab === 'equipos' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Equipos
        </a>
        <a href="{{ route('almacen.index', ['tab' => 'inventario']) }}"
           class="px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 transition-colors
                  {{ $tab === 'inventario' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Inventario
        </a>
        <div class="flex-1"></div>
        {{-- Catálogos --}}
        <div class="flex items-center gap-2 pb-1">
            @if($tab === 'equipos')
            <a href="{{ route('brands.index') }}" class="btn-secondary btn-sm">Marcas</a>
            <a href="{{ route('suppliers.index') }}" class="btn-secondary btn-sm">Proveedores</a>
            @else
            <a href="{{ route('item-catalog.index') }}" class="btn-secondary btn-sm">Catálogo</a>
            <a href="{{ route('shelves.index') }}" class="btn-secondary btn-sm">Estantes</a>
            @endif
        </div>
    </div>

    {{-- ===== EQUIPOS ===== --}}
    @if($tab === 'equipos')
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" action="{{ route('almacen.index') }}" class="flex gap-2 flex-1 max-w-md">
                <input type="hidden" name="tab" value="equipos">
                <input type="text" name="q_eq" value="{{ request('q_eq') }}"
                       placeholder="Buscar SKU, modelo o serie…" class="form-input flex-1">
                <button type="submit" class="btn-secondary btn-sm">Buscar</button>
                @if(request('q_eq'))
                <a href="{{ route('almacen.index', ['tab' => 'equipos']) }}" class="btn-secondary btn-sm">Limpiar</a>
                @endif
            </form>
            <a href="{{ route('equipment.create') }}" class="btn-primary">+ Nuevo equipo</a>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="text-sm text-gray-500">{{ $equipment->total() }} equipo(s)</span>
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
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equipment as $item)
                            @php
                                $badge = match($item->location_status) {
                                    'BODEGA'      => 'badge-green',
                                    'ASIGNADO'    => 'badge-blue',
                                    'VENDIDO'     => 'badge-yellow',
                                    'TALLER'      => 'badge-red',
                                    default       => 'badge-gray',
                                };
                                $statusLabel = match($item->location_status) {
                                    'BODEGA'      => 'Bodega',
                                    'ASIGNADO'    => 'Asignado',
                                    'VENDIDO'     => 'Vendido',
                                    'TALLER'      => 'Taller',
                                    'DESCONOCIDO' => 'Desconocido',
                                    default       => $item->location_status ?? '—',
                                };
                            @endphp
                            <tr>
                                <td class="font-mono text-sm text-gray-700">{{ $item->sku ?? '—' }}</td>
                                <td class="text-gray-700">{{ $item->brand?->name ?? '—' }}</td>
                                <td class="font-medium text-gray-900">{{ $item->model }}</td>
                                <td class="font-mono text-sm text-gray-700">{{ $item->serie }}</td>
                                <td><span class="{{ $item->type === 'COLOR' ? 'badge-blue' : 'badge-gray' }}">{{ $item->type }}</span></td>
                                <td><span class="{{ $badge }}">{{ $statusLabel }}</span></td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('equipment.show', $item) }}" class="btn-secondary btn-sm">Ver</a>
                                        <a href="{{ route('equipment.edit', $item) }}" class="btn-secondary btn-sm">Editar</a>
                                        <form action="{{ route('equipment.destroy', $item) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar equipo?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-gray-400 py-10">No hay equipos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if($equipment->hasPages())
        <div class="flex justify-end">{{ $equipment->appends(['tab' => 'equipos'])->links() }}</div>
        @endif
    </div>
    @endif

    {{-- ===== INVENTARIO ===== --}}
    @if($tab === 'inventario')
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" action="{{ route('almacen.index') }}" class="flex gap-2 flex-1 max-w-md">
                <input type="hidden" name="tab" value="inventario">
                <input type="text" name="q_inv" value="{{ request('q_inv') }}"
                       placeholder="Buscar por código o artículo…" class="form-input flex-1">
                <button type="submit" class="btn-secondary btn-sm">Buscar</button>
                @if(request('q_inv'))
                <a href="{{ route('almacen.index', ['tab' => 'inventario']) }}" class="btn-secondary btn-sm">Limpiar</a>
                @endif
            </form>
            <a href="{{ route('inventory.create') }}" class="btn-primary">+ Nuevo artículo</a>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="text-sm text-gray-500">{{ $inventory->total() }} artículo(s)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Artículo</th>
                                <th>Tipo</th>
                                <th>Estante / Sección</th>
                                <th>Calidad</th>
                                <th>Costo</th>
                                <th>Disponible</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventory as $inv)
                            <tr>
                                <td class="font-mono text-sm">{{ $inv->item_code }}</td>
                                <td class="font-medium text-gray-900">{{ $inv->catalog?->item_name ?? '—' }}</td>
                                <td>
                                    @if($inv->catalog?->item_type === 'TONER')
                                    <span class="badge-blue">Tóner</span>
                                    @elseif($inv->catalog?->item_type === 'REFACCION')
                                    <span class="badge-purple">Refacción</span>
                                    @else
                                    <span class="badge-gray">—</span>
                                    @endif
                                </td>
                                <td class="text-sm text-gray-600">
                                    {{ $inv->shelf?->name ?? '—' }}
                                    @if($inv->section)<span class="text-gray-400">/ {{ $inv->section }}</span>@endif
                                </td>
                                <td class="text-sm text-gray-600">{{ $inv->quality ?? '—' }}</td>
                                <td class="text-sm">${{ $inv->cost ? number_format($inv->cost, 2) : '—' }}</td>
                                <td>
                                    <span class="{{ $inv->is_available ? 'badge-green' : 'badge-red' }}">
                                        {{ $inv->is_available ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('inventory.show', $inv) }}" class="btn-secondary btn-sm">Ver</a>
                                        <a href="{{ route('inventory.edit', $inv) }}" class="btn-secondary btn-sm">Editar</a>
                                        <form action="{{ route('inventory.destroy', $inv) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar artículo?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-gray-400 py-10">No hay artículos en inventario.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if($inventory->hasPages())
        <div class="flex justify-end">{{ $inventory->appends(['tab' => 'inventario'])->links() }}</div>
        @endif
    </div>
    @endif

</div>
@endsection
