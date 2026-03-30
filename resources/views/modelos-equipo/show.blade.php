@extends('layouts.app')
@section('title', $modelo->nombre_modelo)
@section('page-title', $modelo->nombre_modelo)

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('modelos-equipo.index') }}" class="btn-secondary btn-sm">← Modelos</a>
        <a href="{{ route('modelos-equipo.edit', $modelo) }}" class="btn-primary">Editar</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Datos generales --}}
        <div class="card">
            <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Datos del modelo</h3></div>
            <div class="card-body">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Marca</dt><dd class="font-medium">{{ $modelo->marca->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Categoría</dt><dd>{{ $modelo->categoria->nombre ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Nombre comercial</dt><dd>{{ $modelo->nombre_comercial ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Tipo de color</dt>
                        <dd><span class="{{ $modelo->tipo_color === 'MONOCROMO' ? 'badge-gray' : 'badge-blue' }}">{{ $modelo->tipo_color }}</span></dd>
                    </div>
                    <div class="flex justify-between"><dt class="text-gray-500">Tecnología</dt><dd>{{ $modelo->tecnologia ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Formato máx.</dt><dd>{{ $modelo->formato_max ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Estado</dt>
                        <dd>{{ $modelo->es_activo ? '<span class="badge-green">Activo</span>' : '<span class="badge-gray">Inactivo</span>' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Especificaciones --}}
        <div class="card">
            <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Especificaciones</h3></div>
            <div class="card-body">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Vel. B/N</dt><dd>{{ $modelo->velocidad_bn_ppm ? $modelo->velocidad_bn_ppm.' ppm' : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Vel. Color</dt><dd>{{ $modelo->velocidad_color_ppm ? $modelo->velocidad_color_ppm.' ppm' : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Vida útil</dt><dd>{{ $modelo->vida_util_paginas ? number_format($modelo->vida_util_paginas).' págs.' : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Escáner</dt><dd>{{ $modelo->tiene_escaner ? '✓' : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Fax</dt><dd>{{ $modelo->tiene_fax ? '✓' : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Dúplex</dt><dd>{{ $modelo->tiene_duplex ? '✓' : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Red / Wi-Fi</dt><dd>{{ $modelo->tiene_red ? '✓' : '—' }} / {{ $modelo->tiene_wifi ? '✓' : '—' }}</dd></div>
                </dl>
                @if($modelo->descripcion)
                    <p class="mt-3 text-sm text-gray-600 border-t border-gray-100 pt-3">{{ $modelo->descripcion }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Consumibles compatibles --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Consumibles compatibles ({{ $modelo->consumibles->count() }})</h3>
            <a href="{{ route('catalogo-consumibles.create') }}" class="btn-secondary btn-sm">+ Agregar consumible</a>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Consumible</th>
                            <th>Tipo</th>
                            <th>Código OEM</th>
                            <th>Color</th>
                            <th>Rendimiento</th>
                            <th>Oficial</th>
                            <th>Notas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modelo->consumibles as $c)
                        <tr>
                            <td>
                                <a href="{{ route('catalogo-consumibles.show', $c) }}" class="text-blue-600 hover:underline font-medium">
                                    {{ $c->nombre }}
                                </a>
                            </td>
                            <td class="text-gray-500">{{ $c->tipo->nombre ?? '—' }}</td>
                            <td><span class="font-mono text-xs">{{ $c->codigo_oem }}</span></td>
                            <td>{{ $c->color ?? '—' }}</td>
                            <td class="text-gray-500">{{ $c->rendimiento_paginas ? number_format($c->rendimiento_paginas).' págs.' : '—' }}</td>
                            <td>{{ $c->pivot->es_oficial ? '<span class="badge-green">Sí</span>' : '<span class="badge-gray">No</span>' }}</td>
                            <td class="text-gray-400 text-xs">{{ $c->pivot->notas ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-gray-400 py-6">Sin consumibles asociados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Equipos con este modelo --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-gray-700">Equipos con este modelo ({{ $modelo->equipos->count() }})</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>SKU</th><th>Serie</th><th>Marca</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @forelse($modelo->equipos as $eq)
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
