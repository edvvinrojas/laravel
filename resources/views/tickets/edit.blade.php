@extends('layouts.app')
@section('title','Editar Ticket')
@section('page-title','Editar Ticket')

@section('content')
<div class="max-w-2xl">
<form method="POST" action="{{ route('tickets.update',$ticket) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Cliente *</label>
            <select name="client_id" id="clientSel" class="form-select" required>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(old('client_id',$ticket->client_id)==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Sucursal *</label>
            <select name="branch_id" id="branchSel" class="form-select" required>
                @foreach($branches as $b)
                <option value="{{ $b->id }}" @selected(old('branch_id',$ticket->branch_id)==$b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Tipo *</label>
            <select name="report_type" class="form-select" required>
                @foreach(['CONECTIVIDAD','ATASCO','TONER','QUEJAS','COPIA','RUIDOS','IMPRESION','OTROS'] as $t)
                <option value="{{ $t }}" @selected(old('report_type',$ticket->report_type)===$t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Estado *</label>
            <select name="report_status" class="form-select" required>
                @foreach(['PENDIENTE','LISTO','URGENTE','PROGRAMADO','INFORMATIVO','NO_QUEDO_EN_LA_VISITA','ATENCION'] as $s)
                <option value="{{ $s }}" @selected(old('report_status',$ticket->report_status)===$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-2">
            <label class="form-label">Descripción *</label>
            <textarea name="description" class="form-input" rows="3" required>{{ old('description',$ticket->description) }}</textarea>
        </div>
        <div class="col-span-2">
            <label class="form-label">Acción correctiva</label>
            <textarea name="corrective_action" class="form-input" rows="2">{{ old('corrective_action',$ticket->corrective_action) }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('tickets.show',$ticket) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>
@endsection
