<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoConsumible extends Model
{
    protected $table = 'catalogo_consumibles';

    protected $fillable = [
        'tipo_id', 'marca_id', 'codigo_oem', 'codigo_alternativo',
        'nombre', 'color', 'rendimiento_paginas', 'rendimiento_paginas_alt',
        'es_original', 'descripcion', 'es_activo',
    ];

    protected $casts = [
        'es_original' => 'boolean',
        'es_activo'   => 'boolean',
    ];

    public function tipo()  { return $this->belongsTo(TipoConsumible::class, 'tipo_id'); }
    public function marca() { return $this->belongsTo(Brand::class, 'marca_id'); }
    public function modelos()
    {
        return $this->belongsToMany(
            ModeloEquipo::class,
            'compatibilidad_consumible_modelo',
            'consumible_id',
            'modelo_id'
        )->withPivot('es_oficial', 'notas')->withTimestamps();
    }
}
