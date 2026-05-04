<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Client;
use App\Models\Branch;
use App\Models\Area;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    private const REPORT_TYPES = [
        'ATASCO', 'IMPRESION', 'MANCHAS', 'ESCANEO', 'CONECTIVIDAD',
        'TONER', 'QUEJAS', 'COPIA', 'RUIDOS', 'OTROS',
    ];

    private const STATUSES = [
        'PENDIENTE', 'ATENCION', 'PROGRAMADO', 'INFORMATIVO',
        'NO_QUEDO_EN_LA_VISITA', 'LISTO',
    ];

    private const PRIORITIES = ['URGENTE', 'NORMAL', 'BAJA'];

    private function authorizeTicketAccess(Ticket $ticket): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        // Usuario operativo solo puede ver/editar/cerrar sus propios tickets.
        if ($user->rol === 'usuario' && (int) $ticket->created_by !== (int) $user->id) {
            abort(403, 'No tienes permiso para ver tickets de otros usuarios.');
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $tickets = Ticket::with(['client', 'branch', 'creator', 'item'])
            ->when($user && $user->rol === 'usuario', fn($q) => $q->where('created_by', $user->id))
            ->when($request->boolean('mine') && $user, fn($q) => $q->where('created_by', $user->id))
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where(function ($qq) use ($term) {
                    $qq->where('ticket_code', 'like', "%{$term}%")
                       ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->status, fn($q) => $q->where('report_status', $request->status))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->type, fn($q) => $q->where('report_type', $request->type))
            ->orderByRaw("FIELD(priority,'URGENTE','NORMAL','BAJA')")
            ->orderBy('created_at', 'desc')
            ->paginate(20)->withQueryString();

        return view('tickets.index', [
            'tickets'     => $tickets,
            'reportTypes' => self::REPORT_TYPES,
            'statuses'    => self::STATUSES,
            'priorities'  => self::PRIORITIES,
            'canSeeAllTickets' => $user && $user->rol !== 'usuario',
        ]);
    }

    public function create()
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $ticketCodes = Ticket::whereNotNull('ticket_code')->distinct()->orderBy('ticket_code')->pluck('ticket_code')->toArray();
        return view('tickets.create', [
            'clients'     => $clients,
            'reportTypes' => self::REPORT_TYPES,
            'statuses'    => self::STATUSES,
            'priorities'  => self::PRIORITIES,
            'nextCode'    => Ticket::generateTicketCode(),
            'ticketCodes' => $ticketCodes,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ticket_code'   => 'nullable|string|max:50|unique:tickets,ticket_code',
            'client_id'     => 'required|exists:clients,id',
            'branch_id'     => 'required|exists:branches,id',
            'area_id'       => 'nullable|exists:areas,id',
            'item_id'       => 'nullable|exists:items,id',
            'priority'      => 'required|in:'.implode(',', self::PRIORITIES),
            'report_status' => 'required|in:'.implode(',', self::STATUSES),
            'report_type'   => 'required|in:'.implode(',', self::REPORT_TYPES),
            'description'   => 'required|string',
            'evidence_url'  => 'nullable|string|max:500',
            'evidence_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,mp4,mov,heic,webp|max:10240',
        ]);

        $data['evidence'] = $request->filled('evidence_url') ? $request->input('evidence_url') : null;
        if ($request->hasFile('evidence_file')) {
            $data['evidence'] = $request->file('evidence_file')->store('tickets', 'public');
        }
        unset($data['evidence_url'], $data['evidence_file']);

        $data['created_by']   = Auth::id();
        $data['ticket_code']  = trim((string) ($data['ticket_code'] ?? ''));
        if ($data['ticket_code'] === '') {
            $data['ticket_code'] = Ticket::generateTicketCode();
        }

        $ticket = Ticket::create($data);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_code} creado.");
    }

    public function show(Ticket $ticket)
    {
        $this->authorizeTicketAccess($ticket);
        $ticket->load(['client', 'branch', 'area', 'item.brand', 'creator']);
        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $this->authorizeTicketAccess($ticket);
        $clients  = Client::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('client_id', $ticket->client_id)->get();
        $areas    = $ticket->branch_id ? Area::where('branch_id', $ticket->branch_id)->get() : collect();

        return view('tickets.edit', [
            'ticket'      => $ticket,
            'clients'     => $clients,
            'branches'    => $branches,
            'areas'       => $areas,
            'reportTypes' => self::REPORT_TYPES,
            'statuses'    => self::STATUSES,
            'priorities'  => self::PRIORITIES,
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorizeTicketAccess($ticket);
        $data = $request->validate([
            'client_id'         => 'required|exists:clients,id',
            'branch_id'         => 'required|exists:branches,id',
            'area_id'           => 'nullable|exists:areas,id',
            'item_id'           => 'nullable|exists:items,id',
            'priority'          => 'required|in:'.implode(',', self::PRIORITIES),
            'report_status'     => 'required|in:'.implode(',', self::STATUSES),
            'report_type'       => 'required|in:'.implode(',', self::REPORT_TYPES),
            'description'       => 'required|string',
            'corrective_action' => 'nullable|string',
            'evidence_url'      => 'nullable|string|max:500',
            'evidence_file'     => 'nullable|file|mimes:jpg,jpeg,png,pdf,mp4,mov,heic,webp|max:10240',
            'evidence_remove'   => 'nullable|boolean',
        ]);

        if ($request->hasFile('evidence_file')) {
            if ($ticket->evidence && !str_starts_with($ticket->evidence, 'http')) {
                Storage::disk('public')->delete($ticket->evidence);
            }
            $data['evidence'] = $request->file('evidence_file')->store('tickets', 'public');
        } elseif ($request->boolean('evidence_remove')) {
            if ($ticket->evidence && !str_starts_with($ticket->evidence, 'http')) {
                Storage::disk('public')->delete($ticket->evidence);
            }
            $data['evidence'] = null;
        } elseif ($request->filled('evidence_url')) {
            $data['evidence'] = $request->input('evidence_url');
        }
        unset($data['evidence_url'], $data['evidence_file'], $data['evidence_remove']);

        $ticket->update($data);
        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket actualizado.');
    }

    public function close(Ticket $ticket)
    {
        $this->authorizeTicketAccess($ticket);
        $ticket->update(['report_status' => 'LISTO', 'completed_at' => Carbon::now()]);
        return back()->with('success', 'Ticket cerrado.');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorizeTicketAccess($ticket);
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket eliminado.');
    }
}
