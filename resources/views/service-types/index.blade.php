@extends('layouts.app')
@section('title','Tipos de Servicio')
@section('page-title','Tipos de Servicio')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('production.index') }}" class="btn-secondary btn-sm">← Producción</a>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ $serviceTypes->total() }} tipo(s)</span>
            <a href="{{ route('service-types.create') }}" class="btn-primary">+ Nuevo tipo</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Planes</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($serviceTypes as $st)
                        <tr>
                            <td class="font-medium">{{ $st->name }}</td>
                            <td class="text-gray-500 text-xs">{{ $st->description ?? '—' }}</td>
                            <td>{{ $st->monthly_plans_count }}</td>
                            <td><span class="{{ $st->is_active ? 'badge-green' : 'badge-gray' }}">{{ $st->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('service-types.edit', $st) }}" class="btn-secondary btn-sm">Editar</a>
                                    <form action="{{ route('service-types.destroy', $st) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-gray-400 py-8">Sin tipos de servicio registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($serviceTypes->hasPages())<div class="flex justify-end">{{ $serviceTypes->links() }}</div>@endif
</div>
@endsection
