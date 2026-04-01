<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Accesorio;
use App\Models\Consumible;
use App\Models\Stock;
use App\Models\Brand;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $productos = Producto::with('marca', 'stock')
            ->withCount('accesorios', 'consumibles', 'equipos')
            ->when($request->search, fn($q, $s) =>
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('codigo', 'like', "%{$s}%")
            )
            ->when($request->categoria, fn($q, $v) => $q->where('categoria', $v))
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('productos.index', compact('productos'));
    }

    public function create()
    {
        $marcas      = Brand::orderBy('name')->get();
        $accesorios  = Accesorio::where('es_activo', true)->orderBy('nombre')->get();
        $consumibles = Consumible::where('es_activo', true)->orderBy('nombre')->get();
        $skus        = \App\Models\Sku::where('category', 'PRODUCTO')->orderBy('code')->get();

        return view('productos.create', compact('marcas', 'accesorios', 'consumibles', 'skus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'                   => 'required|string|max:200',
            'codigo'                   => 'required|string|max:50',
            'brand_id'                 => 'nullable|exists:brands,id',
            'categoria'                => 'required|in:COPIADORA,IMPRESORA,MFP,ESCANER,FAX,PLOTTER,OTRO',
            'tipo_color'               => 'nullable|in:MONOCROMO,COLOR,AMBOS',
            'formato_max'              => 'nullable|in:A4,A3,CARTA,OFICIO,A2,A1,A0',
            'descripcion'              => 'nullable|string',
            'precio_venta'             => 'nullable|numeric|min:0',
            'precio_renta'             => 'nullable|numeric|min:0',
            'es_activo'                => 'boolean',
            'accesorios_ids'           => 'nullable|array',
            'accesorios_ids.*'         => 'exists:accesorios,id',
            'accesorio_incluido.*'     => 'nullable|boolean',
            'consumibles_ids'          => 'nullable|array',
            'consumibles_ids.*'        => 'exists:consumibles,id',
            'consumible_oficial.*'     => 'nullable|boolean',
            // Stock inicial
            'stock_cantidad'           => 'nullable|integer|min:0',
            'stock_minimo'             => 'nullable|integer|min:0',
            'stock_costo'              => 'nullable|numeric|min:0',
            'stock_ubicacion'          => 'nullable|string|max:200',
        ]);

    $producto = Producto::create([
        'nombre'       => $request->nombre,
        'codigo'       => $request->filled('codigo') ? strtoupper($request->codigo) : \App\Models\SkuFormat::nextSku('PRODUCTO'),
            'brand_id'     => $request->brand_id,
            'categoria'    => $request->categoria,
            'tipo_color'   => $request->tipo_color,
            'formato_max'  => $request->formato_max,
            'descripcion'  => $request->descripcion,
            'precio_venta' => $request->precio_venta,
            'precio_renta' => $request->precio_renta,
            'es_activo'    => $request->boolean('es_activo', true),
        ]);

        // Sync accesorios
        $acsPivot = [];
        foreach ($request->input('accesorios_ids', []) as $aid) {
            $acsPivot[$aid] = ['es_incluido' => isset($request->accesorio_incluido[$aid])];
        }
        $producto->accesorios()->sync($acsPivot);

        // Sync consumibles
        $conPivot = [];
        foreach ($request->input('consumibles_ids', []) as $cid) {
            $conPivot[$cid] = ['es_oficial' => isset($request->consumible_oficial[$cid]) ? true : false];
        }
        $producto->consumibles()->sync($conPivot);

        // Stock inicial
        Stock::create([
            'tipo'                 => 'PRODUCTO',
            'referencia_id'        => $producto->id,
            'cantidad_disponible'  => $request->input('stock_cantidad', 0),
            'cantidad_minima'      => $request->input('stock_minimo', 0),
            'costo'                => $request->stock_costo,
            'ubicacion'            => $request->stock_ubicacion,
        ]);

        return redirect()->route('almacen.index', ['tab' => 'productos'])
            ->with('success', 'Producto registrado correctamente.');
    }

    public function show(Producto $producto)
    {
        $producto->load('marca', 'accesorios', 'consumibles', 'equipos.brand', 'stock');
        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $marcas      = Brand::orderBy('name')->get();
        $accesorios  = Accesorio::orderBy('nombre')->get();
        $consumibles = Consumible::orderBy('nombre')->get();
        $producto->load('accesorios', 'consumibles', 'stock');

        $accsSeleccionados  = $producto->accesorios->keyBy('id');
        $consSeleccionados  = $producto->consumibles->keyBy('id');

        return view('productos.edit', compact('producto', 'marcas', 'accesorios', 'consumibles', 'accsSeleccionados', 'consSeleccionados'));
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre'      => 'required|string|max:200',
            'codigo'      => "required|string|max:50|unique:productos,codigo,{$producto->id}",
            'brand_id'    => 'nullable|exists:brands,id',
            'categoria'   => 'required|in:COPIADORA,IMPRESORA,MFP,ESCANER,FAX,PLOTTER,OTRO',
            'tipo_color'  => 'nullable|in:MONOCROMO,COLOR,AMBOS',
            'formato_max' => 'nullable|in:A4,A3,CARTA,OFICIO,A2,A1,A0',
            'descripcion' => 'nullable|string',
            'precio_venta'=> 'nullable|numeric|min:0',
            'precio_renta'=> 'nullable|numeric|min:0',
            'es_activo'   => 'boolean',
        ]);

        $producto->update([
            'nombre'       => $request->nombre,
            'codigo'       => strtoupper($request->codigo),
            'brand_id'     => $request->brand_id,
            'categoria'    => $request->categoria,
            'tipo_color'   => $request->tipo_color,
            'formato_max'  => $request->formato_max,
            'descripcion'  => $request->descripcion,
            'precio_venta' => $request->precio_venta,
            'precio_renta' => $request->precio_renta,
            'es_activo'    => $request->boolean('es_activo'),
        ]);

        // Sync accesorios
        $acsPivot = [];
        foreach ($request->input('accesorios_ids', []) as $aid) {
            $acsPivot[$aid] = ['es_incluido' => isset($request->accesorio_incluido[$aid])];
        }
        $producto->accesorios()->sync($acsPivot);

        // Sync consumibles
        $conPivot = [];
        foreach ($request->input('consumibles_ids', []) as $cid) {
            $conPivot[$cid] = ['es_oficial' => isset($request->consumible_oficial[$cid]) ? true : false];
        }
        $producto->consumibles()->sync($conPivot);

        // Actualizar stock
        $producto->stock()->updateOrCreate(
            ['tipo' => 'PRODUCTO', 'referencia_id' => $producto->id],
            [
                'cantidad_disponible' => $request->input('stock_cantidad', 0),
                'cantidad_minima'     => $request->input('stock_minimo', 0),
                'costo'               => $request->stock_costo,
                'ubicacion'           => $request->stock_ubicacion,
            ]
        );

        return redirect()->route('almacen.index', ['tab' => 'productos'])
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->accesorios()->detach();
        $producto->consumibles()->detach();
        $producto->stock()->delete();
        $producto->delete();

        return redirect()->route('almacen.index', ['tab' => 'productos'])
            ->with('success', 'Producto eliminado.');
    }
}
