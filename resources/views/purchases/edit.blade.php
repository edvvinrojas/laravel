@extends('layouts.app')
@section('title','Editar Compra')
@section('page-title','Editar Compra')

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('purchases.update',$purchase) }}">
@csrf @method('PUT')
<div class="card">
    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Nombre *</label>
            <input name="name" value="{{ old('name',$purchase->name) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Cantidad *</label>
            <input name="amount" type="number" min="1" value="{{ old('amount',$purchase->amount) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Cantidad autorizada</label>
            <input name="authorized_amount" type="number" min="0" value="{{ old('authorized_amount',$purchase->authorized_amount) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Estado *</label>
            <select name="status" class="form-select" required>
                @foreach(['EN_CURSO','FALTA_AUTORIZACION','FALTA_PAGO_PROVEEDOR','FALTA_FACTURA','EN_TRANSITO','SOLICITUD_GUIA_ALMACEN','PAUSADO_BACK_ORDERS','POR_REVISAR','RECHAZADO','FALTA_ORDEN_SERVICIO','CONCLUIDO'] as $s)
                <option value="{{ $s }}" @selected(old('status',$purchase->status)===$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Tipo *</label>
            <select name="type" class="form-select" required>
                <option value="INTERNA" @selected(old('type',$purchase->type)==='INTERNA')>INTERNA</option>
                <option value="VENTA" @selected(old('type',$purchase->type)==='VENTA')>VENTA</option>
            </select>
        </div>
        <div>
            <label class="form-label">Método envío</label>
            <input name="shipping_method" value="{{ old('shipping_method',$purchase->shipping_method) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Costo envío</label>
            <input name="shipping_cost" type="number" step="0.01" value="{{ old('shipping_cost',$purchase->shipping_cost) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Código guía</label>
            <input name="shipping_code" value="{{ old('shipping_code',$purchase->shipping_code) }}" class="form-input">
        </div>
        <div class="col-span-2">
            <div class="flex items-center justify-between mb-2">
                <label class="form-label mb-0">Cotizaciones de proveedores</label>
                <button type="button" id="addQuote" class="text-blue-600 text-sm">+ Agregar cotización</button>
            </div>
            <div id="quotesContainer" class="space-y-2">
                @foreach($purchase->quotes as $i => $q)
                <div class="grid grid-cols-3 gap-2 items-end quote-row">
                    <div>
                        <label class="form-label text-xs">Proveedor *</label>
                        <input name="quotes[{{ $i }}][supplier_name]" value="{{ old("quotes.$i.supplier_name", $q->supplier_name) }}" class="form-input text-sm" placeholder="Nombre proveedor" required>
                    </div>
                    <div>
                        <label class="form-label text-xs">Costo *</label>
                        <input name="quotes[{{ $i }}][cost]" type="number" step="0.01" min="0" value="{{ old("quotes.$i.cost", $q->cost) }}" class="form-input text-sm" placeholder="0.00" required>
                    </div>
                    <div class="flex gap-2 items-end">
                        <div class="flex-1">
                            <label class="form-label text-xs">Notas</label>
                            <input name="quotes[{{ $i }}][notes]" value="{{ old("quotes.$i.notes", $q->notes) }}" class="form-input text-sm" placeholder="Opcional">
                        </div>
                        <button type="button" class="btn-danger text-xs mb-0.5" onclick="this.closest('.quote-row').remove()">✕</button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="col-span-2">
            <label class="form-label">Comentarios</label>
            <textarea name="comments" class="form-input" rows="2">{{ old('comments',$purchase->comments) }}</textarea>
        </div>
    </div>
    <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Actualizar</button>
        <a href="{{ route('purchases.show',$purchase) }}" class="btn-secondary">Cancelar</a>
    </div>
</div>
</form>
</div>

@push('scripts')
<script>
let quoteIdx = {{ $purchase->quotes->count() }};
document.getElementById('addQuote').addEventListener('click', () => {
    const row = document.createElement('div');
    row.className = 'grid grid-cols-3 gap-2 items-end quote-row';
    row.innerHTML = `
        <div>
            <label class="form-label text-xs">Proveedor *</label>
            <input name="quotes[${quoteIdx}][supplier_name]" class="form-input text-sm" placeholder="Nombre proveedor" required>
        </div>
        <div>
            <label class="form-label text-xs">Costo *</label>
            <input name="quotes[${quoteIdx}][cost]" type="number" step="0.01" min="0" class="form-input text-sm" placeholder="0.00" required>
        </div>
        <div class="flex gap-2 items-end">
            <div class="flex-1">
                <label class="form-label text-xs">Notas</label>
                <input name="quotes[${quoteIdx}][notes]" class="form-input text-sm" placeholder="Opcional">
            </div>
            <button type="button" class="btn-danger text-xs mb-0.5" onclick="this.closest('.quote-row').remove()">✕</button>
        </div>
    `;
    document.getElementById('quotesContainer').appendChild(row);
    quoteIdx++;
});
</script>
@endpush
@endsection
