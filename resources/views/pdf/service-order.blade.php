<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de servicio OS-{{ $serviceOrder->id }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #111; margin: 24px; }
        .top { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f766e; padding-bottom: 10px; }
        .title { font-size: 20px; font-weight: 700; }
        .sub { font-size: 12px; color: #555; }
        .actions { margin: 14px 0; }
        .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-top: 10px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 18px; }
        .item { border-bottom: 1px dashed #ddd; padding: 6px 0; }
        .label { color: #666; font-size: 12px; }
        .value { font-size: 13px; font-weight: 600; }
        .chips { margin-top: 8px; }
        .chip { display: inline-block; margin: 2px 4px 2px 0; padding: 4px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .ok { background: #dcfce7; color: #166534; }
        .off { background: #f3f4f6; color: #6b7280; }
        .status { display: inline-block; margin-top: 6px; padding: 5px 9px; border-radius: 6px; font-size: 11px; font-weight: 700; }
        .status-pendiente { background: #fef9c3; color: #854d0e; }
        .status-completado { background: #dcfce7; color: #166534; }
        .no-print { display: inline-block; padding: 7px 10px; background: #111827; color: #fff; border-radius: 6px; text-decoration: none; }
        .signature { margin-top: 8px; border: 1px solid #ddd; border-radius: 6px; background: #fff; max-height: 90px; }
        @media print { .no-print { display: none; } body { margin: 10px; } }
    </style>
</head>
<body>
    @php
        $statusClass = $serviceOrder->status === 'COMPLETADO' ? 'status-completado' : 'status-pendiente';
        $checkLabels = [
            'UNIDAD_IMAGEN' => 'Unidad de imagen',
            'UNIDAD_REVELADO' => 'Unidad de revelado',
            'FUSOR' => 'Fusor',
            'CALIBRACIONES' => 'Calibraciones',
            'GOMAS' => 'Gomas',
            'BANDA_TRANSFERENCIA' => 'Banda de transferencia',
            'BANDEJAS' => 'Bandejas',
        ];
        $revisado = $serviceOrder->se_reviso ?? [];
    @endphp

    <div class="top">
        <div>
            <div class="title">Orden de Servicio</div>
            <div class="sub">CopyMart ERP</div>
        </div>
        <div style="text-align:right;">
            <div class="sub">Folio</div>
            <div style="font-weight:700;">OS-{{ $serviceOrder->id }}</div>
            <div class="sub">{{ $serviceOrder->created_at->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="actions">
        <a href="#" class="no-print" onclick="window.print(); return false;">Imprimir / Guardar PDF</a>
    </div>

    <div class="status {{ $statusClass }}">{{ $serviceOrder->status }}</div>

    <div class="box">
        <div class="grid">
            <div class="item"><div class="label">Ingeniero</div><div class="value">{{ $serviceOrder->engineer->full_name ?? '—' }}</div></div>
            <div class="item"><div class="label">Tipo de orden</div><div class="value">{{ str_replace('_',' ', $serviceOrder->tipo_orden) }}</div></div>
            <div class="item"><div class="label">Cliente</div><div class="value">{{ $serviceOrder->client->name ?? '—' }}</div></div>
            <div class="item"><div class="label">Sucursal</div><div class="value">{{ $serviceOrder->branch->name ?? '—' }}</div></div>
            <div class="item"><div class="label">Area / ubicacion</div><div class="value">{{ $serviceOrder->area->name ?? '—' }}</div></div>
            <div class="item"><div class="label">Equipo</div><div class="value">{{ $serviceOrder->item->brand->name ?? '' }} {{ $serviceOrder->item->model ?? '' }}</div></div>
            <div class="item"><div class="label">Serie</div><div class="value">{{ $serviceOrder->item->serie ?? '—' }}</div></div>
            <div class="item"><div class="label">SKU</div><div class="value">{{ $serviceOrder->item->sku ?? '—' }}</div></div>
        </div>
    </div>

    <div class="box">
        <div class="label" style="font-weight:700; color:#111;">Checklist de revision</div>
        <div class="chips">
            @foreach($checkLabels as $key => $label)
                <span class="chip {{ in_array($key, $revisado) ? 'ok' : 'off' }}">
                    {{ in_array($key, $revisado) ? 'SI' : 'NO' }} {{ $label }}
                </span>
            @endforeach
        </div>
    </div>

    <div class="box">
        <div class="grid">
            <div class="item"><div class="label">Diagnostico / accion</div><div class="value">{{ $serviceOrder->diagnostico_accion ?: '—' }}</div></div>
            <div class="item"><div class="label">Material pendiente</div><div class="value">{{ $serviceOrder->pendiente_material ?: '—' }}</div></div>
            <div class="item"><div class="label">Entrego toner</div><div class="value">{{ $serviceOrder->entrego_toner ? 'Si' : 'No' }}</div></div>
            <div class="item"><div class="label">Codigos toner</div><div class="value">{{ $serviceOrder->codigos_toner ?: '—' }}</div></div>
            <div class="item"><div class="label">Toner % (K/C/M/Y)</div><div class="value">{{ $serviceOrder->pct_toner_negro ?? 0 }} / {{ $serviceOrder->pct_toner_cyan ?? 0 }} / {{ $serviceOrder->pct_toner_magenta ?? 0 }} / {{ $serviceOrder->pct_toner_amarillo ?? 0 }}</div></div>
            <div class="item"><div class="label">Tiene stock</div><div class="value">{{ $serviceOrder->tiene_stock ? 'Si' : 'No' }}</div></div>
            <div class="item"><div class="label">Firma nombre</div><div class="value">{{ $serviceOrder->firma_nombre ?: '—' }}</div></div>
            <div class="item"><div class="label">Queda pendiente</div><div class="value">{{ $serviceOrder->queda_pendiente ? 'Si' : 'No' }}</div></div>
        </div>

        @if($serviceOrder->queda_pendiente && $serviceOrder->descripcion_pendiente)
            <div class="item" style="margin-top:8px;">
                <div class="label">Descripcion pendiente</div>
                <div class="value">{{ $serviceOrder->descripcion_pendiente }}</div>
            </div>
        @endif

        @if($serviceOrder->firma_imagen)
            <div style="margin-top:8px;">
                <div class="label">Firma digital</div>
                <img src="{{ $serviceOrder->firma_imagen }}" class="signature">
            </div>
        @endif
    </div>
</body>
</html>
