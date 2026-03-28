{{-- resources/views/equipment/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar equipo')
@section('page-title', 'Editar equipo')
@section('breadcrumb', 'Equipos / ' . $equipment->model)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card">
        <div class="card-header">
            <h2 class="text-base font-semibold text-gray-800">Editar: {{ $equipment->brand?->name }} {{ $equipment->model }}</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('equipment.update', $equipment) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- SKU --}}
                <div>
                    <label for="sku" class="form-label">SKU</label>
                    <input
                        type="text"
                        id="sku"
                        name="sku"
                        value="{{ old('sku', $equipment->sku) }}"
                        class="form-input @error('sku') border-red-400 @enderror"
                    >
                    @error('sku')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Marca --}}
                <div>
                    <label for="brand_id" class="form-label">Marca <span class="text-red-500">*</span></label>
                    <select id="brand_id" name="brand_id"
                            class="form-select @error('brand_id') border-red-400 @enderror" required>
                        <option value="">— Seleccionar marca —</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}"
                                {{ old('brand_id', $equipment->brand_id) == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('brand_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Modelo --}}
                <div>
                    <label for="model" class="form-label">Modelo <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="model"
                        name="model"
                        value="{{ old('model', $equipment->model) }}"
                        class="form-input @error('model') border-red-400 @enderror"
                        required
                    >
                    @error('model')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Serie --}}
                <div>
                    <label for="serie" class="form-label">Serie <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="serie"
                        name="serie"
                        value="{{ old('serie', $equipment->serie) }}"
                        class="form-input @error('serie') border-red-400 @enderror"
                        required
                    >
                    @error('serie')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Modelo tóner --}}
                <div>
                    <label for="model_toner" class="form-label">Modelo tóner <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="model_toner"
                        name="model_toner"
                        value="{{ old('model_toner', $equipment->model_toner) }}"
                        class="form-input @error('model_toner') border-red-400 @enderror"
                        required
                    >
                    @error('model_toner')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipo --}}
                <div>
                    <label for="type" class="form-label">Tipo <span class="text-red-500">*</span></label>
                    <select id="type" name="type"
                            class="form-select @error('type') border-red-400 @enderror" required>
                        <option value="">— Seleccionar tipo —</option>
                        <option value="MONOCROMO" {{ old('type', $equipment->type) === 'MONOCROMO' ? 'selected' : '' }}>MONOCROMO</option>
                        <option value="COLOR"     {{ old('type', $equipment->type) === 'COLOR'     ? 'selected' : '' }}>COLOR</option>
                    </select>
                    @error('type')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Proveedor --}}
                <div>
                    <label for="supplier_id" class="form-label">Proveedor</label>
                    <select id="supplier_id" name="supplier_id"
                            class="form-select @error('supplier_id') border-red-400 @enderror">
                        <option value="">— Sin proveedor —</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}"
                                {{ old('supplier_id', $equipment->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Factura / Costo --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="invoice" class="form-label">Factura</label>
                        <input
                            type="text"
                            id="invoice"
                            name="invoice"
                            value="{{ old('invoice', $equipment->invoice) }}"
                            class="form-input @error('invoice') border-red-400 @enderror"
                        >
                        @error('invoice')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="cost" class="form-label">Costo</label>
                        <input
                            type="number"
                            id="cost"
                            name="cost"
                            value="{{ old('cost', $equipment->cost) }}"
                            class="form-input @error('cost') border-red-400 @enderror"
                            min="0"
                            step="0.01"
                        >
                        @error('cost')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Estado / Ubicación --}}
                <div>
                    <label for="location_status" class="form-label">Estado / Ubicación</label>
                    <select id="location_status" name="location_status"
                            class="form-select @error('location_status') border-red-400 @enderror">
                        <option value="">— Sin estado —</option>
                        @foreach(['Disponible', 'Rentado', 'Vendido', 'Taller', 'Almacén', 'Baja'] as $opt)
                            <option value="{{ $opt }}"
                                {{ old('location_status', $equipment->location_status) === $opt ? 'selected' : '' }}>
                                {{ $opt }}
                            </option>
                        @endforeach
                    </select>
                    @error('location_status')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Comentarios --}}
                <div>
                    <label for="comments" class="form-label">Comentarios</label>
                    <textarea
                        id="comments"
                        name="comments"
                        rows="3"
                        class="form-input @error('comments') border-red-400 @enderror"
                    >{{ old('comments', $equipment->comments) }}</textarea>
                    @error('comments')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Activo --}}
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="is_active"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $equipment->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <label for="is_active" class="form-label mb-0">Equipo activo</label>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                    <a href="{{ route('equipment.index') }}" class="btn-secondary">Cancelar</a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
