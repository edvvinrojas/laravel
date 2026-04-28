<?php

namespace App\Console\Commands;

use App\Models\Billing;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpdateOverdueInvoices extends Command
{
    protected $signature = 'billing:mark-overdue';

    protected $description = 'Marca como VENCIDO las facturas PENDIENTE cuya fecha de vencimiento ya pasó.';

    public function handle(): int
    {
        $today = Carbon::today();

        $count = Billing::where('status', 'PENDIENTE')
            ->where('is_active', true)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->update(['status' => 'VENCIDO']);

        $this->info("Facturas marcadas como VENCIDO: {$count}");

        return self::SUCCESS;
    }
}
