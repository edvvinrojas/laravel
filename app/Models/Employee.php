<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id', 'nombre', 'departamento', 'puesto', 'sueldo',
        'nss', 'rfc', 'curp',
        'birthday', 'hire_date', 'phone_emergency', 'contact_emergency', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'birthday'   => 'date',
        'hire_date'  => 'date',
        'sueldo'     => 'decimal:2',
    ];

    public function user()                 { return $this->belongsTo(User::class); }
    public function payrolls()             { return $this->hasMany(Payroll::class); }
    public function vacations()            { return $this->hasMany(Vacation::class); }
    public function administrativeRecords(){ return $this->hasMany(AdministrativeRecord::class); }
    public function absences()             { return $this->hasMany(Absence::class); }

    public function yearsOfService(): int
    {
        if (!$this->hire_date) return 0;
        return (int) $this->hire_date->diffInYears(now());
    }

    public function vacationDaysEntitlement(): int
    {
        $years = $this->yearsOfService();
        if ($years < 1) return 0;
        // LFT: año 1=12, año 2=14, año 3=16, año 4=18, años 5+=20 (+2 cada 5 años)
        if ($years <= 4) return 10 + ($years * 2);
        return 20 + (floor(($years - 5) / 5) * 2);
    }

    public function vacationDaysUsed(): int
    {
        return (int) $this->vacations()->whereIn('status', ['APROBADO', 'ACTIVO'])->sum('vacation_days');
    }

    public function vacationDaysRemaining(): int
    {
        return max(0, $this->vacationDaysEntitlement() - $this->vacationDaysUsed());
    }
}
