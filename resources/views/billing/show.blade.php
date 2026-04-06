@extends('layouts.app')
@section('title','Factura')
@section('page-title','Detalle de Factura')

@section('content')
<div class="flex gap-3 mb-4">
    @if($billing->status !== 'PAGADO')
    <a href="{{ route('billing.edit',$billing) }}" class="btn-primary">Editar</a>
    @endif
    <a href="{{ route('billing.index') }}" class="btn-secondary">← Volver</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold">Factura {{ $billing->invoice_number ?? 'Sin número' }}</h3>
            @php $c=['PENDIENTE'=>'badge-yellow','PAGADO'=>'badge-green','VENCIDO'=>'badge-red']; @endphp
            <span class="{{ $c[$billing->status]??'badge-gray' }}">{{ $billing->status }}</span>
        </div>
        <div class="card-body grid grid-cols-2 gap-4 text-sm">
            <div><p class="text-gray-500">Cliente</p><p class="font-medium">{{ $billing->client->name }}</p></div>
            <div><p class="text-gray-500">Tipo</p><p>{{ $billing->billing_type }}</p></div>
            <div><p class="text-gray-500">Monto</p><p class="text-xl font-bold">${{ number_format($billing->amount,2) }}</p></div>
            <div><p class="text-gray-500">Fecha objetivo</p><p>{{ $billing->target_date->format('d/m/Y') }}</p></div>
            <div><p class="text-gray-500">Vence</p><p class="{{ $billing->status==='VENCIDO'?'text-red-600 font-medium':'' }}">{{ $billing->due_date->format('d/m/Y') }}</p></div>
            <div><p class="text-gray-500">Pago recibido</p><p>{{ $billing->payment_date?->format('d/m/Y') ?? '—' }}</p></div>
            @if($billing->comment)
            <div class="col-span-2"><p class="text-gray-500">Comentario</p><p>{{ $billing->comment }}</p></div>
            @endif
            <div><p class="text-gray-500">UID Factura.com</p><p class="font-mono text-xs">{{ $billing->facturacom_uid ?? '—' }}</p></div>
            <div><p class="text-gray-500">UUID Factura.com</p><p class="font-mono text-xs">{{ $billing->facturacom_uuid ?? '—' }}</p></div>
            <div><p class="text-gray-500">Folio Factura.com</p><p>{{ $billing->facturacom_folio ?? '—' }}</p></div>
            <div><p class="text-gray-500">Estado remoto</p><p>{{ $billing->facturacom_status ?? '—' }}</p></div>
            <div class="col-span-2"><p class="text-gray-500">Última sincronización</p><p>{{ $billing->facturacom_synced_at?->format('d/m/Y H:i') ?? '—' }}</p></div>
        </div>
    </div>

    <div class="space-y-5">
        @if($errors->has('facturacom'))
        <div class="rounded-md border border-red-200 bg-red-50 text-red-700 px-3 py-2 text-sm">
            {{ $errors->first('facturacom') }}
        </div>
        @endif

        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Timbrar CFDI en Factura.com</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('billing.facturacom.stamp',$billing) }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Receptor UID *</label>
                        <input name="receptor_uid" class="form-input" value="{{ old('receptor_uid') }}" required>
                    </div>
                    <div>
                        <label class="form-label">Serie ID *</label>
                        <input name="serie_id" type="number" min="1" class="form-input" value="{{ old('serie_id') }}" required>
                    </div>
                    <div>
                        <label class="form-label">Uso CFDI *</label>
                        <input name="uso_cfdi" class="form-input" value="{{ old('uso_cfdi','G03') }}" required>
                    </div>
                    <div>
                        <label class="form-label">Forma de pago *</label>
                        <input name="forma_pago" class="form-input" value="{{ old('forma_pago','03') }}" required>
                    </div>
                    <div>
                        <label class="form-label">Método de pago *</label>
                        <input name="metodo_pago" class="form-input" value="{{ old('metodo_pago','PUE') }}" required>
                    </div>
                    <div>
                        <label class="form-label">Moneda</label>
                        <input name="moneda" class="form-input" value="{{ old('moneda','MXN') }}">
                    </div>
                    <div>
                        <label class="form-label">Clave Prod/Serv</label>
                        <input name="clave_prod_serv" class="form-input" value="{{ old('clave_prod_serv','81112101') }}">
                    </div>
                    <div>
                        <label class="form-label">Clave Unidad</label>
                        <input name="clave_unidad" class="form-input" value="{{ old('clave_unidad','E48') }}">
                    </div>
                    <div>
                        <label class="form-label">Unidad</label>
                        <input name="unidad" class="form-input" value="{{ old('unidad','Unidad de servicio') }}">
                    </div>
                    <div>
                        <label class="form-label">Objeto Impuesto</label>
                        <select name="objeto_imp" class="form-select">
                            @foreach(['01','02','03','04'] as $obj)
                            <option value="{{ $obj }}" @selected(old('objeto_imp','01')===$obj)>{{ $obj }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Descripción</label>
                        <input name="descripcion" class="form-input" value="{{ old('descripcion', 'Servicio ERP - Folio '.$billing->id) }}">
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">NumOrder</label>
                        <input name="num_order" class="form-input" value="{{ old('num_order', 'BILLING-'.$billing->id) }}">
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Payload JSON (opcional, sobreescribe defaults)</label>
                        <textarea name="raw_payload" rows="6" class="form-input font-mono text-xs">{{ old('raw_payload') }}</textarea>
                    </div>
                    <div class="md:col-span-2 flex items-center justify-between gap-2">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="enviar_correo" value="1" @checked(old('enviar_correo'))>
                            Enviar CFDI por correo desde Factura.com
                        </label>
                        <button class="btn-primary">Timbrar en Factura.com</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-sm">Sincronizar CFDI</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('billing.facturacom.sync',$billing) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Buscar por</label>
                        <select name="search_mode" class="form-select">
                            <option value="">Auto</option>
                            <option value="uid">UID</option>
                            <option value="uuid">UUID</option>
                            <option value="order">NumOrder</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Valor (opcional)</label>
                        <input name="search_value" class="form-input" placeholder="Si lo dejas vacío, usa UID/UUID guardado o BILLING-ID">
                    </div>
                    <div class="md:col-span-3 text-right">
                        <button class="btn-secondary">Consultar en Factura.com</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($billing->status !== 'PAGADO')
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-sm">Registrar pago</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('billing.pay',$billing) }}">
                @csrf @method('PATCH')
                <label class="form-label">Fecha de pago *</label>
                <input name="payment_date" type="date" value="{{ date('Y-m-d') }}" class="form-input mb-4" required>
                <button class="btn-success w-full justify-center">Marcar como pagado</button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
