<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id', 'salary', 'pay_day', 'bonus', 'commission', 'credit_discount', 'total_pay', 'net_pay', 'status',
    ];

    protected $casts = [
        'pay_day'    => 'date',
        'salary'     => 'decimal:2',
        'bonus'      => 'decimal:2',
        'commission' => 'decimal:2',
        'credit_discount' => 'decimal:2',
        'total_pay'  => 'decimal:2',
        'net_pay'    => 'decimal:2',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
}
