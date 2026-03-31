<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TiPeripheral extends Model
{
    protected $table = 'ti_peripherals';

    protected $fillable = [
        'ti_equipment_id', 'tipo', 'marca', 'modelo', 'numero_serie', 'notas',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(TiEquipment::class, 'ti_equipment_id');
    }
}
