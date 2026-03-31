@extends('layouts.app')
@section('title','Nuevo Ticket TI')
@section('page-title','Reportar problema — Mesa de Ayuda TI')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('it-requests.store') }}">
@csrf
<div class="card mb-4">
    <div class="card-header font-semibold">Datos del problema</div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="form-label">Categoría *</label>
            <select name="category" class="form-select" required>
                @foreach(['EMAIL','INTERNET','HARDWARE','SOFTWARE','IMPRESORA','TELEFONO','ACCESOS','OTRO'] as $c)
                <option value="{{ $c }}" @selected(old('category')===$c)>{{ $c }}</option>
                @endforeach
            </select>
            @error('category')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Prioridad *</label>
            <select name="priority" class="form-select" required>
                @foreach(['BAJA','MEDIA','ALTA','URGENTE'] as $p)
                <option value="{{ $p }}" @selected(old('priority','MEDIA')===$p)>{{ $p }}</option>
                @endforeach
            </select>
            @error('priority')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="col-span-2">
            <label class="form-label">Título / Resumen *</label>
            <input name="title" type="text" value="{{ old('title') }}" class="form-input"
                   placeholder="Describe brevemente el problema…" required maxlength="255">
            @error('title')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="col-span-2">
            <label class="form-label">Descripción detallada *</label>
            <textarea name="description" class="form-input" rows="5"
                      placeholder="¿Cuándo ocurre? ¿Qué equipo o sistema afecta? ¿Qué mensaje de error aparece?" required>{{ old('description') }}</textarea>
            @error('description')<p class="form-error">{{ $message }}</p>@enderror
        </div>

    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="btn-primary">Enviar ticket</button>
    <a href="{{ route('it-requests.index') }}" class="btn-secondary">Cancelar</a>
</div>
</form>
</div>
@endsection
