<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $table = 'billings';

    protected $fillable = [
        'billing_type', 'rent_id', 'sale_id', 'client_id', 'branch_id', 'area_id',
        'invoice_number', 'amount', 'target_date', 'due_date', 'payment_date',
        'status', 'follow_up', 'payment_term', 'payment_day', 'comment',
        'is_active', 'created_by',
        'facturacom_uid', 'facturacom_uuid', 'facturacom_folio',
        'facturacom_status', 'facturacom_synced_at', 'facturacom_last_response',
    ];

    protected $casts = [
        'follow_up'    => 'boolean',
        'is_active'    => 'boolean',
        'amount'       => 'decimal:2',
        'target_date'  => 'date',
        'due_date'     => 'date',
        'payment_date' => 'date',
        'facturacom_synced_at' => 'datetime',
        'facturacom_last_response' => 'array',
    ];

    public function rent()         { return $this->belongsTo(Rent::class); }
    public function sale()         { return $this->belongsTo(Sale::class); }
    public function client()       { return $this->belongsTo(Client::class); }
    public function branch()       { return $this->belongsTo(Branch::class); }
    public function area()         { return $this->belongsTo(Area::class); }
    public function creator()      { return $this->belongsTo(User::class, 'created_by'); }
    public function printCounter() { return $this->hasOne(PrintCounter::class); }
}
