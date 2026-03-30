@extends('layouts.app')
@section('title','Modelos de Equipo')
@section('page-title','Modelos de Equipo')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('almacen.index', ['tab' => 'equipos']) }}" class="btn-secondary btn-sm">← Almacén</a>
        <a href="{{ route('modelos-equipo.create') }}" class="btn-primary">+ Nuevo modelo</a>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('modelos-equipo.index') }}" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Buscar modelo o nombre comercial…" class="form-input flex-1 min-w-48">
        <select name="categoria_id" class="form-select">
            <option value="">— Todas las categorías —</option>
            @foreach($categorias as $cat)
                <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->nombre }}
                </option>
            @endforeach
        </select>
        <select name="marca_id" class="form-select">
            <option value="">— Todas las marcas —</option>
            @foreach($marcas as $m)
                <option value="{{ $m->id }}" {{ request('marca_id') == $m->id ? 'selected' : '' }}>
                    {{ $m->name }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary btn-sm">Filtrar</button>
        @if(request()->hasAny(['search','categoria_id','marca_id']))
            <a href="{{ route('modelos-equipo.index') }}" class="btn-secondary btn-sm">Limpiar</a>
        @endif
    </form>

    <div class="card">
        <div class="card-header">
            <span class="text-sm text-gray-500">{{ $modelos->total() }} modelo(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Modelo</th>
                            <th>Marca / Categoría</th>
                            <th>Tipo</th>
                            <th>Vel. B/N</th>
                            <th>Equipos</th>
                            <th>Consumibles</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modelos as $m)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-900">{{ $m->nombre_modelo }}</div>
                                @if($m->nombre_comercial)
                                    <div class="text-xs text-gray-400">{{ $m->nombre_comercial }}</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $m->marca->name ?? '—' }}</div>
                                <div class="text-xs text-gray-400">{{ $m->categoria->nombre ?? '—' }}</div>
                            </td>
                            <td>
                                <span class="{{ $m->tipo_color === 'MONOCROMO' ? 'badge-gray' : 'badge-blue' }}">
                                    {{ $m->tipo_color }}
                                </span>
                            </td>
                            <td class="text-gray-500">{{ $m->velocidad_bn_ppm ? $m->velocidad_bn_ppm.' ppm' : '—' }}</td>
                            <td class="text-gray-500">{{ $m->equipos_count }}</td>
                            <td class="text-gray-500">{{ $m->consumibles_count }}</td>
                            <td>
                                @if($m->es_activo)
                                    <span class="badge-green">Activo</span>
                                @else
                                    <span class="badge-gray">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('modelos-equipo.show', $m) }}" class="btn-secondary btn-sm">Ver</a>
                                    <a href="{{ route('modelos-equipo.edit', $m) }}" class="btn-secondary btn-sm">Editar</a>
                                    <form action="{{ route('modelos-equipo.destroy', $m) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar modelo «{{ addslashes($m->nombre_modelo) }}»?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-gray-400 py-10">No hay modelos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($modelos->hasPages())<div class="flex justify-end">{{ $modelos->links() }}</div>@endif
</div>
@endsection
