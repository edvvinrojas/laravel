<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TiEquipment extends Model
{
    protected $table = 'ti_equipment';

    protected $fillable = [
        'codigo_interno', 'marca', 'modelo', 'numero_serie', 'tipo',
        'procesador', 'ram', 'almacenamiento', 'sistema_operativo',
        'assigned_user_id', 'ubicacion', 'status', 'fecha_compra',
        'notas', 'is_active', 'created_by',
    ];

    protected $casts = [
        'fecha_compra' => 'date',
        'is_active'    => 'boolean',
    ];

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function peripherals(): HasMany
    {
        return $this->hasMany(TiPeripheral::class, 'ti_equipment_id');
    }

    public function licenses(): BelongsToMany
    {
        return $this->belongsToMany(TiLicense::class, 'ti_equipment_license', 'ti_equipment_id', 'ti_license_id')->withTimestamps();
    }
}
