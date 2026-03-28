<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'client_id', 'branch_id', 'area_id', 'report_status',
        'report_type', 'description', 'evidence', 'corrective_action',
        'created_by', 'completed_at',
    ];

    protected $casts = ['completed_at' => 'datetime'];

    public function client()  { return $this->belongsTo(Client::class); }
    public function branch()  { return $this->belongsTo(Branch::class); }
    public function area()    { return $this->belongsTo(Area::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
