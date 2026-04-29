<?php

namespace App\Http\Controllers;

use App\Models\TiEquipment;
use App\Models\TiPeripheral;
use App\Models\TiLicense;
use App\Models\SkuFormat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $users    = User::where('is_active', true)->orderBy('full_name')->get();
        $licenses = $this->assignableLicensesQuery()->orderBy('software')->get();
        $nextEquipmentCode = $this->previewNextEquipmentCode();
        return view('ti-equipment.create', compact('users', 'licenses', 'nextEquipmentCode'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo_mode'       => 'required|in:auto,custom',
            'codigo_interno'    => 'nullable|string|max:50|unique:ti_equipment,codigo_interno',
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
            'perifericos.*.codigo_mode'   => 'nullable|in:auto,custom',
            'perifericos.*.codigo'        => 'nullable|string|max:20|distinct',
            'perifericos.*.tipo'         => 'nullable|in:MONITOR,TECLADO,MOUSE,CARGADOR,DOCKING,HEADSET,CAMARA,ELIMINADOR,OTRO',
            'perifericos.*.marca'        => 'nullable|string|max:100',
            'perifericos.*.modelo'       => 'nullable|string|max:100',
            'perifericos.*.numero_serie' => 'nullable|string|max:100',
            'perifericos.*.notas'        => 'nullable|string',
        ]);

        $data['created_by'] = Auth::id();
        if (($data['codigo_mode'] ?? 'auto') === 'auto') {
            $data['codigo_interno'] = SkuFormat::nextSku('TI_EQUIPO');
        } else {
            $data['codigo_interno'] = trim((string) ($data['codigo_interno'] ?? ''));
            if ($data['codigo_interno'] === '') {
                return back()->withInput()->withErrors(['codigo_interno' => 'Captura un código personalizado para el equipo.']);
            }
        }
        $licenses = collect($data['licenses'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $assignableLicenses = $this->assignableLicensesQuery()
            ->whereIn('id', $licenses)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($assignableLicenses->count() !== $licenses->count()) {
            return back()->withInput()->withErrors([
                'licenses' => 'Una o más licencias seleccionadas están vencidas o inactivas y no pueden asignarse.',
            ]);
        }

        $perifericos = $request->input('perifericos', []);
        unset($data['licenses'], $data['perifericos'], $data['codigo_mode']);

        $equipo = TiEquipment::create($data);

        if ($assignableLicenses->isNotEmpty()) {
            $equipo->licenses()->sync($assignableLicenses->all());
        }

        foreach ($perifericos as $p) {
            if (!empty($p['tipo'])) {
                $mode = $p['codigo_mode'] ?? 'auto';
                if ($mode === 'custom') {
                    $p['codigo'] = trim((string) ($p['codigo'] ?? ''));
                    if ($p['codigo'] === '') {
                        return back()->withInput()->withErrors(['perifericos' => 'Cada periférico personalizado necesita un código.']);
                    }
                    if (TiPeripheral::where('codigo', $p['codigo'])->exists()) {
                        return back()->withInput()->withErrors(['perifericos' => 'Uno de los códigos personalizados de periférico ya existe.']);
                    }
                } else {
                    $p['codigo'] = $this->nextPeripheralCode($p['tipo']);
                }
                unset($p['codigo_mode']);
                $equipo->peripherals()->create($p);
            }
        }

        return redirect()->route('ti-equipment.show', $equipo)->with('success', 'Equipo registrado.');
    }

    public function show(TiEquipment $tiEquipment)
    {
        $tiEquipment->load(['assignedUser', 'peripherals', 'licenses', 'creator']);
        $attachedIds    = $tiEquipment->licenses->pluck('id');
        $availableLicenses = $this->assignableLicensesQuery()
            ->whereNotIn('id', $attachedIds)
            ->orderBy('software')->get();
        return view('ti-equipment.show', compact('tiEquipment', 'availableLicenses'));
    }

    public function edit(TiEquipment $tiEquipment)
    {
        $tiEquipment->load(['peripherals', 'licenses']);
        $users    = User::where('is_active', true)->orderBy('full_name')->get();
        $licenses = $this->assignableLicensesQuery()->orderBy('software')->get();
        $nextEquipmentCode = $this->previewNextEquipmentCode();
        return view('ti-equipment.edit', compact('tiEquipment', 'users', 'licenses', 'nextEquipmentCode'));
    }

    public function update(Request $request, TiEquipment $tiEquipment)
    {
        $data = $request->validate([
            'codigo_mode'       => 'required|in:keep,auto,custom',
            'codigo_interno'    => 'nullable|string|max:50|unique:ti_equipment,codigo_interno,'.$tiEquipment->id,
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

        if (($data['codigo_mode'] ?? 'keep') === 'auto') {
            $data['codigo_interno'] = SkuFormat::nextSku('TI_EQUIPO');
        } elseif (($data['codigo_mode'] ?? 'keep') === 'custom') {
            $data['codigo_interno'] = trim((string) ($data['codigo_interno'] ?? ''));
            if ($data['codigo_interno'] === '') {
                return back()->withInput()->withErrors(['codigo_interno' => 'Captura un código personalizado para el equipo.']);
            }
        } else {
            unset($data['codigo_interno']);
        }

        $licenses = collect($data['licenses'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $assignableLicenses = $this->assignableLicensesQuery()
            ->whereIn('id', $licenses)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($assignableLicenses->count() !== $licenses->count()) {
            return back()->withInput()->withErrors([
                'licenses' => 'Una o más licencias seleccionadas están vencidas o inactivas y no pueden asignarse.',
            ]);
        }

        $blockedCurrentIds = $tiEquipment->licenses()
            ->where(function ($q) {
                $q->where('is_active', false)
                    ->orWhereDate('fecha_vencimiento', '<', now()->toDateString());
            })
            ->pluck('ti_licenses.id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $licensesToSync = $assignableLicenses
            ->merge($blockedCurrentIds)
            ->unique()
            ->values()
            ->all();

        unset($data['licenses'], $data['codigo_mode']);

        $tiEquipment->update($data);
        $tiEquipment->licenses()->sync($licensesToSync);

        return redirect()->route('ti-equipment.show', $tiEquipment)->with('success', 'Equipo actualizado.');
    }

    public function destroy(TiEquipment $tiEquipment)
    {
        $tiEquipment->delete();
        return redirect()->route('ti-equipment.index')->with('success', 'Equipo eliminado.');
    }

    // ─── Periféricos ─────────────────────────────────────────────────────────

    // ─── Responsiva ──────────────────────────────────────────────────────────

    public function responsiva(TiEquipment $tiEquipment)
    {
        $tiEquipment->load(['assignedUser', 'peripherals', 'licenses', 'creator']);
        return view('ti-equipment.responsiva', compact('tiEquipment'));
    }

    // ─── Licencias por equipo ────────────────────────────────────────────────

    public function attachLicense(Request $request, TiEquipment $tiEquipment)
    {
        $request->validate(['license_id' => 'required|exists:ti_licenses,id']);

        $license = TiLicense::findOrFail($request->license_id);

        if (!$license->is_active || ($license->fecha_vencimiento && $license->fecha_vencimiento->lt(now()->startOfDay()))) {
            return back()->withErrors([
                'license_id' => "La licencia '{$license->software}' está vencida o inactiva y no puede vincularse.",
            ]);
        }

        $assigned = $license->equipment()->count();

        if ($assigned >= $license->cantidad_licencias) {
            return back()->withErrors([
                'license_id' => "La licencia '{$license->software}' ya alcanzó su límite de {$license->cantidad_licencias} asignaciones.",
            ]);
        }

        $tiEquipment->licenses()->syncWithoutDetaching([$license->id]);
        return back()->with('success', 'Licencia vinculada.');
    }

    public function detachLicense(TiEquipment $tiEquipment, TiLicense $license)
    {
        $tiEquipment->licenses()->detach($license->id);
        return back()->with('success', 'Licencia desvinculada.');
    }

    // ─── Periféricos ─────────────────────────────────────────────────────────

    public function storePeripheral(Request $request, TiEquipment $tiEquipment)
    {
        $data = $request->validate([
            'codigo_mode'  => 'required|in:auto,custom',
            'codigo'       => 'nullable|string|max:20|unique:ti_peripherals,codigo',
            'tipo'         => 'required|in:MONITOR,TECLADO,MOUSE,CARGADOR,DOCKING,HEADSET,CAMARA,ELIMINADOR,OTRO',
            'marca'        => 'nullable|string|max:100',
            'modelo'       => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100',
            'notas'        => 'nullable|string',
        ]);
        if (($data['codigo_mode'] ?? 'auto') === 'custom') {
            $data['codigo'] = trim((string) ($data['codigo'] ?? ''));
            if ($data['codigo'] === '') {
                return back()->withInput()->withErrors(['codigo' => 'Captura un código personalizado para el periférico.']);
            }
        } else {
            $data['codigo'] = $this->nextPeripheralCode($data['tipo']);
        }
        unset($data['codigo_mode']);
        $tiEquipment->peripherals()->create($data);
        return back()->with('success', 'Periférico agregado.');
    }

    private function nextPeripheralCode(string $tipo): string
    {
        $prefixes = [
            'MONITOR'    => 'LCD',
            'TECLADO'    => 'TEC',
            'MOUSE'      => 'MOU',
            'CARGADOR'   => 'CAR',
            'DOCKING'    => 'DOK',
            'HEADSET'    => 'HST',
            'CAMARA'     => 'CAM',
            'ELIMINADOR' => 'ELI',
            'OTRO'       => 'OTR',
        ];
        $prefix = $prefixes[$tipo] ?? 'OTR';
        $last   = TiPeripheral::where('codigo', 'like', "{$prefix}%")
                    ->orderByDesc('codigo')->value('codigo');
        $next   = $last ? ((int) substr($last, 3)) + 1 : 1;
        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function editPeripheral(TiEquipment $tiEquipment, TiPeripheral $peripheral)
    {
        abort_if($peripheral->ti_equipment_id !== $tiEquipment->id, 404);
        return view('ti-equipment.peripheral-edit', compact('tiEquipment', 'peripheral'));
    }

    public function updatePeripheral(Request $request, TiEquipment $tiEquipment, TiPeripheral $peripheral)
    {
        abort_if($peripheral->ti_equipment_id !== $tiEquipment->id, 404);

        $data = $request->validate([
            'codigo'       => 'required|string|max:20|unique:ti_peripherals,codigo,' . $peripheral->id,
            'tipo'         => 'required|in:MONITOR,TECLADO,MOUSE,CARGADOR,DOCKING,HEADSET,CAMARA,ELIMINADOR,OTRO',
            'marca'        => 'nullable|string|max:100',
            'modelo'       => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100',
            'notas'        => 'nullable|string',
        ]);

        $peripheral->update($data);
        return redirect()->route('ti-equipment.show', $tiEquipment)->with('success', 'Periférico actualizado.');
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
        $data = $request->validate(
            $this->licenseValidationRules(),
            $this->licenseValidationMessages(),
            $this->licenseValidationAttributes()
        );

        $data['created_by'] = Auth::id();
        TiLicense::create($data);
        return back()->with('success', 'Licencia registrada.');
    }

    public function licenseEdit(TiLicense $license)
    {
        return view('ti-equipment.license-edit', compact('license'));
    }

    public function licenseUpdate(Request $request, TiLicense $license)
    {
        $rules = $this->licenseValidationRules();
        $rules['is_active'] = 'nullable|boolean';

        $data = $request->validate(
            $rules,
            $this->licenseValidationMessages(),
            $this->licenseValidationAttributes()
        );

        $assigned = $license->equipment()->count();
        if ($data['cantidad_licencias'] < $assigned) {
            return back()->withInput()->withErrors([
                'cantidad_licencias' => "No puedes bajar a {$data['cantidad_licencias']}: ya hay {$assigned} equipos con esta licencia. Desvincula primero.",
            ]);
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $license->update($data);

        return redirect()->route('ti-equipment.licenses')->with('success', 'Licencia actualizada.');
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

    private function previewNextEquipmentCode(): string
    {
        $format = SkuFormat::where('category', 'TI_EQUIPO')->first();
        return $format ? $format->preview() : $this->nextCode();
    }

    private function assignableLicensesQuery()
    {
        return TiLicense::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('fecha_vencimiento')
                    ->orWhereDate('fecha_vencimiento', '>=', now()->toDateString());
            });
    }

    private function licenseValidationRules(): array
    {
        return [
            'software'           => 'required|string|max:150',
            'tipo'               => 'required|in:OFFICE,ANTIVIRUS,OS,OTRO',
            'clave_licencia'     => 'nullable|string|max:255',
            'proveedor'          => 'nullable|string|max:150',
            'fecha_vencimiento'  => 'nullable|date',
            'cantidad_licencias' => 'required|integer|min:1',
            'notas'              => 'nullable|string',
        ];
    }

    private function licenseValidationMessages(): array
    {
        return [
            'required'                  => 'El campo :attribute es obligatorio.',
            'string'                    => 'El campo :attribute debe ser texto.',
            'integer'                   => 'El campo :attribute debe ser un número entero.',
            'max'                       => 'El campo :attribute no debe exceder :max caracteres.',
            'min'                       => 'El campo :attribute debe ser al menos :min.',
            'date'                      => 'El campo :attribute debe ser una fecha válida.',
            'in'                        => 'El valor seleccionado para :attribute no es válido.',
            'tipo.in'                   => 'El tipo seleccionado no es válido. Opciones permitidas: OFFICE, ANTIVIRUS, OS u OTRO.',
            'cantidad_licencias.min'    => 'La cantidad de licencias debe ser al menos 1.',
        ];
    }

    private function licenseValidationAttributes(): array
    {
        return [
            'software'           => 'software',
            'tipo'               => 'tipo',
            'clave_licencia'     => 'clave de licencia',
            'proveedor'          => 'proveedor',
            'fecha_vencimiento'  => 'fecha de vencimiento',
            'cantidad_licencias' => 'cantidad de licencias',
            'notas'              => 'notas',
            'is_active'          => 'estatus de la licencia',
        ];
    }
}
