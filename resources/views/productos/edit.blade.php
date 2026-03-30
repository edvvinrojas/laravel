@extends('layouts.app')
@section('title','Editar Producto')
@section('page-title','Editar Producto')

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('productos.update', $producto) }}" class="space-y-4">
@csrf @method('PUT')

{{-- Datos básicos --}}
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Datos del producto</h3></div>
    <div class="card-body space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Nombre <span class="text-red-500">*</span></label>
                <input name="nombre" value="{{ old('nombre', $producto->nombre) }}"
                       class="form-input @error('nombre') border-red-400 @enderror" required>
                @error('nombre')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Código <span class="text-red-500">*</span></label>
                <input name="codigo" value="{{ old('codigo', $producto->codigo) }}"
                       class="form-input @error('codigo') border-red-400 @enderror"
                       required style="text-transform:uppercase">
                @error('codigo')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="form-label">Marca</label>
                <select name="brand_id" class="form-select">
                    <option value="">— Sin marca —</option>
                    @foreach($marcas as $m)
                        <option value="{{ $m->id }}" {{ old('brand_id', $producto->brand_id) == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Categoría <span class="text-red-500">*</span></label>
                <select name="categoria" class="form-select @error('categoria') border-red-400 @enderror" required>
                    @foreach(['COPIADORA','IMPRESORA','MFP','ESCANER','FAX','PLOTTER','OTRO'] as $cat)
                        <option value="{{ $cat }}" {{ old('categoria', $producto->categoria) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('categoria')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Tipo de color</label>
                <select name="tipo_color" class="form-select">
                    <option value="">— Seleccionar —</option>
                    @foreach(['MONOCROMO' => 'Monocromo', 'COLOR' => 'Color', 'AMBOS' => 'Ambos'] as $v => $l)
                        <option value="{{ $v }}" {{ old('tipo_color', $producto->tipo_color) === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="form-label">Formato máximo</label>
                <select name="formato_max" class="form-select">
                    <option value="">— Seleccionar —</option>
                    @foreach(['A4','A3','CARTA','OFICIO','A2','A1','A0'] as $f)
                        <option value="{{ $f }}" {{ old('formato_max', $producto->formato_max) === $f ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Precio venta</label>
                <input type="number" name="precio_venta" value="{{ old('precio_venta', $producto->precio_venta) }}"
                       class="form-input" min="0" step="0.01">
            </div>
            <div>
                <label class="form-label">Precio renta/mes</label>
                <input type="number" name="precio_renta" value="{{ old('precio_renta', $producto->precio_renta) }}"
                       class="form-input" min="0" step="0.01">
            </div>
        </div>
        <div>
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" rows="2" class="form-input">{{ old('descripcion', $producto->descripcion) }}</textarea>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" id="es_activo" name="es_activo" value="1"
                   {{ old('es_activo', $producto->es_activo) ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-gray-300 text-blue-600">
            <label for="es_activo" class="form-label mb-0">Producto activo</label>
        </div>
    </div>
</div>

{{-- Stock --}}
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Stock</h3></div>
    <div class="card-body grid grid-cols-2 md:grid-cols-4 gap-4">
        @php $st = $producto->stock; @endphp
        <div>
            <label class="form-label">Cantidad disponible</label>
            <input type="number" name="stock_cantidad" value="{{ old('stock_cantidad', $st?->cantidad_disponible ?? 0) }}"
                   class="form-input" min="0">
        </div>
        <div>
            <label class="form-label">Cantidad mínima</label>
            <input type="number" name="stock_minimo" value="{{ old('stock_minimo', $st?->cantidad_minima ?? 0) }}"
                   class="form-input" min="0">
        </div>
        <div>
            <label class="form-label">Costo unitario</label>
            <input type="number" name="stock_costo" value="{{ old('stock_costo', $st?->costo) }}"
                   class="form-input" min="0" step="0.01">
        </div>
        <div>
            <label class="form-label">Ubicación</label>
            <input type="text" name="stock_ubicacion" value="{{ old('stock_ubicacion', $st?->ubicacion) }}"
                   class="form-input">
        </div>
    </div>
</div>

{{-- Accesorios --}}
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Accesorios compatibles</h3></div>
    <div class="card-body">
        @if($accesorios->isEmpty())
            <p class="text-sm text-gray-400">Sin accesorios.</p>
        @else
        <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
            @foreach($accesorios as $ac)
            @php
                $pivotAc  = $accsSeleccionados->get($ac->id);
                $checked  = old('accesorios_ids') !== null ? in_array($ac->id, old('accesorios_ids',[])) : $pivotAc !== null;
                $incluido = old('accesorios_ids') !== null ? isset(old('accesorio_incluido')[$ac->id]) : ($pivotAc?->pivot->es_incluido ?? false);
            @endphp
            <div class="flex items-center gap-3 p-2 rounded hover:bg-gray-50">
                <input type="checkbox" name="accesorios_ids[]" value="{{ $ac->id }}" id="ac_{{ $ac->id }}"
                       {{ $checked ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-blue-600">
                <label for="ac_{{ $ac->id }}" class="flex-1 text-sm text-gray-800 cursor-pointer">
                    <span class="font-medium">{{ $ac->nombre }}</span>
                    <span class="text-gray-400 ml-1">{{ $ac->codigo }}</span>
                </label>
                <label class="flex items-center gap-1 text-xs text-gray-500">
                    <input type="checkbox" name="accesorio_incluido[{{ $ac->id }}]" value="1"
                           {{ $incluido ? 'checked' : '' }} class="w-3 h-3 rounded border-gray-300 text-green-600">
                    Incluido de fábrica
                </label>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Consumibles --}}
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Consumibles compatibles</h3></div>
    <div class="card-body">
        @if($consumibles->isEmpty())
            <p class="text-sm text-gray-400">Sin consumibles.</p>
        @else
        <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
            @foreach($consumibles as $co)
            @php
                $pivotCo = $consSeleccionados->get($co->id);
                $checked  = old('consumibles_ids') !== null ? in_array($co->id, old('consumibles_ids',[])) : $pivotCo !== null;
                $oficial  = old('consumibles_ids') !== null ? isset(old('consumible_oficial')[$co->id]) : ($pivotCo?->pivot->es_oficial ?? true);
            @endphp
            <div class="flex items-center gap-3 p-2 rounded hover:bg-gray-50">
                <input type="checkbox" name="consumibles_ids[]" value="{{ $co->id }}" id="co_{{ $co->id }}"
                       {{ $checked ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-blue-600">
                <label for="co_{{ $co->id }}" class="flex-1 text-sm text-gray-800 cursor-pointer">
                    <span class="font-medium">{{ $co->nombre }}</span>
                    <span class="badge-gray text-xs ml-1">{{ $co->codigo_oem }}</span>
                    @if($co->color) <span class="text-gray-400">· {{ $co->color }}</span> @endif
                </label>
                <label class="flex items-center gap-1 text-xs text-gray-500">
                    <input type="checkbox" name="consumible_oficial[{{ $co->id }}]" value="1"
                           {{ $oficial ? 'checked' : '' }} class="w-3 h-3 rounded border-gray-300 text-green-600">
                    Oficial
                </label>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('almacen.index', ['tab' => 'productos']) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>

</form>
</div>
@endsection
