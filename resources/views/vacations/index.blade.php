@extends('layouts.app')
@section('title','Vacaciones')
@section('page-title','Vacaciones')

@section('content')

{{-- Panel de días disponibles por empleado (solo admins/gerencia) --}}
@if($employeeStats && $employeeStats->isNotEmpty())
<div class="card mb-5">
    <div class="card-header font-semibold text-sm">Resumen de vacaciones por empleado</div>
    <div class="overflow-x-auto">
        <table class="table text-sm">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th class="text-right">Antigüedad</th>
                    <th class="text-right">Días correspondientes</th>
                    <th class="text-right">Días usados</th>
                    <th class="text-right">Días restantes</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($employeeStats as $es)
            <tr>
                <td class="font-medium">{{ $es['nombre'] }}</td>
                <td class="text-right">{{ $es['years'] }} año(s)</td>
                <td class="text-right">{{ $es['entitlement'] }} días</td>
                <td class="text-right">{{ $es['used'] }}</td>
                <td class="text-right">
                    <span class="{{ $es['remaining'] <= 0 ? 'text-red-600 font-bold' : ($es['remaining'] <= 3 ? 'text-yellow-600 font-semibold' : 'text-green-700 font-semibold') }}">
                        {{ $es['remaining'] }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('vacations.create', ['employee_id' => $es['id']]) }}"
                       class="text-xs text-blue-600 hover:underline">Solicitar</a>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Solicitudes de vacaciones --}}
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2 flex-wrap">
            <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Empleado…">
            <select name="status" class="form-select w-36">
                <option value="">Estado</option>
                @foreach(['PENDIENTE','APROBADO','RECHAZADO'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Buscar</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('rh.index') }}" class="btn-secondary">&larr; RH</a>
            <a href="{{ route('vacations.create') }}" class="btn-primary">+ Solicitar vacaciones</a>
        </div>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Días solicitados</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Días restantes</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($vacations as $v)
            @php $sc=['PENDIENTE'=>'badge-yellow','APROBADO'=>'badge-green','RECHAZADO'=>'badge-red','ACTIVO'=>'badge-blue','PAGADO'=>'badge-purple']; @endphp
            @php
                $u = auth()->user();
                $canApprove = $u->hasFullRhAccess()
                    || $u->department === 'rh'
                    || $v->employee?->direct_manager_user_id === $u->id;
            @endphp
            <tr>
                <td class="font-medium">{{ $v->employee->nombre }}</td>
                <td>{{ $v->vacation_days }}</td>
                <td>{{ $v->start_date->format('d/m/Y') }}</td>
                <td>{{ $v->end_date->format('d/m/Y') }}</td>
                <td>{{ $v->remaining_days }}</td>
                <td><span class="{{ $sc[$v->status]??'badge-gray' }}">{{ $v->status }}</span></td>
                <td class="flex gap-1 flex-wrap">
                    <a href="{{ route('vacations.show',$v) }}" class="btn btn-sm btn-secondary">Ver</a>
                    @if($canApprove && $v->status === 'PENDIENTE')
                    <form action="{{ route('vacations.approve',$v) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-success">Aprobar</button>
                    </form>
                    <form action="{{ route('vacations.reject',$v) }}" method="POST"
                          onsubmit="return confirm('¿Rechazar esta solicitud?')">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-danger">Rechazar</button>
                    </form>
                    @endif
                    @if($v->status === 'PENDIENTE')
                    <a href="{{ route('vacations.edit',$v) }}" class="btn btn-sm btn-primary">Editar</a>
                    @endif
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
