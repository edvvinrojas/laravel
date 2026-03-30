<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaEquipo extends Model
{
    protected $table = 'categorias_equipo';

    protected $fillable = ['nombre', 'codigo', 'descripcion', 'es_activo'];

    protected $casts = ['es_activo' => 'boolean'];

    public function modelos() { return $this->hasMany(ModeloEquipo::class, 'categoria_id'); }
    public function equipos()  { return $this->hasMany(Item::class, 'categoria_id'); }
}
