<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    protected $fillable = [
        'name', 'color', 'description', 'brand', 'equipment', 'code', 'supplier',
    ];

    public function purchases() { return $this->hasMany(Purchase::class); }
}
