<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rent extends Model
{
    protected $fillable = [
        'contract_number', 'client_id', 'branch_id', 'area_id', 'item_id',
        'rent', 'contract_status', 'start_date', 'end_date', 'is_foreign',
        'has_print_service', 'bn_included', 'bn_cost_per_excess',
        'color_included', 'color_cost_per_excess', 'print_notes',
        'is_active', 'created_by',
        'contador_inicial_bn', 'contador_inicial_color',
    ];

    protected $casts = [
        'is_foreign'         => 'boolean',
        'has_print_service'  => 'boolean',
        'is_active'          => 'boolean',
        'rent'               => 'decimal:2',
        'start_date'         => 'date',
        'end_date'           => 'date',
    ];

    public function client()        { return $this->belongsTo(Client::class); }
    public function branch()        { return $this->belongsTo(Branch::class); }
    public function area()          { return $this->belongsTo(Area::class); }
    public function item()          { return $this->belongsTo(Item::class); }
    public function creator()       { return $this->belongsTo(User::class, 'created_by'); }
    public function billings()      { return $this->hasMany(Billing::class); }
    public function printCounters() { return $this->hasMany(PrintCounter::class); }
    public function accesorios()    { return $this->belongsToMany(Accesorio::class, 'rent_accesorio')->withPivot('cantidad','notas')->withTimestamps(); }
    public function consumibles()   { return $this->belongsToMany(Consumible::class, 'rent_consumible')->withPivot('cantidad','notas')->withTimestamps(); }
}
