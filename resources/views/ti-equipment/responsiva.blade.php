<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsiva — {{ $tiEquipment->codigo_interno }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #111; background: #fff; padding: 24px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #1e40af; padding-bottom: 12px; margin-bottom: 16px; }
        .company { font-size: 20px; font-weight: 700; color: #1e40af; }
        .doc-title { font-size: 14px; font-weight: 600; text-align: right; }
        .doc-meta { font-size: 11px; color: #555; text-align: right; margin-top: 4px; }
        h2 { font-size: 13px; font-weight: 700; background: #1e40af; color: #fff; padding: 4px 10px; margin: 14px 0 8px; border-radius: 2px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 16px; }
        .field { display: flex; gap: 4px; padding: 3px 0; border-bottom: 1px dotted #ddd; }
        .label { font-weight: 600; color: #444; min-width: 140px; }
        .value { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th { background: #e0e7ff; text-align: left; padding: 5px 8px; font-size: 11px; }
        td { padding: 4px 8px; border-bottom: 1px solid #eee; font-size: 11px; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; }
        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .firma-section { margin-top: 40px; display: flex; justify-content: space-around; gap: 24px; }
        .firma-box { text-align: center; flex: 1; }
        .firma-line { border-top: 1px solid #000; margin: 0 20px 6px; padding-top: 6px; }
        .footer { margin-top: 20px; font-size: 10px; color: #888; text-align: center; border-top: 1px solid #eee; padding-top: 8px; }
        @media print {
            body { padding: 10px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print" style="background:#f0f9ff;border:1px solid #bae6fd;padding:10px 16px;margin-bottom:16px;border-radius:6px;display:flex;gap:12px;align-items:center;">
    <button onclick="window.print()" style="background:#1e40af;color:#fff;border:none;padding:6px 16px;border-radius:4px;cursor:pointer;font-size:12px;">Imprimir / Guardar PDF</button>
    <a href="{{ route('ti-equipment.show', $tiEquipment) }}" style="font-size:12px;color:#1d4ed8;">← Volver al equipo</a>
</div>

<div class="header">
    <div>
        <div class="company">CopyMart</div>
        <div style="font-size:11px;color:#555;margin-top:2px;">Responsiva de Equipos de Cómputo</div>
    </div>
    <div>
        <div class="doc-title">RESPONSIVA DE EQUIPO</div>
        <div class="doc-meta">Folio: {{ $tiEquipment->codigo_interno }}</div>
        <div class="doc-meta">Fecha: {{ now()->format('d/m/Y') }}</div>
    </div>
</div>

<h2>Datos del Responsable</h2>
<div class="grid-2">
    <div class="field"><span class="label">Nombre:</span><span class="value">{{ $tiEquipment->assignedUser?->full_name ?? '—' }}</span></div>
    <div class="field"><span class="label">Ubicación:</span><span class="value">{{ $tiEquipment->ubicacion ?? '—' }}</span></div>
    <div class="field"><span class="label">Fecha de asignación:</span><span class="value">{{ now()->format('d/m/Y') }}</span></div>
    <div class="field"><span class="label">Estatus:</span><span class="value">{{ $tiEquipment->status }}</span></div>
</div>

<h2>Datos del Equipo</h2>
<div class="grid-2">
    <div class="field"><span class="label">Código interno:</span><span class="value">{{ $tiEquipment->codigo_interno }}</span></div>
    <div class="field"><span class="label">Tipo:</span><span class="value">{{ $tiEquipment->tipo }}</span></div>
    <div class="field"><span class="label">Marca:</span><span class="value">{{ $tiEquipment->marca }}</span></div>
    <div class="field"><span class="label">Modelo:</span><span class="value">{{ $tiEquipment->modelo }}</span></div>
    @if($tiEquipment->numero_serie)
    <div class="field"><span class="label">No. de serie:</span><span class="value">{{ $tiEquipment->numero_serie }}</span></div>
    @endif
    @if($tiEquipment->procesador)
    <div class="field"><span class="label">Procesador:</span><span class="value">{{ $tiEquipment->procesador }}</span></div>
    @endif
    @if($tiEquipment->ram)
    <div class="field"><span class="label">RAM:</span><span class="value">{{ $tiEquipment->ram }}</span></div>
    @endif
    @if($tiEquipment->almacenamiento)
    <div class="field"><span class="label">Almacenamiento:</span><span class="value">{{ $tiEquipment->almacenamiento }}</span></div>
    @endif
    @if($tiEquipment->sistema_operativo)
    <div class="field"><span class="label">Sistema operativo:</span><span class="value">{{ $tiEquipment->sistema_operativo }}</span></div>
    @endif
    @if($tiEquipment->fecha_compra)
    <div class="field"><span class="label">Fecha de compra:</span><span class="value">{{ $tiEquipment->fecha_compra->format('d/m/Y') }}</span></div>
    @endif
</div>

@if($tiEquipment->peripherals->isNotEmpty())
<h2>Periféricos</h2>
<table>
    <thead>
        <tr>
            <th>Código</th>
            <th>Tipo</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>No. Serie</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tiEquipment->peripherals as $p)
        <tr>
            <td><span class="badge badge-blue">{{ $p->codigo ?? '—' }}</span></td>
            <td>{{ $p->tipo }}</td>
            <td>{{ $p->marca ?? '—' }}</td>
            <td>{{ $p->modelo ?? '—' }}</td>
            <td>{{ $p->numero_serie ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if($tiEquipment->licenses->isNotEmpty())
<h2>Licencias de Software</h2>
<table>
    <thead>
        <tr>
            <th>Software</th>
            <th>Tipo</th>
            <th>Proveedor</th>
            <th>Vencimiento</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tiEquipment->licenses as $lic)
        <tr>
            <td>{{ $lic->software }}</td>
            <td>{{ $lic->tipo }}</td>
            <td>{{ $lic->proveedor ?? '—' }}</td>
            <td>{{ $lic->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if($tiEquipment->notas)
<h2>Notas</h2>
<p style="padding:6px 0;border-bottom:1px dotted #ddd;">{{ $tiEquipment->notas }}</p>
@endif

<div class="firma-section">
    <div class="firma-box">
        <div style="height:50px;"></div>
        <div class="firma-line">Firma del responsable</div>
        <div style="font-size:11px;color:#555;">{{ $tiEquipment->assignedUser?->full_name ?? 'Nombre' }}</div>
    </div>
    <div class="firma-box">
        <div style="height:50px;"></div>
        <div class="firma-line">Entregó</div>
        <div style="font-size:11px;color:#555;">Depto. de Tecnologías de Información</div>
    </div>
    <div class="firma-box">
        <div style="height:50px;"></div>
        <div class="firma-line">Autorizó</div>
        <div style="font-size:11px;color:#555;">Gerencia General</div>
    </div>
</div>

<div class="footer">
    Documento generado el {{ now()->format('d/m/Y H:i') }} — CopyMart ERP
</div>

</body>
</html>
