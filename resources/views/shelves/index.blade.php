@extends('layouts.app')
@section('title','Estantes')
@section('page-title','Estantes / Repisas')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <form method="GET" action="{{ route('shelves.index') }}" class="flex gap-2 flex-1 max-w-md">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar estante…" class="form-input flex-1">
            <button type="submit" class="btn-secondary btn-sm">Buscar</button>
            @if(request('search'))<a href="{{ route('shelves.index') }}" class="btn-secondary btn-sm">Limpiar</a>@endif
        </form>
        <a href="{{ route('shelves.create') }}" class="btn-primary">+ Nuevo estante</a>
    </div>
    <div class="card">
        <div class="card-header"><span class="text-sm text-gray-500">{{ $query->total() }} estante(s)</span></div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Nombre</th><th>Sección</th><th>Descripción</th><th>Artículos</th><th>Activo</th><th class="text-right">Acciones</th></tr></thead>
                    <tbody>
                        @forelse($query as $shelf)
                        <tr>
                            <td class="font-medium text-gray-900">{{ $shelf->name }}</td>
                            <td><span class="badge-blue">{{ str_replace('_',' ',$shelf->section) }}</span></td>
                            <td class="text-gray-500 text-sm">{{ $shelf->description ?? '—' }}</td>
                            <td class="text-gray-500">{{ $shelf->inventory_items_count }}</td>
                            <td>
                                @if($shelf->is_active)<span class="badge-green">Sí</span>@else<span class="badge-gray">No</span>@endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('shelves.edit', $shelf) }}" class="btn-secondary btn-sm">Editar</a>
                                    <form action="{{ route('shelves.destroy', $shelf) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar estante «{{ addslashes($shelf->name) }}»?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-gray-400 py-10">No hay estantes registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($query->hasPages())<div class="flex justify-end">{{ $query->links() }}</div>@endif
</div>
@endsection
