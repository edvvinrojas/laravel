@extends('layouts.app')
@section('title','Ticket '.$itRequest->folio)
@section('page-title','Ticket '.$itRequest->folio)
@section('breadcrumb','Mesa de Ayuda TI')

@section('content')
<div class="max-w-3xl space-y-4">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Header card --}}
    <div class="card">
        <div class="card-header">
            <div>
                <span class="font-mono font-semibold text-blue-700">{{ $itRequest->folio }}</span>
                <span class="ml-3 text-gray-700 font-medium">{{ $itRequest->title }}</span>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $sc = match($itRequest->status) {
                        'ABIERTO'    => 'badge-yellow',
                        'EN_PROCESO' => 'badge-blue',
                        'RESUELTO'   => 'badge-green',
                        'CERRADO'    => 'badge-gray',
                    };
                    $pc = match($itRequest->priority) {
                        'URGENTE' => 'badge-red',
                        'ALTA'    => 'badge-yellow',
                        'MEDIA'   => 'badge-blue',
                        default   => 'badge-gray',
                    };
                @endphp
                <span class="badge {{ $pc }}">{{ $itRequest->priority }}</span>
                <span class="badge {{ $sc }}">{{ str_replace('_',' ',$itRequest->status) }}</span>
            </div>
        </div>

        <div class="card-body grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-gray-400 text-xs">Solicitante</p>
                <p class="font-medium">{{ $itRequest->user->full_name }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Categoría</p>
                <span class="badge badge-blue">{{ $itRequest->category }}</span>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Asignado a</p>
                <p class="font-medium">{{ $itRequest->assignedUser?->full_name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Creado</p>
                <p>{{ $itRequest->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    {{-- Description --}}
    <div class="card">
        <div class="card-header font-semibold text-sm">Descripción</div>
        <div class="card-body text-sm text-gray-700 whitespace-pre-wrap">{{ $itRequest->description }}</div>
    </div>

    {{-- Resolution --}}
    @if($itRequest->resolution_notes)
    <div class="card border-green-200">
        <div class="card-header font-semibold text-sm text-green-800">
            Resolución
            @if($itRequest->resolved_at)
            <span class="text-xs font-normal text-gray-400">{{ $itRequest->resolved_at->format('d/m/Y H:i') }}</span>
            @endif
        </div>
        <div class="card-body text-sm text-gray-700 whitespace-pre-wrap">{{ $itRequest->resolution_notes }}</div>
    </div>
    @endif

    {{-- TI actions --}}
    @if($isTi)
    <div class="card">
        <div class="card-header font-semibold text-sm">Acciones TI</div>
        <div class="card-body space-y-4">

            {{-- Quick assign --}}
            @if(!$itRequest->assigned_to && in_array($itRequest->status,['ABIERTO']))
            <form method="POST" action="{{ route('it-requests.assign', $itRequest) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn-primary btn-sm">Asignarme este ticket</button>
            </form>
            @endif

            {{-- Update status / assign / resolve --}}
            <form method="POST" action="{{ route('it-requests.update', $itRequest) }}">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label text-xs">Estado</label>
                        <select name="status" class="form-select text-sm">
                            @foreach(['ABIERTO','EN_PROCESO','RESUELTO','CERRADO'] as $s)
                            <option value="{{ $s }}" @selected($itRequest->status===$s)>{{ str_replace('_',' ',$s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs">Asignar a</label>
                        <select name="assigned_to" class="form-select text-sm">
                            <option value="">Sin asignar</option>
                            @foreach($tiUsers as $u)
                            <option value="{{ $u->id }}" @selected($itRequest->assigned_to==$u->id)>{{ $u->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs">Categoría</label>
                        <select name="category" class="form-select text-sm">
                            @foreach(['EMAIL','INTERNET','HARDWARE','SOFTWARE','IMPRESORA','TELEFONO','ACCESOS','OTRO'] as $c)
                            <option value="{{ $c }}" @selected($itRequest->category===$c)>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs">Prioridad</label>
                        <select name="priority" class="form-select text-sm">
                            @foreach(['BAJA','MEDIA','ALTA','URGENTE'] as $p)
                            <option value="{{ $p }}" @selected($itRequest->priority===$p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="title"       value="{{ $itRequest->title }}">
                    <input type="hidden" name="description" value="{{ $itRequest->description }}">
                    <div class="col-span-2">
                        <label class="form-label text-xs">Notas de resolución</label>
                        <textarea name="resolution_notes" class="form-input text-sm" rows="3">{{ old('resolution_notes', $itRequest->resolution_notes) }}</textarea>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn-primary btn-sm">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Owner actions --}}
    <div class="flex gap-3">
        @if($itRequest->user_id === auth()->id() && $itRequest->status === 'ABIERTO')
        <a href="{{ route('it-requests.edit', $itRequest) }}" class="btn-secondary btn-sm">Editar</a>
        @endif
        @if($isTi || $itRequest->user_id === auth()->id())
        <form method="POST" action="{{ route('it-requests.destroy', $itRequest) }}"
              onsubmit="return confirm('¿Eliminar este ticket?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger btn-sm">Eliminar</button>
        </form>
        @endif
        <a href="{{ route('it-requests.index') }}" class="btn-secondary btn-sm">Volver</a>
    </div>

</div>
@endsection
