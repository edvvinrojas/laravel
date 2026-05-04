@extends('layouts.app')
@section('title','Nuevo Usuario')
@section('page-title','Nuevo Usuario')

@section('content')
<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="xl:col-span-2">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="card">
                <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="form-label">Empleado asignado</label>
                        <input type="hidden" name="employee_id" id="employee_id" value="{{ old('employee_id') }}">
                        <input id="selected_employee_name" class="form-input bg-gray-50" placeholder="Selecciona un empleado desde la lista de la derecha" value="" readonly>
                        @error('employee_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="col-span-2"><label class="form-label">Nombre completo *</label><input id="full_name" name="full_name" value="{{ old('full_name') }}" class="form-input" required>@error('full_name')<p class="form-error">{{ $message }}</p>@enderror</div>
                    <div><label class="form-label">Usuario *</label><input id="username" name="username" value="{{ old('username') }}" class="form-input" required>@error('username')<p class="form-error">{{ $message }}</p>@enderror</div>
                    <div><label class="form-label">Email *</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="form-input" required>@error('email')<p class="form-error">{{ $message }}</p>@enderror</div>
                    <div><label class="form-label">Contraseña *</label><input name="password" type="password" class="form-input" required>@error('password')<p class="form-error">{{ $message }}</p>@enderror</div>
                    <div><label class="form-label">Confirmar contraseña *</label><input name="password_confirmation" type="password" class="form-input" required></div>
                    <div><label class="form-label">Rol *</label><select name="rol" class="form-select" required><option value="usuario" @selected(old('rol')==='usuario')>Usuario</option><option value="gerencia" @selected(old('rol')==='gerencia')>Gerencia</option><option value="administrador" @selected(old('rol')==='administrador')>Administrador</option></select></div>
                    <div><label class="form-label">Departamento *</label><select id="department" name="department" class="form-select" required>@foreach(['rh'=>'RH','administracion'=>'Administración','comercial'=>'Comercial','operaciones'=>'Operaciones','ti'=>'TI'] as $v=>$l)<option value="{{ $v }}" @selected(old('department')===$v)>{{ $l }}</option>@endforeach</select></div>
                    <div class="flex items-center gap-2 pt-3"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',true))><label class="text-sm">Activo</label></div>
                </div>
                <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
                    <button type="submit" class="btn-primary">Crear usuario</button>
                    <a href="{{ route('users.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>

    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-sm text-gray-700">Empleados sin cuenta</h3>
                <span class="badge badge-blue">{{ $employeesWithoutAccount->count() }}</span>
            </div>
            <div class="card-body space-y-3">
                <input id="employeeSearch" type="text" class="form-input" placeholder="Buscar empleado...">
                <div id="employeeList" class="max-h-[560px] overflow-y-auto space-y-2 pr-1">
                    @forelse($employeesWithoutAccount as $employee)
                        <button
                            type="button"
                            class="employee-pick w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-left hover:border-blue-300 hover:bg-blue-50"
                            data-id="{{ $employee->id }}"
                            data-name="{{ $employee->nombre }}"
                            data-department="{{ strtolower($employee->departamento ?? '') }}"
                        >
                            <p class="text-sm font-semibold text-gray-800">{{ $employee->nombre }}</p>
                            <p class="text-xs text-gray-500">{{ $employee->puesto ?: 'Sin puesto' }} · {{ strtoupper($employee->departamento ?: 'SIN DEPTO') }}</p>
                        </button>
                    @empty
                        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">No hay empleados disponibles para asignar cuenta.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const employeeIdInput = document.getElementById('employee_id');
    const selectedEmployeeNameInput = document.getElementById('selected_employee_name');
    const fullNameInput = document.getElementById('full_name');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const departmentSelect = document.getElementById('department');
    const employeeSearchInput = document.getElementById('employeeSearch');
    const pickButtons = Array.from(document.querySelectorAll('.employee-pick'));

    const deaccent = (text) => text
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase();

    const normalizeDepartment = (department) => {
        const raw = deaccent((department || '').trim());
        if (!raw) return '';
        if (raw.includes('admin')) return 'administracion';
        if (raw.includes('comerc')) return 'comercial';
        if (raw.includes('oper')) return 'operaciones';
        if (raw === 'rh' || raw.includes('human')) return 'rh';
        if (raw === 'ti' || raw.includes('sistem') || raw.includes('informat')) return 'ti';
        return '';
    };

    const buildUsername = (name) => {
        const clean = deaccent(name)
            .replace(/[^a-z0-9\s]/g, ' ')
            .trim()
            .replace(/\s+/g, '.');
        return clean.slice(0, 30) || '';
    };

    const applySelection = (btn) => {
        const id = btn.dataset.id || '';
        const name = btn.dataset.name || '';
        const department = normalizeDepartment(btn.dataset.department || '');

        employeeIdInput.value = id;
        selectedEmployeeNameInput.value = name;
        fullNameInput.value = name;

        if (!usernameInput.value.trim()) {
            usernameInput.value = buildUsername(name);
        }
        if (!emailInput.value.trim()) {
            const username = buildUsername(name).replace(/\.+/g, '.').replace(/^\.|\.$/g, '');
            emailInput.value = username ? `${username}@copymart.com` : '';
        }
        if (department) {
            departmentSelect.value = department;
        }

        pickButtons.forEach((b) => {
            b.classList.remove('border-blue-500', 'bg-blue-50');
            b.classList.add('border-gray-200', 'bg-white');
        });
        btn.classList.remove('border-gray-200', 'bg-white');
        btn.classList.add('border-blue-500', 'bg-blue-50');
    };

    pickButtons.forEach((btn) => {
        btn.addEventListener('click', () => applySelection(btn));
    });

    if (employeeSearchInput) {
        employeeSearchInput.addEventListener('input', () => {
            const term = deaccent(employeeSearchInput.value || '');
            pickButtons.forEach((btn) => {
                const haystack = deaccent(`${btn.dataset.name || ''} ${btn.dataset.department || ''}`);
                btn.classList.toggle('hidden', term !== '' && !haystack.includes(term));
            });
        });
    }

    const oldId = employeeIdInput.value;
    if (oldId) {
        const oldBtn = pickButtons.find((b) => b.dataset.id === oldId);
        if (oldBtn) applySelection(oldBtn);
    }
})();
</script>
@endpush
@endsection
