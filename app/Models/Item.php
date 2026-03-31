<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'sku', 'brand_id', 'producto_id',
        'model', 'serie', 'model_toner', 'type',
        'supplier_id', 'invoice', 'cost',
        'fecha_compra', 'fecha_instalacion', 'fecha_garantia_fin',
        'location_status', 'ubicacion_fisica',
        'contador_inicial_bn', 'contador_inicial_color', 'contador_inicial_scan',
        'direccion_ip', 'mac_address',
        'comments', 'is_active',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'cost'               => 'decimal:2',
        'fecha_compra'       => 'date',
        'fecha_instalacion'  => 'date',
        'fecha_garantia_fin' => 'date',
    ];

    public function brand()     { return $this->belongsTo(Brand::class); }
    public function supplier()  { return $this->belongsTo(Supplier::class); }
    public function producto()  { return $this->belongsTo(Producto::class, 'producto_id'); }
    public function rents()     { return $this->hasMany(Rent::class); }
    public function sales()     { return $this->hasMany(Sale::class); }
    public function repairs()   { return $this->hasMany(Repair::class); }
    public function inventory() { return $this->belongsToMany(InventoryItem::class, 'inventory_equipment', 'item_id', 'inventory_id'); }
}
