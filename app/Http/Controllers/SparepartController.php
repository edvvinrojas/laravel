<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use App\Models\Brand;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SparepartController extends Controller
{
    public function index(Request $request)
    {
        $spareparts = Sparepart::with('brandModel', 'supplierModel')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('code', 'like', "%{$request->search}%"))
            ->orderBy('name')->paginate(20)->withQueryString();
        return view('spareparts.index', compact('spareparts'));
    }

    public function create()
    {
        $brands    = Brand::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('spareparts.create', compact('brands', 'suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'color'       => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'brand_id'    => 'nullable|exists:brands,id',
            'brand'       => 'nullable|string|max:100',
            'equipment'   => 'nullable|string|max:255',
            'code'        => 'nullable|string|max:100|unique:spareparts',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier'    => 'nullable|string|max:100',
        ]);

        Sparepart::create($data);
        return redirect()->route('spareparts.index')->with('success', 'Refacción registrada.');
    }

    public function show(Sparepart $sparepart)
    {
        $sparepart->load('brandModel', 'supplierModel');
        return view('spareparts.show', compact('sparepart'));
    }

    public function edit(Sparepart $sparepart)
    {
        $brands    = Brand::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('spareparts.edit', compact('sparepart', 'brands', 'suppliers'));
    }

    public function update(Request $request, Sparepart $sparepart)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'color'       => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'brand_id'    => 'nullable|exists:brands,id',
            'brand'       => 'nullable|string|max:100',
            'equipment'   => 'nullable|string|max:255',
            'code'        => "nullable|string|max:100|unique:spareparts,code,{$sparepart->id}",
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier'    => 'nullable|string|max:100',
        ]);

        $sparepart->update($data);
        return redirect()->route('spareparts.show', $sparepart)->with('success', 'Refacción actualizada.');
    }

    public function destroy(Sparepart $sparepart)
    {
        $sparepart->delete();
        return redirect()->route('spareparts.index')->with('success', 'Refacción eliminada.');
    }
}
