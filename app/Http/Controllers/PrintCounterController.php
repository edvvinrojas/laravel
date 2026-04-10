<?php

namespace App\Http\Controllers;

use App\Models\PrintCounter;
use App\Models\Rent;
use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrintCounterController extends Controller
{
    public function index(Request $request)
    {
        $counters = PrintCounter::with(['rent.client', 'rent.item'])
            ->when($request->search, fn($q) => $q->whereHas('rent.client', fn($c) => $c->where('name', 'like', "%{$request->search}%")))
            ->when($request->rent_id, fn($q) => $q->where('rent_id', $request->rent_id))
            ->orderBy('period_year', 'desc')->orderBy('period_month', 'desc')
            ->paginate(20)->withQueryString();

        $rents = Rent::where('has_print_service', true)->where('contract_status', 'VIGENTE')->with('client')->get();
        return view('print-counters.index', compact('counters', 'rents'));
    }

    public function create()
    {
        $rents = Rent::where('has_print_service', true)
            ->where('contract_status', 'VIGENTE')
            ->with(['client', 'latestPrintCounter'])
            ->get();

        // Para cada renta: usar el último contador registrado como "anterior",
        // o el contador inicial de la renta si aún no hay ninguno.
        $rentDefaults = $rents->mapWithKeys(fn($r) => [
            $r->id => [
                'bn'           => $r->latestPrintCounter?->bn_current    ?? $r->contador_inicial_bn    ?? 0,
                'color'        => $r->latestPrintCounter?->color_current ?? $r->contador_inicial_color ?? 0,
                'bn_included'  => $r->bn_included    ?? 0,
                'color_included' => $r->color_included ?? 0,
                'bn_costo'     => $r->bn_cost_per_excess    ?? 0,
                'color_costo'  => $r->color_cost_per_excess ?? 0,
            ],
        ]);

        return view('print-counters.create', compact('rents', 'rentDefaults'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rent_id'       => 'required|exists:rents,id',
            'period_month'  => 'required|integer|min:1|max:12',
            'period_year'   => 'required|integer|min:2020',
            'bn_previous'   => 'required|integer|min:0',
            'bn_current'    => 'required|integer|min:0|gte:bn_previous',
            'color_previous'=> 'required|integer|min:0',
            'color_current' => 'required|integer|min:0|gte:color_previous',
            'counter_photo' => 'nullable|image|max:5120',
            'notes'         => 'nullable|string|max:500',
            'reading_date'  => 'required|date',
        ], [
            'bn_current.gte' => 'El contador BN actual no puede ser menor que el anterior.',
            'color_current.gte' => 'El contador Color actual no puede ser menor que el anterior.',
        ]);

        // Validar que no exista otro contador para el mismo mes/año de la misma renta
        $existing = PrintCounter::where('rent_id', $data['rent_id'])
            ->where('period_month', $data['period_month'])
            ->where('period_year', $data['period_year'])
            ->where('is_active', true)
            ->first();

        if ($existing) {
            return back()
                ->withInput()
                ->withErrors(['period' => 'Ya existe un contador activo para este mes en esta renta.']);
        }

        if ($request->hasFile('counter_photo')) {
            $data['counter_photo_url'] = $request->file('counter_photo')->store('print-counters', 'public');
        }
        unset($data['counter_photo']);

        $data['created_by'] = auth()->id();

        PrintCounter::create($data);

        return redirect()->route('print-counters.index')->with('success', 'Contador registrado.');
    }

    public function show(PrintCounter $printCounter)
    {
        $printCounter->load(['rent.client', 'rent.item', 'billing', 'creator']);
        return view('print-counters.show', compact('printCounter'));
    }

    public function edit(PrintCounter $printCounter)
    {
        $rents = Rent::where('has_print_service', true)->with('client')->get();
        return view('print-counters.edit', compact('printCounter', 'rents'));
    }

    public function update(Request $request, PrintCounter $printCounter)
    {
        $data = $request->validate([
            'bn_previous'    => 'required|integer|min:0',
            'bn_current'     => 'required|integer|min:0',
            'color_previous' => 'required|integer|min:0',
            'color_current'  => 'required|integer|min:0',
            'counter_photo'  => 'nullable|image|max:5120',
            'notes'          => 'nullable|string|max:500',
            'reading_date'   => 'required|date',
        ]);

        if ($request->hasFile('counter_photo')) {
            if ($printCounter->counter_photo_url) {
                Storage::disk('public')->delete($printCounter->counter_photo_url);
            }
            $data['counter_photo_url'] = $request->file('counter_photo')->store('print-counters', 'public');
        }
        unset($data['counter_photo']);

        $printCounter->update($data);

        return redirect()->route('print-counters.show', $printCounter)->with('success', 'Contador actualizado.');
    }

    public function billExcess(PrintCounter $printCounter)
    {
        if ($printCounter->is_billed) {
            return back()->with('error', 'Este contador ya fue facturado.');
        }
        if ($printCounter->total_excess_amount <= 0) {
            return back()->with('error', 'No hay exceso que facturar para este contador.');
        }

        $rent  = $printCounter->rent;
        $month = str_pad($printCounter->period_month, 2, '0', STR_PAD_LEFT);

        $billing = Billing::create([
            'billing_type' => 'EXCESO',
            'rent_id'      => $rent->id,
            'sale_id'      => null,
            'client_id'    => $rent->client_id,
            'branch_id'    => $rent->branch_id,
            'area_id'      => $rent->area_id,
            'amount'       => $printCounter->total_excess_amount,
            'target_date'  => now()->startOfMonth(),
            'due_date'     => now()->endOfMonth(),
            'status'       => 'PENDIENTE',
            'comment'      => "Exceso impresión {$month}/{$printCounter->period_year} — BN: {$printCounter->bn_excess} págs × \${$printCounter->bn_cost_per_page} | Color: {$printCounter->color_excess} págs × \${$printCounter->color_cost_per_page}",
            'created_by'   => auth()->id(),
        ]);

        $printCounter->update(['is_billed' => true, 'billing_id' => $billing->id]);

        return redirect()->route('print-counters.show', $printCounter)
            ->with('success', "Cobro de exceso generado por \$" . number_format($billing->amount, 2) . ". Folio: #{$billing->id}");
    }

    public function destroy(PrintCounter $printCounter)
    {
        $printCounter->update(['is_active' => false]);
        return redirect()->route('print-counters.index')->with('success', 'Contador eliminado.');
    }
}
