@extends('layouts.app')
@section('title','Ausencia')
@section('page-title','Detalle de Ausencia')

@section('content')
@php $isApprover = in_array(auth()->user()->rol, ['gerencia','administrador']); @endphp
<div class="flex gap-3 mb-4 flex-wrap">
    @if($isApprover && $absence->status === 'PENDIENTE')
        <form action="{{ route('absences.approve', $absence) }}" method="POST">
            @csrf @method('PATCH')
            <button type="submit" class="btn-primary">✓ Aprobar</button>
        </form>
        <form action="{{ route('absences.reject', $absence) }}" method="POST"
              onsubmit="return confirm('¿Rechazar esta solicitud?')">
            @csrf @method('PATCH')
            <button type="submit" class="btn-danger">✗ Rechazar</button>
        </form>
    @endif
    <a href="{{ route('absences.edit',$absence) }}" class="btn-secondary">Editar</a>
    <a href="{{ route('absences.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="card max-w-md">
    <div class="card-header"><h3 class="font-semibold">{{ $absence->employee->nombre }}</h3>
        @php $sc=['PENDIENTE'=>'badge-yellow','APROBADO'=>'badge-green','RECHAZADO'=>'badge-red']; @endphp
        <span class="{{ $sc[$absence->status]??'badge-gray' }}">{{ $absence->status }}</span>
    </div>
    <div class="card-body text-sm space-y-3">
        <div class="flex justify-between"><span class="text-gray-500">Tipo</span><span>{{ str_replace('_',' ',$absence->absence_type) }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Período</span><span>{{ $absence->start_date->format('d/m/Y') }} — {{ $absence->end_date->format('d/m/Y') }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Justificado</span><span>@if($absence->is_justified)<span class="badge-green">Sí</span>@else<span class="badge-red">No</span>@endif</span></div>
        @if($absence->justification)<div><p class="text-gray-500">Justificación</p><p>{{ $absence->justification }}</p></div>@endif
        @if($absence->notes)<div><p class="text-gray-500">Notas</p><p>{{ $absence->notes }}</p></div>@endif
        @if($absence->reviewedBy)<div><p class="text-gray-500">Revisado por</p><p>{{ $absence->reviewedBy->full_name }}</p></div>@endif
    </div>
</div>
@endsection
