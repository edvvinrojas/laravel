<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'client_id', 'quote_number', 'status', 'notes', 'valid_until', 'total', 'created_by',
    ];

    protected $casts = [
        'total'       => 'decimal:2',
        'valid_until' => 'date',
    ];

    public function client()   { return $this->belongsTo(Client::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function lines()    { return $this->hasMany(QuoteLine::class); }
}
