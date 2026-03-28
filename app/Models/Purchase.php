<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'sparepart_id', 'user_id', 'name', 'amount', 'authorized_amount',
        'quality', 'justification', 'type',
        'supplier1_name', 'supplier1_cost', 'supplier2_name', 'supplier2_cost',
        'supplier3_name', 'supplier3_cost',
        'authorized_by_area_chief_id', 'authorized_by_area_chief_date',
        'authorized_by_admin_id', 'authorized_by_admin_date',
        'quotation_file', 'supplier_payment_file', 'supplier_invoice_file',
        'is_paid', 'shipping_method', 'shipping_cost', 'shipping_code',
        'status', 'comments', 'end_date',
    ];

    protected $casts = ['is_paid' => 'boolean'];

    public function sparepart()          { return $this->belongsTo(Sparepart::class); }
    public function user()               { return $this->belongsTo(User::class); }
    public function areaChief()          { return $this->belongsTo(User::class, 'authorized_by_area_chief_id'); }
    public function admin()              { return $this->belongsTo(User::class, 'authorized_by_admin_id'); }
}
