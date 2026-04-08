@extends('layouts.app')
@section('title','Nueva Orden de Servicio')
@section('page-title','Nueva Orden de Servicio')

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('service-orders.store') }}" enctype="multipart/form-data">
@csrf

<input type="hidden" name="area_id" id="areaIdInput" value="{{ old('area_id') }}">
<input type="hidden" name="item_id" id="itemIdInput" value="{{ old('item_id') }}">

{{-- Sección 1: Datos generales --}}
<div class="card mb-4">
    <div class="card-header"><h3 class="font-semibold text-sm">Datos del servicio</h3></div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="form-label">Ingeniero de servicio *</label>
            <select name="engineer_id" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($engineers as $u)
                <option value="{{ $u->id }}" @selected(old('engineer_id', auth()->id())==$u->id)>{{ $u->full_name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Tipo de orden *</label>
            <select name="tipo_orden" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach(['PREVENTIVO','CORRECTIVO','ENTREGA','INSTALACION','CAMBIO_EQUIPO','DIGITALIZACION','INSTALACION_DRIVERS'] as $t)
                <option value="{{ $t }}" @selected(old('tipo_orden')===$t)>{{ str_replace('_',' ',$t) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Cliente *</label>
            <select name="client_id" id="clientSel" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Sucursal *</label>
            <select name="branch_id" id="branchSel" class="form-select" required>
                <option value="">Seleccione cliente primero…</option>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="form-label">Ubicación *</label>
            <select id="locationSel" class="form-select" required>
                <option value="">Seleccione sucursal primero…</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">Se muestran las impresoras activas de la sucursal por ubicación (área).</p>
        </div>

        <div class="col-span-2 grid grid-cols-3 gap-3 text-sm">
            <div class="bg-gray-50 rounded p-3">
                <p class="text-xs text-gray-500">Modelo</p>
                <p id="itemModel" class="font-medium mt-0.5">—</p>
            </div>
            <div class="bg-gray-50 rounded p-3">
                <p class="text-xs text-gray-500">Serie</p>
                <p id="itemSerie" class="font-medium mt-0.5">—</p>
            </div>
            <div class="bg-gray-50 rounded p-3">
                <p class="text-xs text-gray-500">No. equipo (SKU)</p>
                <p id="itemSku" class="font-medium mt-0.5">—</p>
            </div>
        </div>

    </div>
</div>

{{-- Sección 2: Se revisó --}}
<div class="card mb-4">
    <div class="card-header"><h3 class="font-semibold text-sm">Se revisó (checklist)</h3></div>
    <div class="card-body">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach(['UNIDAD_IMAGEN'=>'Unidad de imagen','UNIDAD_REVELADO'=>'Unidad de revelado','FUSOR'=>'Fusor','CALIBRACIONES'=>'Calibraciones','GOMAS'=>'Gomas','BANDA_TRANSFERENCIA'=>'Banda de transferencia','BANDEJAS'=>'Bandejas'] as $val => $label)
            <label class="flex items-center gap-2 text-sm p-2 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" name="se_reviso[]" value="{{ $val }}" class="form-checkbox"
                    @checked(in_array($val, old('se_reviso', [])))>
                {{ $label }}
            </label>
            @endforeach
        </div>
    </div>
</div>

{{-- Sección 3: Diagnóstico y tóner --}}
<div class="card mb-4">
    <div class="card-header"><h3 class="font-semibold text-sm">Diagnóstico y tóner</h3></div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div class="col-span-2">
            <label class="form-label">Diagnóstico / Acción correctiva</label>
            <textarea name="diagnostico_accion" class="form-input" rows="4">{{ old('diagnostico_accion') }}</textarea>
        </div>

        <div>
            <label class="form-label">Se entregó tóner *</label>
            <select name="entrego_toner" id="entregaToner" class="form-select" required>
                <option value="0" @selected(old('entrego_toner', '0')==='0')>No</option>
                <option value="1" @selected(old('entrego_toner')==='1')>Sí</option>
            </select>
        </div>

        <div id="tonerCodes" class="{{ old('entrego_toner')==='1' ? '' : 'hidden' }}">
            <label class="form-label">Código(s) de tóner entregado</label>
            <input name="codigos_toner" value="{{ old('codigos_toner') }}" class="form-input" placeholder="TON-001, TON-002…">
        </div>

        <div class="col-span-2">
            <label class="form-label">Porcentajes de tóner</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach(['negro'=>'Negro','cyan'=>'Cyan','magenta'=>'Magenta','amarillo'=>'Amarillo'] as $key => $label)
                <div>
                    <label class="text-xs text-gray-500">{{ $label }}</label>
                    <div class="flex items-center gap-1">
                        <input name="pct_toner_{{ $key }}" type="number" min="0" max="100"
                            value="{{ old('pct_toner_'.$key) }}" class="form-input" placeholder="0-100">
                        <span class="text-sm text-gray-500">%</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="col-span-2">
            <label class="form-label">Tóner / refacción pendiente</label>
            <textarea name="pendiente_material" class="form-input" rows="2" placeholder="Escribir libremente lo que queda pendiente de surtir…">{{ old('pendiente_material') }}</textarea>
        </div>

    </div>
</div>

{{-- Sección 4: Evidencias --}}
<div class="card mb-4">
    <div class="card-header"><h3 class="font-semibold text-sm">Evidencias fotográficas</h3></div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="form-label">Foto de evidencia del servicio</label>
            <input type="file" name="evidencia_foto" accept="image/*" class="form-input">
        </div>

        <div>
            <label class="form-label">Foto página de estado</label>
            <input type="file" name="pagina_estado_foto" accept="image/*" class="form-input">
        </div>

        <div>
            <label class="form-label">Tiene stock *</label>
            <select name="tiene_stock" id="tieneStock" class="form-select" required>
                <option value="0" @selected(old('tiene_stock', '0')==='0')>No</option>
                <option value="1" @selected(old('tiene_stock')==='1')>Sí</option>
            </select>
        </div>

        <div id="stockPhoto" class="{{ old('tiene_stock')==='1' ? '' : 'hidden' }}">
            <label class="form-label">Foto del stock</label>
            <input type="file" name="foto_stock" accept="image/*" class="form-input">
        </div>

    </div>
</div>

{{-- Sección 5: Firma y pendientes --}}
<div class="card mb-4">
    <div class="card-header"><h3 class="font-semibold text-sm">Firma y pendientes</h3></div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
            <label class="form-label">Nombre de quien firma el servicio *</label>
            <input name="firma_nombre" value="{{ old('firma_nombre') }}" class="form-input" placeholder="Nombre completo" required>
        </div>

        <div>
            <label class="form-label">¿Queda algo pendiente? *</label>
            <select name="queda_pendiente" id="quedaPendiente" class="form-select" required>
                <option value="0" @selected(old('queda_pendiente', '0')==='0')>No</option>
                <option value="1" @selected(old('queda_pendiente')==='1')>Sí</option>
            </select>
        </div>

        <div class="col-span-2" id="pendienteDesc" style="{{ old('queda_pendiente')==='1' ? '' : 'display:none' }}">
            <label class="form-label">Descripción de lo pendiente</label>
            <textarea name="descripcion_pendiente" class="form-input" rows="2">{{ old('descripcion_pendiente') }}</textarea>
        </div>

        <div class="col-span-2">
            <label class="form-label">Firma digital</label>
            <div class="border border-gray-300 rounded-lg overflow-hidden bg-white">
                <canvas id="signaturePad" width="600" height="150" class="w-full cursor-crosshair touch-none" style="touch-action:none"></canvas>
            </div>
            <input type="hidden" name="firma_imagen" id="firmaImagen">
            <button type="button" onclick="clearSignature()" class="btn-secondary btn-sm mt-2">Limpiar firma</button>
        </div>

    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="btn-primary">Guardar orden</button>
    <a href="{{ route('service-orders.index') }}" class="btn-secondary">Cancelar</a>
</div>

</form>
</div>

@push('scripts')
<script>
const clientSel = document.getElementById('clientSel');
const branchSel = document.getElementById('branchSel');
const locationSel = document.getElementById('locationSel');
const areaInput = document.getElementById('areaIdInput');
const itemInput = document.getElementById('itemIdInput');

const oldBranch = "{{ old('branch_id') }}";
const oldArea = "{{ old('area_id') }}";
const oldItem = "{{ old('item_id') }}";
const oldLocation = oldArea && oldItem ? `${oldArea}:${oldItem}` : '';
let canUseOldBranch = true;

clientSel.addEventListener('change', async function() {
    await loadBranches(this.value);
});

branchSel.addEventListener('change', async function() {
    await loadLocations(this.value);
});

locationSel.addEventListener('change', function() {
    syncSelectedLocation();
});

async function loadBranches(clientId) {
    branchSel.innerHTML = '<option value="">Cargando…</option>';
    resetLocationSelect('Seleccione sucursal primero…');
    clearItemInfo();

    if (!clientId) {
        branchSel.innerHTML = '<option value="">Seleccione cliente primero…</option>';
        return;
    }

    const response = await fetch(`/api/clients/${clientId}/branches`);
    const branches = await response.json();

    branchSel.innerHTML = '<option value="">Seleccionar sucursal…</option>';
    branches.forEach((branch) => {
        const opt = document.createElement('option');
        opt.value = branch.id;
        opt.textContent = branch.name;
        branchSel.appendChild(opt);
    });

    if (canUseOldBranch && oldBranch) {
        branchSel.value = oldBranch;
        await loadLocations(oldBranch, true);
        canUseOldBranch = false;
    }
}

async function loadLocations(branchId, useOld = false) {
    resetLocationSelect('Cargando…');
    clearItemInfo();

    if (!branchId) {
        resetLocationSelect('Seleccione sucursal primero…');
        return;
    }

    const response = await fetch(`/api/branches/${branchId}/service-locations`);
    const locations = await response.json();

    resetLocationSelect('Seleccionar ubicación…');
    locations.forEach((row) => {
        const opt = document.createElement('option');
        opt.value = `${row.area_id}:${row.item_id}`;
        opt.textContent = `${row.area_name} - ${row.brand} ${row.model} (${row.serie})`;
        opt.dataset.areaId = row.area_id;
        opt.dataset.itemId = row.item_id;
        opt.dataset.model = row.model || '';
        opt.dataset.serie = row.serie || '';
        opt.dataset.sku = row.sku || '';
        locationSel.appendChild(opt);
    });

    if (useOld && oldLocation) {
        locationSel.value = oldLocation;
    }

    syncSelectedLocation();
}

function syncSelectedLocation() {
    const selected = locationSel.options[locationSel.selectedIndex];

    if (!selected || !selected.dataset.areaId) {
        areaInput.value = '';
        itemInput.value = '';
        clearItemInfo();
        return;
    }

    areaInput.value = selected.dataset.areaId;
    itemInput.value = selected.dataset.itemId;
    document.getElementById('itemModel').textContent = selected.dataset.model || '—';
    document.getElementById('itemSerie').textContent = selected.dataset.serie || '—';
    document.getElementById('itemSku').textContent = selected.dataset.sku || '—';
}

function resetLocationSelect(message) {
    locationSel.innerHTML = `<option value="">${message}</option>`;
}

function clearItemInfo() {
    document.getElementById('itemModel').textContent = '—';
    document.getElementById('itemSerie').textContent = '—';
    document.getElementById('itemSku').textContent = '—';
}

// Toggles
document.getElementById('entregaToner').addEventListener('change', function() {
    document.getElementById('tonerCodes').classList.toggle('hidden', this.value !== '1');
});
document.getElementById('tieneStock').addEventListener('change', function() {
    document.getElementById('stockPhoto').classList.toggle('hidden', this.value !== '1');
});
document.getElementById('quedaPendiente').addEventListener('change', function() {
    document.getElementById('pendienteDesc').style.display = this.value === '1' ? '' : 'none';
});

// Firma digital (canvas)
const canvas = document.getElementById('signaturePad');
const ctx    = canvas.getContext('2d');
let drawing  = false;

function getPos(e) {
    const r = canvas.getBoundingClientRect();
    const t = e.touches ? e.touches[0] : e;
    return { x: (t.clientX - r.left) * (canvas.width / r.width), y: (t.clientY - r.top) * (canvas.height / r.height) };
}

canvas.addEventListener('mousedown',  e => { drawing = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
canvas.addEventListener('mousemove',  e => { if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.strokeStyle = '#1e293b'; ctx.lineWidth = 2; ctx.stroke(); });
canvas.addEventListener('mouseup',    () => { drawing = false; saveSignature(); });
canvas.addEventListener('mouseleave', () => { drawing = false; });
canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); }, {passive:false});
canvas.addEventListener('touchmove',  e => { e.preventDefault(); if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.strokeStyle = '#1e293b'; ctx.lineWidth = 2; ctx.stroke(); }, {passive:false});
canvas.addEventListener('touchend',   () => { drawing = false; saveSignature(); });

function saveSignature() {
    document.getElementById('firmaImagen').value = canvas.toDataURL('image/png');
}

function clearSignature() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    document.getElementById('firmaImagen').value = '';
}

if (clientSel.value) {
    loadBranches(clientSel.value);
}
</script>
@endpush
@endsection
