@extends('layouts.app')
@section('title','Editar Contador')
@section('page-title','Editar Contador')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('print-counters.update',$printCounter) }}" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-2 gap-4">
        <div><label class="form-label">Contador BN anterior *</label><input name="bn_previous" type="number" min="0" value="{{ old('bn_previous',$printCounter->bn_previous) }}" class="form-input" required></div>
        <div><label class="form-label">Contador BN actual *</label><input name="bn_current" type="number" min="0" value="{{ old('bn_current',$printCounter->bn_current) }}" class="form-input" required></div>
        <div><label class="form-label">Contador Color anterior *</label><input name="color_previous" type="number" min="0" value="{{ old('color_previous',$printCounter->color_previous) }}" class="form-input" required></div>
        <div><label class="form-label">Contador Color actual *</label><input name="color_current" type="number" min="0" value="{{ old('color_current',$printCounter->color_current) }}" class="form-input" required></div>
        <div><label class="form-label">Fecha lectura *</label><input name="reading_date" type="date" value="{{ old('reading_date',$printCounter->reading_date?->format('Y-m-d')) }}" class="form-input" required></div>
        <div>
            <label class="form-label">Foto del contador</label>
            @if($printCounter->counter_photo_url)
            <div class="mb-2">
                <img src="{{ Storage::url($printCounter->counter_photo_url) }}" class="h-24 rounded border object-cover">
                <p class="text-xs text-gray-400 mt-1">Foto actual — sube una nueva para reemplazarla</p>
            </div>
            @endif
            <input name="counter_photo" type="file" accept="image/*" class="form-input">
        </div>
        <div class="col-span-2"><label class="form-label">Notas</label><textarea name="notes" class="form-input" rows="2">{{ old('notes',$printCounter->notes) }}</textarea></div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('print-counters.show',$printCounter) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
