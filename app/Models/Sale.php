<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'client_id', 'branch_id', 'area_id', 'item_id',
        'invoice_number', 'sale_status', 'sale_price',
        'is_foreign', 'services_included', 'services_quantity',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'is_foreign'        => 'boolean',
        'is_active'         => 'boolean',
        'services_included' => 'boolean',
        'sale_price'        => 'decimal:2',
    ];

    public function client()     { return $this->belongsTo(Client::class); }
    public function branch()     { return $this->belongsTo(Branch::class); }
    public function area()       { return $this->belongsTo(Area::class); }
    public function item()       { return $this->belongsTo(Item::class); }
    public function items()      { return $this->belongsToMany(Item::class, 'sale_item')->withPivot(['branch_id', 'area_id'])->withTimestamps(); }
    public function spareparts() { return $this->belongsToMany(Sparepart::class, 'sale_sparepart')->withTimestamps(); }
    public function inventoryItems() { return $this->belongsToMany(InventoryItem::class, 'sale_inventory', 'sale_id', 'inventory_id')->withTimestamps(); }
    public function creator()    { return $this->belongsTo(User::class, 'created_by'); }
    public function billings()   { return $this->hasMany(Billing::class); }
}
