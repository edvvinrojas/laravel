<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TiLicense extends Model
{
    protected $table = 'ti_licenses';

    protected $fillable = [
        'software', 'tipo', 'clave_licencia', 'proveedor',
        'fecha_vencimiento', 'cantidad_licencias', 'is_active', 'notas', 'created_by',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'is_active'         => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(TiEquipment::class, 'ti_equipment_license', 'ti_license_id', 'ti_equipment_id')->withTimestamps();
    }
}
