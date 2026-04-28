@extends('layouts.app')
@section('title','Editar Usuario')
@section('page-title','Editar Usuario')

@section('content')
<form method="POST" action="{{ route('users.update',$user) }}">
@csrf @method('PUT')

{{-- Datos básicos --}}
<div class="card max-w-2xl mb-5">
    <div class="card-header"><h3 class="font-semibold">Datos del usuario</h3></div>
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="form-label">Nombre completo *</label>
            <input name="full_name" value="{{ old('full_name',$user->full_name) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Usuario *</label>
            <input name="username" value="{{ old('username',$user->username) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Email *</label>
            <input name="email" type="email" value="{{ old('email',$user->email) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Nueva contraseña</label>
            <input name="password" type="password" class="form-input" placeholder="Dejar vacío para no cambiar">
        </div>
        <div>
            <label class="form-label">Confirmar contraseña</label>
            <input name="password_confirmation" type="password" class="form-input">
        </div>
        <div>
            <label class="form-label">Rol *</label>
            <select name="rol" id="rolSelect" class="form-select" required onchange="syncRolRestrictions()">
                <option value="usuario"       @selected(old('rol',$user->rol)==='usuario')>Usuario</option>
                <option value="gerencia"      @selected(old('rol',$user->rol)==='gerencia')>Gerencia</option>
                <option value="administrador" @selected(old('rol',$user->rol)==='administrador')>Administrador</option>
            </select>
        </div>
        <div>
            <label class="form-label">Departamento *</label>
            <select name="department" id="deptSelect" class="form-select" required onchange="applyDeptPreset()">
                @foreach(['rh'=>'RH','administracion'=>'Administración','comercial'=>'Comercial','operaciones'=>'Operaciones','ti'=>'TI'] as $v=>$l)
                <option value="{{ $v }}" @selected(old('department',$user->department)===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 pt-3">
            <input type="checkbox" name="is_active" id="isActive" value="1" @checked(old('is_active',$user->is_active)) class="form-checkbox">
            <label for="isActive" class="text-sm">Activo</label>
        </div>
    </div>
</div>

{{-- Permisos granulares --}}
@php
$currentPerms = $user->permissions ?? [];
$sections = [
    'Comercial'      => ['ventas'=>'Ventas','rentas'=>'Rentas','clientes'=>'Clientes','atencion_clientes'=>'Atención a Clientes','produccion'=>'Producción'],
    'Administración' => ['compras'=>'Compras','almacen'=>'Almacén','cobranza'=>'Cobranza','facturacion'=>'Facturación','inventario'=>'Inventario','usuarios'=>'Usuarios'],
    'Operaciones'    => ['rutas'=>'Rutas','ordenes_servicio'=>'Órdenes de Servicio','taller'=>'Taller','recursos_humanos'=>'Recursos Humanos'],
    'TI'             => ['ti'=>'TI','reportes'=>'Reportes','auditoria'=>'Auditoría','configuracion'=>'Configuración','migraciones'=>'Migraciones BD'],
];
$actions = ['view'=>'Ver','create'=>'Crear','edit'=>'Editar','delete'=>'Eliminar'];
@endphp

<div class="card mb-5">
    <div class="card-header flex items-center justify-between">
        <h3 class="font-semibold">Permisos granulares</h3>
        <div class="flex gap-2">
            <button type="button" onclick="selectAll()" class="btn-secondary btn-sm">Activar todo</button>
            <button type="button" onclick="clearAll()" class="btn-secondary btn-sm">Limpiar todo</button>
        </div>
    </div>
    <div class="card-body space-y-6">

        @foreach($sections as $sectionName => $areas)
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">{{ $sectionName }}</p>
            <div class="space-y-2">
                @foreach($areas as $areaKey => $areaLabel)
                @php
                    $areaPerms  = $currentPerms[$areaKey] ?? null;
                    $areaActive = !empty($areaPerms);
                @endphp
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    {{-- Cabecera del área --}}
                    <label class="flex items-center gap-3 px-4 py-2.5 bg-gray-50 cursor-pointer hover:bg-gray-100 select-none">
                        <input type="checkbox"
                               class="area-toggle form-checkbox"
                               data-area="{{ $areaKey }}"
                               @checked($areaActive)
                               onchange="toggleArea('{{ $areaKey }}', this.checked)">
                        <span class="text-sm font-medium text-gray-800">{{ $areaLabel }}</span>
                    </label>

                    {{-- Acciones del área --}}
                    <div id="perms-{{ $areaKey }}"
                         class="{{ $areaActive ? '' : 'hidden' }} px-4 py-3 bg-white border-t border-gray-100">
                        <div class="flex flex-wrap gap-x-6 gap-y-2">
                            @foreach($actions as $actionKey => $actionLabel)
                            <label class="flex items-center gap-2 text-sm cursor-pointer perm-delete-label
                                          {{ $actionKey === 'delete' ? 'delete-perm' : '' }}">
                                <input type="checkbox"
                                       name="permissions[{{ $areaKey }}][{{ $actionKey }}]"
                                       value="1"
                                       class="form-checkbox action-checkbox {{ $actionKey === 'delete' ? 'delete-check' : '' }}"
                                       data-area="{{ $areaKey }}"
                                       @checked(!empty($areaPerms[$actionKey])) >
                                <span class="text-gray-700">{{ $actionLabel }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

    </div>
</div>

<div class="flex gap-3 max-w-2xl">
    <button type="submit" class="btn-primary">Guardar cambios</button>
    <a href="{{ route('users.show',$user) }}" class="btn-secondary">Cancelar</a>
</div>

</form>
@endsection

@push('scripts')
<script>
// Mostrar/ocultar panel de acciones del área
function toggleArea(area, active) {
    const panel = document.getElementById('perms-' + area);
    panel.classList.toggle('hidden', !active);
    if (!active) {
        panel.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
    } else {
        // Activar TODAS las acciones al marcar el área
        panel.querySelectorAll('input[type=checkbox]').forEach(cb => {
            cb.checked = true;
        });
    }
    syncRolRestrictions();
}

// Sin restricciones por rol: todo se controla por checkboxes de permisos.
function syncRolRestrictions() {
    document.querySelectorAll('.delete-perm').forEach(label => {
        label.style.display = '';
    });
}

// Presets por departamento
const deptPresets = {
    rh:            ['usuarios','recursos_humanos'],
    administracion:['ventas','rentas','compras','almacen','cobranza','facturacion','inventario'],
    comercial:     ['ventas','rentas','clientes','atencion_clientes','produccion','cobranza','facturacion'],
    operaciones:   ['inventario','rutas','ordenes_servicio','taller'],
    ti:            ['ti','reportes','auditoria','configuracion','migraciones'],
};

function applyDeptPreset() {
    const dept = document.getElementById('deptSelect').value;
    const preset = deptPresets[dept] || [];
    // Limpiar todas las áreas primero
    document.querySelectorAll('.area-toggle').forEach(cb => {
        cb.checked = false;
        toggleArea(cb.dataset.area, false);
    });
    // Activar las del preset
    preset.forEach(area => {
        const cb = document.querySelector('.area-toggle[data-area="' + area + '"]');
        if (cb) { cb.checked = true; toggleArea(area, true); }
    });
}

function selectAll() {
    document.querySelectorAll('.area-toggle').forEach(cb => {
        cb.checked = true;
        toggleArea(cb.dataset.area, true);
        // Activar todas las acciones
        document.querySelectorAll('[data-area="' + cb.dataset.area + '"].action-checkbox').forEach(a => {
            a.checked = true;
        });
    });
}

function clearAll() {
    document.querySelectorAll('.area-toggle').forEach(cb => {
        cb.checked = false;
        toggleArea(cb.dataset.area, false);
    });
}

// Aplicar restricciones de rol al cargar
syncRolRestrictions();
</script>
@endpush
