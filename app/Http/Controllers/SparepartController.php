<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use App\Models\Supplier;
use App\Models\Brand;
use Illuminate\Http\Request;

class SparepartController extends Controller
{
    private function normalizeBrandValue(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (in_array($value, ['__add_new__', '+ Agregar nueva marca...', '+ Agregar nueva marca…'], true)) {
            return null;
        }

        return $value;
    }

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
        $codePrefixes = $this->getCodePrefixes();
        return view('spareparts.create', compact('suppliers', 'colors', 'brands', 'codePrefixes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'color'       => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'brand'       => 'nullable|string|max:100',
            'brand_new'   => 'nullable|string|max:100',
            'equipment'   => 'nullable|string|max:255',
            'code'           => 'nullable|string|max:100',
            'supplier'       => 'nullable|string|max:100',
            'unit_price'     => 'nullable|numeric|min:0',
            'total_price'    => 'nullable|numeric|min:0',
            'invoice_number' => 'nullable|string|max:100',
            'quantity'       => 'required|integer|min:1|max:500',
        ]);

        $quantity = (int) $data['quantity'];
        $baseCode = $this->stripSequenceSuffix($data['code'] ?? null);
        $selectedBrand = $this->normalizeBrandValue($data['brand'] ?? null);
        $newBrand = $this->normalizeBrandValue($data['brand_new'] ?? null);
        $data['brand'] = $newBrand ?? $selectedBrand;
        $data['unit_price']     = !empty($data['unit_price'])     ? $data['unit_price']     : null;
        $data['total_price']    = !empty($data['total_price'])    ? $data['total_price']    : null;
        $data['invoice_number'] = !empty($data['invoice_number']) ? $data['invoice_number'] : null;
        unset($data['quantity'], $data['code'], $data['brand_new']);

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
            'brand_new'   => 'nullable|string|max:100',
            'equipment'   => 'nullable|string|max:255',
            'code'           => "nullable|string|max:100|unique:spareparts,code,{$sparepart->id}",
            'supplier'       => 'nullable|string|max:100',
            'unit_price'     => 'nullable|numeric|min:0',
            'total_price'    => 'nullable|numeric|min:0',
            'invoice_number' => 'nullable|string|max:100',
        ]);

        $selectedBrand = $this->normalizeBrandValue($data['brand'] ?? null);
        $newBrand = $this->normalizeBrandValue($data['brand_new'] ?? null);
        $data['brand'] = $newBrand ?? $selectedBrand;
        unset($data['brand_new']);
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
        $catalogBrands = Brand::orderBy('name')
            ->pluck('name')
            ->filter(fn ($name) => is_string($name) && trim($name) !== '')
            ->map(fn ($name) => trim($name))
            ->values()
            ->toArray();

        $legacyBrands = Sparepart::whereNotNull('brand')
            ->pluck('brand')
            ->filter(fn ($name) => is_string($name) && trim($name) !== '')
            ->map(fn ($name) => trim($name))
            ->reject(fn ($name) => in_array($name, ['+ Agregar nueva marca...'], true))
            ->values()
            ->toArray();

        $allBrands = array_unique(array_merge($catalogBrands, $legacyBrands));
        sort($allBrands, SORT_NATURAL | SORT_FLAG_CASE);

        return array_values($allBrands);
    }

    /**
     * Devuelve los prefijos base únicos usados (sin sufijo -NN).
     */
    private function getCodePrefixes(): array
    {
        return Sparepart::whereNotNull('code')
            ->pluck('code')
            ->map(fn ($c) => $this->stripSequenceSuffix($c))
            ->filter()
            ->unique()
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
