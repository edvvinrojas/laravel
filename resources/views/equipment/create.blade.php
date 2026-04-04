{{-- resources/views/equipment/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Nuevo equipo')
@section('page-title', 'Nuevo equipo')

@section('content')
<div class="mb-4">
    <a href="{{ route('equipment.index') }}" class="btn-secondary">← Volver a equipos</a>
</div>
<div class="max-w-3xl">
<form action="{{ route('equipment.store') }}" method="POST" class="space-y-4">
@csrf

<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Datos del equipo</h3></div>
    <div class="card-body space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Marca <span class="text-red-500">*</span></label>
                <select name="brand_id" class="form-select @error('brand_id') border-red-400 @enderror" required>
                    <option value="">— Seleccionar marca —</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
                @error('brand_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Modelo (texto libre) <span class="text-red-500">*</span></label>
                <input name="model" value="{{ old('model') }}"
                       class="form-input @error('model') border-red-400 @enderror"
                       required placeholder="Nombre del modelo">
                @error('model')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Tipo color <span class="text-red-500">*</span></label>
                <select name="type" class="form-select @error('type') border-red-400 @enderror" required>
                    <option value="">— Seleccionar —</option>
                    <option value="MONOCROMO" {{ old('type') === 'MONOCROMO' ? 'selected' : '' }}>Monocromo</option>
                    <option value="COLOR" {{ old('type') === 'COLOR' ? 'selected' : '' }}>Color</option>
                </select>
                @error('type')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Modelo de tóner <span class="text-red-500">*</span></label>
                <input name="model_toner" value="{{ old('model_toner') }}"
                       class="form-input @error('model_toner') border-red-400 @enderror"
                       required placeholder="Ej. TK-1175, CE285A">
                @error('model_toner')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Identificación</h3></div>
    <div class="card-body space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">SKU</label>
                <select name="sku" class="form-select @error('sku') border-red-400 @enderror">
                    <option value="">— Seleccionar SKU —</option>
                    @foreach($skus as $s)
                        <option value="{{ $s->code }}" @selected(old('sku') === $s->code)>{{ $s->code }}</option>
                    @endforeach
                </select>
                @error('sku')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Serie <span class="text-red-500">*</span></label>
                <input name="serie" value="{{ old('serie') }}"
                       class="form-input @error('serie') border-red-400 @enderror"
                       required>
                @error('serie')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Adquisición y estado</h3></div>
    <div class="card-body space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Proveedor</label>
                <select name="supplier_id" class="form-select @error('supplier_id') border-red-400 @enderror">
                    <option value="">— Sin proveedor —</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                    @endforeach
                </select>
                @error('supplier_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Estado / Ubicación</label>
                <select name="location_status" class="form-select @error('location_status') border-red-400 @enderror">
                    @foreach(['BODEGA' => 'Bodega', 'ASIGNADO' => 'Asignado', 'VENDIDO' => 'Vendido', 'TALLER' => 'Taller', 'DESCONOCIDO' => 'Desconocido'] as $val => $lbl)
                        <option value="{{ $val }}" {{ old('location_status', 'BODEGA') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('location_status')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Factura</label>
                <input name="invoice" value="{{ old('invoice') }}"
                       class="form-input @error('invoice') border-red-400 @enderror">
                @error('invoice')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Costo</label>
                <input type="number" name="cost" value="{{ old('cost') }}"
                       class="form-input @error('cost') border-red-400 @enderror"
                       min="0" step="0.01">
                @error('cost')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="form-label">Comentarios</label>
            <textarea name="comments" rows="3"
                      class="form-input @error('comments') border-red-400 @enderror">{{ old('comments') }}</textarea>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', '1') ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-gray-300 text-blue-600">
            <label for="is_active" class="form-label mb-0">Equipo activo</label>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('equipment.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>

</form>
</div>

@push('scripts')
<script>
    const brandSelect = document.querySelector('select[name="brand_id"]');
    const skuSelect = document.querySelector('select[name="sku"]');
    const brands = @json($brands->keyBy('id')->map(fn($b) => $b->name));

    brandSelect.addEventListener('change', function() {
        const brandName = brands[this.value] || '';
        if (brandName) {
            skuSelect.options[0].text = '— Auto: ' + brandName.toUpperCase() + '-### —';
        } else {
            skuSelect.options[0].text = '— Seleccionar SKU —';
        }
    });
</script>
@endpush

@endsection
