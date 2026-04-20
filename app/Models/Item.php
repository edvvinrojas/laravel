<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'sku',
        'brand_id',
        'model',
        'serie',
        'model_toner',
        'type',
        'supplier_id',
        'invoice',
        'cost',
        'location_status',
        'comments',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'cost'      => 'decimal:2',
    ];

    public function brand()     { return $this->belongsTo(Brand::class); }
    public function supplier()  { return $this->belongsTo(Supplier::class); }
    public function rents()     { return $this->belongsToMany(Rent::class, 'rent_item')->withPivot(['branch_id', 'area_id', 'contador_inicial_bn', 'contador_inicial_color', 'has_print_service', 'bn_included', 'bn_cost_per_excess', 'color_included', 'color_cost_per_excess'])->withTimestamps(); }
    public function sales()     { return $this->belongsToMany(Sale::class, 'sale_item')->withPivot(['branch_id', 'area_id'])->withTimestamps(); }
    public function repairs()   { return $this->hasMany(Repair::class); }
    public function inventory() { return $this->belongsToMany(InventoryItem::class, 'inventory_equipment', 'item_id', 'inventory_id'); }
}
