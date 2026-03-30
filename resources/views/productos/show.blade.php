@extends('layouts.app')
@section('title', $producto->nombre)
@section('page-title', $producto->nombre)

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('almacen.index', ['tab' => 'productos']) }}" class="btn-secondary btn-sm">← Almacén</a>
        <a href="{{ route('productos.edit', $producto) }}" class="btn-primary">Editar</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="card">
            <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Datos del producto</h3></div>
            <div class="card-body">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Código</dt><dd class="font-mono font-medium">{{ $producto->codigo }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Marca</dt><dd>{{ $producto->marca->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Categoría</dt><dd><span class="badge-gray">{{ $producto->categoria }}</span></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Tipo color</dt><dd>{{ $producto->tipo_color ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Formato máx.</dt><dd>{{ $producto->formato_max ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Precio venta</dt><dd>{{ $producto->precio_venta ? '$'.number_format($producto->precio_venta,2) : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Precio renta/mes</dt><dd>{{ $producto->precio_renta ? '$'.number_format($producto->precio_renta,2) : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Estado</dt>
                        <dd>{{ $producto->es_activo ? '<span class="badge-green">Activo</span>' : '<span class="badge-gray">Inactivo</span>' }}</dd>
                    </div>
                </dl>
                @if($producto->descripcion)
                    <p class="mt-3 text-sm text-gray-600 border-t border-gray-100 pt-3">{{ $producto->descripcion }}</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Stock</h3></div>
            <div class="card-body">
                @php $st = $producto->stock; @endphp
                @if($st)
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Disponible</dt>
                        <dd><span class="{{ $st->bajo_minimo ? 'badge-red' : 'badge-green' }} text-base font-bold">{{ $st->cantidad_disponible }}</span></dd>
                    </div>
                    <div class="flex justify-between"><dt class="text-gray-500">Mínimo</dt><dd>{{ $st->cantidad_minima }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Costo unit.</dt><dd>{{ $st->costo ? '$'.number_format($st->costo,2) : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Ubicación</dt><dd>{{ $st->ubicacion ?? '—' }}</dd></div>
                </dl>
                @if($st->bajo_minimo)
                    <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                        ⚠ Stock por debajo del mínimo
                    </div>
                @endif
                @else
                    <p class="text-sm text-gray-400">Sin registro de stock.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Accesorios --}}
    <div class="card">
        <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Accesorios compatibles ({{ $producto->accesorios->count() }})</h3></div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Código</th><th>Nombre</th><th>Precio</th><th>Incluido de fábrica</th><th>Notas</th></tr></thead>
                    <tbody>
                        @forelse($producto->accesorios as $ac)
                        <tr>
                            <td class="font-mono text-xs">{{ $ac->codigo }}</td>
                            <td class="font-medium">{{ $ac->nombre }}</td>
                            <td>{{ $ac->precio ? '$'.number_format($ac->precio,2) : '—' }}</td>
                            <td>{{ $ac->pivot->es_incluido ? '<span class="badge-green">Sí</span>' : '<span class="badge-gray">No</span>' }}</td>
                            <td class="text-gray-400 text-xs">{{ $ac->pivot->notas ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-gray-400 py-6">Sin accesorios asociados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Consumibles --}}
    <div class="card">
        <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Consumibles compatibles ({{ $producto->consumibles->count() }})</h3></div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Código OEM</th><th>Nombre</th><th>Tipo</th><th>Color</th><th>Rendimiento</th><th>Oficial</th></tr></thead>
                    <tbody>
                        @forelse($producto->consumibles as $co)
                        <tr>
                            <td class="font-mono text-xs">{{ $co->codigo_oem }}</td>
                            <td class="font-medium">{{ $co->nombre }}</td>
                            <td class="text-gray-500 text-sm">{{ $co->tipo }}</td>
                            <td class="text-gray-500">{{ $co->color ?? '—' }}</td>
                            <td class="text-gray-500 text-sm">{{ $co->rendimiento_paginas ? number_format($co->rendimiento_paginas).' p.' : '—' }}</td>
                            <td>{{ $co->pivot->es_oficial ? '<span class="badge-green">Sí</span>' : '<span class="badge-gray">No</span>' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-gray-400 py-6">Sin consumibles asociados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Equipos físicos --}}
    <div class="card">
        <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Equipos físicos con este producto ({{ $producto->equipos->count() }})</h3></div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>SKU</th><th>Serie</th><th>Marca</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @forelse($producto->equipos as $eq)
                        <tr>
                            <td class="font-mono text-xs">{{ $eq->sku ?? '—' }}</td>
                            <td class="font-medium">{{ $eq->serie }}</td>
                            <td>{{ $eq->brand->name ?? '—' }}</td>
                            <td><span class="badge-gray text-xs">{{ $eq->location_status }}</span></td>
                            <td><a href="{{ route('equipment.show', $eq) }}" class="btn-secondary btn-sm">Ver</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-gray-400 py-6">Sin equipos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
