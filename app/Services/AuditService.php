<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(string $action, string $module, ?int $recordId = null, ?string $detail = null): void
    {
        try {
            AuditLog::create([
                'user_id'    => Auth::id(),
                'action'     => strtoupper($action),
                'module'     => $module,
                'record_id'  => $recordId,
                'detail'     => $detail,
                'ip_address' => Request::ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Nunca romper la app por un fallo de auditoría
        }
    }
}
