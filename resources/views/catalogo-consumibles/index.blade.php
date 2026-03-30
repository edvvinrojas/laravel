@extends('layouts.app')
@section('title','Catálogo de Consumibles')
@section('page-title','Catálogo de Consumibles')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('almacen.index', ['tab' => 'inventario']) }}" class="btn-secondary btn-sm">← Almacén</a>
        <a href="{{ route('catalogo-consumibles.create') }}" class="btn-primary">+ Nuevo consumible</a>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('catalogo-consumibles.index') }}" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Buscar por nombre o código…" class="form-input flex-1 min-w-48">
        <select name="tipo_id" class="form-select">
            <option value="">— Todos los tipos —</option>
            @foreach($tipos as $t)
                <option value="{{ $t->id }}" {{ request('tipo_id') == $t->id ? 'selected' : '' }}>{{ $t->nombre }}</option>
            @endforeach
        </select>
        <select name="marca_id" class="form-select">
            <option value="">— Todas las marcas —</option>
            @foreach($marcas as $m)
                <option value="{{ $m->id }}" {{ request('marca_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary btn-sm">Filtrar</button>
        @if(request()->hasAny(['search','tipo_id','marca_id']))
            <a href="{{ route('catalogo-consumibles.index') }}" class="btn-secondary btn-sm">Limpiar</a>
        @endif
    </form>

    <div class="card">
        <div class="card-header">
            <span class="text-sm text-gray-500">{{ $consumibles->total() }} consumible(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Código OEM</th>
                            <th>Marca</th>
                            <th>Color</th>
                            <th>Rendimiento</th>
                            <th>Original</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consumibles as $c)
                        <tr>
                            <td class="font-medium text-gray-900">{{ $c->nombre }}</td>
                            <td class="text-gray-500 text-sm">{{ $c->tipo->nombre ?? '—' }}</td>
                            <td><span class="font-mono text-xs badge-gray">{{ $c->codigo_oem }}</span></td>
                            <td class="text-gray-500">{{ $c->marca->name ?? '—' }}</td>
                            <td class="text-gray-500">{{ $c->color ?? '—' }}</td>
                            <td class="text-gray-500">{{ $c->rendimiento_paginas ? number_format($c->rendimiento_paginas) : '—' }}</td>
                            <td>{{ $c->es_original ? '<span class="badge-green">Sí</span>' : '<span class="badge-gray">No</span>' }}</td>
                            <td>{{ $c->es_activo ? '<span class="badge-green">Activo</span>' : '<span class="badge-gray">Inactivo</span>' }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('catalogo-consumibles.show', $c) }}" class="btn-secondary btn-sm">Ver</a>
                                    <a href="{{ route('catalogo-consumibles.edit', $c) }}" class="btn-secondary btn-sm">Editar</a>
                                    <form action="{{ route('catalogo-consumibles.destroy', $c) }}" method="POST"
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
    @if($consumibles->hasPages())<div class="flex justify-end">{{ $consumibles->links() }}</div>@endif
</div>
@endsection
