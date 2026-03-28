<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'module', 'record_id', 'detail', 'ip_address', 'created_at',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }

    public static function record(string $action, string $module, ?int $recordId = null, ?string $detail = null): void
    {
        static::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'module'     => $module,
            'record_id'  => $recordId,
            'detail'     => $detail,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
