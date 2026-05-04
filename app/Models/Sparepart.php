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
        'unit_price',
        'total_price',
        'invoice_number',
    ];

    protected $casts = [
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function purchases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /** Devuelve el nombre de la marca (FK si existe, texto si no). */
    public function getBrandNameAttribute(): ?string
    {
        $value = trim((string) $this->brand);
        if ($value === '') {
            return null;
        }

        if (in_array($value, ['+ Agregar nueva marca...', '+ Agregar nueva marca…'], true)) {
            return null;
        }

        return $value;
    }

    /** Devuelve el nombre del proveedor (FK si existe, texto si no). */
    public function getSupplierNameAttribute(): ?string
    {
        return $this->supplier;
    }
}
