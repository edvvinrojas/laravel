@extends('layouts.app')
@section('title','Vacaciones')
@section('page-title','Detalle Vacaciones')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('vacations.edit',$vacation) }}" class="btn-primary">Editar</a>
    <a href="{{ route('vacations.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="card max-w-md">
    <div class="card-header"><h3 class="font-semibold">{{ $vacation->employee->nombre }}</h3>
        @php $sc=['PENDIENTE'=>'badge-yellow','APROBADO'=>'badge-green','RECHAZADO'=>'badge-red']; @endphp
        <span class="{{ $sc[$vacation->status]??'badge-gray' }}">{{ $vacation->status }}</span>
    </div>
    <div class="card-body text-sm space-y-3">
        <div class="flex justify-between"><span class="text-gray-500">Período</span><span>{{ $vacation->start_date->format('d/m/Y') }} — {{ $vacation->end_date->format('d/m/Y') }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Días solicitados</span><span class="font-medium">{{ $vacation->vacation_days }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Días restantes</span><span>{{ $vacation->remaining_days }}</span></div>
        @if($vacation->notes)<div><p class="text-gray-500">Notas</p><p>{{ $vacation->notes }}</p></div>@endif
        <div><p class="text-gray-500">Solicitado por</p><p>{{ $vacation->requestedBy->full_name }}</p></div>
    </div>
</div>
@endsection
