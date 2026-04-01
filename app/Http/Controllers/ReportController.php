<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Client;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Rent;
use App\Models\Repair;
use App\Models\Sale;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // ── KPIs ──
        $summary = [
            'clients'            => Client::count(),
            'sales_total'        => Sale::count(),
            'sales_this_month'   => Sale::whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)->count(),
            'rents_total'        => Rent::count(),
            'rents_active'       => Rent::where('contract_status', 'VIGENTE')->count(),
            'billing_overdue'    => Billing::where('status', 'VENCIDO')->count(),
            'billing_pending'    => Billing::where('status', 'PENDIENTE')->count(),
            'tickets_open'       => Ticket::whereNotIn('report_status', ['LISTO'])->count(),
            'equipment_total'    => Item::count(),
            'repairs_pending'    => Repair::where('estatus', 'PENDIENTE')->count(),
            'purchases_active'   => Purchase::whereNotIn('status', ['CONCLUIDO', 'RECHAZADO', 'ENTREGADO'])->count(),
        ];

        // ── Ventas por mes (últimos 12 meses) ──
        $salesByMonth = Sale::select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // ── Rentas por estado ──
        $rentsByStatus = Rent::select('contract_status as status', DB::raw('COUNT(*) as count'))
            ->groupBy('contract_status')
            ->get();

        // ── Cobranza (billing aging) ──
        $billingAging = [
            'pagado'   => Billing::where('status', 'PAGADO')->count(),
            'vigente'  => Billing::where('status', 'PENDIENTE')
                            ->where('due_date', '>=', now()->toDateString())->count(),
            'vencido'  => Billing::where(function ($query) {
                                $query->where('status', 'VENCIDO')
                                    ->orWhere(fn ($q) => $q->where('status', 'PENDIENTE')
                                        ->where('due_date', '<', now()->toDateString()));
                            })->count(),
        ];

        // ── Tickets por tipo ──
        $ticketsByType = Ticket::select('report_type as type', DB::raw('COUNT(*) as count'))
            ->groupBy('report_type')
            ->get();

        // ── Equipos por ubicación ──
        $equipmentByLocation = Item::select('location_status as location', DB::raw('COUNT(*) as count'))
            ->groupBy('location_status')
            ->get();

        return view('reports.index', compact(
            'summary',
            'salesByMonth',
            'rentsByStatus',
            'billingAging',
            'ticketsByType',
            'equipmentByLocation',
        ));
    }
}
