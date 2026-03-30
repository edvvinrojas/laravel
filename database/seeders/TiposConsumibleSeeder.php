<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposConsumibleSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Tóner',              'codigo' => 'TONER',  'descripcion' => 'Cartuchos de tóner para impresoras láser'],
            ['nombre' => 'Tambor / Drum',      'codigo' => 'DRUM',   'descripcion' => 'Unidad de tambor fotoconductor'],
            ['nombre' => 'Kit de mantenimiento','codigo' => 'MK',     'descripcion' => 'Kit de mantenimiento con rodillo y fusor'],
            ['nombre' => 'Fusor',              'codigo' => 'FUSOR',  'descripcion' => 'Unidad fusora'],
            ['nombre' => 'Rodillo de recogida','codigo' => 'RODILLO','descripcion' => 'Rodillo de recogida / alimentación de papel'],
            ['nombre' => 'Cartucho de tinta',  'codigo' => 'TINTA',  'descripcion' => 'Cartucho de tinta para impresoras inkjet'],
            ['nombre' => 'Unidad de imagen',   'codigo' => 'IU',     'descripcion' => 'Unidad de imagen (imaging unit)'],
            ['nombre' => 'Banda de transferencia','codigo' => 'BELT', 'descripcion' => 'Correa / banda de transferencia de imagen'],
            ['nombre' => 'Filtro de ozono',    'codigo' => 'OZONE',  'descripcion' => 'Filtro de ozono para copiadoras'],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipos_consumible')->updateOrInsert(
                ['codigo' => $tipo['codigo']],
                $tipo
            );
        }
    }
}
