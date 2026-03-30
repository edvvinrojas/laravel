<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasEquipoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Copiadora',   'codigo' => 'COPI',  'descripcion' => 'Equipos de copiado (multifuncionales de alta producción)', 'es_activo' => true],
            ['nombre' => 'Impresora',   'codigo' => 'IMP',   'descripcion' => 'Impresoras de escritorio láser o inkjet',                   'es_activo' => true],
            ['nombre' => 'MFP',         'codigo' => 'MFP',   'descripcion' => 'Multifuncional (imprime, escanea, copia y fax)',             'es_activo' => true],
            ['nombre' => 'Escáner',     'codigo' => 'SCAN',  'descripcion' => 'Escáneres de documento',                                    'es_activo' => true],
            ['nombre' => 'Fax',         'codigo' => 'FAX',   'descripcion' => 'Equipos de fax dedicados',                                  'es_activo' => true],
            ['nombre' => 'Plotter',     'codigo' => 'PLOT',  'descripcion' => 'Impresoras de gran formato',                                'es_activo' => true],
        ];

        foreach ($categorias as $cat) {
            DB::table('categorias_equipo')->updateOrInsert(
                ['codigo' => $cat['codigo']],
                array_merge($cat, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
