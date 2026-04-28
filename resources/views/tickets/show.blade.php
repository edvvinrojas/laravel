@extends('layouts.app')
@section('title','Ticket '.$ticket->ticket_code)
@section('page-title','Ticket '.$ticket->ticket_code)

@section('content')
<div class="flex gap-3 mb-4">
    @if($ticket->report_status !== 'LISTO')
    <a href="{{ route('tickets.edit',$ticket) }}" class="btn-primary">Editar</a>
    <form method="POST" action="{{ route('tickets.close',$ticket) }}">@csrf @method('PATCH')<button class="btn-success">Cerrar ticket</button></form>
    @endif
    <a href="{{ route('tickets.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="card max-w-3xl">
    <div class="card-header">
        <div>
            <p class="text-xs text-gray-500 font-mono">{{ $ticket->ticket_code }}</p>
            <h3 class="font-semibold">{{ $ticket->client->name }} — {{ $ticket->branch->name }}</h3>
            @if($ticket->area) <p class="text-xs text-gray-500">Área: {{ $ticket->area->name }}</p> @endif
        </div>
        @php
            $sc = ['URGENTE'=>'badge-red','PENDIENTE'=>'badge-yellow','ATENCION'=>'badge-yellow','PROGRAMADO'=>'badge-blue','LISTO'=>'badge-green','INFORMATIVO'=>'badge-gray','NO_QUEDO_EN_LA_VISITA'=>'badge-purple'];
            $pc = ['URGENTE'=>'badge-red','NORMAL'=>'badge-blue','BAJA'=>'badge-gray'];
        @endphp
        <div class="flex gap-2">
            <span class="{{ $pc[$ticket->priority]??'badge-gray' }} text-sm">{{ $ticket->priority ?? 'NORMAL' }}</span>
            <span class="{{ $sc[$ticket->report_status]??'badge-gray' }} text-sm">{{ str_replace('_',' ',$ticket->report_status) }}</span>
        </div>
    </div>
    <div class="card-body text-sm space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <p class="text-gray-500">Tipo de falla</p>
                <p class="font-medium">{{ $ticket->report_type }}</p>
            </div>
            <div>
                <p class="text-gray-500">Equipo</p>
                @if($ticket->item)
                    <p class="font-medium">{{ $ticket->item->brand?->name }} {{ $ticket->item->model }}</p>
                    <p class="text-xs text-gray-400">Serie: {{ $ticket->item->serie ?? $ticket->item->sku }}</p>
                @else
                    <p class="text-gray-400">No especificado</p>
                @endif
            </div>
        </div>

        <div>
            <p class="text-gray-500 mb-1">Explicación del problema</p>
            <p>{{ $ticket->description }}</p>
        </div>
        @if($ticket->corrective_action)
        <div>
            <p class="text-gray-500 mb-1">Acción correctiva</p>
            <p>{{ $ticket->corrective_action }}</p>
        </div>
        @endif
        @if($ticket->evidence)
        @php $isFile = !str_starts_with($ticket->evidence, 'http'); @endphp
        <div>
            <p class="text-gray-500 mb-1">Evidencia</p>
            @if($isFile)
                @php
                    $ext = strtolower(pathinfo($ticket->evidence, PATHINFO_EXTENSION));
                    $url = Storage::url($ticket->evidence);
                @endphp
                @if(in_array($ext, ['jpg','jpeg','png','webp','heic']))
                    <a href="{{ $url }}" target="_blank">
                        <img src="{{ $url }}" alt="Evidencia" class="max-h-64 rounded border">
                    </a>
                @else
                    <a href="{{ $url }}" target="_blank" class="text-blue-600 underline break-all">
                        Descargar archivo ({{ strtoupper($ext) }})
                    </a>
                @endif
            @else
                <a href="{{ $ticket->evidence }}" target="_blank" class="text-blue-600 underline break-all">
                    {{ $ticket->evidence }}
                </a>
            @endif
        </div>
        @endif
        <div class="grid grid-cols-2 gap-3 border-t pt-3">
            <div><p class="text-gray-500">Levantado por</p><p>{{ $ticket->creator->full_name ?? $ticket->creator->username }}</p></div>
            <div><p class="text-gray-500">Fecha de levantamiento</p><p>{{ $ticket->created_at->format('d/m/Y H:i') }}</p></div>
            @if($ticket->completed_at)
            <div><p class="text-gray-500">Cerrado</p><p>{{ $ticket->completed_at->format('d/m/Y H:i') }}</p></div>
            @endif
        </div>
    </div>
</div>
@endsection
