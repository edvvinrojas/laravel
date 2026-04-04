@extends('layouts.app')
@section('title','Inventario')
@section('page-title','Inventario de Insumos')

@section('content')
<div class="space-y-4">
    {{-- Catálogos rápidos --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('almacen.index', ['tab' => 'inventario']) }}" class="btn-secondary btn-sm">← Almacén</a>
        <a href="{{ route('item-catalog.index') }}" class="btn-secondary btn-sm">Catálogo de artículos</a>
        <a href="{{ route('shelves.index') }}" class="btn-secondary btn-sm">Estantes</a>
        <a href="{{ route('service-types.index') }}" class="btn-secondary btn-sm">Tipos de servicio</a>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <form method="GET" action="{{ route('inventory.index') }}" class="flex gap-2 flex-1 max-w-md">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por código o tipo…" class="form-input flex-1">
            <button type="submit" class="btn-secondary btn-sm">Buscar</button>
            @if(request('search'))
            <a href="{{ route('inventory.index') }}" class="btn-secondary btn-sm">Limpiar</a>
            @endif
        </form>
        <a href="{{ route('inventory.create') }}" class="btn-primary">+ Nuevo artículo</a>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="text-sm text-gray-500">{{ $query->total() }} artículo(s) encontrado(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo / Nombre</th>
                            <th>Proveedor</th>
                            <th>Ubicación</th>
                            <th>Costo</th>
                            <th>Disponible</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($query as $item)
                        <tr>
                            <td class="font-mono text-sm">{{ $item->item_code }}</td>
                            <td>
                                <p class="font-medium text-gray-900">{{ $item->catalog?->item_name ?? '—' }}</p>
                                <p class="text-xs text-gray-500">{{ $item->catalog?->item_type ?? '' }}</p>
                            </td>
                            <td class="text-gray-700">{{ $item->supplier?->name ?? '—' }}</td>
                            <td class="text-gray-700">
                                {{ $item->shelf?->name ?? '—' }}
                                @if($item->section) <span class="text-xs text-gray-500">/ {{ $item->section }}</span> @endif
                            </td>
                            <td>${{ $item->cost ? number_format($item->cost,2) : '—' }}</td>
                            <td>
                                @if($item->is_available)
                                <span class="badge-green">Sí</span>
                                @else
                                <span class="badge-gray">No</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('inventory.show',$item) }}" class="btn-secondary btn-sm">Ver</a>
                                    <a href="{{ route('inventory.edit',$item) }}" class="btn-secondary btn-sm">Editar</a>
                                    <form action="{{ route('inventory.destroy',$item) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar artículo «{{ addslashes($item->item_code) }}»?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-gray-400 py-10">No se encontraron artículos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($query->hasPages())
    <div class="flex justify-end">{{ $query->links() }}</div>
    @endif
</div>
@endsection
