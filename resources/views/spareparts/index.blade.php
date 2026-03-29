@extends('layouts.app')
@section('title','Refacciones')
@section('page-title','Refacciones')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="flex items-center gap-3 flex-1">
            <a href="{{ route('almacen.index', ['tab' => 'refacciones']) }}" class="btn-secondary btn-sm">← Almacén</a>
            <form method="GET" class="flex gap-2 flex-1 max-w-sm">
                <input name="search" value="{{ request('search') }}" class="form-input w-56" placeholder="Nombre / código…">
                <button class="btn-secondary btn-sm">Buscar</button>
            </form>
        </div>
        <a href="{{ route('spareparts.create') }}" class="btn-primary">+ Nueva refacción</a>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Código</th><th>Nombre</th><th>Marca</th><th>Equipo</th><th>Proveedor</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($spareparts as $s)
            <tr>
                <td class="font-mono text-xs">{{ $s->code ?? '—' }}</td>
                <td class="font-medium">{{ $s->name }}</td>
                <td>{{ $s->brand ?? '—' }}</td>
                <td>{{ $s->equipment ?? '—' }}</td>
                <td>{{ $s->supplier ?? '—' }}</td>
                <td class="flex gap-1">
                    <a href="{{ route('spareparts.show',$s) }}" class="btn btn-sm btn-secondary">Ver</a>
                    <a href="{{ route('spareparts.edit',$s) }}" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" action="{{ route('spareparts.destroy',$s) }}" onsubmit="return confirm('¿Eliminar?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-8 text-gray-400">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $spareparts->links() }}</div>
</div>
@endsection
