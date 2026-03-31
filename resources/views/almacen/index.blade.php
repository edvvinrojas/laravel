@extends('layouts.app')
@section('title','Almacén')
@section('page-title','Almacén')

@section('content')
<div class="space-y-4">

    {{-- Pestañas --}}
    <div class="overflow-x-auto">
    <div class="flex border-b border-gray-200 gap-0.5 min-w-max">
        @foreach([
            'equipos'     => 'Equipos',
            'productos'   => 'Productos',
            'accesorios'  => 'Accesorios',
            'consumibles' => 'Consumibles',
            'refacciones' => 'Refacciones',
            'inventario'  => 'Inventario tóner',
        ] as $slug => $label)
        <a href="{{ route('almacen.index', ['tab' => $slug]) }}"
           class="px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 transition-colors whitespace-nowrap
                  {{ $tab === $slug ? 'border-blue-600 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            {{ $label }}
        </a>
        @endforeach
        <div class="flex-1"></div>
        {{-- Acciones de contexto --}}
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
                                <th>SKU</th><th>Marca</th><th>Modelo / Producto</th><th>Serie</th>
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
                                <td>
                                    <div class="font-medium text-gray-900">{{ $item->model }}</div>
                                    @if($item->producto)
                                        <div class="text-xs text-blue-500">{{ $item->producto->nombre }}</div>
                                    @endif
                                </td>
                                <td class="font-mono text-sm">{{ $item->serie }}</td>
                                <td><span class="{{ $item->type === 'COLOR' ? 'badge-blue' : 'badge-gray' }}">{{ $item->type }}</span></td>
                                <td><span class="{{ $badge }}">{{ $item->location_status }}</span></td>
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

    {{-- ===== PRODUCTOS ===== --}}
    @if($tab === 'productos')
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" action="{{ route('almacen.index') }}" class="flex gap-2 flex-1 max-w-md">
                <input type="hidden" name="tab" value="productos">
                <input type="text" name="q_pr" value="{{ request('q_pr') }}"
                       placeholder="Buscar nombre o código…" class="form-input flex-1">
                <button type="submit" class="btn-secondary btn-sm">Buscar</button>
                @if(request('q_pr'))
                <a href="{{ route('almacen.index', ['tab' => 'productos']) }}" class="btn-secondary btn-sm">✕</a>
                @endif
            </form>
            <a href="{{ route('productos.create') }}" class="btn-primary">+ Nuevo producto</a>
        </div>
        <div class="card">
            <div class="card-header"><span class="text-sm text-gray-500">{{ $productos->total() }} producto(s)</span></div>
            <div class="card-body p-0">
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Código</th><th>Nombre</th><th>Marca</th><th>Categoría</th>
                                <th>Accesorios</th><th>Consumibles</th><th>Equipos</th>
                                <th>Stock</th><th>Estado</th><th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productos as $p)
                            @php $st = $p->stock; @endphp
                            <tr>
                                <td class="font-mono text-xs badge-gray">{{ $p->codigo }}</td>
                                <td class="font-medium text-gray-900">{{ $p->nombre }}</td>
                                <td class="text-gray-500">{{ $p->marca->name ?? '—' }}</td>
                                <td><span class="badge-gray text-xs">{{ $p->categoria }}</span></td>
                                <td class="text-gray-500 text-center">{{ $p->accesorios_count }}</td>
                                <td class="text-gray-500 text-center">{{ $p->consumibles_count }}</td>
                                <td class="text-gray-500 text-center">{{ $p->equipos_count }}</td>
                                <td>
                                    @if($st)
                                        <span class="{{ $st->bajo_minimo ? 'badge-red' : 'badge-green' }}">
                                            {{ $st->cantidad_disponible }}
                                        </span>
                                    @else
                                        <span class="badge-gray">—</span>
                                    @endif
                                </td>
                                <td>{!! $p->es_activo ? '<span class="badge-green">Activo</span>' : '<span class="badge-gray">Inactivo</span>' !!}</td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('productos.show', $p) }}" class="btn-secondary btn-sm">Ver</a>
                                        <a href="{{ route('productos.edit', $p) }}" class="btn-secondary btn-sm">Editar</a>
                                        <form action="{{ route('productos.destroy', $p) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar «{{ addslashes($p->nombre) }}»?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="10" class="text-center text-gray-400 py-10">No hay productos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if($productos->hasPages())
        <div class="flex justify-end">{{ $productos->appends(['tab' => 'productos'])->links() }}</div>
        @endif
    </div>
    @endif

    {{-- ===== ACCESORIOS ===== --}}
    @if($tab === 'accesorios')
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" action="{{ route('almacen.index') }}" class="flex gap-2 flex-1 max-w-md">
                <input type="hidden" name="tab" value="accesorios">
                <input type="text" name="q_ac" value="{{ request('q_ac') }}"
                       placeholder="Buscar nombre o código…" class="form-input flex-1">
                <button type="submit" class="btn-secondary btn-sm">Buscar</button>
                @if(request('q_ac'))
                <a href="{{ route('almacen.index', ['tab' => 'accesorios']) }}" class="btn-secondary btn-sm">✕</a>
                @endif
            </form>
            <a href="{{ route('accesorios.create') }}" class="btn-primary">+ Nuevo accesorio</a>
        </div>
        <div class="card">
            <div class="card-header"><span class="text-sm text-gray-500">{{ $accesorios->total() }} accesorio(s)</span></div>
            <div class="card-body p-0">
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Código</th><th>Nombre</th><th>Precio</th>
                                <th>Stock</th><th>Estado</th><th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accesorios as $ac)
                            @php $st = $ac->stock; @endphp
                            <tr>
                                <td class="font-mono text-xs">{{ $ac->codigo }}</td>
                                <td class="font-medium text-gray-900">{{ $ac->nombre }}</td>
                                <td class="text-gray-500">{{ $ac->precio ? '$'.number_format($ac->precio,2) : '—' }}</td>
                                <td>
                                    @if($st)
                                        <span class="{{ $st->bajo_minimo ? 'badge-red' : 'badge-green' }}">
                                            {{ $st->cantidad_disponible }}
                                        </span>
                                    @else
                                        <span class="badge-gray">—</span>
                                    @endif
                                </td>
                                <td>{!! $ac->es_activo ? '<span class="badge-green">Activo</span>' : '<span class="badge-gray">Inactivo</span>' !!}</td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('accesorios.edit', $ac) }}" class="btn-secondary btn-sm">Editar</a>
                                        <form action="{{ route('accesorios.destroy', $ac) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar «{{ addslashes($ac->nombre) }}»?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-gray-400 py-10">No hay accesorios registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if($accesorios->hasPages())
        <div class="flex justify-end">{{ $accesorios->appends(['tab' => 'accesorios'])->links() }}</div>
        @endif
    </div>
    @endif

    {{-- ===== CONSUMIBLES ===== --}}
    @if($tab === 'consumibles')
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" action="{{ route('almacen.index') }}" class="flex gap-2 flex-1 max-w-md">
                <input type="hidden" name="tab" value="consumibles">
                <input type="text" name="q_co" value="{{ request('q_co') }}"
                       placeholder="Buscar nombre o código OEM…" class="form-input flex-1">
                <button type="submit" class="btn-secondary btn-sm">Buscar</button>
                @if(request('q_co'))
                <a href="{{ route('almacen.index', ['tab' => 'consumibles']) }}" class="btn-secondary btn-sm">✕</a>
                @endif
            </form>
            <a href="{{ route('consumibles.create') }}" class="btn-primary">+ Nuevo consumible</a>
        </div>
        <div class="card">
            <div class="card-header"><span class="text-sm text-gray-500">{{ $consumibles->total() }} consumible(s)</span></div>
            <div class="card-body p-0">
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Código OEM</th><th>Nombre</th><th>Tipo</th><th>Color</th>
                                <th>Marca</th><th>Rendimiento</th><th>Stock</th><th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($consumibles as $c)
                            @php $st = $c->stock; @endphp
                            <tr>
                                <td class="font-mono text-xs badge-gray">{{ $c->codigo_oem }}</td>
                                <td class="font-medium text-gray-900">{{ $c->nombre }}</td>
                                <td class="text-gray-500 text-sm">{{ $c->tipo }}</td>
                                <td class="text-gray-500 text-sm">{{ $c->color ?? '—' }}</td>
                                <td class="text-gray-500">{{ $c->marca->name ?? '—' }}</td>
                                <td class="text-gray-500 text-sm">{{ $c->rendimiento_paginas ? number_format($c->rendimiento_paginas).' p.' : '—' }}</td>
                                <td>
                                    @if($st)
                                        <span class="{{ $st->bajo_minimo ? 'badge-red' : 'badge-green' }}">
                                            {{ $st->cantidad_disponible }}
                                        </span>
                                    @else
                                        <span class="badge-gray">—</span>
                                    @endif
                                </td>
                                <td>{!! $c->es_activo ? '<span class="badge-green">Activo</span>' : '<span class="badge-gray">Inactivo</span>' !!}</td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('consumibles.edit', $c) }}" class="btn-secondary btn-sm">Editar</a>
                                        <form action="{{ route('consumibles.destroy', $c) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar «{{ addslashes($c->nombre) }}»?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="text-center text-gray-400 py-10">No hay consumibles registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if($consumibles->hasPages())
        <div class="flex justify-end">{{ $consumibles->appends(['tab' => 'consumibles'])->links() }}</div>
        @endif
    </div>
    @endif

    {{-- ===== REFACCIONES ===== --}}
    @if($tab === 'refacciones')
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" action="{{ route('almacen.index') }}" class="flex gap-2 flex-1 max-w-md">
                <input type="hidden" name="tab" value="refacciones">
                <input type="text" name="q_sp" value="{{ request('q_sp') }}"
                       placeholder="Buscar nombre, código o marca…" class="form-input flex-1">
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
                            <tr>
                                <td class="font-mono text-xs">{{ $sp->code ?? '—' }}</td>
                                <td class="font-medium text-gray-900">{{ $sp->name }}</td>
                                <td>{{ $sp->color ?? '—' }}</td>
                                <td>{{ $sp->brand ?? '—' }}</td>
                                <td class="text-sm text-gray-500">{{ $sp->equipment ?? '—' }}</td>
                                <td class="text-sm text-gray-500">{{ $sp->supplier ?? '—' }}</td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('spareparts.show', $sp) }}" class="btn-secondary btn-sm">Ver</a>
                                        <a href="{{ route('spareparts.edit', $sp) }}" class="btn-secondary btn-sm">Editar</a>
                                        <form action="{{ route('spareparts.destroy', $sp) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar refacción?')">
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

    {{-- ===== INVENTARIO TÓNER ===== --}}
    @if($tab === 'inventario')
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" action="{{ route('almacen.index') }}" class="flex gap-2 flex-1 max-w-md">
                <input type="hidden" name="tab" value="inventario">
                <input type="text" name="q_inv" value="{{ request('q_inv') }}"
                       placeholder="Buscar código o artículo…" class="form-input flex-1">
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
