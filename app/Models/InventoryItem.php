<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'item_code', 'catalog_id', 'section', 'shelf_id', 'quality',
        'entry_date', 'supplier_id', 'invoice', 'cost',
        'is_available', 'comments', 'is_active',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'is_active'    => 'boolean',
        'cost'         => 'decimal:2',
        'entry_date'   => 'date',
    ];

    public function catalog()  { return $this->belongsTo(ItemCatalog::class, 'catalog_id'); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function shelf()    { return $this->belongsTo(Shelf::class); }
    public function items()    { return $this->belongsToMany(Item::class, 'inventory_equipment', 'inventory_id', 'item_id'); }
    public function sales()    { return $this->belongsToMany(Sale::class, 'sale_inventory', 'inventory_id', 'sale_id')->withTimestamps(); }
}
