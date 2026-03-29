@extends('layouts.app')
@section('title','Proveedores')
@section('page-title','Proveedores')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('almacen.index', ['tab' => 'equipos']) }}" class="btn-secondary btn-sm">← Almacén</a>
        <a href="{{ route('suppliers.create') }}" class="btn-primary">+ Nuevo proveedor</a>
    </div>
    <div class="flex gap-2">
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex gap-2 flex-1 max-w-md">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar proveedor…" class="form-input flex-1">
            <button type="submit" class="btn-secondary btn-sm">Buscar</button>
            @if(request('search'))<a href="{{ route('suppliers.index') }}" class="btn-secondary btn-sm">Limpiar</a>@endif
        </form>
    </div>
    <div class="card">
        <div class="card-header"><span class="text-sm text-gray-500">{{ $query->total() }} proveedor(es)</span></div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Nombre</th><th>Equipos</th><th class="text-right">Acciones</th></tr></thead>
                    <tbody>
                        @forelse($query as $supplier)
                        <tr>
                            <td class="font-medium text-gray-900">{{ $supplier->name }}</td>
                            <td class="text-gray-500">{{ $supplier->items_count }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-secondary btn-sm">Editar</a>
                                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar proveedor «{{ addslashes($supplier->name) }}»?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-gray-400 py-10">No hay proveedores registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($query->hasPages())<div class="flex justify-end">{{ $query->links() }}</div>@endif
</div>
@endsection
