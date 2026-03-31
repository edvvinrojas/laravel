<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stock';

    protected $fillable = [
        'tipo', 'referencia_id',
        'cantidad_disponible', 'cantidad_minima',
        'costo', 'ubicacion',
    ];

    protected $casts = [
        'costo' => 'decimal:2',
    ];

    /**
     * Devuelve el nombre del elemento según el tipo.
     */
    public function getNombreAttribute(): string
    {
        return match($this->tipo) {
            'PRODUCTO'   => Producto::find($this->referencia_id)?->nombre ?? '—',
            'ACCESORIO'  => Accesorio::find($this->referencia_id)?->nombre ?? '—',
            'CONSUMIBLE' => Consumible::find($this->referencia_id)?->nombre ?? '—',
            default      => '—',
        };
    }

    public function getCodigoAttribute(): string
    {
        return match($this->tipo) {
            'PRODUCTO'   => Producto::find($this->referencia_id)?->codigo ?? '—',
            'ACCESORIO'  => Accesorio::find($this->referencia_id)?->codigo ?? '—',
            'CONSUMIBLE' => Consumible::find($this->referencia_id)?->codigo_oem ?? '—',
            default      => '—',
        };
    }

    /** ¿Está por debajo del mínimo? */
    public function getBajoMinimoAttribute(): bool
    {
        return $this->cantidad_disponible < $this->cantidad_minima;
    }
}
