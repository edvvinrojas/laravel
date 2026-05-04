@extends('layouts.app')
@section('title','Nueva Ruta')
@section('page-title','Nueva Ruta')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('routes.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Código de ruta</label>
            <input name="route_code" value="{{ old('route_code') }}" class="form-input font-mono" placeholder="Vacío = usar {{ $nextCode }}" list="route_codes_list">
            <datalist id="route_codes_list">
                @foreach($routeCodes as $rc)
                    <option value="{{ $rc }}"></option>
                @endforeach
            </datalist>
            <p class="text-xs text-gray-400 mt-1">Siguiente sugerido: <span class="font-mono font-semibold text-blue-600">{{ $nextCode }}</span></p>
            @error('route_code')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div><label class="form-label">Chofer *</label><input name="driver_name" value="{{ old('driver_name') }}" class="form-input" required></div>
        <div><label class="form-label">Vehículo</label><input name="vehicle" value="{{ old('vehicle') }}" class="form-input"></div>
        <div><label class="form-label">Estado *</label>
            <select name="status" class="form-select" required>
                @foreach(['PROGRAMADA','EN_RUTA','PAUSADA','COMPLETADA','CANCELADA'] as $s)
                <option value="{{ $s }}" @selected(old('status','PROGRAMADA')===$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="form-label">Fecha programada *</label><input name="scheduled_date" type="date" value="{{ old('scheduled_date',date('Y-m-d')) }}" class="form-input" required></div>
        <div class="col-span-2"><label class="form-label">Notas</label><textarea name="notes" class="form-input" rows="2">{{ old('notes') }}</textarea></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('routes.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
