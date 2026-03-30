@extends('layouts.app')
@section('title','Categorías de Equipo')
@section('page-title','Categorías de Equipo')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('almacen.index', ['tab' => 'equipos']) }}" class="btn-secondary btn-sm">← Almacén</a>
        <a href="{{ route('categorias-equipo.create') }}" class="btn-primary">+ Nueva categoría</a>
    </div>

    <div class="card">
        <div class="card-header"><span class="text-sm text-gray-500">{{ $categorias->count() }} categoría(s)</span></div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Código</th>
                            <th>Modelos</th>
                            <th>Equipos</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categorias as $cat)
                        <tr>
                            <td class="font-medium text-gray-900">{{ $cat->nombre }}</td>
                            <td><span class="badge-gray font-mono">{{ $cat->codigo }}</span></td>
                            <td class="text-gray-500">{{ $cat->modelos_count }}</td>
                            <td class="text-gray-500">{{ $cat->equipos_count }}</td>
                            <td>
                                @if($cat->es_activo)
                                    <span class="badge-green">Activo</span>
                                @else
                                    <span class="badge-gray">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('categorias-equipo.edit', $cat) }}" class="btn-secondary btn-sm">Editar</a>
                                    <form action="{{ route('categorias-equipo.destroy', $cat) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar categoría «{{ addslashes($cat->nombre) }}»?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-gray-400 py-10">No hay categorías registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
