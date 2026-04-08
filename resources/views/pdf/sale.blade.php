<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta {{ $sale->invoice_number ?? ('V-'.$sale->id) }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #111; margin: 24px; }
        .top { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #ea580c; padding-bottom: 10px; }
        .title { font-size: 20px; font-weight: 700; }
        .sub { font-size: 12px; color: #555; }
        .actions { margin: 14px 0; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-blue { background: #dbeafe; color: #1e3a8a; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 18px; margin-top: 14px; }
        .item { border-bottom: 1px dashed #ddd; padding: 6px 0; }
        .label { color: #666; font-size: 12px; }
        .value { font-size: 13px; font-weight: 600; }
        .total { margin-top: 18px; font-size: 22px; font-weight: 800; color: #ea580c; }
        .no-print { display: inline-block; padding: 7px 10px; background: #111827; color: #fff; border-radius: 6px; text-decoration: none; }
        @media print { .no-print { display: none; } body { margin: 10px; } }
    </style>
</head>
<body>
    <div class="top">
        <div>
            <div class="title">Comprobante de Venta</div>
            <div class="sub">CopyMart ERP</div>
        </div>
        <div style="text-align:right;">
            <div class="sub">Folio</div>
            <div style="font-weight:700;">{{ $sale->invoice_number ?? ('V-'.$sale->id) }}</div>
            <div class="sub">{{ $sale->created_at->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="actions">
        <a href="#" class="no-print" onclick="window.print(); return false;">Imprimir / Guardar PDF</a>
    </div>

    @php
        $statusClass = [
            'PENDIENTE' => 'badge-yellow',
            'CONFIRMADA' => 'badge-blue',
            'ENTREGADA' => 'badge-green',
            'CANCELADA' => 'badge-red',
        ][$sale->sale_status] ?? 'badge-blue';
    @endphp

    <span class="badge {{ $statusClass }}">{{ $sale->sale_status }}</span>

    <div class="grid">
        <div class="item"><div class="label">Cliente</div><div class="value">{{ $sale->client->name ?? '—' }}</div></div>
        <div class="item"><div class="label">Equipo</div><div class="value">{{ $sale->item->brand->name ?? '' }} {{ $sale->item->model ?? '' }}</div></div>
        <div class="item"><div class="label">Serie</div><div class="value">{{ $sale->item->serie ?? '—' }}</div></div>
        <div class="item"><div class="label">Sucursal</div><div class="value">{{ $sale->branch->name ?? '—' }}</div></div>
        <div class="item"><div class="label">Área</div><div class="value">{{ $sale->area->name ?? '—' }}</div></div>
        <div class="item"><div class="label">Registró</div><div class="value">{{ $sale->creator?->full_name ?? $sale->creator?->username ?? '—' }}</div></div>
    </div>

    <div class="total">Total: ${{ number_format((float)$sale->sale_price, 2) }}</div>
</body>
</html>
