<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use App\Models\Supplier;
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
        $suppliers = Supplier::orderBy('name')->get();
        $colors = $this->getAvailableColors();
        $brands = $this->getAvailableBrands();
        return view('spareparts.create', compact('suppliers', 'colors', 'brands'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'color'       => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'brand'       => 'nullable|string|max:100',
            'equipment'   => 'nullable|string|max:255',
            'code'        => 'nullable|string|max:100',
            'supplier'    => 'nullable|string|max:100',
            'quantity'    => 'required|integer|min:1|max:500',
        ]);

        $quantity = (int) $data['quantity'];
        $baseCode = $this->stripSequenceSuffix($data['code'] ?? null);
        unset($data['quantity'], $data['code']);

        $created = 0;
        for ($i = 0; $i < $quantity; $i++) {
            $row = $data;
            $row['code'] = $baseCode ? $this->nextSequentialCode($baseCode) : null;
            Sparepart::create($row);
            $created++;
        }

        $msg = $quantity === 1
            ? 'Refacción registrada.'
            : "{$created} refacciones registradas.";

        return redirect()->route('spareparts.index')->with('success', $msg);
    }

    /**
     * Si el usuario captura "DV-512C-05" lo recorta a "DV-512C" para empezar la secuencia limpia.
     */
    private function stripSequenceSuffix(?string $code): ?string
    {
        $code = trim((string) $code);
        if ($code === '') return null;
        return preg_replace('/-\d{1,4}$/', '', $code);
    }

    /**
     * Devuelve el siguiente "BASE-NN" disponible en la tabla, padding mínimo 2 dígitos.
     */
    private function nextSequentialCode(string $base): string
    {
        $existing = Sparepart::where('code', 'like', $base . '-%')
            ->pluck('code')
            ->map(function ($c) use ($base) {
                if (preg_match('/^' . preg_quote($base, '/') . '-(\d+)$/', $c, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->max();

        $next = ((int) $existing) + 1;
        $width = max(2, strlen((string) $next));
        return $base . '-' . str_pad((string) $next, $width, '0', STR_PAD_LEFT);
    }

    public function show(Sparepart $sparepart)
    {
        return view('spareparts.show', compact('sparepart'));
    }

    public function edit(Sparepart $sparepart)
    {
        $suppliers = Supplier::orderBy('name')->get();
        $colors = $this->getAvailableColors();
        $brands = $this->getAvailableBrands();
        $existingSequential = $this->getExistingSequential($sparepart->code);
        return view('spareparts.edit', compact('sparepart', 'suppliers', 'colors', 'brands', 'existingSequential'));
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

    /**
     * Devuelve el siguiente consecutivo para un código base (API AJAX).
     */
    public function apiNextSequential(Request $request)
    {
        $code = $request->query('code', '');
        $baseCode = $this->stripSequenceSuffix($code);
        
        if (!$baseCode) {
            return response()->json(['next' => null]);
        }

        $next = $this->nextSequentialCode($baseCode);
        return response()->json(['next' => $next]);
    }

    /**
     * Devuelve los colores disponibles (predefinidos + únicos de la BD).
     */
    private function getAvailableColors(): array
    {
        $predefined = ['Blanco', 'Negro', 'Gris', 'Rojo', 'Azul', 'Verde', 'Amarillo', 'Naranja', 'Cian', 'Magenta'];
        $custom = Sparepart::where('color', '!=', null)
            ->distinct('color')
            ->pluck('color')
            ->filter()
            ->values()
            ->toArray();
        
        $all = array_merge($predefined, $custom);
        return array_unique(array_filter($all));
    }

    /**
     * Devuelve las marcas disponibles (únicas de la BD).
     */
    private function getAvailableBrands(): array
    {
        return Sparepart::where('brand', '!=', null)
            ->distinct('brand')
            ->pluck('brand')
            ->filter()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Devuelve los códigos consecutivos existentes para una base de código.
     */
    private function getExistingSequential(?string $code): array
    {
        if (!$code) {
            return [];
        }

        $baseCode = $this->stripSequenceSuffix($code);
        if (!$baseCode) {
            return [];
        }

        return Sparepart::where('code', 'like', $baseCode . '-%')
            ->orderBy('code')
            ->pluck('code')
            ->toArray();
    }
}
