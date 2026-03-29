<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'client_id', 'name', 'phone', 'email', 'company', 'rol',
        'latitude', 'longitude', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function client() { return $this->belongsTo(Client::class); }
}
