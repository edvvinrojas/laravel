<?php

namespace App\Http\Controllers;

use App\Models\Absence;
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

        $vacationsQuery = Vacation::query()
            ->with(['employee.user'])
            ->where('status', 'PENDIENTE')
            ->when($search !== '', fn($q) => $q->whereHas('employee', fn($e) => $e->where('nombre', 'like', "%{$search}%")));

        if (!$isAdmin) {
            $vacationsQuery->whereHas('employee', function ($q) use ($user) {
                $q->where('direct_manager_user_id', $user->id)
                    ->orWhereHas('user', fn($uq) => $uq->where('department', $user->department));
            });
        }

        $absencesQuery = Absence::query()
            ->with(['employee.user'])
            ->where('status', 'PENDIENTE')
            ->when($search !== '', fn($q) => $q->whereHas('employee', fn($e) => $e->where('nombre', 'like', "%{$search}%")));

        if (!$isAdmin) {
            $absencesQuery->whereHas('employee', function ($q) use ($user) {
                $q->where('direct_manager_user_id', $user->id)
                    ->orWhereHas('user', fn($uq) => $uq->where('department', $user->department));
            });
        }

        $ticketsQuery = Ticket::query()
            ->with(['client', 'creator'])
            ->whereIn('report_status', ['PENDIENTE', 'ATENCION', 'PROGRAMADO'])
            ->when($search !== '', fn($q) => $q->where(function ($sq) use ($search) {
                $sq->where('ticket_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$search}%"));
            }));

        if (!$isAdmin) {
            $ticketsQuery->whereHas('creator', fn($q) => $q->where('department', $user->department));
        }

        $stats = [
            'vacations_pending' => (clone $vacationsQuery)->count(),
            'absences_pending' => (clone $absencesQuery)->count(),
            'tickets_open' => (clone $ticketsQuery)->count(),
        ];

        $vacations = $vacationsQuery->orderBy('start_date')->limit(25)->get();
        $absences = $absencesQuery->orderBy('start_date')->limit(25)->get();
        $tickets = $ticketsQuery->orderBy('created_at', 'desc')->limit(25)->get();

        return view('supervision.requests', compact('vacations', 'absences', 'tickets', 'stats', 'search'));
    }
}
