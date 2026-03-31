<?php

namespace App\Http\Controllers;

use App\Models\Repair;
use App\Models\Item;
use Illuminate\Http\Request;

class RepairController extends Controller
{
    public function index(Request $request)
    {
        $repairs = Repair::with(['item.brand'])
            ->when($request->search, fn($q) => $q->whereHas('item', fn($i) =>
                $i->where('serie', 'like', "%{$request->search}%")
                  ->orWhere('model', 'like', "%{$request->search}%")
            ))
            ->when($request->status, fn($q) => $q->where('estado_taller', $request->status))
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20)->withQueryString();

        return view('repairs.index', compact('repairs'));
    }

    public function create()
    {
        $items = Item::where('is_active', true)->with('brand')->orderBy('model')->get();
        return view('repairs.create', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_id'           => 'required|exists:items,id',
            'procedencia'       => 'required|string|max:255',
            'estado_taller'     => 'required|in:PENDIENTE,PAUSADO,LISTO',
            'ubicacion'         => 'nullable|in:ZONA_1,ZONA_2,ZONA_3,ZONA_4,BASURA',
            'proceso'           => 'required|in:DESCONOCIDO,PROCESO_1,PROCESO_2,PROCESO_3',
            'estatus'           => 'required|in:EN_ESPERA_AUTORIZACION,EN_ESPERA_PIEZA,PAUSADO,LISTO',
            'diagnostico_inicial' => 'nullable|string',
            'comments'          => 'nullable|string',
        ]);

        $item = Item::findOrFail($data['item_id']);
        $item->update(['location_status' => 'TALLER']);

        Repair::create($data);

        return redirect()->route('repairs.index')->with('success', 'Equipo ingresado al taller.');
    }

    public function show(Repair $repair)
    {
        $repair->load(['item.brand']);
        return view('repairs.show', compact('repair'));
    }

    public function edit(Repair $repair)
    {
        return view('repairs.edit', compact('repair'));
    }

    public function update(Request $request, Repair $repair)
    {
        $data = $request->validate([
            'estado_taller'     => 'required|in:PENDIENTE,PAUSADO,LISTO',
            'ubicacion'         => 'nullable|in:ZONA_1,ZONA_2,ZONA_3,ZONA_4,BASURA',
            'proceso'           => 'required|in:DESCONOCIDO,PROCESO_1,PROCESO_2,PROCESO_3',
            'estatus'           => 'required|in:EN_ESPERA_AUTORIZACION,EN_ESPERA_PIEZA,PAUSADO,LISTO',
            'diagnostico_inicial' => 'nullable|string',
            'comments'          => 'nullable|string',
            'folio_escaneado'   => 'nullable|string',
        ]);

        if ($data['estado_taller'] === 'LISTO' && $repair->estado_taller !== 'LISTO') {
            $data['fecha_conclusion'] = now();
            $repair->item->update(['location_status' => 'BODEGA']);
        }

        $repair->update($data);
        return redirect()->route('repairs.show', $repair)->with('success', 'Reparación actualizada.');
    }

    public function destroy(Repair $repair)
    {
        $repair->update(['is_active' => false]);
        return redirect()->route('repairs.index')->with('success', 'Registro eliminado.');
    }
}
