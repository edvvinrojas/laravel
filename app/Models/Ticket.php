<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_code', 'client_id', 'branch_id', 'area_id', 'item_id',
        'report_status', 'report_type', 'priority',
        'description', 'evidence', 'corrective_action',
        'created_by', 'completed_at',
    ];

    protected $casts = ['completed_at' => 'datetime'];

    public function client()  { return $this->belongsTo(Client::class); }
    public function branch()  { return $this->belongsTo(Branch::class); }
    public function area()    { return $this->belongsTo(Area::class); }
    public function item()    { return $this->belongsTo(Item::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public static function generateTicketCode(): string
    {
        $year = date('Y');
        $prefix = "TKT-{$year}-";

        return DB::transaction(function () use ($prefix, $year) {
            $last = static::where('ticket_code', 'like', $prefix.'%')
                ->orderByDesc('ticket_code')
                ->lockForUpdate()
                ->value('ticket_code');

            $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
            return sprintf('TKT-%s-%04d', $year, $next);
        });
    }
}
