<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    protected $fillable = [
        'item_id', 'fecha_alta',
        'procedencia', 'estado_taller', 'fecha_conclusion',
        'folio_escaneado', 'foto_evidencia', 'ubicacion', 'proceso',
        'estatus', 'diagnostico_inicial', 'comments', 'is_active',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'fecha_alta'       => 'datetime',
        'fecha_conclusion' => 'datetime',
    ];

    public function item() { return $this->belongsTo(Item::class); }
}
