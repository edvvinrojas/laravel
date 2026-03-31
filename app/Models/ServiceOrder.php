<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    protected $fillable = [
        'engineer_id', 'client_id', 'branch_id', 'area_id', 'item_id',
        'tipo_orden', 'se_reviso', 'diagnostico_accion',
        'entrego_toner', 'codigos_toner',
        'pct_toner_negro', 'pct_toner_cyan', 'pct_toner_magenta', 'pct_toner_amarillo',
        'evidencia_foto', 'pendiente_material',
        'tiene_stock', 'foto_stock',
        'firma_nombre', 'firma_imagen',
        'queda_pendiente', 'descripcion_pendiente',
        'pagina_estado_foto', 'status', 'created_by',
    ];

    protected $casts = [
        'se_reviso'       => 'array',
        'entrego_toner'   => 'boolean',
        'tiene_stock'     => 'boolean',
        'queda_pendiente' => 'boolean',
    ];

    public function engineer() { return $this->belongsTo(User::class, 'engineer_id'); }
    public function client()   { return $this->belongsTo(Client::class); }
    public function branch()   { return $this->belongsTo(Branch::class); }
    public function area()     { return $this->belongsTo(Area::class); }
    public function item()     { return $this->belongsTo(Item::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
}
