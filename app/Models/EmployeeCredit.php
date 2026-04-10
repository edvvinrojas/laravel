<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCredit extends Model
{
    protected $fillable = [
        'employee_id',
        'credit_amount',
        'credit_reason',
        'biweekly_discount',
        'pending_amount',
        'pending_biweeks',
        'approval_date',
        'payment_end_date',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'credit_amount' => 'decimal:2',
        'biweekly_discount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
        'approval_date' => 'date',
        'payment_end_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
