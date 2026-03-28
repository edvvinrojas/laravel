<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    protected $table = 'route_stops';

    protected $fillable = [
        'route_id', 'client_id', 'branch_id', 'stop_order',
        'address', 'city', 'notes', 'is_completed', 'visit_status', 'no_visit_reason',
    ];

    protected $casts = ['is_completed' => 'boolean'];

    public function route()  { return $this->belongsTo(Route::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
}
