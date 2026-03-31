@extends('layouts.app')
@section('title','Solicitar Vacaciones')
@section('page-title','Solicitar Vacaciones')

@section('content')
<div class="max-w-xl">
<form method="POST" action="{{ route('vacations.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">

        <div class="col-span-2">
            <label class="form-label">Empleado *</label>
            <select name="employee_id" id="empSel" class="form-select" required onchange="loadEntitlement(this.value)">
                <option value="">Seleccionar…</option>
                @foreach($employees as $e)
                <option value="{{ $e->id }}" @selected(old('employee_id', request('employee_id'))==$e->id)>{{ $e->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div id="entitlementBox" class="col-span-2 hidden">
            <div class="grid grid-cols-3 gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg text-center text-sm">
                <div>
                    <p class="text-xs text-blue-600 font-medium">Antigüedad</p>
                    <p id="eYears" class="font-bold text-blue-900 text-lg">—</p>
                    <p class="text-xs text-blue-500">años</p>
                </div>
                <div>
                    <p class="text-xs text-blue-600 font-medium">Días correspondientes</p>
                    <p id="eEntitlement" class="font-bold text-blue-900 text-lg">—</p>
                    <p class="text-xs text-blue-500">días / año</p>
                </div>
                <div>
                    <p class="text-xs text-green-600 font-medium">Días disponibles</p>
                    <p id="eRemaining" class="font-bold text-green-800 text-lg">—</p>
                    <p class="text-xs text-green-500">días restantes</p>
                </div>
            </div>
        </div>

        <div>
            <label class="form-label">Días solicitados *</label>
            <input name="vacation_days" id="vacDays" type="number" min="1" value="{{ old('vacation_days') }}" class="form-input" required>
        </div>

        <div>
            <label class="form-label">Fecha inicio *</label>
            <input name="start_date" type="date" value="{{ old('start_date') }}" class="form-input" required>
        </div>

        <div>
            <label class="form-label">Fecha fin *</label>
            <input name="end_date" type="date" value="{{ old('end_date') }}" class="form-input" required>
        </div>

        <div class="col-span-2">
            <label class="form-label">Notas</label>
            <textarea name="notes" class="form-input" rows="2">{{ old('notes') }}</textarea>
        </div>

    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Enviar solicitud</button>
        <a href="{{ route('vacations.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>

@push('scripts')
<script>
const empData = @json($employeeData);

function loadEntitlement(empId) {
    const box = document.getElementById('entitlementBox');
    if (!empId || !empData[empId]) { box.classList.add('hidden'); return; }
    const d = empData[empId];
    document.getElementById('eYears').textContent       = d.years;
    document.getElementById('eEntitlement').textContent = d.entitlement;
    document.getElementById('eRemaining').textContent   = d.remaining;
    document.getElementById('vacDays').max              = d.remaining;
    box.classList.remove('hidden');
}

// Auto-load if old value
const sel = document.getElementById('empSel');
if (sel.value) loadEntitlement(sel.value);
</script>
@endpush
@endsection
