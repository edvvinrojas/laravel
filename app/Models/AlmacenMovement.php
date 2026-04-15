<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlmacenMovement extends Model
{
    protected $fillable = [
        'movement_type',
        'equipment_id',
        'inventory_id',
        'client_id',
        'branch_id',
        'area_id',
        'person_name',
        'reason',
        'created_by',
    ];

    public function equipment()
    {
        return $this->belongsTo(Item::class, 'equipment_id');
    }

    public function inventory()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
