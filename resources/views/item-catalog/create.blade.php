@extends('layouts.app')
@section('title','Nuevo Artículo de Catálogo')
@section('page-title','Nuevo Artículo de Catálogo')

@section('content')
<div class="mb-4">
    <a href="{{ route('item-catalog.index') }}" class="btn-secondary">← Volver al catálogo</a>
</div>
<div class="max-w-2xl">
<form method="POST" action="{{ route('item-catalog.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="form-label">Nombre del artículo <span class="text-red-500">*</span></label>
            <input name="item_name" value="{{ old('item_name') }}" class="form-input @error('item_name') border-red-400 @enderror" required autofocus placeholder="Ej: Tóner HP 85A Negro">
            @error('item_name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Tipo <span class="text-red-500">*</span></label>
            <select name="item_type" id="item_type" class="form-select @error('item_type') border-red-400 @enderror" required onchange="toggleColor()">
                <option value="">Seleccionar…</option>
                <option value="TONER" @selected(old('item_type')=='TONER')>Tóner</option>
                <option value="REFACCION" @selected(old('item_type')=='REFACCION')>Refacción</option>
            </select>
            @error('item_type')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Marca</label>
            <select name="brand_id" class="form-select">
                <option value="">Sin marca</option>
                @foreach($brands as $b)
                <option value="{{ $b->id }}" @selected(old('brand_id')==$b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div id="color_field" class="{{ old('item_type') !== 'TONER' ? 'hidden' : '' }}">
            <label class="form-label">Color del tóner</label>
            <select name="color" class="form-select">
                <option value="">N/A</option>
                <option value="K" @selected(old('color')=='K')>Negro (K)</option>
                <option value="C" @selected(old('color')=='C')>Cyan (C)</option>
                <option value="M" @selected(old('color')=='M')>Magenta (M)</option>
                <option value="Y" @selected(old('color')=='Y')>Amarillo (Y)</option>
            </select>
        </div>
        <div class="col-span-2">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-input" rows="2">{{ old('description') }}</textarea>
        </div>
        <div class="col-span-2">
            <label class="form-label">Uso / Compatibilidad</label>
            <textarea name="usage" class="form-input" rows="2" placeholder="Modelos compatibles, notas de uso…">{{ old('usage') }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('item-catalog.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@push('scripts')
<script>
function toggleColor() {
    const t = document.getElementById('item_type').value;
    document.getElementById('color_field').classList.toggle('hidden', t !== 'TONER');
}
</script>
@endpush
@endsection
