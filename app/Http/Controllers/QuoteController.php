<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\Client;
use App\Models\Item;
use App\Models\Sparepart;
use App\Models\InventoryItem;
use App\Models\Employee;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    private function generateQuoteNumber(): string
    {
        $year = date('Y');
        $last = Quote::whereYear('created_at', $year)
            ->where('quote_number', 'like', "COT-{$year}-%")
            ->orderByDesc('quote_number')
            ->value('quote_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'COT-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $quotes = Quote::with('client', 'creator')
            ->when($request->search, fn($q) =>
                $q->where('quote_number', 'like', "%{$request->search}%")
                  ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$request->search}%"))
            )
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Cotizaciones pendientes de revisión: creadas por subordinados directos, no terminadas
        $pendingReview = collect();
        $subordinateUserIds = Employee::where('direct_manager_user_id', Auth::id())
            ->whereNotNull('user_id')
            ->pluck('user_id');

        if ($subordinateUserIds->isNotEmpty()) {
            $pendingReview = Quote::with('client', 'creator')
                ->whereIn('created_by', $subordinateUserIds)
                ->whereNotIn('status', ['APROBADA', 'RECHAZADA'])
                ->orderByDesc('created_at')
                ->get();
        }

        return view('quotes.index', compact('quotes', 'pendingReview'));
    }

    public function create()
    {
        $clients    = Client::where('is_active', true)->orderBy('name')->get();
        $items      = Item::where('is_active', true)->with('brand')->orderBy('model')->get();
        $spareparts = Sparepart::where('is_active', true)->orderBy('name')->get();
        $inventory  = InventoryItem::where('is_active', true)
            ->where('is_available', true)
            ->with('catalog')
            ->orderBy('item_code')
            ->get();
        $nextNumber = $this->generateQuoteNumber();

        return view('quotes.create', compact('clients', 'items', 'spareparts', 'inventory', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'notes'       => 'nullable|string|max:2000',
            'valid_until' => 'nullable|date|after_or_equal:today',
            'lines'       => 'required|array|min:1',
            'lines.*.product_type' => 'required|in:item,sparepart,inventory,manual',
            'lines.*.product_id'   => 'nullable|integer',
            'lines.*.description'  => 'required|string|max:255',
            'lines.*.quantity'     => 'required|integer|min:1',
            'lines.*.unit_price'   => 'required|numeric|min:0',
        ]);

        $quote = DB::transaction(function () use ($data) {
            $total = collect($data['lines'])->sum(fn($l) => $l['quantity'] * $l['unit_price']);

            $quote = Quote::create([
                'client_id'    => $data['client_id'],
                'quote_number' => $this->generateQuoteNumber(),
                'status'       => 'BORRADOR',
                'notes'        => $data['notes'] ?? null,
                'valid_until'  => $data['valid_until'] ?? null,
                'total'        => $total,
                'created_by'   => Auth::id(),
            ]);

            foreach ($data['lines'] as $line) {
                $quote->lines()->create([
                    'product_type' => $line['product_type'] !== 'manual' ? $line['product_type'] : null,
                    'product_id'   => $line['product_type'] !== 'manual' ? ($line['product_id'] ?? null) : null,
                    'description'  => $line['description'],
                    'quantity'     => $line['quantity'],
                    'unit_price'   => $line['unit_price'],
                    'total'        => $line['quantity'] * $line['unit_price'],
                ]);
            }

            // Notificar al jefe directo del usuario autenticado
            $employee = Employee::where('user_id', Auth::id())->first();
            if ($employee && $employee->direct_manager_user_id) {
                $client = Client::find($data['client_id']);
                Notification::create([
                    'user_id'    => $employee->direct_manager_user_id,
                    'type'       => 'COTIZACION',
                    'title'      => 'Nueva cotización generada',
                    'message'    => Auth::user()->full_name . ' generó la cotización ' . $quote->quote_number .
                                   ' para el cliente ' . ($client->name ?? 'N/A') .
                                   ' por $' . number_format($quote->total, 2) . '.',
                    'link'       => route('quotes.show', $quote),
                    'is_read'    => false,
                    'created_at' => now(),
                ]);
            }

            return $quote;
        });

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Cotización creada correctamente.');
    }

    public function show(Quote $quote)
    {
        $quote->load('client', 'lines', 'creator');
        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        if (in_array($quote->status, ['APROBADA', 'RECHAZADA'])) {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'No se puede editar una cotización ' . strtolower($quote->status) . '.');
        }

        $clients    = Client::where('is_active', true)->orderBy('name')->get();
        $items      = Item::where('is_active', true)->with('brand')->orderBy('model')->get();
        $spareparts = Sparepart::where('is_active', true)->orderBy('name')->get();
        $inventory  = InventoryItem::where('is_active', true)
            ->where('is_available', true)
            ->with('catalog')
            ->orderBy('item_code')
            ->get();
        $quote->load('lines');

        $existingLines = $quote->lines->map(fn($l) => [
            'type'        => $l->product_type ?? 'manual',
            'id'          => $l->product_id,
            'description' => $l->description,
            'quantity'    => $l->quantity,
            'unit_price'  => (float) $l->unit_price,
        ])->values()->toArray();

        return view('quotes.edit', compact('quote', 'clients', 'items', 'spareparts', 'inventory', 'existingLines'));
    }

    public function update(Request $request, Quote $quote)
    {
        if (in_array($quote->status, ['APROBADA', 'RECHAZADA'])) {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'No se puede modificar una cotización ' . strtolower($quote->status) . '.');
        }

        $data = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'status'      => 'required|in:BORRADOR,ENVIADA,APROBADA,RECHAZADA',
            'notes'       => 'nullable|string|max:2000',
            'valid_until' => 'nullable|date',
            'lines'       => 'required|array|min:1',
            'lines.*.product_type' => 'required|in:item,sparepart,inventory,manual',
            'lines.*.product_id'   => 'nullable|integer',
            'lines.*.description'  => 'required|string|max:255',
            'lines.*.quantity'     => 'required|integer|min:1',
            'lines.*.unit_price'   => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, $quote) {
            $total = collect($data['lines'])->sum(fn($l) => $l['quantity'] * $l['unit_price']);

            $quote->update([
                'client_id'   => $data['client_id'],
                'status'      => $data['status'],
                'notes'       => $data['notes'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'total'       => $total,
            ]);

            $quote->lines()->delete();
            foreach ($data['lines'] as $line) {
                $quote->lines()->create([
                    'product_type' => $line['product_type'] !== 'manual' ? $line['product_type'] : null,
                    'product_id'   => $line['product_type'] !== 'manual' ? ($line['product_id'] ?? null) : null,
                    'description'  => $line['description'],
                    'quantity'     => $line['quantity'],
                    'unit_price'   => $line['unit_price'],
                    'total'        => $line['quantity'] * $line['unit_price'],
                ]);
            }
        });

        return redirect()->route('quotes.show', $quote)->with('success', 'Cotización actualizada.');
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();
        return redirect()->route('quotes.index')->with('success', 'Cotización eliminada.');
    }

    public function approve(Quote $quote)
    {
        $this->authorizeManagerAction($quote);

        $quote->update(['status' => 'APROBADA']);

        // Notificar al creador
        if ($quote->created_by) {
            Notification::create([
                'user_id'    => $quote->created_by,
                'type'       => 'COTIZACION',
                'title'      => 'Cotización aprobada',
                'message'    => 'Tu cotización ' . $quote->quote_number . ' para ' . $quote->client->name . ' fue APROBADA por ' . Auth::user()->full_name . '.',
                'link'       => route('quotes.show', $quote),
                'is_read'    => false,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Cotización ' . $quote->quote_number . ' aprobada.');
    }

    public function reject(Request $request, Quote $quote)
    {
        $this->authorizeManagerAction($quote);

        $quote->update(['status' => 'RECHAZADA']);

        // Notificar al creador
        if ($quote->created_by) {
            Notification::create([
                'user_id'    => $quote->created_by,
                'type'       => 'COTIZACION',
                'title'      => 'Cotización rechazada',
                'message'    => 'Tu cotización ' . $quote->quote_number . ' para ' . $quote->client->name . ' fue RECHAZADA por ' . Auth::user()->full_name . '.',
                'link'       => route('quotes.show', $quote),
                'is_read'    => false,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Cotización ' . $quote->quote_number . ' rechazada.');
    }

    /**
     * Verifica que el usuario autenticado es jefe directo del creador de la cotización.
     * Admins y gerencia pueden aprobar/rechazar cualquier cotización.
     */
    private function authorizeManagerAction(Quote $quote): void
    {
        $user = Auth::user();
        if ($user->isGerencia()) {
            return;
        }

        $isManager = Employee::where('user_id', $quote->created_by)
            ->where('direct_manager_user_id', $user->id)
            ->exists();

        abort_unless($isManager, 403, 'No tienes permiso para realizar esta acción.');
    }
}
