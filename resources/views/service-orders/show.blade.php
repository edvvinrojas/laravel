@extends('layouts.app')
@section('title','Orden de Servicio #{{ $serviceOrder->id }}')
@section('page-title','Orden de Servicio')

@section('content')
@php
    $checkLabels = [
        'UNIDAD_IMAGEN'=>'Unidad de imagen','UNIDAD_REVELADO'=>'Unidad de revelado','FUSOR'=>'Fusor',
        'CALIBRACIONES'=>'Calibraciones','GOMAS'=>'Gomas','BANDA_TRANSFERENCIA'=>'Banda de transferencia','BANDEJAS'=>'Bandejas'
    ];
    $revisado = $serviceOrder->se_reviso ?? [];
@endphp

<div class="flex gap-3 mb-4">
    <a href="{{ route('service-orders.index') }}" class="btn-secondary">← Volver</a>
    @if($serviceOrder->status === 'PENDIENTE')
    <form action="{{ route('service-orders.update', $serviceOrder) }}" method="POST">
        @csrf @method('PUT')
        <input type="hidden" name="status" value="COMPLETADO">
        <input type="hidden" name="diagnostico_accion" value="{{ $serviceOrder->diagnostico_accion }}">
        <button type="submit" class="btn-primary">✓ Marcar completado</button>
    </form>
    @endif
</div>

<div class="space-y-5 max-w-3xl">

    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h3 class="font-semibold">Orden #{{ $serviceOrder->id }} — {{ str_replace('_',' ', $serviceOrder->tipo_orden) }}</h3>
            @if($serviceOrder->status === 'COMPLETADO')
                <span class="badge-green">Completado</span>
            @else
                <span class="badge-yellow">Pendiente</span>
            @endif
        </div>
        <div class="card-body grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div><p class="text-gray-500">Ingeniero</p><p class="font-medium">{{ $serviceOrder->engineer->full_name }}</p></div>
            <div><p class="text-gray-500">Cliente</p><p>{{ $serviceOrder->client->name }}</p></div>
            <div><p class="text-gray-500">Sucursal</p><p>{{ $serviceOrder->branch?->name ?? '—' }}</p></div>
            <div><p class="text-gray-500">Área/Ubicación</p><p>{{ $serviceOrder->area?->name ?? '—' }}</p></div>
            <div><p class="text-gray-500">Modelo</p><p>{{ $serviceOrder->item?->model ?? '—' }}</p></div>
            <div><p class="text-gray-500">Serie</p><p class="font-mono">{{ $serviceOrder->item?->serie ?? '—' }}</p></div>
            <div><p class="text-gray-500">No. equipo</p><p class="font-mono">{{ $serviceOrder->item?->sku ?? '—' }}</p></div>
            <div><p class="text-gray-500">Fecha</p><p>{{ $serviceOrder->created_at->format('d/m/Y H:i') }}</p></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-sm">Se revisó</h3></div>
        <div class="card-body">
            <div class="flex flex-wrap gap-2">
                @foreach($checkLabels as $val => $label)
                <span class="px-3 py-1 rounded-full text-xs font-medium {{ in_array($val, $revisado) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-400' }}">
                    {{ in_array($val, $revisado) ? '✓' : '✗' }} {{ $label }}
                </span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-sm">Diagnóstico y tóner</h3></div>
        <div class="card-body space-y-4 text-sm">
            @if($serviceOrder->diagnostico_accion)
            <div><p class="text-gray-500 mb-1">Diagnóstico / Acción correctiva</p>
                <p class="whitespace-pre-line">{{ $serviceOrder->diagnostico_accion }}</p></div>
            @endif

            <div class="flex items-center gap-4">
                <span class="{{ $serviceOrder->entrego_toner ? 'badge-green' : 'badge-gray' }}">
                    {{ $serviceOrder->entrego_toner ? '✓ Entregó tóner' : 'No entregó tóner' }}
                </span>
                @if($serviceOrder->codigos_toner)
                <span class="text-gray-600">Cód: {{ $serviceOrder->codigos_toner }}</span>
                @endif
            </div>

            @if($serviceOrder->pct_toner_negro !== null)
            <div>
                <p class="text-gray-500 mb-2">Porcentajes de tóner</p>
                <div class="grid grid-cols-4 gap-3">
                    @foreach(['negro'=>'Negro','cyan'=>'Cyan','magenta'=>'Magenta','amarillo'=>'Amarillo'] as $key => $lbl)
                    @php $pct = $serviceOrder->{'pct_toner_'.$key}; @endphp
                    <div class="text-center">
                        <div class="relative w-12 h-12 mx-auto mb-1">
                            <svg viewBox="0 0 36 36" class="w-12 h-12 -rotate-90">
                                <circle cx="18" cy="18" r="15" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                                <circle cx="18" cy="18" r="15" fill="none" stroke="{{ $key==='negro'?'#1e293b':($key==='cyan'?'#0891b2':($key==='magenta'?'#db2777':'#ca8a04')) }}" stroke-width="4"
                                    stroke-dasharray="{{ ($pct ?? 0) * 94.25 / 100 }} 94.25"/>
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-xs font-bold">{{ $pct ?? 0 }}%</span>
                        </div>
                        <p class="text-xs text-gray-500">{{ $lbl }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($serviceOrder->pendiente_material)
            <div><p class="text-gray-500 mb-1">Material pendiente</p><p>{{ $serviceOrder->pendiente_material }}</p></div>
            @endif
        </div>
    </div>

    {{-- Fotos --}}
    @if($serviceOrder->evidencia_foto || $serviceOrder->pagina_estado_foto || $serviceOrder->foto_stock)
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-sm">Evidencias fotográficas</h3></div>
        <div class="card-body grid grid-cols-1 sm:grid-cols-3 gap-4">
            @if($serviceOrder->evidencia_foto)
            <div><p class="text-xs text-gray-500 mb-1">Evidencia del servicio</p>
                <img src="{{ Storage::url($serviceOrder->evidencia_foto) }}" class="rounded-lg border max-h-48 object-cover w-full"></div>
            @endif
            @if($serviceOrder->pagina_estado_foto)
            <div><p class="text-xs text-gray-500 mb-1">Página de estado</p>
                <img src="{{ Storage::url($serviceOrder->pagina_estado_foto) }}" class="rounded-lg border max-h-48 object-cover w-full"></div>
            @endif
            @if($serviceOrder->tiene_stock && $serviceOrder->foto_stock)
            <div><p class="text-xs text-gray-500 mb-1">Stock disponible</p>
                <img src="{{ Storage::url($serviceOrder->foto_stock) }}" class="rounded-lg border max-h-48 object-cover w-full"></div>
            @endif
        </div>
    </div>
    @endif

    {{-- Firma y pendientes --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-sm">Firma y pendientes</h3></div>
        <div class="card-body grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Firmó</p>
                <p class="font-medium">{{ $serviceOrder->firma_nombre ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">¿Queda pendiente?</p>
                <span class="{{ $serviceOrder->queda_pendiente ? 'badge-red' : 'badge-green' }}">
                    {{ $serviceOrder->queda_pendiente ? 'Sí' : 'No' }}
                </span>
                @if($serviceOrder->queda_pendiente && $serviceOrder->descripcion_pendiente)
                <p class="mt-1 text-gray-700">{{ $serviceOrder->descripcion_pendiente }}</p>
                @endif
            </div>
            @if($serviceOrder->firma_imagen)
            <div class="col-span-2">
                <p class="text-gray-500 mb-2">Firma digital</p>
                <img src="{{ $serviceOrder->firma_imagen }}" class="border rounded-lg bg-white max-h-24">
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
