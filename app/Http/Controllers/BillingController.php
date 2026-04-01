<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Client;
use App\Models\PrintCounter;
use App\Models\Rent;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'cobranza');

        $billings = Billing::with(['client'])
            ->when($request->search, fn($q) =>
                $q->where(fn($q2) => $q2
                    ->whereHas('client', fn($c) => $c->where('name', 'like', "%{$request->search}%"))
                    ->orWhere('invoice_number', 'like', "%{$request->search}%"))
            )
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type,   fn($q) => $q->where('billing_type', $request->type))
            ->when($request->date_from, fn($q) => $q->where('target_date', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->where('target_date', '<=', $request->date_to))
            ->where('is_active', true)
            ->orderBy('due_date')
            ->paginate(20)->withQueryString();

        $totals = [
            'pending' => Billing::where('status', 'PENDIENTE')->where('is_active', true)->sum('amount'),
            'overdue' => Billing::where('status', 'VENCIDO')->where('is_active', true)->sum('amount'),
            'paid'    => Billing::where('status', 'PAGADO')->whereMonth('payment_date', now()->month)->sum('amount'),
        ];

        return view('billing.index', compact('billings', 'totals', 'tab'));
    }

    public function create()
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $rents   = Rent::where('contract_status', 'VIGENTE')->with('client')->get();
        $sales   = Sale::whereIn('sale_status', ['PENDIENTE', 'CONFIRMADA'])->with('client')->get();
        return view('billing.create', compact('clients', 'rents', 'sales'));
    }

    public function store(Request $request)
    {
        $base = $request->validate([
            'billing_type'   => 'required|in:RENTA,VENTA',
            'rent_id'        => 'nullable|exists:rents,id',
            'sale_id'        => 'nullable|exists:sales,id',
            'invoice_number' => 'nullable|string|max:50|unique:billings',
            'amount'         => 'required|numeric|min:0',
            'target_date'    => 'required|date',
            'due_date'       => 'required|date',
            'payment_term'   => 'nullable|integer|min:0',
            'payment_day'    => 'nullable|integer|min:1|max:31',
            'comment'        => 'nullable|string',
        ]);

        // Enforce: exactly one FK must match billing_type
        if ($base['billing_type'] === 'RENTA') {
            if (empty($base['rent_id'])) {
                return back()->withErrors(['rent_id' => 'Debe seleccionar una renta.'])->withInput();
            }
            $base['sale_id'] = null;
            $source = Rent::findOrFail($base['rent_id']);
        } else {
            if (empty($base['sale_id'])) {
                return back()->withErrors(['sale_id' => 'Debe seleccionar una venta.'])->withInput();
            }
            $base['rent_id'] = null;
            $source = Sale::findOrFail($base['sale_id']);
        }

        // Derive client/branch/area from the linked transaction — avoids denormalization
        $base['client_id'] = $source->client_id;
        $base['branch_id'] = $source->branch_id ?? null;
        $base['area_id']   = $source->area_id   ?? null;
        $base['created_by'] = auth()->id();
        $base['status']     = 'PENDIENTE';

        // Para rentas con servicio de impresión: recalcular monto server-side
        // sumando todos los contadores no facturados. Esto evita que el excedente
        // se pierda si el usuario olvida incluirlo o el JS no lo cargó.
        $unbilledCounters = collect();
        $unbilledCounters = collect();
        if ($base['billing_type'] === 'RENTA' && $source->has_print_service) {
            $unbilledCounters = PrintCounter::where('rent_id', $source->id)
                ->where('is_active', true)
                ->where('is_billed', false)
                ->with('rent')
                ->get()
                ->filter(fn($pc) => $pc->total_excess_amount > 0);

            $excess = $unbilledCounters->sum(fn($pc) => $pc->total_excess_amount);
            $base['amount'] = round((float) $source->rent + (float) $excess, 2);
        }

        $billing = Billing::create($base);

        if ($unbilledCounters->isNotEmpty()) {
            PrintCounter::whereIn('id', $unbilledCounters->pluck('id'))
                ->update(['is_billed' => true, 'billing_id' => $billing->id]);
        }

        return redirect()->route('billing.index')->with('success', 'Factura creada correctamente.');
    }

    public function show(Billing $billing)
    {
        $billing->load(['client', 'branch', 'area', 'rent.item', 'sale.item', 'creator', 'printCounter']);
        return view('billing.show', compact('billing'));
    }

    public function edit(Billing $billing)
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        return view('billing.edit', compact('billing', 'clients'));
    }

    public function update(Request $request, Billing $billing)
    {
        $data = $request->validate([
            'invoice_number' => "nullable|string|max:50|unique:billings,invoice_number,{$billing->id}",
            'amount'         => 'required|numeric|min:0',
            'target_date'    => 'required|date',
            'due_date'       => 'required|date',
            'payment_term'   => 'nullable|integer|min:0',
            'payment_day'    => 'nullable|integer|min:1|max:31',
            'comment'        => 'nullable|string',
            'follow_up'      => 'boolean',
        ]);

        $data['follow_up'] = $request->boolean('follow_up');
        $billing->update($data);

        return redirect()->route('billing.show', $billing)->with('success', 'Factura actualizada.');
    }

    public function markPaid(Request $request, Billing $billing)
    {
        $request->validate(['payment_date' => 'required|date']);
        $billing->update(['status' => 'PAGADO', 'payment_date' => $request->payment_date]);
        return back()->with('success', 'Factura marcada como pagada.');
    }

    public function destroy(Billing $billing)
    {
        $billing->update(['is_active' => false]);
        return redirect()->route('billing.index')->with('success', 'Factura eliminada.');
    }
}
