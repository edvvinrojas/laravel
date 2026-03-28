<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'company', 'rol',
        'latitude', 'longitude', 'is_client', 'is_active',
    ];

    protected $casts = [
        'is_client' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function client() { return $this->hasOne(Client::class); }
}
