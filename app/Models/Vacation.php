<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacation extends Model
{
    protected $fillable = [
        'employee_id', 'vacation_days', 'start_date', 'end_date',
        'requested_by', 'status', 'remaining_days', 'notes',
    ];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function employee()    { return $this->belongsTo(Employee::class); }
    public function requestedBy() { return $this->belongsTo(User::class, 'requested_by'); }
}
