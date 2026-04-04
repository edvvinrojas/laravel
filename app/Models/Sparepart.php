<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    protected $fillable = [
        'name', 'color', 'description',
        'brand',
        'equipment',
        'code',
        'supplier',
    ];

    public function purchases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /** Devuelve el nombre de la marca (FK si existe, texto si no). */
    public function getBrandNameAttribute(): ?string
    {
        return $this->brand;
    }

    /** Devuelve el nombre del proveedor (FK si existe, texto si no). */
    public function getSupplierNameAttribute(): ?string
    {
        return $this->supplier;
    }
}
