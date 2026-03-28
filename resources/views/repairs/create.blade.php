@extends('layouts.app')
@section('title','Ingreso a Taller')
@section('page-title','Ingreso a Taller')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('repairs.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="form-label">Equipo *</label>
            <select name="item_id" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($items as $i)
                <option value="{{ $i->id }}" @selected(old('item_id')==$i->id)>{{ $i->brand->name ?? '' }} {{ $i->model }} — {{ $i->serie }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Procedencia *</label>
            <select name="procedencia" class="form-select" required>
                @foreach(['BODEGA','ASIGNADO','VENDIDO','DESCONOCIDO'] as $p)
                <option value="{{ $p }}" @selected(old('procedencia')===$p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Estado taller *</label>
            <select name="estado_taller" class="form-select" required>
                @foreach(['PENDIENTE','PAUSADO','LISTO'] as $s)
                <option value="{{ $s }}" @selected(old('estado_taller','PENDIENTE')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Estatus *</label>
            <select name="estatus" class="form-select" required>
                @foreach(['EN_ESPERA_AUTORIZACION','EN_ESPERA_PIEZA','PAUSADO','LISTO'] as $s)
                <option value="{{ $s }}" @selected(old('estatus','EN_ESPERA_AUTORIZACION')===$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Proceso *</label>
            <select name="proceso" class="form-select" required>
                @foreach(['DESCONOCIDO','PROCESO_1','PROCESO_2','PROCESO_3'] as $p)
                <option value="{{ $p }}" @selected(old('proceso','DESCONOCIDO')===$p)>{{ str_replace('_',' ',$p) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Ubicación</label>
            <select name="ubicacion" class="form-select">
                <option value="">Sin asignar</option>
                @foreach(['ZONA_1','ZONA_2','ZONA_3','ZONA_4','BASURA'] as $u)
                <option value="{{ $u }}" @selected(old('ubicacion')===$u)>{{ str_replace('_',' ',$u) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-2">
            <label class="form-label">Diagnóstico inicial</label>
            <textarea name="diagnostico_inicial" class="form-input" rows="3">{{ old('diagnostico_inicial') }}</textarea>
        </div>
        <div class="col-span-2">
            <label class="form-label">Comentarios</label>
            <textarea name="comments" class="form-input" rows="2">{{ old('comments') }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('repairs.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
