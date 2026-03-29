@extends('layouts.app')
@section('title','Plan de Producción')
@section('page-title','Plan de Producción')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('production.edit', $production) }}" class="btn-primary">Editar</a>
    <a href="{{ route('production.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="card max-w-2xl">
    <div class="card-header">
        <div>
            <h3 class="font-semibold">{{ $production->client?->name }}</h3>
            <p class="text-sm text-gray-500">{{ $production->branch?->name }}{{ $production->area ? ' — '.$production->area->name : '' }}</p>
        </div>
        @php
            $badge = match($production->attendance_status) {
                'VISITADO' => 'badge-green', 'NO_QUEDO' => 'badge-red', default => 'badge-yellow'
            };
            $label = match($production->attendance_status) {
                'VISITADO' => 'Visitado', 'NO_QUEDO' => 'No quedó', default => 'Pendiente'
            };
        @endphp
        <span class="{{ $badge }}">{{ $label }}</span>
    </div>
    <div class="card-body grid grid-cols-2 gap-4 text-sm">
        <div><p class="text-gray-500">Fecha de visita</p><p class="font-medium">{{ $production->visit_date->format('d/m/Y H:i') }}</p></div>
        <div><p class="text-gray-500">Tipo de servicio</p><p>{{ $production->serviceType?->name ?? '—' }}</p></div>
        <div><p class="text-gray-500">Creado por</p><p>{{ $production->creator?->full_name ?? '—' }}</p></div>
        <div><p class="text-gray-500">Ticket relacionado</p>
            @if($production->ticket)
            <a href="{{ route('tickets.show', $production->ticket) }}" class="text-blue-600 hover:underline"># {{ $production->ticket->id }}</a>
            @else<p>—</p>@endif
        </div>
        @if($production->description)
        <div class="col-span-2"><p class="text-gray-500">Descripción</p><p>{{ $production->description }}</p></div>
        @endif
        @if($production->users->count())
        <div class="col-span-2">
            <p class="text-gray-500 mb-1">Técnicos</p>
            <div class="flex flex-wrap gap-2">
                @foreach($production->users as $u)
                <span class="badge-blue">{{ $u->full_name }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
