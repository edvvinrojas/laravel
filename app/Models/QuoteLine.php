<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteLine extends Model
{
    protected $table = 'quote_lines';

    protected $fillable = [
        'quote_id', 'product_type', 'product_id',
        'description', 'quantity', 'unit_price', 'total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total'      => 'decimal:2',
        'quantity'   => 'integer',
    ];

    public function quote() { return $this->belongsTo(Quote::class); }

    public function product()
    {
        return match ($this->product_type) {
            'item'      => $this->belongsTo(Item::class, 'product_id'),
            'sparepart' => $this->belongsTo(Sparepart::class, 'product_id'),
            'inventory' => $this->belongsTo(InventoryItem::class, 'product_id'),
            default     => null,
        };
    }
}
