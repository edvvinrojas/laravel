<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username', 'email', 'password', 'full_name', 'avatar',
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
        $perms = $this->permissions ?? [];

        if (!is_array($perms) || $permission === '') {
            return false;
        }

        // Soporta permisos granulares en formato "area.action".
        if (str_contains($permission, '.')) {
            [$area, $action] = explode('.', $permission, 2);
            return !empty($perms[$area])
                && is_array($perms[$area])
                && !empty($perms[$area][$action]);
        }

        // Compatibilidad: permiso de area completa equivale a tener "view".
        return !empty($perms[$permission])
            && is_array($perms[$permission])
            && !empty($perms[$permission]['view']);
    }

    public function hasFullRhAccess(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $rhPerms = ($this->permissions ?? [])['recursos_humanos'] ?? null;

        if (!is_array($rhPerms)) {
            return false;
        }

        return !empty($rhPerms['view'])
            && !empty($rhPerms['create'])
            && !empty($rhPerms['edit'])
            && !empty($rhPerms['delete']);
    }

    public function employee()       { return $this->hasOne(Employee::class); }
    public function managedEmployees(){ return $this->hasMany(Employee::class, 'direct_manager_user_id'); }
    public function notifications()  { return $this->hasMany(Notification::class); }
    public function auditLogs()      { return $this->hasMany(AuditLog::class); }
    public function clients()        { return $this->hasMany(Client::class, 'user_id'); }
    public function monthlyPlans()   { return $this->belongsToMany(MonthlyPlan::class, 'monthly_plan_users'); }
}
