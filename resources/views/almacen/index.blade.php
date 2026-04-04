@extends('layouts.app')
@section('title','Almacén')
@section('page-title','Almacén')

@section('content')
<div class="space-y-4">

    <div class="overflow-x-auto">
    <div class="flex border-b border-gray-200 gap-0.5 min-w-max">
        @foreach([
            'equipos' => 'Equipos',
            'refacciones' => 'Refacciones',
            'inventario' => 'Inventario tóner',
        ] as $slug => $label)
        <a href="{{ route('almacen.index', ['tab' => $slug]) }}"
           class="px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 transition-colors whitespace-nowrap
                  {{ $tab === $slug ? 'border-blue-600 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            {{ $label }}
        </a>
        @endforeach
        <div class="flex-1"></div>
        <div class="flex items-center gap-2 pb-1 px-2">
            @if($tab === 'equipos')
                <a href="{{ route('brands.index') }}" class="btn-secondary btn-sm">Marcas</a>
                <a href="{{ route('suppliers.index') }}" class="btn-secondary btn-sm">Proveedores</a>
            @elseif($tab === 'inventario')
                <a href="{{ route('item-catalog.index') }}" class="btn-secondary btn-sm">Catálogo</a>
                <a href="{{ route('shelves.index') }}" class="btn-secondary btn-sm">Estantes</a>
            @endif
        </div>
    </div>
    </div>

    @if($tab === 'equipos')
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" action="{{ route('almacen.index') }}" class="flex gap-2 flex-1 max-w-md">
                <input type="hidden" name="tab" value="equipos">
                <input type="text" name="q_eq" value="{{ request('q_eq') }}" placeholder="Buscar SKU, modelo o serie…" class="form-input flex-1">
                <button type="submit" class="btn-secondary btn-sm">Buscar</button>
                @if(request('q_eq'))
                <a href="{{ route('almacen.index', ['tab' => 'equipos']) }}" class="btn-secondary btn-sm">✕</a>
                @endif
            </form>
            <a href="{{ route('equipment.create') }}" class="btn-primary">+ Nuevo equipo</a>
        </div>
        <div class="card">
            <div class="card-header"><span class="text-sm text-gray-500">{{ $equipment->total() }} equipo(s)</span></div>
            <div class="card-body p-0">
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>SKU</th><th>Marca</th><th>Modelo</th><th>Serie</th>
                                <th>Tipo</th><th>Estado</th><th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equipment as $item)
                            @php
                                $badge = match($item->location_status) {
                                    'BODEGA' => 'badge-green', 'ASIGNADO' => 'badge-blue',
                                    'VENDIDO' => 'badge-yellow', 'TALLER' => 'badge-red',
                                    default  => 'badge-gray',
                                };
                            @endphp
                            <tr>
                                <td class="font-mono text-sm">{{ $item->sku ?? '—' }}</td>
                                <td>{{ $item->brand?->name ?? '—' }}</td>
                                <td><div class="font-medium text-gray-900">{{ $item->model }}</div></td>
                                <td class="font-mono text-sm">{{ $item->serie }}</td>
                                <td><span class="{{ $item->type === 'COLOR' ? 'badge-blue' : 'badge-gray' }}">{{ $item->type }}</span></td>
                                <td><span class="{{ $badge }}">{{ $item->location_status }}</span></td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('equipment.show', $item) }}" class="btn-secondary btn-sm">Ver</a>
                                        <a href="{{ route('equipment.edit', $item) }}" class="btn-secondary btn-sm">Editar</a>
                                        <form action="{{ route('equipment.destroy', $item) }}" method="POST" onsubmit="return confirm('¿Eliminar equipo?')">
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

    @if($tab === 'refacciones')
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" action="{{ route('almacen.index') }}" class="flex gap-2 flex-1 max-w-md">
                <input type="hidden" name="tab" value="refacciones">
                <input type="text" name="q_sp" value="{{ request('q_sp') }}" placeholder="Buscar nombre, código o marca…" class="form-input flex-1">
                <button type="submit" class="btn-secondary btn-sm">Buscar</button>
                @if(request('q_sp'))
                <a href="{{ route('almacen.index', ['tab' => 'refacciones']) }}" class="btn-secondary btn-sm">✕</a>
                @endif
            </form>
            <a href="{{ route('spareparts.create') }}" class="btn-primary">+ Nueva refacción</a>
        </div>
        <div class="card">
            <div class="card-header"><span class="text-sm text-gray-500">{{ $spareparts->total() }} refacción(es)</span></div>
            <div class="card-body p-0">
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Código</th><th>Nombre</th><th>Color</th><th>Marca</th><th>Equipo compatible</th><th>Proveedor</th><th class="text-right">Acciones</th></tr></thead>
                        <tbody>
                            @forelse($spareparts as $sp)
                            @php
                                $colorBadge = match($sp->color) {
                                    'K' => 'bg-gray-800 text-white',
                                    'Y' => 'bg-yellow-400 text-gray-900',
                                    'M' => 'bg-pink-500 text-white',
                                    'C' => 'bg-cyan-500 text-white',
                                    'COLOR' => 'bg-gradient-to-r from-yellow-400 via-pink-500 to-cyan-500 text-white',
                                    'NA' => 'bg-gray-100 text-gray-600 border border-gray-200',
                                    default => 'bg-gray-100 text-gray-600 border border-gray-200',
                                };

                                $colorLabel = $sp->color ?: '—';
                            @endphp
                            <tr>
                                <td class="font-mono text-xs">{{ $sp->code ?? '—' }}</td>
                                <td class="font-medium text-gray-900">{{ $sp->name }}</td>
                                <td>
                                    <span class="inline-flex min-w-10 items-center justify-center rounded-md px-2 py-1 text-xs font-semibold {{ $colorBadge }}">
                                        {{ $colorLabel }}
                                    </span>
                                </td>
                                <td>{{ $sp->brand_name ?? '—' }}</td>
                                <td class="text-sm text-gray-500">{{ $sp->equipment ?? '—' }}</td>
                                <td class="text-sm text-gray-500">{{ $sp->supplier_name ?? '—' }}</td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('spareparts.show', $sp) }}" class="btn-secondary btn-sm">Ver</a>
                                        <a href="{{ route('spareparts.edit', $sp) }}" class="btn-secondary btn-sm">Editar</a>
                                        <form action="{{ route('spareparts.destroy', $sp) }}" method="POST" onsubmit="return confirm('¿Eliminar refacción?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-gray-400 py-10">No hay refacciones.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if($spareparts->hasPages())
        <div class="flex justify-end">{{ $spareparts->appends(['tab' => 'refacciones'])->links() }}</div>
        @endif
    </div>
    @endif

    @if($tab === 'inventario')
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" action="{{ route('almacen.index') }}" class="flex gap-2 flex-1 max-w-md">
                <input type="hidden" name="tab" value="inventario">
                <input type="text" name="q_inv" value="{{ request('q_inv') }}" placeholder="Buscar código o artículo…" class="form-input flex-1">
                <button type="submit" class="btn-secondary btn-sm">Buscar</button>
                @if(request('q_inv'))
                <a href="{{ route('almacen.index', ['tab' => 'inventario']) }}" class="btn-secondary btn-sm">✕</a>
                @endif
            </form>
            <a href="{{ route('inventory.create') }}" class="btn-primary">+ Nuevo artículo</a>
        </div>
        <div class="card">
            <div class="card-header"><span class="text-sm text-gray-500">{{ $inventory->total() }} artículo(s)</span></div>
            <div class="card-body p-0">
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Código</th><th>Artículo</th><th>Tipo</th><th>Estante / Sección</th><th>Calidad</th><th>Costo</th><th>Disponible</th><th class="text-right">Acciones</th></tr></thead>
                        <tbody>
                            @forelse($inventory as $inv)
                            <tr>
                                <td class="font-mono text-sm">{{ $inv->item_code }}</td>
                                <td class="font-medium">{{ $inv->catalog?->item_name ?? '—' }}</td>
                                <td>
                                    @if($inv->catalog?->item_type === 'TONER')
                                        <span class="badge-blue">Tóner</span>
                                    @else
                                        <span class="badge-gray">{{ $inv->catalog?->item_type ?? '—' }}</span>
                                    @endif
                                </td>
                                <td class="text-sm text-gray-600">{{ $inv->shelf?->name ?? '—' }}@if($inv->section) / {{ $inv->section }}@endif</td>
                                <td class="text-sm">{{ $inv->quality ?? '—' }}</td>
                                <td class="text-sm">${{ $inv->cost ? number_format($inv->cost,2) : '—' }}</td>
                                <td><span class="{{ $inv->is_available ? 'badge-green' : 'badge-red' }}">{{ $inv->is_available ? 'Sí' : 'No' }}</span></td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('inventory.show', $inv) }}" class="btn-secondary btn-sm">Ver</a>
                                        <a href="{{ route('inventory.edit', $inv) }}" class="btn-secondary btn-sm">Editar</a>
                                        <form action="{{ route('inventory.destroy', $inv) }}" method="POST" onsubmit="return confirm('¿Eliminar artículo?')">
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
