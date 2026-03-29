@extends('layouts.app')
@section('title','Marcas')
@section('page-title','Marcas')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('almacen.index', ['tab' => 'equipos']) }}" class="btn-secondary btn-sm">← Almacén</a>
        <a href="{{ route('brands.create') }}" class="btn-primary">+ Nueva marca</a>
    </div>
    <div class="flex gap-2">
        <form method="GET" action="{{ route('brands.index') }}" class="flex gap-2 flex-1 max-w-md">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o prefijo…" class="form-input flex-1">
            <button type="submit" class="btn-secondary btn-sm">Buscar</button>
            @if(request('search'))<a href="{{ route('brands.index') }}" class="btn-secondary btn-sm">Limpiar</a>@endif
        </form>
    </div>
    <div class="card">
        <div class="card-header"><span class="text-sm text-gray-500">{{ $query->total() }} marca(s)</span></div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Nombre</th><th>Prefijo</th><th>Equipos</th><th class="text-right">Acciones</th></tr></thead>
                    <tbody>
                        @forelse($query as $brand)
                        <tr>
                            <td class="font-medium text-gray-900">{{ $brand->name }}</td>
                            <td><span class="badge-gray font-mono">{{ $brand->prefix }}</span></td>
                            <td class="text-gray-500">{{ $brand->items_count }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('brands.edit', $brand) }}" class="btn-secondary btn-sm">Editar</a>
                                    <form action="{{ route('brands.destroy', $brand) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar marca «{{ addslashes($brand->name) }}»?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-gray-400 py-10">No hay marcas registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($query->hasPages())<div class="flex justify-end">{{ $query->links() }}</div>@endif
</div>
@endsection
