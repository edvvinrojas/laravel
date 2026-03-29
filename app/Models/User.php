<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username', 'email', 'password', 'full_name',
        'rol', 'department', 'is_active', 'is_hidden', 'permissions',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'   => 'hashed',
            'is_active'  => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->rol, (array) $roles);
    }

    public function isAdmin(): bool
    {
        return $this->rol === 'administrador';
    }

    public function isGerencia(): bool
    {
        return in_array($this->rol, ['administrador', 'gerencia']);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) return true;
        $perms = $this->permissions ?? [];
        return in_array($permission, $perms) || ($perms[$permission] ?? false);
    }

    public function employee()       { return $this->hasOne(Employee::class); }
    public function notifications()  { return $this->hasMany(Notification::class); }
    public function auditLogs()      { return $this->hasMany(AuditLog::class); }
    public function clients()        { return $this->hasMany(Client::class, 'user_id'); }
    public function monthlyPlans()   { return $this->belongsToMany(MonthlyPlan::class, 'monthly_plan_users'); }
}
