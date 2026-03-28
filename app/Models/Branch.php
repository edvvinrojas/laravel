<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'client_id', 'is_main', 'name', 'address',
        'colonia', 'zip_code', 'city', 'latitude', 'longitude',
    ];

    protected $casts = ['is_main' => 'boolean'];

    public function client()       { return $this->belongsTo(Client::class); }
    public function areas()        { return $this->hasMany(Area::class); }
    public function routeStops()   { return $this->hasMany(RouteStop::class); }
}
