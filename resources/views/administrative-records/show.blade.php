@extends('layouts.app')
@section('title','Registro Administrativo')
@section('page-title','Registro Administrativo')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('administrative-records.edit',$administrativeRecord) }}" class="btn-primary">Editar</a>
    <a href="{{ route('administrative-records.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="card max-w-xl">
    <div class="card-header">
        <div>
            <h3 class="font-semibold">{{ $administrativeRecord->employee->nombre }}</h3>
            <p class="text-xs text-gray-500">{{ str_replace('_',' ',$administrativeRecord->type_administrative) }}</p>
        </div>
    </div>
    <div class="card-body text-sm space-y-3">
        @if($administrativeRecord->suspended_days > 0)<div class="flex justify-between"><span class="text-gray-500">Días suspensión</span><span class="font-medium text-red-600">{{ $administrativeRecord->suspended_days }} días</span></div>@endif
        @if($administrativeRecord->start_date)<div class="flex justify-between"><span class="text-gray-500">Período</span><span>{{ $administrativeRecord->start_date->format('d/m/Y') }} — {{ $administrativeRecord->end_date?->format('d/m/Y') ?? '—' }}</span></div>@endif
        <div><p class="text-gray-500 mb-1">Descripción</p><p>{{ $administrativeRecord->description }}</p></div>
        <div class="flex justify-between border-t pt-2"><span class="text-gray-500">Emitido por</span><span>{{ $administrativeRecord->issuedBy->full_name }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Fecha</span><span>{{ $administrativeRecord->created_at->format('d/m/Y') }}</span></div>
    </div>
</div>
@endsection
