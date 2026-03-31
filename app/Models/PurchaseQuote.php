<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseQuote extends Model
{
    protected $table = 'purchase_quotes';

    protected $fillable = ['purchase_id', 'supplier_name', 'cost', 'notes'];

    protected $casts = ['cost' => 'decimal:2'];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
