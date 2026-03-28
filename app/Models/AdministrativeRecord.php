<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrativeRecord extends Model
{
    protected $fillable = [
        'employee_id', 'type_administrative', 'suspended_days',
        'start_date', 'end_date', 'file_path', 'description', 'issued_by',
    ];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function issuedBy() { return $this->belongsTo(User::class, 'issued_by'); }
}
