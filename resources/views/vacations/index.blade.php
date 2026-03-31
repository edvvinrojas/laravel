@extends('layouts.app')
@section('title','Vacaciones')
@section('page-title','Vacaciones')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Empleado…">
            <select name="status" class="form-select w-36"><option value="">Estado</option>@foreach(['PENDIENTE','APROBADO','RECHAZADO'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>@endforeach</select>
            <button class="btn-secondary">Buscar</button>
        </form>
        <div class="flex gap-2"><a href="{{ route('rh.index') }}" class="btn-secondary">&larr; RH</a><a href="{{ route('vacations.create') }}" class="btn-primary">+ Solicitar vacaciones</a></div>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Empleado</th><th>Días</th><th>Inicio</th><th>Fin</th><th>Días restantes</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($vacations as $v)
            @php $sc=['PENDIENTE'=>'badge-yellow','APROBADO'=>'badge-green','RECHAZADO'=>'badge-red','ACTIVO'=>'badge-blue','PAGADO'=>'badge-purple']; @endphp
            <tr>
                <td class="font-medium">{{ $v->employee->nombre }}</td>
                <td>{{ $v->vacation_days }}</td>
                <td>{{ $v->start_date->format('d/m/Y') }}</td>
                <td>{{ $v->end_date->format('d/m/Y') }}</td>
                <td>{{ $v->remaining_days }}</td>
                <td><span class="{{ $sc[$v->status]??'badge-gray' }}">{{ $v->status }}</span></td>
                <td class="flex gap-1">
                    <a href="{{ route('vacations.show',$v) }}" class="btn btn-sm btn-secondary">Ver</a>
                    <a href="{{ route('vacations.edit',$v) }}" class="btn btn-sm btn-primary">Editar</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-8 text-gray-400">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $vacations->links() }}</div>
</div>
@endsection
