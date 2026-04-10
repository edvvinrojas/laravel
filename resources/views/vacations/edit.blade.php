@extends('layouts.app')
@section('title','Editar Vacaciones')
@section('page-title','Editar Vacaciones')

@section('content')
<div class="max-w-xl">
<form method="POST" action="{{ route('vacations.update',$vacation) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="form-label">Días *</label><input name="vacation_days" type="number" min="1" value="{{ old('vacation_days',$vacation->vacation_days) }}" class="form-input" required></div>
        <div><label class="form-label">Días restantes</label><input value="{{ $vacation->remaining_days }}" class="form-input bg-gray-50" readonly></div>
        <div><label class="form-label">Inicio *</label><input name="start_date" type="date" value="{{ old('start_date',$vacation->start_date?->format('Y-m-d')) }}" class="form-input" required></div>
        <div><label class="form-label">Fin *</label><input name="end_date" type="date" value="{{ old('end_date',$vacation->end_date?->format('Y-m-d')) }}" class="form-input" required></div>
        <div><label class="form-label">Estado</label><input value="{{ $vacation->status }}" class="form-input bg-gray-50" readonly></div>
        <div class="col-span-2"><label class="form-label">Notas</label><textarea name="notes" class="form-input" rows="2">{{ old('notes',$vacation->notes) }}</textarea></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('vacations.show',$vacation) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
