@extends('layouts.app')
@section('title','Editar Ausencia')
@section('page-title','Editar Ausencia')

@section('content')
<div class="max-w-xl">
<form method="POST" action="{{ route('absences.update',$absence) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="form-label">Tipo *</label><select name="absence_type" class="form-select" required>@foreach(['ENFERMEDAD','AUSENTISMO','PERMISO_PERSONAL','OTRO'] as $t)<option value="{{ $t }}" @selected(old('absence_type',$absence->absence_type)===$t)>{{ str_replace('_',' ',$t) }}</option>@endforeach</select></div>
        <div><label class="form-label">Estado *</label><select name="status" class="form-select" required>@foreach(['PENDIENTE','APROBADO','RECHAZADO','ACTIVO','PAGADO'] as $s)<option value="{{ $s }}" @selected(old('status',$absence->status)===$s)>{{ $s }}</option>@endforeach</select></div>
        <div><label class="form-label">Inicio *</label><input name="start_date" type="date" value="{{ old('start_date',$absence->start_date?->format('Y-m-d')) }}" class="form-input" required></div>
        <div><label class="form-label">Fin *</label><input name="end_date" type="date" value="{{ old('end_date',$absence->end_date?->format('Y-m-d')) }}" class="form-input" required></div>
        <div class="flex items-center gap-2 pt-3"><input type="checkbox" name="is_justified" value="1" @checked(old('is_justified',$absence->is_justified))><label class="text-sm">Justificado</label></div>
        <div class="col-span-2"><label class="form-label">Justificación</label><textarea name="justification" class="form-input" rows="2">{{ old('justification',$absence->justification) }}</textarea></div>
        <div class="col-span-2"><label class="form-label">Notas</label><textarea name="notes" class="form-input" rows="2">{{ old('notes',$absence->notes) }}</textarea></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('absences.show',$absence) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
