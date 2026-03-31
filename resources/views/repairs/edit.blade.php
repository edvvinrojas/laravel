@extends('layouts.app')
@section('title','Editar Reparación')
@section('page-title','Editar Reparación')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('repairs.update',$repair) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-header"><h3 class="font-semibold text-sm">{{ $repair->item->brand->name ?? '' }} {{ $repair->item->model ?? '' }} — {{ $repair->item->serie ?? '' }}</h3></div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="form-label">Estado taller *</label><select name="estado_taller" class="form-select" required>@foreach(['PENDIENTE','PAUSADO','LISTO'] as $s)<option value="{{ $s }}" @selected(old('estado_taller',$repair->estado_taller)===$s)>{{ $s }}</option>@endforeach</select></div>
        <div><label class="form-label">Estatus *</label><select name="estatus" class="form-select" required>@foreach(['EN_ESPERA_AUTORIZACION','EN_ESPERA_PIEZA','PAUSADO','LISTO'] as $s)<option value="{{ $s }}" @selected(old('estatus',$repair->estatus)===$s)>{{ str_replace('_',' ',$s) }}</option>@endforeach</select></div>
        <div><label class="form-label">Proceso *</label><select name="proceso" class="form-select" required>@foreach(['DESCONOCIDO','PROCESO_1','PROCESO_2','PROCESO_3'] as $p)<option value="{{ $p }}" @selected(old('proceso',$repair->proceso)===$p)>{{ str_replace('_',' ',$p) }}</option>@endforeach</select></div>
        <div><label class="form-label">Ubicación</label><select name="ubicacion" class="form-select"><option value="">Sin asignar</option>@foreach(['ZONA_1','ZONA_2','ZONA_3','ZONA_4','BASURA'] as $u)<option value="{{ $u }}" @selected(old('ubicacion',$repair->ubicacion)===$u)>{{ str_replace('_',' ',$u) }}</option>@endforeach</select></div>
        <div><label class="form-label">Folio escaneado</label><input name="folio_escaneado" value="{{ old('folio_escaneado',$repair->folio_escaneado) }}" class="form-input"></div>
        <div class="col-span-2"><label class="form-label">Diagnóstico</label><textarea name="diagnostico_inicial" class="form-input" rows="3">{{ old('diagnostico_inicial',$repair->diagnostico_inicial) }}</textarea></div>
        <div class="col-span-2"><label class="form-label">Comentarios</label><textarea name="comments" class="form-input" rows="2">{{ old('comments',$repair->comments) }}</textarea></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('repairs.show',$repair) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
