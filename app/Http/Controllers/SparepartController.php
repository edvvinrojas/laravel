<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use App\Models\Brand;
use App\Models\Shelf;
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

    private function nextInternalCode(string $code): string
    {
        $last = Sparepart::where('internal_code', 'like', "{$code}-%")
            ->orderByDesc('internal_code')->value('internal_code');
        $seq  = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;
        return $code . '-' . str_pad($seq, 2, '0', STR_PAD_LEFT);
    }

    public function create()
    {
        $brands    = Brand::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $shelves   = Shelf::where('is_active', true)->orderBy('name')->get();
        return view('spareparts.create', compact('brands', 'suppliers', 'shelves'));
    }

    public function store(Request $request)
    {
        // Bulk import
        if ($request->has('bulk')) {
            return $this->storeBulk($request);
        }

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'color'       => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'brand_id'    => 'nullable|exists:brands,id',
            'brand'       => 'nullable|string|max:100',
            'equipment'   => 'nullable|string|max:255',
            'code'        => 'nullable|string|max:100|unique:spareparts',
            'shelf_id'    => 'nullable|exists:shelves,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier'    => 'nullable|string|max:100',
        ]);

        if (!empty($data['code'])) {
            $data['internal_code'] = $this->nextInternalCode($data['code']);
        }

        Sparepart::create($data);
        return redirect()->route('spareparts.index')->with('success', 'Refacción registrada.');
    }

    private function storeBulk(Request $request)
    {
        $rows = $request->input('items', []);
        $count = 0;
        foreach ($rows as $row) {
            if (empty($row['name'])) continue;
            $internalCode = !empty($row['code']) ? $this->nextInternalCode($row['code']) : null;
            Sparepart::create([
                'name'          => $row['name'],
                'code'          => $row['code'] ?? null,
                'internal_code' => $internalCode,
                'equipment'     => $row['equipment'] ?? null,
                'brand'         => $row['brand'] ?? null,
                'supplier'      => $row['supplier'] ?? null,
                'description'   => $row['description'] ?? null,
            ]);
            $count++;
        }
        return redirect()->route('spareparts.index')->with('success', "{$count} refacciones importadas correctamente.");
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
        $shelves   = Shelf::where('is_active', true)->orderBy('name')->get();
        return view('spareparts.edit', compact('sparepart', 'brands', 'suppliers', 'shelves'));
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
            'shelf_id'    => 'nullable|exists:shelves,id',
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
