<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Rent;
use App\Models\Billing;
use App\Models\Ticket;
use App\Models\Item;
use App\Models\Employee;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'clients'         => Client::where('is_active', true)->count(),
            'rents_active'    => Rent::where('contract_status', 'VIGENTE')->count(),
            'billing_pending' => Billing::where('status', 'PENDIENTE')->count(),
            'billing_overdue' => Billing::where('status', 'VENCIDO')->count(),
            'tickets_pending' => Ticket::where('report_status', 'PENDIENTE')->count(),
            'tickets_urgent'  => Ticket::where('report_status', 'URGENTE')->count(),
            'items_total'     => Item::where('is_active', true)->count(),
            'employees'       => Employee::where('is_active', true)->count(),
        ];

        $overdue_billings = Billing::with('client')
            ->where('status', 'VENCIDO')
            ->where('is_active', true)
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $recent_tickets = Ticket::with(['client', 'creator'])
            ->whereIn('report_status', ['PENDIENTE', 'URGENTE'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $today = Carbon::today();
        $upcoming_billings = Billing::with('client')
            ->where('status', 'PENDIENTE')
            ->whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $today->copy()->addDays(7))
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'overdue_billings', 'recent_tickets', 'upcoming_billings'));
    }
}
