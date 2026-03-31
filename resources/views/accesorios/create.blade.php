@extends('layouts.app')
@section('title','Nuevo Accesorio')
@section('page-title','Nuevo Accesorio')

@section('content')
<div class="max-w-lg">
<form method="POST" action="{{ route('accesorios.store') }}" class="space-y-4">
@csrf
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Datos del accesorio</h3></div>
    <div class="card-body space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Nombre <span class="text-red-500">*</span></label>
                <input name="nombre" value="{{ old('nombre') }}"
                       class="form-input @error('nombre') border-red-400 @enderror"
                       required placeholder="Ej. Bandeja extra 250 hojas">
                @error('nombre')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Código <span class="text-red-500">*</span></label>
                <input name="codigo" value="{{ old('codigo') }}"
                       class="form-input @error('codigo') border-red-400 @enderror"
                       required placeholder="Ej. ACC-001" style="text-transform:uppercase">
                @error('codigo')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="form-label">Precio</label>
            <input type="number" name="precio" value="{{ old('precio') }}"
                   class="form-input" min="0" step="0.01" placeholder="0.00">
        </div>
        <div>
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" rows="2" class="form-input">{{ old('descripcion') }}</textarea>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" id="es_activo" name="es_activo" value="1"
                   {{ old('es_activo', '1') ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-gray-300 text-blue-600">
            <label for="es_activo" class="form-label mb-0">Activo</label>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3 class="text-sm font-semibold text-gray-700">Stock inicial</h3></div>
    <div class="card-body grid grid-cols-2 gap-4">
        <div>
            <label class="form-label">Cantidad disponible</label>
            <input type="number" name="stock_cantidad" value="{{ old('stock_cantidad', 0) }}" class="form-input" min="0">
        </div>
        <div>
            <label class="form-label">Cantidad mínima</label>
            <input type="number" name="stock_minimo" value="{{ old('stock_minimo', 0) }}" class="form-input" min="0">
        </div>
        <div>
            <label class="form-label">Costo unitario</label>
            <input type="number" name="stock_costo" value="{{ old('stock_costo') }}" class="form-input" min="0" step="0.01">
        </div>
        <div>
            <label class="form-label">Ubicación</label>
            <input type="text" name="stock_ubicacion" value="{{ old('stock_ubicacion') }}" class="form-input" placeholder="Estante / pasillo">
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('almacen.index', ['tab' => 'accesorios']) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
