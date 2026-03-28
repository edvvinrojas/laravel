@extends('layouts.app')
@section('title','Ticket')
@section('page-title','Detalle de Ticket')

@section('content')
<div class="flex gap-3 mb-4">
    @if($ticket->report_status !== 'LISTO')
    <a href="{{ route('tickets.edit',$ticket) }}" class="btn-primary">Editar</a>
    <form method="POST" action="{{ route('tickets.close',$ticket) }}">@csrf @method('PATCH')<button class="btn-success">Cerrar ticket</button></form>
    @endif
    <a href="{{ route('tickets.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="card max-w-2xl">
    <div class="card-header">
        <div>
            <p class="text-xs text-gray-500">{{ $ticket->report_type }}</p>
            <h3 class="font-semibold">{{ $ticket->client->name }} — {{ $ticket->branch->name }}</h3>
        </div>
        @php $sc=['URGENTE'=>'badge-red','PENDIENTE'=>'badge-yellow','ATENCION'=>'badge-yellow','PROGRAMADO'=>'badge-blue','LISTO'=>'badge-green','INFORMATIVO'=>'badge-gray','NO_QUEDO_EN_LA_VISITA'=>'badge-purple']; @endphp
        <span class="{{ $sc[$ticket->report_status]??'badge-gray' }} text-sm">{{ str_replace('_',' ',$ticket->report_status) }}</span>
    </div>
    <div class="card-body text-sm space-y-4">
        <div>
            <p class="text-gray-500 mb-1">Descripción</p>
            <p>{{ $ticket->description }}</p>
        </div>
        @if($ticket->corrective_action)
        <div>
            <p class="text-gray-500 mb-1">Acción correctiva</p>
            <p>{{ $ticket->corrective_action }}</p>
        </div>
        @endif
        <div class="grid grid-cols-2 gap-3 border-t pt-3">
            <div><p class="text-gray-500">Creado por</p><p>{{ $ticket->creator->full_name }}</p></div>
            <div><p class="text-gray-500">Fecha</p><p>{{ $ticket->created_at->format('d/m/Y H:i') }}</p></div>
            @if($ticket->completed_at)
            <div><p class="text-gray-500">Cerrado</p><p>{{ $ticket->completed_at->format('d/m/Y H:i') }}</p></div>
            @endif
        </div>
    </div>
</div>
@endsection
