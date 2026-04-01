<?php

namespace App\Http\Controllers;

use App\Models\Sku;
use App\Models\SkuFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkuController extends Controller
{
    public function index()
    {
        $formats = SkuFormat::orderBy('category')->get();

        // Agrupar SKUs por categoría
        $skusByCategory = Sku::orderBy('code')->get()->groupBy('category');

        return view('sku.index', compact('formats', 'skusByCategory'));
    }

    public function update(Request $request, SkuFormat $skuFormat)
    {
        $validated = $request->validate([
            'prefix' => 'required|string|max:20',
            'pad'    => 'required|integer|min:1|max:6',
        ]);

        $skuFormat->update($validated);

        return back()->with('success', "Formato de {$skuFormat->label} actualizado.");
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:30',
            'prefix'   => 'nullable|string|max:20',
            'pad'      => 'nullable|integer|min:1|max:6',
        ]);

        $code = DB::transaction(function () use ($request) {
            $format = SkuFormat::where('category', $request->category)->lockForUpdate()->firstOrFail();
            if ($request->filled('prefix')) {
                $format->prefix = $request->prefix;
            }
            if ($request->filled('pad')) {
                $format->pad = (int) $request->pad;
            }
            $format->save();

            $code = $format->prefix . str_pad('1', $format->pad, '0', STR_PAD_LEFT);
            Sku::create(['code' => $code, 'category' => $request->category]);
            return $code;
        });

        return back()->with('success', "SKU {$code} creado.");
    }

    public function destroy(Sku $sku)
    {
        $code = $sku->code;
        $sku->delete();
        return back()->with('success', "SKU {$code} eliminado.");
    }

    public function reset(SkuFormat $skuFormat)
    {
        $skuFormat->update(['last_number' => 0]);
        return back()->with('success', "Contador de {$skuFormat->label} reiniciado.");
    }
}
