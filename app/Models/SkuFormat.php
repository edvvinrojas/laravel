<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkuFormat extends Model
{
    protected $fillable = ['prefix', 'pad', 'last_number'];

    /**
     * Generate the next SKU for a given category, incrementing the counter.
     */
    public static function nextSku(string $category): string
    {
        $format = self::where('category', $category)->lockForUpdate()->firstOrFail();
        $format->increment('last_number');

        return $format->prefix . str_pad($format->last_number, $format->pad, '0', STR_PAD_LEFT);
    }

    /**
     * Preview what the next SKU would look like (without incrementing).
     */
    public function preview(): string
    {
        $next = $this->last_number + 1;
        return $this->prefix . str_pad($next, $this->pad, '0', STR_PAD_LEFT);
    }
}
