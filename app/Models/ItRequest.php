<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItRequest extends Model
{
    protected $table = 'it_requests';

    protected $fillable = [
        'folio', 'user_id', 'category', 'title', 'description',
        'priority', 'status', 'assigned_to', 'resolution_notes',
        'resolved_at', 'is_active',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
