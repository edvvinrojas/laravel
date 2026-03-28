<?php

namespace App\Http\Controllers;

use App\Models\PrintCounter;
use App\Models\Rent;
use Illuminate\Http\Request;

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
        $rents = Rent::where('has_print_service', true)->where('contract_status', 'VIGENTE')->with('client')->get();
        return view('print-counters.create', compact('rents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rent_id'             => 'required|exists:rents,id',
            'period_month'        => 'required|integer|min:1|max:12',
            'period_year'         => 'required|integer|min:2020',
            'bn_previous'         => 'required|integer|min:0',
            'bn_current'          => 'required|integer|min:0',
            'color_previous'      => 'required|integer|min:0',
            'color_current'       => 'required|integer|min:0',
            'counter_photo_url'   => 'nullable|string|max:500',
            'notes'               => 'nullable|string|max:500',
            'reading_date'        => 'required|date',
        ]);

        $rent = Rent::find($data['rent_id']);
        $data['bn_included']        = $rent->bn_included ?? 0;
        $data['bn_cost_per_page']   = $rent->bn_cost_per_excess ?? 0;
        $data['color_included']     = $rent->color_included ?? 0;
        $data['color_cost_per_page'] = $rent->color_cost_per_excess ?? 0;
        $data['bn_printed']         = max(0, $data['bn_current'] - $data['bn_previous']);
        $data['bn_excess']          = max(0, $data['bn_printed'] - $data['bn_included']);
        $data['bn_excess_amount']   = $data['bn_excess'] * $data['bn_cost_per_page'];
        $data['color_printed']      = max(0, $data['color_current'] - $data['color_previous']);
        $data['color_excess']       = max(0, $data['color_printed'] - $data['color_included']);
        $data['color_excess_amount'] = $data['color_excess'] * $data['color_cost_per_page'];
        $data['total_excess_amount'] = $data['bn_excess_amount'] + $data['color_excess_amount'];
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
            'notes'          => 'nullable|string|max:500',
            'reading_date'   => 'required|date',
        ]);

        $data['bn_printed']   = max(0, $data['bn_current'] - $data['bn_previous']);
        $data['color_printed'] = max(0, $data['color_current'] - $data['color_previous']);
        $printCounter->update($data);

        return redirect()->route('print-counters.show', $printCounter)->with('success', 'Contador actualizado.');
    }

    public function destroy(PrintCounter $printCounter)
    {
        $printCounter->update(['is_active' => false]);
        return redirect()->route('print-counters.index')->with('success', 'Contador eliminado.');
    }
}
