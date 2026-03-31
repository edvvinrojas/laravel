@extends('layouts.app')
@section('title','Editar Ticket '.$itRequest->folio)
@section('page-title','Editar Ticket '.$itRequest->folio)

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('it-requests.update', $itRequest) }}">
@csrf @method('PUT')
<div class="card mb-4">
    <div class="card-header font-semibold">Editar solicitud <span class="font-mono text-blue-700">{{ $itRequest->folio }}</span></div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="form-label">Categoría *</label>
            <select name="category" class="form-select" required>
                @foreach(['EMAIL','INTERNET','HARDWARE','SOFTWARE','IMPRESORA','TELEFONO','ACCESOS','OTRO'] as $c)
                <option value="{{ $c }}" @selected(old('category',$itRequest->category)===$c)>{{ $c }}</option>
                @endforeach
            </select>
            @error('category')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Prioridad *</label>
            <select name="priority" class="form-select" required>
                @foreach(['BAJA','MEDIA','ALTA','URGENTE'] as $p)
                <option value="{{ $p }}" @selected(old('priority',$itRequest->priority)===$p)>{{ $p }}</option>
                @endforeach
            </select>
            @error('priority')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        @if($isTi)
        <div>
            <label class="form-label">Estado</label>
            <select name="status" class="form-select">
                @foreach(['ABIERTO','EN_PROCESO','RESUELTO','CERRADO'] as $s)
                <option value="{{ $s }}" @selected(old('status',$itRequest->status)===$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
        </div>
        @else
        <input type="hidden" name="status" value="{{ $itRequest->status }}">
        @endif

        <div class="col-span-2">
            <label class="form-label">Título *</label>
            <input name="title" type="text" value="{{ old('title',$itRequest->title) }}" class="form-input" required maxlength="255">
            @error('title')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="col-span-2">
            <label class="form-label">Descripción *</label>
            <textarea name="description" class="form-input" rows="5" required>{{ old('description',$itRequest->description) }}</textarea>
            @error('description')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        @if($isTi)
        <div class="col-span-2">
            <label class="form-label">Notas de resolución</label>
            <textarea name="resolution_notes" class="form-input" rows="3">{{ old('resolution_notes',$itRequest->resolution_notes) }}</textarea>
        </div>
        @endif

    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="btn-primary">Guardar cambios</button>
    <a href="{{ route('it-requests.show', $itRequest) }}" class="btn-secondary">Cancelar</a>
</div>
</form>
</div>
@endsection
