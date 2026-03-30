@extends('layouts.app')
@section('title', $consumible->nombre)
@section('page-title', $consumible->nombre)

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('catalogo-consumibles.index') }}" class="btn-secondary btn-sm">← Catálogo</a>
        <a href="{{ route('catalogo-consumibles.edit', $consumible) }}" class="btn-primary">Editar</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="card">
            <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Datos del consumible</h3></div>
            <div class="card-body">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Tipo</dt><dd class="font-medium">{{ $consumible->tipo->nombre ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Marca</dt><dd>{{ $consumible->marca->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Código OEM</dt><dd class="font-mono">{{ $consumible->codigo_oem }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Código alt.</dt><dd class="font-mono">{{ $consumible->codigo_alternativo ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Color</dt><dd>{{ $consumible->color ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Original</dt>
                        <dd>{{ $consumible->es_original ? '<span class="badge-green">Sí</span>' : '<span class="badge-gray">No</span>' }}</dd>
                    </div>
                    <div class="flex justify-between"><dt class="text-gray-500">Estado</dt>
                        <dd>{{ $consumible->es_activo ? '<span class="badge-green">Activo</span>' : '<span class="badge-gray">Inactivo</span>' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Rendimiento</h3></div>
            <div class="card-body">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Páginas (original)</dt>
                        <dd class="font-medium">{{ $consumible->rendimiento_paginas ? number_format($consumible->rendimiento_paginas) : '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Páginas (alternativo)</dt>
                        <dd>{{ $consumible->rendimiento_paginas_alt ? number_format($consumible->rendimiento_paginas_alt) : '—' }}</dd>
                    </div>
                </dl>
                @if($consumible->descripcion)
                    <p class="mt-3 text-sm text-gray-600 border-t border-gray-100 pt-3">{{ $consumible->descripcion }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Modelos compatibles --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-gray-700">Modelos compatibles ({{ $consumible->modelos->count() }})</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Modelo</th><th>Marca</th><th>Categoría</th><th>Oficial</th><th>Notas</th><th></th></tr></thead>
                    <tbody>
                        @forelse($consumible->modelos as $mod)
                        <tr>
                            <td class="font-medium">{{ $mod->nombre_modelo }}</td>
                            <td class="text-gray-500">{{ $mod->marca->name ?? '—' }}</td>
                            <td class="text-gray-500">{{ $mod->categoria->nombre ?? '—' }}</td>
                            <td>{{ $mod->pivot->es_oficial ? '<span class="badge-green">Sí</span>' : '<span class="badge-gray">No</span>' }}</td>
                            <td class="text-gray-400 text-xs">{{ $mod->pivot->notas ?? '—' }}</td>
                            <td><a href="{{ route('modelos-equipo.show', $mod) }}" class="btn-secondary btn-sm">Ver modelo</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-gray-400 py-6">Sin modelos asociados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
