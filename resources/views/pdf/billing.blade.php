<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $billing->invoice_number ?? ('FAC-'.$billing->id) }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #111; margin: 24px; }
        .top { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #f59e0b; padding-bottom: 10px; }
        .top-left { display: flex; align-items: center; gap: 12px; }
        .logo { height: 48px; width: auto; }
        .title { font-size: 20px; font-weight: 700; }
        .sub { font-size: 12px; color: #555; }
        .actions { margin: 14px 0; }
        .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-top: 10px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 18px; }
        .item { border-bottom: 1px dashed #ddd; padding: 6px 0; }
        .label { color: #666; font-size: 12px; }
        .value { font-size: 13px; font-weight: 600; }
        .totals { margin-top: 12px; border-top: 1px solid #ddd; padding-top: 10px; }
        .total { font-size: 22px; font-weight: 800; color: #b45309; }
        .no-print { display: inline-block; padding: 7px 10px; background: #111827; color: #fff; border-radius: 6px; text-decoration: none; }
        @media print { .no-print { display: none; } body { margin: 10px; } }
    </style>
</head>
<body>
    <div class="top">
        <div class="top-left">
            <img src="{{ asset('img/logo.svg') }}" alt="CopyMart" class="logo">
            <div>
                <div class="title">Factura</div>
                {{-- <div class="sub">CopyMart ERP</div> --}}
            </div>
        </div>
        <div style="text-align:right;">
            <div class="sub">Folio</div>
            <div style="font-weight:700;">{{ $billing->invoice_number ?? ('FAC-'.$billing->id) }}</div>
            <div class="sub">{{ $billing->target_date?->format('d/m/Y') ?? '—' }}</div>
        </div>
    </div>

    <div class="actions">
        <a href="#" class="no-print" onclick="window.print(); return false;">Imprimir / Guardar PDF</a>
    </div>

    <div class="box">
        <div class="grid">
            <div class="item"><div class="label">Cliente</div><div class="value">{{ $billing->client->name ?? '—' }}</div></div>
            <div class="item"><div class="label">Tipo</div><div class="value">{{ $billing->billing_type }}</div></div>
            <div class="item"><div class="label">Vencimiento</div><div class="value">{{ $billing->due_date?->format('d/m/Y') ?? '—' }}</div></div>
            <div class="item"><div class="label">Estatus</div><div class="value">{{ $billing->status }}</div></div>
            <div class="item"><div class="label">Sucursal / Área</div><div class="value">{{ $billing->branch->name ?? '—' }} / {{ $billing->area->name ?? '—' }}</div></div>
            <div class="item"><div class="label">Pago recibido</div><div class="value">{{ $billing->payment_date?->format('d/m/Y') ?? '—' }}</div></div>
        </div>

        @if($billing->rent)
        <div class="item" style="margin-top:8px;"><div class="label">Renta asociada</div><div class="value">{{ $billing->rent->contract_number ?? ('R-'.$billing->rent->id) }} - {{ $billing->rent->item->brand->name ?? '' }} {{ $billing->rent->item->model ?? '' }}</div></div>
        @endif

        @if($billing->sale)
        <div class="item" style="margin-top:8px;"><div class="label">Venta asociada</div><div class="value">{{ $billing->sale->invoice_number ?? ('V-'.$billing->sale->id) }} - {{ $billing->sale->item->brand->name ?? '' }} {{ $billing->sale->item->model ?? '' }}</div></div>
        @endif

        @if($billing->comment)
        <div class="item" style="margin-top:8px;"><div class="label">Comentario</div><div class="value">{{ $billing->comment }}</div></div>
        @endif

        <div class="totals">
            <div class="label">Subtotal aprox.</div>
            <div class="value">${{ number_format((float)$billing->amount / 1.16, 2) }}</div>
            <div class="label" style="margin-top:6px;">IVA (16%) aprox.</div>
            <div class="value">${{ number_format((float)$billing->amount - ((float)$billing->amount / 1.16), 2) }}</div>
            <div class="total" style="margin-top:10px;">Total: ${{ number_format((float)$billing->amount, 2) }}</div>
        </div>
    </div>
</body>
</html>
