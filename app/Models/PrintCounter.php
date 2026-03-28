<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintCounter extends Model
{
    protected $table = 'print_counters';

    protected $fillable = [
        'rent_id', 'billing_id', 'period_month', 'period_year',
        'bn_previous', 'bn_current', 'bn_printed', 'bn_included', 'bn_excess',
        'bn_cost_per_page', 'bn_excess_amount',
        'color_previous', 'color_current', 'color_printed', 'color_included', 'color_excess',
        'color_cost_per_page', 'color_excess_amount', 'total_excess_amount',
        'counter_photo_url', 'notes', 'reading_date', 'is_billed', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_billed'  => 'boolean',
        'is_active'  => 'boolean',
        'reading_date' => 'date',
    ];

    public function rent()    { return $this->belongsTo(Rent::class); }
    public function billing() { return $this->belongsTo(Billing::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
