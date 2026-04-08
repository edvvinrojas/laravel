<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Client;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = ServiceOrder::with(['engineer', 'client', 'branch'])
            ->when($request->search, fn($q) => $q->whereHas('client', fn($c) => $c->where('name', 'like', "%{$request->search}%")))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(20)->withQueryString();

        return view('service-orders.index', compact('orders'));
    }

    public function create()
    {
        $engineers = User::where('is_active', true)
            ->where('department', 'operaciones')
            ->orderBy('full_name')
            ->get();

        if ($engineers->isEmpty()) {
            $engineers = User::where('is_active', true)->orderBy('full_name')->get();
        }

        $clients   = Client::where('is_active', true)->orderBy('name')->get();
        return view('service-orders.create', compact('engineers', 'clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'engineer_id'           => 'required|exists:users,id',
            'client_id'             => 'required|exists:clients,id',
            'branch_id'             => 'required|exists:branches,id',
            'area_id'               => 'required|exists:areas,id',
            'item_id'               => 'required|exists:items,id',
            'tipo_orden'            => 'required|in:PREVENTIVO,CORRECTIVO,ENTREGA,INSTALACION,CAMBIO_EQUIPO,DIGITALIZACION,INSTALACION_DRIVERS',
            'se_reviso'             => 'nullable|array',
            'se_reviso.*'           => 'in:UNIDAD_IMAGEN,UNIDAD_REVELADO,FUSOR,CALIBRACIONES,GOMAS,BANDA_TRANSFERENCIA,BANDEJAS',
            'diagnostico_accion'    => 'nullable|string',
            'entrego_toner'         => 'required|boolean',
            'codigos_toner'         => 'nullable|required_if:entrego_toner,1|string|max:500',
            'pct_toner_negro'       => 'nullable|integer|min:0|max:100',
            'pct_toner_cyan'        => 'nullable|integer|min:0|max:100',
            'pct_toner_magenta'     => 'nullable|integer|min:0|max:100',
            'pct_toner_amarillo'    => 'nullable|integer|min:0|max:100',
            'evidencia_foto'        => 'nullable|image|max:5120',
            'pendiente_material'    => 'nullable|string',
            'tiene_stock'           => 'required|boolean',
            'foto_stock'            => 'nullable|required_if:tiene_stock,1|image|max:5120',
            'firma_nombre'          => 'required|string|max:255',
            'queda_pendiente'       => 'required|boolean',
            'descripcion_pendiente' => 'nullable|required_if:queda_pendiente,1|string',
            'pagina_estado_foto'    => 'nullable|image|max:5120',
            'firma_imagen'          => 'nullable|string',
        ]);

        $data['entrego_toner']   = $request->boolean('entrego_toner');
        $data['tiene_stock']     = $request->boolean('tiene_stock');
        $data['queda_pendiente'] = $request->boolean('queda_pendiente');
        $data['created_by']      = Auth::id();
        $data['status']          = 'PENDIENTE';

        if (!$data['entrego_toner']) {
            $data['codigos_toner'] = null;
        }

        if (!$data['queda_pendiente']) {
            $data['descripcion_pendiente'] = null;
        }

        foreach (['evidencia_foto', 'foto_stock', 'pagina_estado_foto'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('service-orders', 'public');
            }
        }

        if (!$data['tiene_stock']) {
            $data['foto_stock'] = null;
        }

        if ($request->filled('firma_imagen')) {
            $data['firma_imagen'] = $request->input('firma_imagen');
        }

        $order = ServiceOrder::create($data);

        return redirect()->route('service-orders.show', $order)->with('success', 'Orden de servicio creada.');
    }

    public function show(ServiceOrder $serviceOrder)
    {
        $serviceOrder->load(['engineer', 'client', 'branch', 'area', 'item.brand', 'creator']);
        return view('service-orders.show', compact('serviceOrder'));
    }

    public function edit(ServiceOrder $serviceOrder)
    {
        $engineers = User::where('is_active', true)->orderBy('full_name')->get();
        $clients   = Client::where('is_active', true)->orderBy('name')->get();
        return view('service-orders.edit', compact('serviceOrder', 'engineers', 'clients'));
    }

    public function update(Request $request, ServiceOrder $serviceOrder)
    {
        $data = $request->validate([
            'status'                => 'required|in:PENDIENTE,COMPLETADO',
            'diagnostico_accion'    => 'nullable|string',
            'pendiente_material'    => 'nullable|string',
            'queda_pendiente'       => 'boolean',
            'descripcion_pendiente' => 'nullable|string',
        ]);

        $data['queda_pendiente'] = $request->boolean('queda_pendiente');
        $serviceOrder->update($data);

        return redirect()->route('service-orders.show', $serviceOrder)->with('success', 'Orden actualizada.');
    }

    public function destroy(ServiceOrder $serviceOrder)
    {
        $serviceOrder->delete();
        return redirect()->route('service-orders.index')->with('success', 'Orden eliminada.');
    }
}
