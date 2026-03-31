<?php

namespace App\Http\Controllers;

use App\Models\ItRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class ItRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isTi = $user->rol === 'administrador' || $user->department === 'ti';

        $query = ItRequest::with(['user', 'assignedUser'])
            ->when(!$isTi, fn($q) => $q->where('user_id', $user->id))
            ->when($request->status,   fn($q) => $q->where('status', $request->status))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->search,   fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('folio', 'like', "%{$request->search}%")
                   ->orWhere('title', 'like', "%{$request->search}%");
            }))
            ->orderByRaw("FIELD(status,'ABIERTO','EN_PROCESO','RESUELTO','CERRADO')")
            ->orderByRaw("FIELD(priority,'URGENTE','ALTA','MEDIA','BAJA')")
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        return view('it-requests.index', compact('query', 'isTi'));
    }

    public function create()
    {
        return view('it-requests.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category'    => 'required|in:EMAIL,INTERNET,HARDWARE,SOFTWARE,IMPRESORA,TELEFONO,ACCESOS,OTRO',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'priority'    => 'required|in:BAJA,MEDIA,ALTA,URGENTE',
        ]);

        $data['user_id'] = auth()->id();
        $data['folio']   = $this->nextFolio();
        $data['status']  = 'ABIERTO';

        $ticket = ItRequest::create($data);
        $link   = route('it-requests.show', $ticket);

        // Notify TI staff and admins
        User::where(function ($q) {
            $q->where('department', 'ti')->orWhere('rol', 'administrador');
        })->where('is_active', true)->where('id', '!=', auth()->id())->each(
            fn($u) => Notification::create([
                'user_id'    => $u->id,
                'type'       => 'IT_TICKET',
                'title'      => "Nuevo ticket [{$ticket->folio}]",
                'message'    => auth()->user()->full_name . " reportó: {$ticket->title}",
                'link'       => $link,
                'is_read'    => false,
                'created_at' => now(),
            ])
        );

        return redirect()->route('it-requests.show', $ticket)
            ->with('success', "Ticket {$ticket->folio} creado correctamente.");
    }

    public function show(ItRequest $itRequest)
    {
        $user  = auth()->user();
        $isTi  = $user->rol === 'administrador' || $user->department === 'ti';

        abort_if(!$isTi && $itRequest->user_id !== $user->id, 403);

        $itRequest->load(['user', 'assignedUser']);
        $tiUsers = $isTi ? User::where(function ($q) {
            $q->where('department', 'ti')->orWhere('rol', 'administrador');
        })->where('is_active', true)->orderBy('full_name')->get() : collect();

        return view('it-requests.show', compact('itRequest', 'isTi', 'tiUsers'));
    }

    public function edit(ItRequest $itRequest)
    {
        $user = auth()->user();
        $isTi = $user->rol === 'administrador' || $user->department === 'ti';

        // Only owner can edit if ABIERTO; TI can always edit
        abort_if(!$isTi && ($itRequest->user_id !== $user->id || $itRequest->status !== 'ABIERTO'), 403);

        return view('it-requests.edit', compact('itRequest', 'isTi'));
    }

    public function update(Request $request, ItRequest $itRequest)
    {
        $user = auth()->user();
        $isTi = $user->rol === 'administrador' || $user->department === 'ti';

        abort_if(!$isTi && ($itRequest->user_id !== $user->id || $itRequest->status !== 'ABIERTO'), 403);

        if ($isTi) {
            $data = $request->validate([
                'category'         => 'required|in:EMAIL,INTERNET,HARDWARE,SOFTWARE,IMPRESORA,TELEFONO,ACCESOS,OTRO',
                'title'            => 'required|string|max:255',
                'description'      => 'required|string',
                'priority'         => 'required|in:BAJA,MEDIA,ALTA,URGENTE',
                'status'           => 'required|in:ABIERTO,EN_PROCESO,RESUELTO,CERRADO',
                'assigned_to'      => 'nullable|exists:users,id',
                'resolution_notes' => 'nullable|string',
            ]);

            $wasResolved = !in_array($itRequest->status, ['RESUELTO', 'CERRADO'])
                && in_array($data['status'], ['RESUELTO', 'CERRADO']);

            if ($wasResolved) {
                $data['resolved_at'] = now();
            }

            $itRequest->update($data);

            // Notify requester on resolution
            if ($wasResolved && $itRequest->user_id !== $user->id) {
                Notification::create([
                    'user_id'    => $itRequest->user_id,
                    'type'       => 'IT_TICKET',
                    'title'      => "Ticket resuelto [{$itRequest->folio}]",
                    'message'    => "Tu solicitud \"{$itRequest->title}\" fue marcada como {$data['status']}.",
                    'link'       => route('it-requests.show', $itRequest),
                    'is_read'    => false,
                    'created_at' => now(),
                ]);
            }
        } else {
            $data = $request->validate([
                'category'    => 'required|in:EMAIL,INTERNET,HARDWARE,SOFTWARE,IMPRESORA,TELEFONO,ACCESOS,OTRO',
                'title'       => 'required|string|max:255',
                'description' => 'required|string',
                'priority'    => 'required|in:BAJA,MEDIA,ALTA,URGENTE',
            ]);
            $itRequest->update($data);
        }

        return redirect()->route('it-requests.show', $itRequest)->with('success', 'Ticket actualizado.');
    }

    public function destroy(ItRequest $itRequest)
    {
        $user = auth()->user();
        $isTi = $user->rol === 'administrador' || $user->department === 'ti';
        abort_if(!$isTi && $itRequest->user_id !== $user->id, 403);

        $itRequest->delete();
        return redirect()->route('it-requests.index')->with('success', 'Ticket eliminado.');
    }

    // ─── Quick-assign to self ─────────────────────────────────────────────────

    public function assign(ItRequest $itRequest)
    {
        $user = auth()->user();
        abort_if($user->rol !== 'administrador' && $user->department !== 'ti', 403);

        $itRequest->update([
            'assigned_to' => $user->id,
            'status'      => 'EN_PROCESO',
        ]);

        // Notify requester
        if ($itRequest->user_id !== $user->id) {
            Notification::create([
                'user_id'    => $itRequest->user_id,
                'type'       => 'IT_TICKET',
                'title'      => "Ticket en proceso [{$itRequest->folio}]",
                'message'    => "Tu solicitud \"{$itRequest->title}\" fue tomada por {$user->full_name}.",
                'link'       => route('it-requests.show', $itRequest),
                'is_read'    => false,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Ticket asignado a ti.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function nextFolio(): string
    {
        $year = now()->year;
        $last = ItRequest::whereYear('created_at', $year)
            ->orderByDesc('id')->value('folio');

        if ($last && preg_match('/TK-\d{4}-(\d+)$/', $last, $m)) {
            $next = (int)$m[1] + 1;
        } else {
            $next = 1;
        }

        return 'TK-' . $year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
