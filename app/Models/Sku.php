<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    protected $fillable = [
        'code', 'description', 'category', 'is_used',
    ];

    protected $casts = [
        'is_used' => 'boolean',
    ];
}
