@extends('layouts.app')
@section('title','Ausentismo')
@section('page-title','Ausentismo')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Empleado…">
            <select name="status" class="form-select w-36"><option value="">Estado</option>@foreach(['PENDIENTE','APROBADO','RECHAZADO'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>@endforeach</select>
            <button class="btn-secondary">Buscar</button>
        </form>
        <div class="flex gap-2"><a href="{{ route('rh.index') }}" class="btn-secondary">&larr; RH</a><a href="{{ route('absences.create') }}" class="btn-primary">+ Registrar ausencia</a></div>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Empleado</th><th>Tipo</th><th>Inicio</th><th>Fin</th><th>Justificado</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($absences as $a)
            <tr>
                <td class="font-medium">{{ $a->employee->nombre }}</td>
                <td><span class="badge-blue text-xs">{{ str_replace('_',' ',$a->absence_type) }}</span></td>
                <td>{{ $a->start_date->format('d/m/Y') }}</td>
                <td>{{ $a->end_date->format('d/m/Y') }}</td>
                <td>@if($a->is_justified)<span class="badge-green">Sí</span>@else<span class="badge-red">No</span>@endif</td>
                <td>@php $sc=['PENDIENTE'=>'badge-yellow','APROBADO'=>'badge-green','RECHAZADO'=>'badge-red']; @endphp<span class="{{ $sc[$a->status]??'badge-gray' }}">{{ $a->status }}</span></td>
                <td class="flex gap-1 flex-wrap">
                    <a href="{{ route('absences.show',$a) }}" class="btn btn-sm btn-secondary">Ver</a>
                    @if(in_array(auth()->user()->rol, ['administrador','gerencia']) && $a->status === 'PENDIENTE')
                    <form action="{{ route('absences.approve',$a) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-success">Aprobar</button>
                    </form>
                    <form action="{{ route('absences.reject',$a) }}" method="POST"
                          onsubmit="return confirm('¿Rechazar esta ausencia?')">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-danger">Rechazar</button>
                    </form>
                    @endif
                    <a href="{{ route('absences.edit',$a) }}" class="btn btn-sm btn-primary">Editar</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-8 text-gray-400">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $absences->links() }}</div>
</div>
@endsection
