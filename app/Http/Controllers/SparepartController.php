<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use Illuminate\Http\Request;

class SparepartController extends Controller
{
    public function index(Request $request)
    {
        $spareparts = Sparepart::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('code', 'like', "%{$request->search}%"))
            ->orderBy('name')->paginate(20)->withQueryString();
        return view('spareparts.index', compact('spareparts'));
    }

    public function create()
    {
        return view('spareparts.create');
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
            'brand'       => 'nullable|string|max:100',
            'equipment'   => 'nullable|string|max:255',
            'code'        => 'nullable|string|max:100|unique:spareparts',
            'supplier'    => 'nullable|string|max:100',
        ]);

        Sparepart::create($data);
        return redirect()->route('spareparts.index')->with('success', 'Refacción registrada.');
    }

    private function storeBulk(Request $request)
    {
        $rows = $request->input('items', []);
        $count = 0;
        foreach ($rows as $row) {
            if (empty($row['name'])) continue;
            Sparepart::create([
                'name'        => $row['name'],
                'code'        => $row['code'] ?? null,
                'equipment'   => $row['equipment'] ?? null,
                'brand'       => $row['brand'] ?? null,
                'supplier'    => $row['supplier'] ?? null,
                'description' => $row['description'] ?? null,
            ]);
            $count++;
        }
        return redirect()->route('spareparts.index')->with('success', "{$count} refacciones importadas correctamente.");
    }

    public function show(Sparepart $sparepart)
    {
        return view('spareparts.show', compact('sparepart'));
    }

    public function edit(Sparepart $sparepart)
    {
        return view('spareparts.edit', compact('sparepart'));
    }

    public function update(Request $request, Sparepart $sparepart)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'color'       => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'brand'       => 'nullable|string|max:100',
            'equipment'   => 'nullable|string|max:255',
            'code'        => "nullable|string|max:100|unique:spareparts,code,{$sparepart->id}",
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
