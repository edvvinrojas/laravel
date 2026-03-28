@extends('layouts.app')
@section('title','Nuevo Contador')
@section('page-title','Registrar Contador de Impresión')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('print-counters.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="form-label">Renta *</label>
            <select name="rent_id" class="form-select" required>
                <option value="">Seleccionar renta…</option>
                @foreach($rents as $r)
                <option value="{{ $r->id }}" @selected(old('rent_id')==$r->id||request('rent_id')==$r->id)>{{ $r->client->name }} — {{ $r->contract_number }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Mes *</label>
            <select name="period_month" class="form-select" required>
                @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" @selected(old('period_month',date('n'))==$m)>{{ DateTime::createFromFormat('!m',$m)->format('F') }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="form-label">Año *</label>
            <input name="period_year" type="number" value="{{ old('period_year',date('Y')) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Contador BN anterior *</label>
            <input name="bn_previous" type="number" min="0" value="{{ old('bn_previous',0) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Contador BN actual *</label>
            <input name="bn_current" type="number" min="0" value="{{ old('bn_current',0) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Contador Color anterior *</label>
            <input name="color_previous" type="number" min="0" value="{{ old('color_previous',0) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Contador Color actual *</label>
            <input name="color_current" type="number" min="0" value="{{ old('color_current',0) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Fecha de lectura *</label>
            <input name="reading_date" type="date" value="{{ old('reading_date',date('Y-m-d')) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">URL foto contador</label>
            <input name="counter_photo_url" value="{{ old('counter_photo_url') }}" class="form-input">
        </div>
        <div class="col-span-2">
            <label class="form-label">Notas</label>
            <textarea name="notes" class="form-input" rows="2">{{ old('notes') }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('print-counters.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
