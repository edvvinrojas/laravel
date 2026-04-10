@extends('layouts.app')
@section('title','Empleados')
@section('page-title','Empleados')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ request('search') }}" class="form-input w-56" placeholder="Nombre / NSS / RFC…">
            <button class="btn-secondary">Buscar</button>
        </form>
        <div class="flex gap-2"><a href="{{ route('rh.index') }}" class="btn-secondary">&larr; RH</a><a href="{{ route('employees.create') }}" class="btn-primary">+ Nuevo empleado</a></div>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Nombre</th><th>NSS</th><th>RFC</th><th>Ingreso</th><th>Fecha baja</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($employees as $e)
            <tr>
                <td class="font-medium">{{ $e->nombre }}</td>
                <td class="font-mono text-xs">{{ $e->nss }}</td>
                <td class="font-mono text-xs">{{ $e->rfc }}</td>
                <td>{{ $e->hire_date->format('d/m/Y') }}</td>
                <td>{{ $e->termination_date?->format('d/m/Y') ?? 'N/A' }}</td>
                <td>@if($e->is_active)<span class="badge-green">Activo</span>@else<span class="badge-gray">Inactivo</span>@endif</td>
                <td class="flex gap-1">
                    <a href="{{ route('employees.show',$e) }}" class="btn btn-sm btn-secondary">Ver</a>
                    <a href="{{ route('employees.edit',$e) }}" class="btn btn-sm btn-primary">Editar</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-8 text-gray-400">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $employees->links() }}</div>
</div>
@endsection
