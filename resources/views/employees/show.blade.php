@extends('layouts.app')
@section('title','Empleado')
@section('page-title','Detalle de Empleado')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('employees.edit',$employee) }}" class="btn-primary">Editar</a>
    <a href="{{ route('employees.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="card lg:col-span-2">
        <div class="card-header">
            <h3 class="font-semibold">{{ $employee->nombre }}</h3>
            @if($employee->is_active)<span class="badge-green">Activo</span>@else<span class="badge-gray">Inactivo</span>@endif
        </div>
        <div class="card-body grid grid-cols-2 gap-4 text-sm">
            @if($employee->departamento)
            <div><p class="text-gray-500">Departamento</p><p>{{ $employee->departamento }}</p></div>
            @endif
            @if($employee->puesto)
            <div><p class="text-gray-500">Puesto</p><p>{{ $employee->puesto }}</p></div>
            @endif
            @if($employee->sueldo)
            <div><p class="text-gray-500">Sueldo mensual</p><p class="font-medium">${{ number_format($employee->sueldo, 2) }}</p></div>
            @endif
            <div><p class="text-gray-500">NSS</p><p class="font-mono">{{ $employee->nss }}</p></div>
            <div><p class="text-gray-500">RFC</p><p class="font-mono">{{ $employee->rfc }}</p></div>
            <div><p class="text-gray-500">CURP</p><p class="font-mono text-xs">{{ $employee->curp }}</p></div>
            <div><p class="text-gray-500">Nacimiento</p><p>{{ $employee->birthday->format('d/m/Y') }}</p></div>
            <div><p class="text-gray-500">Ingreso</p><p>{{ $employee->hire_date->format('d/m/Y') }}</p></div>
            <div><p class="text-gray-500">Tel. emergencia</p><p>{{ $employee->phone_emergency }}</p></div>
            <div class="col-span-2"><p class="text-gray-500">Contacto emergencia</p><p>{{ $employee->contact_emergency }}</p></div>
            @if($employee->user)<div class="col-span-2"><p class="text-gray-500">Usuario sistema</p><p>{{ $employee->user->full_name }} ({{ $employee->user->email }})</p></div>@endif
        </div>
    </div>
    <div class="space-y-4">
        <div class="card">
            <div class="card-header"><h4 class="text-sm font-semibold">Últimas nóminas</h4><a href="{{ route('payrolls.create') }}?employee_id={{ $employee->id }}" class="btn btn-sm btn-primary">+ Nómina</a></div>
            <ul class="divide-y divide-gray-100">
                @forelse($employee->payrolls->take(4) as $p)
                <li class="px-4 py-2 flex justify-between text-sm"><span>{{ $p->pay_day->format('d/m/Y') }}</span><span class="font-medium">${{ number_format($p->total_pay,2) }}</span></li>
                @empty<li class="px-4 py-4 text-center text-sm text-gray-400">Sin nóminas</li>@endforelse
            </ul>
        </div>
        <div class="card">
            <div class="card-header"><h4 class="text-sm font-semibold">Vacaciones</h4><a href="{{ route('vacations.create') }}?employee_id={{ $employee->id }}" class="btn btn-sm btn-primary">+ Solicitud</a></div>
            <ul class="divide-y divide-gray-100">
                @forelse($employee->vacations->take(3) as $v)
                <li class="px-4 py-2 flex justify-between text-sm"><span>{{ $v->start_date->format('d/m') }}–{{ $v->end_date->format('d/m/Y') }}</span><span class="{{ $v->status==='APROBADO'?'badge-green':'badge-yellow' }}">{{ $v->status }}</span></li>
                @empty<li class="px-4 py-4 text-center text-sm text-gray-400">Sin vacaciones</li>@endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
