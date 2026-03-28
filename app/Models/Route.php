<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'route_code', 'driver_name', 'vehicle', 'status',
        'scheduled_date', 'total_stops', 'completed_stops', 'notes', 'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'scheduled_date' => 'date',
    ];

    public function stops() { return $this->hasMany(RouteStop::class); }
}
