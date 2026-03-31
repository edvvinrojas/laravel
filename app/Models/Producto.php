<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'nombre', 'codigo', 'brand_id', 'categoria',
        'tipo_color', 'formato_max', 'descripcion',
        'precio_venta', 'precio_renta', 'es_activo',
    ];

    protected $casts = [
        'es_activo'    => 'boolean',
        'precio_venta' => 'decimal:2',
        'precio_renta' => 'decimal:2',
    ];

    public function marca()       { return $this->belongsTo(Brand::class, 'brand_id'); }
    public function equipos()     { return $this->hasMany(Item::class, 'producto_id'); }
    public function stock()       { return $this->hasOne(Stock::class, 'referencia_id')->where('tipo', 'PRODUCTO'); }

    public function accesorios()
    {
        return $this->belongsToMany(Accesorio::class, 'producto_accesorio', 'producto_id', 'accesorio_id')
                    ->withPivot('es_incluido', 'notas')
                    ->withTimestamps();
    }

    public function consumibles()
    {
        return $this->belongsToMany(Consumible::class, 'producto_consumible', 'producto_id', 'consumible_id')
                    ->withPivot('es_oficial', 'notas')
                    ->withTimestamps();
    }
}
