@extends('layouts.app')
@section('title','Usuarios')
@section('page-title','Usuarios del Sistema')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Nombre / correo…">
            <select name="rol" class="form-select w-40"><option value="">Rol</option>@foreach(['administrador','gerencia','usuario'] as $r)<option value="{{ $r }}" @selected(request('rol')===$r)>{{ ucfirst($r) }}</option>@endforeach</select>
            <button class="btn-secondary">Buscar</button>
        </form>
        @if(auth()->user()->hasPermission('usuarios.create'))
            <a href="{{ route('users.create') }}" class="btn-primary">+ Nuevo usuario</a>
        @endif
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Depto.</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($users as $u)
            <tr>
                <td class="font-medium">{{ $u->full_name }}</td>
                <td class="text-gray-500 text-sm">{{ $u->email }}</td>
                <td><span class="{{ $u->rol==='administrador'?'badge-red':($u->rol==='gerencia'?'badge-blue':'badge-gray') }}">{{ $u->rol }}</span></td>
                <td><span class="badge-purple text-xs">{{ $u->department }}</span></td>
                <td>@if($u->is_active)<span class="badge-green">Activo</span>@else<span class="badge-gray">Inactivo</span>@endif</td>
                <td class="flex gap-1">
                    @if(auth()->user()->hasPermission('usuarios.view'))
                        <a href="{{ route('users.show',$u) }}" class="btn btn-sm btn-secondary">Ver</a>
                    @endif
                    @if(auth()->user()->hasPermission('usuarios.edit'))
                        <a href="{{ route('users.edit',$u) }}" class="btn btn-sm btn-primary">Editar</a>
                    @endif
                    @if(auth()->user()->hasPermission('usuarios.delete') && $u->id !== auth()->id())
                        <form method="POST" action="{{ route('users.destroy',$u) }}" onsubmit="return confirm('¿Desactivar usuario?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Desactivar</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-8 text-gray-400">Sin usuarios</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $users->links() }}</div>
</div>
@endsection
