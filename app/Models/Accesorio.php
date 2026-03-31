<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accesorio extends Model
{
    protected $table = 'accesorios';

    protected $fillable = ['nombre', 'codigo', 'descripcion', 'precio', 'es_activo'];

    protected $casts = [
        'es_activo' => 'boolean',
        'precio'    => 'decimal:2',
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_accesorio', 'accesorio_id', 'producto_id')
                    ->withPivot('es_incluido', 'notas')
                    ->withTimestamps();
    }

    public function stock()
    {
        return $this->hasOne(Stock::class, 'referencia_id')->where('tipo', 'ACCESORIO');
    }
}
