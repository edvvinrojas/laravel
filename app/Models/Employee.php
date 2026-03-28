<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id', 'nombre', 'nss', 'rfc', 'curp',
        'birthday', 'hire_date', 'phone_emergency', 'contact_emergency', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'birthday'   => 'date',
        'hire_date'  => 'date',
    ];

    public function user()                 { return $this->belongsTo(User::class); }
    public function payrolls()             { return $this->hasMany(Payroll::class); }
    public function vacations()            { return $this->hasMany(Vacation::class); }
    public function administrativeRecords(){ return $this->hasMany(AdministrativeRecord::class); }
    public function absences()             { return $this->hasMany(Absence::class); }
}
