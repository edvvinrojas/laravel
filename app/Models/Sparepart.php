<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sparepart extends Model
{
    protected $fillable = [
        'name', 'color', 'description',
        'brand', 'brand_id',
        'equipment',
        'code', 'internal_code',
        'shelf_id',
        'supplier', 'supplier_id',
    ];

    public function purchases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function brandModel(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function supplierModel(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class);
    }

    /** Devuelve el nombre de la marca (FK si existe, texto si no). */
    public function getBrandNameAttribute(): ?string
    {
        return $this->brandModel?->name ?? $this->brand;
    }

    /** Devuelve el nombre del proveedor (FK si existe, texto si no). */
    public function getSupplierNameAttribute(): ?string
    {
        return $this->supplierModel?->name ?? $this->supplier;
    }
}
