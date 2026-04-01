<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintCounter extends Model
{
    protected $table = 'print_counters';

    protected $fillable = [
        'rent_id', 'billing_id', 'period_month', 'period_year',
        'bn_previous', 'bn_current',
        'color_previous', 'color_current',
        'counter_photo_url', 'notes', 'reading_date',
        'is_billed', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_billed'    => 'boolean',
        'is_active'    => 'boolean',
        'reading_date' => 'date',
    ];

    // ── Relaciones ────────────────────────────────────────────────
    public function rent()    { return $this->belongsTo(Rent::class); }
    public function billing() { return $this->belongsTo(Billing::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    // ── Valores calculados (no se guardan en BD) ──────────────────
    public function getBnPrintedAttribute(): int
    {
        return max(0, $this->bn_current - $this->bn_previous);
    }

    public function getColorPrintedAttribute(): int
    {
        return max(0, $this->color_current - $this->color_previous);
    }

    public function getBnExcessAttribute(): int
    {
        return max(0, $this->bn_printed - ($this->rent?->bn_included ?? 0));
    }

    public function getColorExcessAttribute(): int
    {
        return max(0, $this->color_printed - ($this->rent?->color_included ?? 0));
    }

    public function getBnExcessAmountAttribute(): float
    {
        return $this->bn_excess * (float) ($this->rent?->bn_cost_per_excess ?? 0);
    }

    public function getColorExcessAmountAttribute(): float
    {
        return $this->color_excess * (float) ($this->rent?->color_cost_per_excess ?? 0);
    }

    public function getTotalExcessAmountAttribute(): float
    {
        return $this->bn_excess_amount + $this->color_excess_amount;
    }
}
