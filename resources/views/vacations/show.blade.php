@extends('layouts.app')
@section('title','Vacaciones')
@section('page-title','Detalle de Vacaciones')

@section('content')
@php
    $u = auth()->user();
    $isApprover = $u->hasFullRhAccess() || $u->department === 'rh' || $vacation->employee?->direct_manager_user_id === $u->id;
@endphp

<div class="flex gap-3 mb-4 flex-wrap">
    @if($isApprover && $vacation->status === 'PENDIENTE')
        <form action="{{ route('vacations.approve', $vacation) }}" method="POST">
            @csrf @method('PATCH')
            <button type="submit" class="btn-primary">✓ Aprobar</button>
        </form>
        <form action="{{ route('vacations.reject', $vacation) }}" method="POST"
              onsubmit="return confirm('¿Rechazar esta solicitud?')">
            @csrf @method('PATCH')
            <button type="submit" class="btn-danger">✗ Rechazar</button>
        </form>
    @endif
    @if($vacation->status === 'PENDIENTE')
    <a href="{{ route('vacations.edit',$vacation) }}" class="btn-secondary">Editar</a>
    @endif
    <a href="{{ route('vacations.index') }}" class="btn-secondary">← Volver</a>
</div>

<div class="card max-w-md">
    <div class="card-header flex items-center justify-between">
        <h3 class="font-semibold">{{ $vacation->employee->nombre }}</h3>
        @php $sc=['PENDIENTE'=>'badge-yellow','APROBADO'=>'badge-green','RECHAZADO'=>'badge-red']; @endphp
        <span class="{{ $sc[$vacation->status]??'badge-gray' }}">{{ $vacation->status }}</span>
    </div>
    <div class="card-body text-sm space-y-3">
        <div class="flex justify-between"><span class="text-gray-500">Período</span>
            <span>{{ $vacation->start_date->format('d/m/Y') }} — {{ $vacation->end_date->format('d/m/Y') }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Días solicitados</span>
            <span class="font-semibold text-blue-700">{{ $vacation->vacation_days }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Antigüedad</span>
            <span>{{ $employee->yearsOfService() }} año(s)</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Días correspondientes</span>
            <span>{{ $employee->vacationDaysEntitlement() }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Días restantes (tras esta)</span>
            <span class="font-semibold {{ $vacation->remaining_days < 0 ? 'text-red-600' : 'text-green-700' }}">{{ $vacation->remaining_days }}</span></div>
        @if($vacation->notes)
        <div><p class="text-gray-500">Notas</p><p>{{ $vacation->notes }}</p></div>
        @endif
        <div><p class="text-gray-500">Solicitado por</p><p>{{ $vacation->requestedBy->full_name }}</p></div>
    </div>
</div>
@endsection
