@extends('layouts.app')
@section('title','Editar Registro')
@section('page-title','Editar Registro Administrativo')

@section('content')
<div class="max-w-xl">
<form method="POST" action="{{ route('administrative-records.update',$administrativeRecord) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="form-label">Tipo *</label><select name="type_administrative" class="form-select" required>@foreach(['RETROALIMENTACION_ESCRITA','AMONESTACION','ACTA_ADMINISTRATIVA','ENTREVISTA_AUSENTISMO'] as $t)<option value="{{ $t }}" @selected(old('type_administrative',$administrativeRecord->type_administrative)===$t)>{{ str_replace('_',' ',$t) }}</option>@endforeach</select></div>
        <div><label class="form-label">Días suspensión</label><input name="suspended_days" type="number" min="0" value="{{ old('suspended_days',$administrativeRecord->suspended_days) }}" class="form-input"></div>
        <div><label class="form-label">Inicio</label><input name="start_date" type="date" value="{{ old('start_date',$administrativeRecord->start_date?->format('Y-m-d')) }}" class="form-input"></div>
        <div><label class="form-label">Fin</label><input name="end_date" type="date" value="{{ old('end_date',$administrativeRecord->end_date?->format('Y-m-d')) }}" class="form-input"></div>
        <div class="col-span-2"><label class="form-label">Descripción *</label><textarea name="description" class="form-input" rows="4" required>{{ old('description',$administrativeRecord->description) }}</textarea></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('administrative-records.show',$administrativeRecord) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
