<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renta {{ $rent->contract_number ?? ('R-'.$rent->id) }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #111; margin: 24px; }
        .top { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }
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
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table th, .table td { border-bottom: 1px solid #eee; text-align: left; padding: 6px; font-size: 12px; }
        .no-print { display: inline-block; padding: 7px 10px; background: #111827; color: #fff; border-radius: 6px; text-decoration: none; }
        @media print { .no-print { display: none; } body { margin: 10px; } }
    </style>
</head>
<body>
    <div class="top">
        <div class="top-left">
            <img src="{{ asset('img/logo.svg') }}" alt="CopyMart" class="logo">
            <div>
                <div class="title">Contrato de Renta</div>
                {{-- <div class="sub">CopyMart ERP</div> --}}
            </div>
        </div>
        <div style="text-align:right;">
            <div class="sub">Contrato</div>
            <div style="font-weight:700;">{{ $rent->contract_number ?? ('R-'.$rent->id) }}</div>
            <div class="sub">{{ $rent->created_at->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="actions">
        <a href="#" class="no-print" onclick="window.print(); return false;">Imprimir / Guardar PDF</a>
    </div>

    <div class="box">
        <div class="grid">
            <div class="item"><div class="label">Cliente</div><div class="value">{{ $rent->client->name ?? '—' }}</div></div>
            <div class="item"><div class="label">Equipos</div><div class="value">{{ $rent->items->count() ?: 1 }}</div></div>
            <div class="item"><div class="label">Renta mensual</div><div class="value">${{ number_format((float)$rent->rent, 2) }}</div></div>
            <div class="item"><div class="label">Inicio</div><div class="value">{{ $rent->start_date?->format('d/m/Y') ?? '—' }}</div></div>
            <div class="item"><div class="label">Fin</div><div class="value">{{ $rent->end_date?->format('d/m/Y') ?? '—' }}</div></div>
            <div class="item"><div class="label">Estatus</div><div class="value">{{ $rent->contract_status }}</div></div>
            <div class="item"><div class="label">Ubicación</div><div class="value">{{ $rent->branch->name ?? '—' }} / {{ $rent->area->name ?? '—' }}</div></div>
        </div>

        <table class="table" style="margin-top:14px;">
            <thead>
                <tr>
                    <th>Equipo</th>
                    <th>Serie</th>
                    <th>Sucursal</th>
                    <th>Area</th>
                    <th>Cont. BN</th>
                    <th>Cont. Color</th>
                </tr>
            </thead>
            <tbody>
            @foreach(($rent->items->count() ? $rent->items : collect([$rent->item])) as $eq)
                @php
                    $branchId = $eq->pivot->branch_id ?? $rent->branch_id;
                    $areaId = $eq->pivot->area_id ?? $rent->area_id;
                    $branch = $rent->client->branches->firstWhere('id', $branchId);
                    $area = $branch ? $branch->areas->firstWhere('id', $areaId) : null;
                @endphp
                <tr>
                    <td>{{ $eq->brand->name ?? '' }} {{ $eq->model ?? '' }}</td>
                    <td>{{ $eq->serie ?? '—' }}</td>
                    <td>{{ $branch->name ?? '—' }}</td>
                    <td>{{ $area->name ?? '—' }}</td>
                    <td>{{ (int) ($eq->pivot->contador_inicial_bn ?? $rent->contador_inicial_bn ?? 0) }}</td>
                    <td>{{ (int) ($eq->pivot->contador_inicial_color ?? $rent->contador_inicial_color ?? 0) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if($rent->has_print_service)
    <div class="box">
        <div style="font-weight:700; margin-bottom:6px;">Servicio de impresión</div>
        <div class="grid">
            <div class="item"><div class="label">BN incluidas</div><div class="value">{{ number_format((int)$rent->bn_included) }}</div></div>
            <div class="item"><div class="label">Costo exceso BN</div><div class="value">${{ number_format((float)$rent->bn_cost_per_excess, 4) }}</div></div>
            <div class="item"><div class="label">Color incluidas</div><div class="value">{{ number_format((int)$rent->color_included) }}</div></div>
            <div class="item"><div class="label">Costo exceso Color</div><div class="value">${{ number_format((float)$rent->color_cost_per_excess, 4) }}</div></div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Período</th>
                    <th>BN impreso</th>
                    <th>Color impreso</th>
                    <th>Exceso total</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rent->printCounters as $pc)
                <tr>
                    <td>{{ sprintf('%02d', $pc->period_month) }}/{{ $pc->period_year }}</td>
                    <td>{{ number_format($pc->bn_printed) }}</td>
                    <td>{{ number_format($pc->color_printed) }}</td>
                    <td>${{ number_format((float)$pc->total_excess_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Sin contadores registrados</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @endif
</body>
</html>
