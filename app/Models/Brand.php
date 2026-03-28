<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name', 'prefix'];

    public function items()       { return $this->hasMany(Item::class); }
    public function catalogItems(){ return $this->hasMany(ItemCatalog::class); }
}
