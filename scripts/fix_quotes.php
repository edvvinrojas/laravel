<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Quote;

$rows = [
    ['number' => 'COT-2026-001', 'total' => 45000, 'notes' => 'Impresoras HP para oficina administrativa'],
    ['number' => 'COT-2026-002', 'total' => 38000, 'notes' => 'Propuesta equipos Canon para archivo central'],
    ['number' => 'COT-2026-003', 'total' => 52000, 'notes' => 'Renovacion flota Kyocera departamento TI'],
    ['number' => 'COT-2026-004', 'total' => 63000, 'notes' => 'Equipos Ricoh para area de produccion'],
    ['number' => 'COT-2026-005', 'total' => 71000, 'notes' => 'Solucion color Epson para diseno grafico'],
];

foreach ($rows as $i => $r) {
    $q = Quote::firstOrCreate(
        ['quote_number' => $r['number']],
        [
            'client_id' => 1,
            'status' => 'ENVIADA',
            'total' => $r['total'],
            'valid_until' => now()->addDays(30)->toDateString(),
            'notes' => $r['notes'],
            'created_by' => 1,
        ]
    );

    if ($i < 3) {
        $q->status = 'APROBADA';
    } else {
        $q->status = 'ENVIADA';
    }

    $q->save();
    echo $q->quote_number . ' => ' . $q->status . PHP_EOL;
}

echo 'OK' . PHP_EOL;
