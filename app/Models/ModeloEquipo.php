<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModeloEquipo extends Model
{
    protected $table = 'modelos_equipo';

    protected $fillable = [
        'marca_id', 'categoria_id', 'nombre_modelo', 'nombre_comercial',
        'tipo_color', 'tecnologia', 'formato_max',
        'velocidad_bn_ppm', 'velocidad_color_ppm', 'vida_util_paginas',
        'tiene_escaner', 'tiene_fax', 'tiene_duplex', 'tiene_red', 'tiene_wifi',
        'descripcion', 'es_activo',
    ];

    protected $casts = [
        'tiene_escaner' => 'boolean',
        'tiene_fax'     => 'boolean',
        'tiene_duplex'  => 'boolean',
        'tiene_red'     => 'boolean',
        'tiene_wifi'    => 'boolean',
        'es_activo'     => 'boolean',
    ];

    public function marca()      { return $this->belongsTo(Brand::class, 'marca_id'); }
    public function categoria()  { return $this->belongsTo(CategoriaEquipo::class, 'categoria_id'); }
    public function equipos()    { return $this->hasMany(Item::class, 'modelo_id'); }
    public function consumibles()
    {
        return $this->belongsToMany(
            CatalogoConsumible::class,
            'compatibilidad_consumible_modelo',
            'modelo_id',
            'consumible_id'
        )->withPivot('es_oficial', 'notas')->withTimestamps();
    }
}
