@extends('layouts.app')
@section('title','Ingreso a Taller')
@section('page-title','Ingreso a Taller')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('repairs.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="form-label">Equipo *</label>
            <select name="item_id" id="itemSelect" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($items as $i)
                <option
                    value="{{ $i->id }}"
                    data-client="{{ $i->assigned_client_info['client'] ?? '' }}"
                    data-source="{{ $i->assigned_client_info['source'] ?? '' }}"
                    data-branch="{{ $i->assigned_client_info['branch'] ?? '' }}"
                    data-area="{{ $i->assigned_client_info['area'] ?? '' }}"
                    data-reference="{{ $i->assigned_client_info['reference'] ?? '' }}"
                    @selected(old('item_id')==$i->id)
                >{{ $i->brand->name ?? '' }} {{ $i->model }} — {{ $i->serie }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Procedencia *</label>
            <input name="procedencia" id="procedenciaInput" value="{{ old('procedencia') }}" class="form-input" required
                placeholder="Ej: Cliente ABC, Bodega central, Comprado por empleado…">
            <p class="text-xs text-gray-400 mt-1">Indica de dónde viene el equipo</p>
        </div>
        <div>
            <label class="form-label">Estado taller *</label>
            <select name="estado_taller" class="form-select" required>
                @foreach(['PENDIENTE','PAUSADO','LISTO'] as $s)
                <option value="{{ $s }}" @selected(old('estado_taller','PENDIENTE')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Estatus *</label>
            <select name="estatus" class="form-select" required>
                @foreach(['EN_ESPERA_AUTORIZACION','EN_ESPERA_PIEZA','PAUSADO','LISTO'] as $s)
                <option value="{{ $s }}" @selected(old('estatus','EN_ESPERA_AUTORIZACION')===$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Proceso *</label>
            <select name="proceso" class="form-select" required>
                @foreach(['DESCONOCIDO','PROCESO_1','PROCESO_2','PROCESO_3'] as $p)
                <option value="{{ $p }}" @selected(old('proceso','DESCONOCIDO')===$p)>{{ str_replace('_',' ',$p) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Ubicación</label>
            <select name="ubicacion" class="form-select">
                <option value="">Sin asignar</option>
                @foreach(['ZONA_1','ZONA_2','ZONA_3','ZONA_4','BASURA'] as $u)
                <option value="{{ $u }}" @selected(old('ubicacion')===$u)>{{ str_replace('_',' ',$u) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-2">
            <label class="form-label">Diagnóstico inicial</label>
            <textarea name="diagnostico_inicial" class="form-input" rows="3">{{ old('diagnostico_inicial') }}</textarea>
        </div>
        <div class="col-span-2">
            <label class="form-label">Comentarios</label>
            <textarea name="comments" class="form-input" rows="2">{{ old('comments') }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('repairs.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const itemSelect = document.getElementById('itemSelect');
    const procedenciaInput = document.getElementById('procedenciaInput');
    let lastAutoValue = '';

    function updateAssignedClientInfo() {
        const option = itemSelect.options[itemSelect.selectedIndex];
        const client = option?.dataset.client?.trim();
        const currentValue = procedenciaInput.value.trim();

        if (!client) {
            if (currentValue === lastAutoValue) {
                procedenciaInput.value = '';
            }
            lastAutoValue = '';
            return;
        }

        const parts = [];
        parts.push(client);
        if (option.dataset.source && option.dataset.reference) {
            parts.push(option.dataset.source + ' ' + option.dataset.reference);
        } else if (option.dataset.source) {
            parts.push(option.dataset.source);
        } else if (option.dataset.reference) {
            parts.push(option.dataset.reference);
        }
        if (option.dataset.branch) {
            parts.push(option.dataset.branch);
        }
        if (option.dataset.area) {
            parts.push(option.dataset.area);
        }

        const autoValue = parts.join(' - ');
        if (currentValue === '' || currentValue === lastAutoValue) {
            procedenciaInput.value = autoValue;
        }
        lastAutoValue = autoValue;
    }

    itemSelect.addEventListener('change', updateAssignedClientInfo);
    updateAssignedClientInfo();
});
</script>
@endsection
