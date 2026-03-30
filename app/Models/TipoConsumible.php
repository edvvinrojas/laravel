<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoConsumible extends Model
{
    protected $table = 'tipos_consumible';

    public $timestamps = false;

    protected $fillable = ['nombre', 'codigo', 'descripcion'];

    public function consumibles() { return $this->hasMany(CatalogoConsumible::class, 'tipo_id'); }
}
