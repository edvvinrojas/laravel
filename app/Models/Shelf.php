<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    protected $table = 'shelves';
    protected $fillable = ['name', 'section', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function inventoryItems() { return $this->hasMany(InventoryItem::class, 'shelf_id'); }
}
