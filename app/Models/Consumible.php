<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consumible extends Model
{
    protected $table = 'consumibles';

    protected $fillable = [
        'nombre', 'codigo_oem', 'codigo_alternativo', 'brand_id',
        'tipo', 'color', 'rendimiento_paginas',
        'es_original', 'descripcion', 'es_activo',
    ];

    protected $casts = [
        'es_original' => 'boolean',
        'es_activo'   => 'boolean',
    ];

    public function marca()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_consumible', 'consumible_id', 'producto_id')
                    ->withPivot('es_oficial', 'notas')
                    ->withTimestamps();
    }

    public function stock()
    {
        return $this->hasOne(Stock::class, 'referencia_id')->where('tipo', 'CONSUMIBLE');
    }
}
