<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Item;
use App\Models\Sparepart;
use App\Models\Client;
use App\Models\Sale;
use App\Models\Quote;
use App\Models\QuoteLine;

// ─── 1. EQUIPOS (4 restantes; HP ya fue creado) ─────────────────────────────
$equipos = [
    ['brand_id'=>2, 'model'=>'imageRUNNER 2525i',       'type'=>'MONOCROMO', 'model_toner'=>'C-EXV33',  'serie'=>'CN2026002', 'cost'=>9200],
    ['brand_id'=>3, 'model'=>'ECOSYS M2040dn',           'type'=>'MONOCROMO', 'model_toner'=>'TK-1175',  'serie'=>'KY2026003', 'cost'=>7800],
    ['brand_id'=>4, 'model'=>'IM 350F',                  'type'=>'MONOCROMO', 'model_toner'=>'MP 2501',  'serie'=>'RC2026004', 'cost'=>10500],
    ['brand_id'=>5, 'model'=>'WorkForce Pro WF-C5290',   'type'=>'COLOR',     'model_toner'=>'T02E',     'serie'=>'EP2026005', 'cost'=>12000],
];

echo "=== EQUIPOS ===\n";
foreach ($equipos as $e) {
    if (Item::where('serie', $e['serie'])->exists()) {
        echo "  Ya existe: {$e['serie']}\n";
        continue;
    }
    $item = Item::create([
        'brand_id'        => $e['brand_id'],
        'model'           => $e['model'],
        'type'            => $e['type'],
        'model_toner'     => $e['model_toner'],
        'serie'           => $e['serie'],
        'cost'            => $e['cost'],
        'location_status' => 'BODEGA',
        'is_active'       => true,
    ]);
    echo "  Creado ID:{$item->id} serie:{$e['serie']} marca:{$e['brand_id']}\n";
}

// ─── 2. REFACCIONES (5, marcas distintas) ────────────────────────────────────
$refacciones = [
    ['brand'=>'HP',      'name'=>'Fusor HP 4250',               'code'=>'REF-HP-001', 'unit_price'=>1200, 'total_price'=>6000],
    ['brand'=>'Canon',   'name'=>'Rodillo Transferencia Canon',  'code'=>'REF-CN-001', 'unit_price'=>850,  'total_price'=>6800],
    ['brand'=>'Kyocera', 'name'=>'Kit Mantenimiento Kyocera',    'code'=>'REF-KY-001', 'unit_price'=>1500, 'total_price'=>4500],
    ['brand'=>'Ricoh',   'name'=>'Tambor Ricoh MP2501',          'code'=>'REF-RC-001', 'unit_price'=>2200, 'total_price'=>8800],
    ['brand'=>'Epson',   'name'=>'Cabezal Impresión Epson',      'code'=>'REF-EP-001', 'unit_price'=>3500, 'total_price'=>7000],
];

echo "\n=== REFACCIONES ===\n";
foreach ($refacciones as $r) {
    if (Sparepart::where('code', $r['code'])->exists()) {
        echo "  Ya existe: {$r['code']}\n";
        continue;
    }
    $sp = Sparepart::create([
        'brand'       => $r['brand'],
        'name'        => $r['name'],
        'code'        => $r['code'],
        'unit_price'  => $r['unit_price'],
        'total_price' => $r['total_price'],
        'is_active'   => true,
    ]);
    echo "  Creado ID:{$sp->id} {$r['name']}\n";
}

// ─── 3. VENTAS (5) ────────────────────────────────────────────────────────────
// Necesitamos un cliente y un equipo disponible
$client = Client::first();
if (!$client) {
    echo "\n  ERROR: No hay clientes en la BD. Crea al menos uno.\n";
    exit(1);
}

$items = Item::where('location_status', 'BODEGA')->where('is_active', true)->take(5)->get();
if ($items->count() < 5) {
    echo "\n  AVISO: Solo hay {$items->count()} equipos en bodega para ventas\n";
}

echo "\n=== VENTAS ===\n";
if (Sale::whereIn('invoice_number', ['FAC-2026-001','FAC-2026-002','FAC-2026-003','FAC-2026-004','FAC-2026-005'])->exists()) {
    echo "  Ventas ya existen, omitiendo.\n";
    goto cotizaciones;
}
$ventasData = [
    ['sale_price' => 15000, 'invoice_number' => 'FAC-2026-001'],
    ['sale_price' => 18000, 'invoice_number' => 'FAC-2026-002'],
    ['sale_price' => 14500, 'invoice_number' => 'FAC-2026-003'],
    ['sale_price' => 21000, 'invoice_number' => 'FAC-2026-004'],
    ['sale_price' => 25000, 'invoice_number' => 'FAC-2026-005'],
];

foreach ($ventasData as $i => $vd) {
    $item = $items->get($i);
    if (!$item) {
        echo "  Sin equipo para venta #".($i+1)."\n";
        continue;
    }
    $sale = Sale::create([
        'client_id'      => $client->id,
        'item_id'        => $item->id,
        'sale_price'     => $vd['sale_price'],
        'invoice_number' => $vd['invoice_number'],
        'sale_status'    => 'CONFIRMADA',
        'is_active'      => true,
        'created_by'     => 1,
    ]);
    $sale->items()->attach($item->id, [
        'branch_id' => null,
        'area_id'   => null,
    ]);
    $item->update(['location_status' => 'VENDIDO']);
    echo "  Creada venta ID:{$sale->id} cliente:{$client->name} precio:\${$vd['sale_price']}\n";
}

// ─── 4. COTIZACIONES (5) ──────────────────────────────────────────────────────
cotizaciones:
echo "\n=== COTIZACIONES ===\n";
$cotizaciones = [
    ['notes' => 'Impresoras HP para oficina administrativa',    'total' => 45000],
    ['notes' => 'Propuesta equipos Canon para archivo central', 'total' => 38000],
    ['notes' => 'Renovación flota Kyocera departamento TI',     'total' => 52000],
    ['notes' => 'Equipos Ricoh para área de producción',        'total' => 63000],
    ['notes' => 'Solución color Epson para diseño gráfico',     'total' => 71000],
];

$quoteIds = [];
foreach ($cotizaciones as $i => $c) {
    $quote = Quote::create([
        'client_id'    => $client->id,
        'quote_number' => 'COT-2026-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
        'status'       => 'ENVIADA',
        'total'        => $c['total'],
        'valid_until'  => now()->addDays(30)->toDateString(),
        'notes'        => $c['notes'],
        'created_by'   => 1,
    ]);
    $quoteIds[] = $quote->id;
    echo "  Creada cotización ID:{$quote->id}: {$c['notes']}\n";
}

// ─── 5. APROBAR 3 COTIZACIONES ────────────────────────────────────────────────
echo "\n=== APROBACIONES (3 de 5) ===\n";
foreach (array_slice($quoteIds, 0, 3) as $qid) {
    $quote = Quote::find($qid);
    $quote->update(['status' => 'APROBADA']);
    echo "  Aprobada cotización ID:{$qid}\n";
}

echo "\n✓ Todo completado.\n";
