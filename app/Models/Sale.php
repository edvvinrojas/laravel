<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'client_id', 'branch_id', 'area_id', 'item_id',
        'invoice_number', 'sale_status', 'sale_price',
        'is_foreign', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_foreign' => 'boolean',
        'is_active'  => 'boolean',
        'sale_price' => 'decimal:2',
    ];

    public function client()   { return $this->belongsTo(Client::class); }
    public function branch()   { return $this->belongsTo(Branch::class); }
    public function area()     { return $this->belongsTo(Area::class); }
    public function item()     { return $this->belongsTo(Item::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function billings() { return $this->hasMany(Billing::class); }
}
