{{-- resources/views/equipment/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Editar equipo')
@section('page-title', 'Editar equipo')

@section('content')
<div class="max-w-3xl">
<form action="{{ route('equipment.update', $equipment) }}" method="POST" class="space-y-4">
@csrf @method('PUT')

{{-- Identificación --}}
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Identificación</h3></div>
    <div class="card-body space-y-4">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="form-label">SKU</label>
                <input name="sku" value="{{ old('sku', $equipment->sku) }}"
                       class="form-input @error('sku') border-red-400 @enderror">
                @error('sku')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Serie <span class="text-red-500">*</span></label>
                <input name="serie" value="{{ old('serie', $equipment->serie) }}"
                       class="form-input @error('serie') border-red-400 @enderror"
                       required>
                @error('serie')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Dirección IP</label>
                <input name="direccion_ip" value="{{ old('direccion_ip', $equipment->direccion_ip) }}"
                       class="form-input @error('direccion_ip') border-red-400 @enderror"
                       placeholder="192.168.1.100">
                @error('direccion_ip')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">MAC Address</label>
                <input name="mac_address" value="{{ old('mac_address', $equipment->mac_address) }}"
                       class="form-input @error('mac_address') border-red-400 @enderror"
                       placeholder="AA:BB:CC:DD:EE:FF">
                @error('mac_address')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Ubicación física</label>
                <input name="ubicacion_fisica" value="{{ old('ubicacion_fisica', $equipment->ubicacion_fisica) }}"
                       class="form-input @error('ubicacion_fisica') border-red-400 @enderror"
                       placeholder="Ej. Piso 2 – Oficina 204">
                @error('ubicacion_fisica')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Modelo del catálogo --}}
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Modelo de catálogo</h3></div>
    <div class="card-body space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Categoría</label>
                <select name="categoria_id" id="categoria_id"
                        class="form-select @error('categoria_id') border-red-400 @enderror">
                    <option value="">— Sin categoría —</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}"
                                {{ old('categoria_id', $equipment->categoria_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('categoria_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Modelo</label>
                <select name="modelo_id" id="modelo_id"
                        class="form-select @error('modelo_id') border-red-400 @enderror">
                    <option value="">— Sin modelo —</option>
                    @foreach($modelos as $mod)
                        <option value="{{ $mod->id }}"
                                {{ old('modelo_id', $equipment->modelo_id) == $mod->id ? 'selected' : '' }}
                                data-categoria="{{ $mod->categoria_id }}">
                            {{ $mod->marca->name ?? '' }} {{ $mod->nombre_modelo }}
                        </option>
                    @endforeach
                </select>
                @error('modelo_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Marca <span class="text-red-500">*</span></label>
                <select name="brand_id" class="form-select @error('brand_id') border-red-400 @enderror" required>
                    <option value="">— Seleccionar marca —</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}"
                                {{ old('brand_id', $equipment->brand_id) == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
                @error('brand_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Modelo (texto libre) <span class="text-red-500">*</span></label>
                <input name="model" value="{{ old('model', $equipment->model) }}"
                       class="form-input @error('model') border-red-400 @enderror" required>
                @error('model')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="form-label">Tipo color <span class="text-red-500">*</span></label>
                <select name="type" class="form-select @error('type') border-red-400 @enderror" required>
                    <option value="">— Seleccionar —</option>
                    <option value="MONOCROMO" {{ old('type', $equipment->type) === 'MONOCROMO' ? 'selected' : '' }}>Monocromo</option>
                    <option value="COLOR"     {{ old('type', $equipment->type) === 'COLOR'     ? 'selected' : '' }}>Color</option>
                </select>
                @error('type')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Tipo de equipo</label>
                <select name="tipo_equipo" class="form-select @error('tipo_equipo') border-red-400 @enderror">
                    <option value="">— Seleccionar —</option>
                    @foreach(['COPIADORA','IMPRESORA','MFP','ESCANER','FAX','PLOTTER'] as $te)
                        <option value="{{ $te }}" {{ old('tipo_equipo', $equipment->tipo_equipo) === $te ? 'selected' : '' }}>{{ $te }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Formato máximo</label>
                <select name="formato_max" class="form-select @error('formato_max') border-red-400 @enderror">
                    <option value="">— Seleccionar —</option>
                    @foreach(['A4','A3','CARTA','OFICIO','A2','A1','A0'] as $f)
                        <option value="{{ $f }}" {{ old('formato_max', $equipment->formato_max) === $f ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="form-label">Modelo de tóner <span class="text-red-500">*</span></label>
            <input name="model_toner" value="{{ old('model_toner', $equipment->model_toner) }}"
                   class="form-input @error('model_toner') border-red-400 @enderror" required>
            @error('model_toner')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- Fechas y contadores --}}
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Fechas y contadores iniciales</h3></div>
    <div class="card-body space-y-4">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="form-label">Fecha de compra</label>
                <input type="date" name="fecha_compra"
                       value="{{ old('fecha_compra', $equipment->fecha_compra?->format('Y-m-d')) }}"
                       class="form-input @error('fecha_compra') border-red-400 @enderror">
                @error('fecha_compra')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Fecha de instalación</label>
                <input type="date" name="fecha_instalacion"
                       value="{{ old('fecha_instalacion', $equipment->fecha_instalacion?->format('Y-m-d')) }}"
                       class="form-input @error('fecha_instalacion') border-red-400 @enderror">
                @error('fecha_instalacion')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Fin de garantía</label>
                <input type="date" name="fecha_garantia_fin"
                       value="{{ old('fecha_garantia_fin', $equipment->fecha_garantia_fin?->format('Y-m-d')) }}"
                       class="form-input @error('fecha_garantia_fin') border-red-400 @enderror">
                @error('fecha_garantia_fin')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="form-label">Contador inicial B/N</label>
                <input type="number" name="contador_inicial_bn"
                       value="{{ old('contador_inicial_bn', $equipment->contador_inicial_bn ?? 0) }}"
                       class="form-input" min="0">
            </div>
            <div>
                <label class="form-label">Contador inicial Color</label>
                <input type="number" name="contador_inicial_color"
                       value="{{ old('contador_inicial_color', $equipment->contador_inicial_color ?? 0) }}"
                       class="form-input" min="0">
            </div>
            <div>
                <label class="form-label">Contador inicial Scan</label>
                <input type="number" name="contador_inicial_scan"
                       value="{{ old('contador_inicial_scan', $equipment->contador_inicial_scan ?? 0) }}"
                       class="form-input" min="0">
            </div>
        </div>
    </div>
</div>

{{-- Adquisición y estado --}}
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Adquisición y estado</h3></div>
    <div class="card-body space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Proveedor</label>
                <select name="supplier_id" class="form-select @error('supplier_id') border-red-400 @enderror">
                    <option value="">— Sin proveedor —</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}"
                                {{ old('supplier_id', $equipment->supplier_id) == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Estado / Ubicación</label>
                <select name="location_status" class="form-select @error('location_status') border-red-400 @enderror">
                    @foreach(['BODEGA' => 'Bodega', 'ASIGNADO' => 'Asignado', 'VENDIDO' => 'Vendido', 'TALLER' => 'Taller', 'DESCONOCIDO' => 'Desconocido'] as $val => $lbl)
                        <option value="{{ $val }}"
                                {{ old('location_status', $equipment->location_status) === $val ? 'selected' : '' }}>
                            {{ $lbl }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Factura</label>
                <input name="invoice" value="{{ old('invoice', $equipment->invoice) }}"
                       class="form-input @error('invoice') border-red-400 @enderror">
            </div>
            <div>
                <label class="form-label">Costo</label>
                <input type="number" name="cost" value="{{ old('cost', $equipment->cost) }}"
                       class="form-input @error('cost') border-red-400 @enderror"
                       min="0" step="0.01">
            </div>
        </div>
        <div>
            <label class="form-label">Comentarios</label>
            <textarea name="comments" rows="3"
                      class="form-input @error('comments') border-red-400 @enderror">{{ old('comments', $equipment->comments) }}</textarea>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $equipment->is_active) ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-gray-300 text-blue-600">
            <label for="is_active" class="form-label mb-0">Equipo activo</label>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar cambios</button>
        <a href="{{ route('equipment.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>

</form>
</div>

<script>
const catSelect = document.getElementById('categoria_id');
const modSelect = document.getElementById('modelo_id');
const allOpts   = Array.from(modSelect.options).map(o => ({el: o, cat: o.dataset.categoria}));

catSelect.addEventListener('change', () => {
    const catId = catSelect.value;
    allOpts.forEach(({el, cat}) => {
        el.hidden = catId && cat !== catId;
    });
    if (catId) modSelect.value = '';
});
</script>
@endsection
