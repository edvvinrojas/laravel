<?php

namespace App\Http\Controllers;

use App\Models\SkuFormat;
use Illuminate\Http\Request;

class SkuController extends Controller
{
    public function index()
    {
        $formats = SkuFormat::orderBy('category')->get();
        return view('sku.index', compact('formats'));
    }

    public function update(Request $request, SkuFormat $skuFormat)
    {
        $validated = $request->validate([
            'prefix' => 'required|string|max:20',
            'pad'    => 'required|integer|min:1|max:6',
        ]);

        $skuFormat->update($validated);

        return back()->with('success', "Formato de {$skuFormat->label} actualizado. Siguiente: {$skuFormat->preview()}");
    }

    public function reset(SkuFormat $skuFormat)
    {
        $skuFormat->update(['last_number' => 0]);
        return back()->with('success', "Contador de {$skuFormat->label} reiniciado.");
    }
}
