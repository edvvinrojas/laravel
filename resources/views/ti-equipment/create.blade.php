@extends('layouts.app')
@section('title','Nuevo Equipo TI')
@section('page-title','Registrar Equipo TI')

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('ti-equipment.store') }}">
@csrf
<div class="card mb-4">
    <div class="card-header font-semibold">Datos del equipo</div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="form-label">Código interno *</label>
            <select name="codigo_interno" class="form-select font-mono" required>
                <option value="">— Seleccionar SKU —</option>
                @foreach($skus as $s)
                    <option value="{{ $s->code }}" @selected(old('codigo_interno') === $s->code)>{{ $s->code }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Tipo *</label>
            <select name="tipo" class="form-select" required>
                @foreach(['PC','LAPTOP','SERVIDOR','IMPRESORA','TELEFONO','TABLET','SWITCH','ROUTER','OTRO'] as $t)
                <option value="{{ $t }}" @selected(old('tipo')===$t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Marca *</label>
            <input name="marca" type="text" value="{{ old('marca') }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Modelo *</label>
            <input name="modelo" type="text" value="{{ old('modelo') }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">No. serie</label>
            <input name="numero_serie" type="text" value="{{ old('numero_serie') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Estatus *</label>
            <select name="status" class="form-select" required>
                @foreach(['ACTIVO','BAJA','REPARACION','BODEGA'] as $s)
                <option value="{{ $s }}" @selected(old('status',$s)===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Procesador</label>
            <input name="procesador" type="text" value="{{ old('procesador') }}" class="form-input" placeholder="Intel Core i7-12700…">
        </div>
        <div>
            <label class="form-label">RAM</label>
            <input name="ram" type="text" value="{{ old('ram') }}" class="form-input" placeholder="16 GB DDR4">
        </div>
        <div>
            <label class="form-label">Almacenamiento</label>
            <input name="almacenamiento" type="text" value="{{ old('almacenamiento') }}" class="form-input" placeholder="512 GB SSD + 1 TB HDD">
        </div>
        <div>
            <label class="form-label">Sistema operativo</label>
            <input name="sistema_operativo" type="text" value="{{ old('sistema_operativo') }}" class="form-input" placeholder="Windows 11 Pro">
        </div>
        <div>
            <label class="form-label">Asignado a</label>
            <select name="assigned_user_id" class="form-select">
                <option value="">Sin asignar</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(old('assigned_user_id')==$u->id)>{{ $u->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Ubicación</label>
            <input name="ubicacion" type="text" value="{{ old('ubicacion') }}" class="form-input" placeholder="Oficina, piso, escritorio…">
        </div>
        <div>
            <label class="form-label">Fecha de compra</label>
            <input name="fecha_compra" type="date" value="{{ old('fecha_compra') }}" class="form-input">
        </div>
        <div class="col-span-2">
            <label class="form-label">Notas</label>
            <textarea name="notas" class="form-input" rows="2">{{ old('notas') }}</textarea>
        </div>
    </div>
</div>

{{-- Licencias --}}
@if($licenses->count())
<div class="card mb-4">
    <div class="card-header font-semibold">Licencias asociadas</div>
    <div class="card-body grid grid-cols-2 md:grid-cols-3 gap-2">
        @foreach($licenses as $lic)
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="licenses[]" value="{{ $lic->id }}"
                   @checked(in_array($lic->id, old('licenses',[])))>
            {{ $lic->software }}
            <span class="text-xs text-gray-400">({{ $lic->tipo }})</span>
        </label>
        @endforeach
    </div>
</div>
@endif

{{-- Periféricos --}}
<div class="card mb-4">
    <div class="card-header font-semibold flex justify-between">
        Periféricos
        <button type="button" id="addPeri" class="text-blue-600 text-sm">+ Agregar</button>
    </div>
    <div class="card-body" id="periContainer">
        <p id="noPeri" class="text-sm text-gray-400">Sin periféricos registrados. Pulsa "+ Agregar".</p>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="btn-primary">Registrar equipo</button>
    <a href="{{ route('ti-equipment.index') }}" class="btn-secondary">Cancelar</a>
</div>
</form>
</div>

@push('scripts')
<script>
let periIdx = 0;
document.getElementById('addPeri').addEventListener('click', () => {
    document.getElementById('noPeri')?.remove();
    const row = document.createElement('div');
    row.className = 'grid grid-cols-2 md:grid-cols-5 gap-2 mb-2 items-end';
    row.innerHTML = `
        <div>
            <label class="form-label text-xs">Tipo *</label>
            <select name="perifericos[${periIdx}][tipo]" class="form-select text-sm">
                ${['MONITOR','TECLADO','MOUSE','CARGADOR','DOCKING','HEADSET','CAMARA','OTRO'].map(t=>`<option>${t}</option>`).join('')}
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Marca</label>
            <input name="perifericos[${periIdx}][marca]" class="form-input text-sm" type="text">
        </div>
        <div>
            <label class="form-label text-xs">Modelo</label>
            <input name="perifericos[${periIdx}][modelo]" class="form-input text-sm" type="text">
        </div>
        <div>
            <label class="form-label text-xs">No. serie</label>
            <input name="perifericos[${periIdx}][numero_serie]" class="form-input text-sm" type="text">
        </div>
        <div class="flex items-end">
            <button type="button" class="btn-danger text-xs w-full" onclick="this.closest('div.grid').remove()">✕ Quitar</button>
        </div>
    `;
    document.getElementById('periContainer').appendChild(row);
    periIdx++;
});
</script>
@endpush
@endsection
