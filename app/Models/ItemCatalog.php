<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCatalog extends Model
{
    protected $table = 'item_catalog';

    protected $fillable = [
        'item_name', 'description', 'item_type', 'brand_id',
        'color', 'usage', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function brand()         { return $this->belongsTo(Brand::class); }
    public function inventoryItems(){ return $this->hasMany(InventoryItem::class, 'catalog_id'); }
    public function stock()         { return $this->hasOne(ItemStock::class, 'catalog_id'); }
}
