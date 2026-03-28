<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $fillable = [
        'employee_id', 'absence_type', 'start_date', 'end_date',
        'is_justified', 'justification', 'file_path', 'status', 'notes', 'reviewed_by',
    ];

    protected $casts = [
        'is_justified' => 'boolean',
        'start_date'   => 'date',
        'end_date'     => 'date',
    ];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function reviewedBy() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
