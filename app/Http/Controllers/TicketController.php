<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Client;
use App\Models\Branch;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::with(['client', 'branch', 'creator'])
            ->when($request->search, fn($q) => $q->whereHas('client', fn($c) => $c->where('name', 'like', "%{$request->search}%")))
            ->when($request->status, fn($q) => $q->where('report_status', $request->status))
            ->when($request->type, fn($q) => $q->where('report_type', $request->type))
            ->orderByRaw("FIELD(report_status,'URGENTE','PENDIENTE','ATENCION','PROGRAMADO','INFORMATIVO','NO_QUEDO_EN_LA_VISITA','LISTO')")
            ->orderBy('created_at', 'desc')
            ->paginate(20)->withQueryString();

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        return view('tickets.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'     => 'required|exists:clients,id',
            'branch_id'     => 'required|exists:branches,id',
            'area_id'       => 'nullable|exists:areas,id',
            'report_status' => 'required|in:PENDIENTE,LISTO,URGENTE,PROGRAMADO,INFORMATIVO,NO_QUEDO_EN_LA_VISITA,ATENCION',
            'report_type'   => 'required|in:CONECTIVIDAD,ATASCO,TONER,QUEJAS,COPIA,RUIDOS,IMPRESION,OTROS',
            'description'   => 'required|string',
            'evidence'      => 'nullable|string|max:500',
        ]);

        $data['created_by'] = auth()->id();
        $ticket = Ticket::create($data);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket creado.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['client', 'branch', 'area', 'creator']);
        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $clients  = Client::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('client_id', $ticket->client_id)->get();
        $areas    = $ticket->branch_id ? Area::where('branch_id', $ticket->branch_id)->get() : collect();
        return view('tickets.edit', compact('ticket', 'clients', 'branches', 'areas'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'branch_id'        => 'required|exists:branches,id',
            'area_id'          => 'nullable|exists:areas,id',
            'report_status'    => 'required|in:PENDIENTE,LISTO,URGENTE,PROGRAMADO,INFORMATIVO,NO_QUEDO_EN_LA_VISITA,ATENCION',
            'report_type'      => 'required|in:CONECTIVIDAD,ATASCO,TONER,QUEJAS,COPIA,RUIDOS,IMPRESION,OTROS',
            'description'      => 'required|string',
            'corrective_action' => 'nullable|string',
            'evidence'         => 'nullable|string|max:500',
        ]);

        $ticket->update($data);
        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket actualizado.');
    }

    public function close(Ticket $ticket)
    {
        $ticket->update(['report_status' => 'LISTO', 'completed_at' => Carbon::now()]);
        return back()->with('success', 'Ticket cerrado.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket eliminado.');
    }
}
