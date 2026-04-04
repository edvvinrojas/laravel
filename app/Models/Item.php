<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'sku', 'brand_id',
        'model', 'serie', 'model_toner', 'type',
        'supplier_id', 'invoice', 'cost',
        'location_status',
        'comments', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'cost'      => 'decimal:2',
    ];

    public function brand()     { return $this->belongsTo(Brand::class); }
    public function supplier()  { return $this->belongsTo(Supplier::class); }
    public function rents()     { return $this->hasMany(Rent::class); }
    public function sales()     { return $this->hasMany(Sale::class); }
    public function repairs()   { return $this->hasMany(Repair::class); }
    public function inventory() { return $this->belongsToMany(InventoryItem::class, 'inventory_equipment', 'item_id', 'inventory_id'); }
}
