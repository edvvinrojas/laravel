@extends('layouts.app')
@section('title','Nuevo Ticket')
@section('page-title','Nuevo Ticket')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('tickets.store') }}">
@csrf
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
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
                <option value="">Seleccionar cliente primero…</option>
            </select>
        </div>
        <div>
            <label class="form-label">Tipo de reporte *</label>
            <select name="report_type" class="form-select" required>
                <option value="">Seleccionar…</option>
                @foreach(['CONECTIVIDAD','ATASCO','TONER','QUEJAS','COPIA','RUIDOS','IMPRESION','OTROS'] as $t)
                <option value="{{ $t }}" @selected(old('report_type')===$t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Estado *</label>
            <select name="report_status" class="form-select" required>
                @foreach(['PENDIENTE','URGENTE','ATENCION','PROGRAMADO','INFORMATIVO'] as $s)
                <option value="{{ $s }}" @selected(old('report_status','PENDIENTE')===$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-2">
            <label class="form-label">Descripción *</label>
            <textarea name="description" class="form-input" rows="3" required>{{ old('description') }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
        <a href="{{ route('tickets.index') }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>

@push('scripts')
<script>
document.getElementById('clientSel').addEventListener('change', function() {
    const clientId = this.value;
    const branchSel = document.getElementById('branchSel');
    branchSel.innerHTML = '<option value="">Cargando…</option>';
    if (!clientId) { branchSel.innerHTML = '<option value="">Seleccionar cliente primero…</option>'; return; }
    fetch(`/clients/${clientId}/branches`)
        .then(r => r.json())
        .then(branches => {
            branchSel.innerHTML = '<option value="">Seleccionar sucursal…</option>';
            branches.forEach(b => branchSel.innerHTML += `<option value="${b.id}">${b.name}</option>`);
        });
});
</script>
@endpush
@endsection
