<?php

namespace App\Http\Controllers;

use App\Models\TiEquipment;
use App\Models\TiPeripheral;
use App\Models\TiLicense;
use App\Models\User;
use Illuminate\Http\Request;

class TiEquipmentController extends Controller
{
    public function index(Request $request)
    {
        $equipment = TiEquipment::with('assignedUser')
            ->when($request->search, fn($q) => $q->where('codigo_interno', 'like', "%{$request->search}%")
                ->orWhere('modelo', 'like', "%{$request->search}%")
                ->orWhere('marca', 'like', "%{$request->search}%"))
            ->when($request->tipo,   fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('codigo_interno')
            ->paginate(20)->withQueryString();

        $licenses = TiLicense::where('is_active', true)->orderBy('software')->get();

        return view('ti-equipment.index', compact('equipment', 'licenses'));
    }

    public function create()
    {
        $users    = User::where('is_active', true)->orderBy('name')->get();
        $licenses = TiLicense::where('is_active', true)->orderBy('software')->get();
        $nextCode = $this->nextCode();
        return view('ti-equipment.create', compact('users', 'licenses', 'nextCode'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo_interno'    => 'required|string|max:50|unique:ti_equipment',
            'marca'             => 'required|string|max:100',
            'modelo'            => 'required|string|max:150',
            'numero_serie'      => 'nullable|string|max:100|unique:ti_equipment',
            'tipo'              => 'required|in:PC,LAPTOP,SERVIDOR,IMPRESORA,TELEFONO,TABLET,SWITCH,ROUTER,OTRO',
            'procesador'        => 'nullable|string|max:100',
            'ram'               => 'nullable|string|max:50',
            'almacenamiento'    => 'nullable|string|max:100',
            'sistema_operativo' => 'nullable|string|max:100',
            'assigned_user_id'  => 'nullable|exists:users,id',
            'ubicacion'         => 'nullable|string|max:200',
            'status'            => 'required|in:ACTIVO,BAJA,REPARACION,BODEGA',
            'fecha_compra'      => 'nullable|date',
            'notas'             => 'nullable|string',
            'licenses'          => 'nullable|array',
            'licenses.*'        => 'exists:ti_licenses,id',
            // Periféricos
            'perifericos.*.tipo'         => 'nullable|in:MONITOR,TECLADO,MOUSE,CARGADOR,DOCKING,HEADSET,CAMARA,OTRO',
            'perifericos.*.marca'        => 'nullable|string|max:100',
            'perifericos.*.modelo'       => 'nullable|string|max:100',
            'perifericos.*.numero_serie' => 'nullable|string|max:100',
            'perifericos.*.notas'        => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();
        $licenses = $data['licenses'] ?? [];
        $perifericos = $request->input('perifericos', []);
        unset($data['licenses'], $data['perifericos']);

        $equipo = TiEquipment::create($data);

        if ($licenses) $equipo->licenses()->sync($licenses);

        foreach ($perifericos as $p) {
            if (!empty($p['tipo'])) {
                $equipo->peripherals()->create($p);
            }
        }

        return redirect()->route('ti-equipment.show', $equipo)->with('success', 'Equipo registrado.');
    }

    public function show(TiEquipment $tiEquipment)
    {
        $tiEquipment->load(['assignedUser', 'peripherals', 'licenses', 'creator']);
        return view('ti-equipment.show', compact('tiEquipment'));
    }

    public function edit(TiEquipment $tiEquipment)
    {
        $tiEquipment->load(['peripherals', 'licenses']);
        $users    = User::where('is_active', true)->orderBy('name')->get();
        $licenses = TiLicense::where('is_active', true)->orderBy('software')->get();
        return view('ti-equipment.edit', compact('tiEquipment', 'users', 'licenses'));
    }

    public function update(Request $request, TiEquipment $tiEquipment)
    {
        $data = $request->validate([
            'marca'             => 'required|string|max:100',
            'modelo'            => 'required|string|max:150',
            'numero_serie'      => 'nullable|string|max:100|unique:ti_equipment,numero_serie,'.$tiEquipment->id,
            'tipo'              => 'required|in:PC,LAPTOP,SERVIDOR,IMPRESORA,TELEFONO,TABLET,SWITCH,ROUTER,OTRO',
            'procesador'        => 'nullable|string|max:100',
            'ram'               => 'nullable|string|max:50',
            'almacenamiento'    => 'nullable|string|max:100',
            'sistema_operativo' => 'nullable|string|max:100',
            'assigned_user_id'  => 'nullable|exists:users,id',
            'ubicacion'         => 'nullable|string|max:200',
            'status'            => 'required|in:ACTIVO,BAJA,REPARACION,BODEGA',
            'fecha_compra'      => 'nullable|date',
            'notas'             => 'nullable|string',
            'licenses'          => 'nullable|array',
            'licenses.*'        => 'exists:ti_licenses,id',
        ]);

        $licenses = $data['licenses'] ?? [];
        unset($data['licenses']);

        $tiEquipment->update($data);
        $tiEquipment->licenses()->sync($licenses);

        return redirect()->route('ti-equipment.show', $tiEquipment)->with('success', 'Equipo actualizado.');
    }

    public function destroy(TiEquipment $tiEquipment)
    {
        $tiEquipment->delete();
        return redirect()->route('ti-equipment.index')->with('success', 'Equipo eliminado.');
    }

    // ─── Periféricos ─────────────────────────────────────────────────────────

    public function storePeripheral(Request $request, TiEquipment $tiEquipment)
    {
        $data = $request->validate([
            'tipo'         => 'required|in:MONITOR,TECLADO,MOUSE,CARGADOR,DOCKING,HEADSET,CAMARA,OTRO',
            'marca'        => 'nullable|string|max:100',
            'modelo'       => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100',
            'notas'        => 'nullable|string',
        ]);
        $tiEquipment->peripherals()->create($data);
        return back()->with('success', 'Periférico agregado.');
    }

    public function destroyPeripheral(TiEquipment $tiEquipment, TiPeripheral $peripheral)
    {
        $peripheral->delete();
        return back()->with('success', 'Periférico eliminado.');
    }

    // ─── Licencias (CRUD independiente) ──────────────────────────────────────

    public function licensesIndex()
    {
        $licenses = TiLicense::withCount('equipment')->orderBy('software')->paginate(20);
        return view('ti-equipment.licenses', compact('licenses'));
    }

    public function licenseStore(Request $request)
    {
        $data = $request->validate([
            'software'           => 'required|string|max:150',
            'tipo'               => 'required|in:OFFICE,ANTIVIRUS,OS,OTRO',
            'clave_licencia'     => 'nullable|string|max:255',
            'proveedor'          => 'nullable|string|max:150',
            'fecha_vencimiento'  => 'nullable|date',
            'cantidad_licencias' => 'required|integer|min:1',
            'notas'              => 'nullable|string',
        ]);
        $data['created_by'] = auth()->id();
        TiLicense::create($data);
        return back()->with('success', 'Licencia registrada.');
    }

    public function licenseDestroy(TiLicense $license)
    {
        $license->delete();
        return back()->with('success', 'Licencia eliminada.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function nextCode(): string
    {
        $last = TiEquipment::orderByDesc('id')->value('codigo_interno');
        if ($last && preg_match('/TI-(\d+)$/', $last, $m)) {
            return 'TI-' . str_pad((int)$m[1] + 1, 4, '0', STR_PAD_LEFT);
        }
        return 'TI-0001';
    }
}
