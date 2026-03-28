{{-- resources/views/clients/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Clientes')
@section('page-title', 'Clientes')
@section('breadcrumb', 'Gestión de clientes')

@section('content')
<div class="space-y-4">

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <form method="GET" action="{{ route('clients.index') }}" class="flex gap-2 flex-1 max-w-md">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Buscar por nombre, RFC o ciudad…"
                class="form-input flex-1"
            >
            <button type="submit" class="btn-secondary btn-sm">Buscar</button>
            @if(request('search'))
                <a href="{{ route('clients.index') }}" class="btn-secondary btn-sm">Limpiar</a>
            @endif
        </form>
        <a href="{{ route('clients.create') }}" class="btn-primary">
            + Nuevo cliente
        </a>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <span class="text-sm text-gray-500">
                {{ $query->total() }} {{ Str::plural('cliente', $query->total()) }} encontrado{{ $query->total() === 1 ? '' : 's' }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Nombre comercial</th>
                            <th>RFC</th>
                            <th>Ciudad</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($query as $client)
                        <tr>
                            <td class="font-medium text-gray-900">{{ $client->name }}</td>
                            <td class="text-gray-600">{{ $client->comercial_name ?? '—' }}</td>
                            <td class="font-mono text-sm text-gray-700">{{ $client->rfc ?? '—' }}</td>
                            <td class="text-gray-600">{{ $client->city ?? '—' }}</td>
                            <td class="text-gray-600">
                                @if($client->contact)
                                    {{ $client->contact->name }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td>
                                @if($client->is_active)
                                    <span class="badge-green">Activo</span>
                                @else
                                    <span class="badge-gray">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('clients.show', $client) }}"
                                       class="btn-secondary btn-sm">Ver</a>
                                    <a href="{{ route('clients.edit', $client) }}"
                                       class="btn-secondary btn-sm">Editar</a>
                                    <form action="{{ route('clients.destroy', $client) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Eliminar cliente «{{ addslashes($client->name) }}»?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-400 py-10">
                                No se encontraron clientes.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if($query->hasPages())
    <div class="flex justify-end">
        {{ $query->links() }}
    </div>
    @endif

</div>
@endsection
