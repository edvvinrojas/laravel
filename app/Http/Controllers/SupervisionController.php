<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Employee;
use App\Models\Quote;
use App\Models\Ticket;
use App\Models\Vacation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->rol === 'administrador';
        $search = trim((string) $request->input('search', ''));

        $managedEmployees = $isAdmin
            ? Employee::query()->where('is_active', '=', true)->orderBy('nombre', 'asc')->get()
            : Employee::query()->where('direct_manager_user_id', '=', $user->id)->where('is_active', '=', true)->orderBy('nombre', 'asc')->get();

        $managedEmployeeIds = $managedEmployees->pluck('id')->filter()->values();
        $managedUserIds = $managedEmployees->pluck('user_id')->filter()->values();

        $vacationsQuery = Vacation::query()
            ->with(['employee.user'])
            ->where('status', 'PENDIENTE')
            ->when($search !== '', fn($q) => $q->whereHas('employee', fn($e) => $e->where('nombre', 'like', "%{$search}%")));

        $vacationsQuery->whereIn('employee_id', $managedEmployeeIds);

        $absencesQuery = Absence::query()
            ->with(['employee.user'])
            ->where('status', 'PENDIENTE')
            ->when($search !== '', fn($q) => $q->whereHas('employee', fn($e) => $e->where('nombre', 'like', "%{$search}%")));

        $absencesQuery->whereIn('employee_id', $managedEmployeeIds);

        $ticketsQuery = Ticket::query()
            ->with(['client', 'creator'])
            ->whereIn('report_status', ['PENDIENTE', 'ATENCION', 'PROGRAMADO'])
            ->when($search !== '', fn($q) => $q->where(function ($sq) use ($search) {
                $sq->where('ticket_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$search}%"));
            }));

        $ticketsQuery->whereIn('created_by', $managedUserIds);

        $quotesQuery = Quote::query()
            ->with(['client', 'creator'])
            ->whereIn('status', ['BORRADOR', 'ENVIADA'])
            ->when($search !== '', fn($q) => $q->where(function ($sq) use ($search) {
                $sq->where('quote_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$search}%"));
            }));

        $quotesQuery->whereIn('created_by', $managedUserIds);

        $stats = [
            'vacations_pending' => (clone $vacationsQuery)->count(),
            'absences_pending' => (clone $absencesQuery)->count(),
            'tickets_open' => (clone $ticketsQuery)->count(),
            'quotes_pending' => (clone $quotesQuery)->count(),
        ];

        $vacations = $vacationsQuery->orderBy('start_date')->limit(25)->get();
        $absences = $absencesQuery->orderBy('start_date')->limit(25)->get();
        $tickets = $ticketsQuery->orderBy('created_at', 'desc')->limit(25)->get();
        $quotes = $quotesQuery->orderBy('created_at', 'desc')->limit(25)->get();

        return view('supervision.requests', compact('vacations', 'absences', 'tickets', 'quotes', 'stats', 'search', 'managedEmployees'));
    }
}
