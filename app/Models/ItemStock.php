<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemStock extends Model
{
    protected $table = 'item_stock';
    protected $fillable = ['catalog_id', 'stock_min', 'stock_max'];

    public function catalog() { return $this->belongsTo(ItemCatalog::class, 'catalog_id'); }
}
