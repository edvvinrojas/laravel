<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Client;
use App\Models\PrintCounter;
use App\Models\Rent;
use App\Models\Sale;
use App\Services\FacturaComService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'cobranza');

        $billings = Billing::with(['client'])
            ->when($request->search, fn($q) =>
                $q->where(fn($q2) => $q2
                    ->whereHas('client', fn($c) => $c->where('name', 'like', "%{$request->search}%"))
                    ->orWhere('invoice_number', 'like', "%{$request->search}%"))
            )
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type,   fn($q) => $q->where('billing_type', $request->type))
            ->when($request->date_from, fn($q) => $q->where('target_date', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->where('target_date', '<=', $request->date_to))
            ->where('is_active', true)
            ->orderBy('due_date')
            ->paginate(20)->withQueryString();

        $totals = [
            'pending' => Billing::where('status', 'PENDIENTE')->where('is_active', true)->sum('amount'),
            'overdue' => Billing::where('status', 'VENCIDO')->where('is_active', true)->sum('amount'),
            'paid'    => Billing::where('status', 'PAGADO')->whereMonth('payment_date', now()->month)->sum('amount'),
        ];

        return view('billing.index', compact('billings', 'totals', 'tab'));
    }

    public function create()
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $rents   = Rent::where('contract_status', 'VIGENTE')->with('client')->get();
        $sales   = Sale::whereIn('sale_status', ['PENDIENTE', 'CONFIRMADA'])->with('client')->get();
        return view('billing.create', compact('clients', 'rents', 'sales'));
    }

    public function store(Request $request)
    {
        $base = $request->validate([
            'billing_type'   => 'required|in:RENTA,VENTA',
            'rent_id'        => 'nullable|exists:rents,id',
            'sale_id'        => 'nullable|exists:sales,id',
            'invoice_number' => 'nullable|string|max:50|unique:billings',
            'amount'         => 'required|numeric|min:0',
            'target_date'    => 'required|date',
            'due_date'       => 'required|date',
            'payment_term'   => 'nullable|integer|min:0',
            'payment_day'    => 'nullable|integer|min:1|max:31',
            'comment'        => 'nullable|string',
        ]);

        // Enforce: exactly one FK must match billing_type
        if ($base['billing_type'] === 'RENTA') {
            if (empty($base['rent_id'])) {
                return back()->withErrors(['rent_id' => 'Debe seleccionar una renta.'])->withInput();
            }
            $base['sale_id'] = null;
            $source = Rent::findOrFail($base['rent_id']);
        } else {
            if (empty($base['sale_id'])) {
                return back()->withErrors(['sale_id' => 'Debe seleccionar una venta.'])->withInput();
            }
            $base['rent_id'] = null;
            $source = Sale::findOrFail($base['sale_id']);
        }

        // Derive client/branch/area from the linked transaction — avoids denormalization
        $base['client_id'] = $source->client_id;
        $base['branch_id'] = $source->branch_id ?? null;
        $base['area_id']   = $source->area_id   ?? null;
        $base['created_by'] = Auth::id();
        $base['status']     = 'PENDIENTE';

        // Para rentas con servicio de impresión: recalcular monto server-side
        // sumando todos los contadores no facturados. Esto evita que el excedente
        // se pierda si el usuario olvida incluirlo o el JS no lo cargó.
        $unbilledCounters = collect();
        $unbilledCounters = collect();
        if ($base['billing_type'] === 'RENTA' && $source->has_print_service) {
            $unbilledCounters = PrintCounter::where('rent_id', $source->id)
                ->where('is_active', true)
                ->where('is_billed', false)
                ->with('rent')
                ->get()
                ->filter(fn($pc) => $pc->total_excess_amount > 0);

            $excess = $unbilledCounters->sum(fn($pc) => $pc->total_excess_amount);
            $base['amount'] = round((float) $source->rent + (float) $excess, 2);
        }

        $billing = Billing::create($base);

        if ($unbilledCounters->isNotEmpty()) {
            PrintCounter::whereIn('id', $unbilledCounters->pluck('id'))
                ->update(['is_billed' => true, 'billing_id' => $billing->id]);
        }

        return redirect()->route('billing.index')->with('success', 'Factura creada correctamente.');
    }

    public function show(Billing $billing)
    {
        $billing->load(['client', 'branch', 'area', 'rent.item', 'sale.item', 'creator', 'printCounter']);
        return view('billing.show', compact('billing'));
    }

    public function pdf(Billing $billing)
    {
        $billing->load(['client', 'branch', 'area', 'rent.item.brand', 'sale.item.brand', 'creator', 'printCounter']);
        return view('pdf.billing', compact('billing'));
    }

    public function edit(Billing $billing)
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        return view('billing.edit', compact('billing', 'clients'));
    }

    public function update(Request $request, Billing $billing)
    {
        $data = $request->validate([
            'invoice_number' => "nullable|string|max:50|unique:billings,invoice_number,{$billing->id}",
            'amount'         => 'required|numeric|min:0',
            'target_date'    => 'required|date',
            'due_date'       => 'required|date',
            'payment_term'   => 'nullable|integer|min:0',
            'payment_day'    => 'nullable|integer|min:1|max:31',
            'comment'        => 'nullable|string',
            'follow_up'      => 'boolean',
        ]);

        $data['follow_up'] = $request->boolean('follow_up');
        $billing->update($data);

        return redirect()->route('billing.show', $billing)->with('success', 'Factura actualizada.');
    }

    public function markPaid(Request $request, Billing $billing)
    {
        $request->validate(['payment_date' => 'required|date']);
        $billing->update(['status' => 'PAGADO', 'payment_date' => $request->payment_date]);
        return back()->with('success', 'Factura marcada como pagada.');
    }

    public function stampFacturaCom(Request $request, Billing $billing, FacturaComService $facturaCom)
    {
        if (! $facturaCom->isConfigured()) {
            return back()->withErrors([
                'facturacom' => 'Configura FACTURACOM_API_KEY y FACTURACOM_SECRET_KEY en el .env',
            ]);
        }

        $validated = $request->validate([
            'receptor_uid' => 'required|string|max:50',
            'serie_id' => 'required|integer|min:1',
            'uso_cfdi' => 'required|string|max:10',
            'forma_pago' => 'required|string|max:10',
            'metodo_pago' => 'required|string|max:10',
            'moneda' => 'nullable|string|max:5',
            'clave_prod_serv' => 'nullable|string|max:20',
            'clave_unidad' => 'nullable|string|max:10',
            'unidad' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'objeto_imp' => 'nullable|in:01,02,03,04',
            'enviar_correo' => 'nullable|boolean',
            'num_order' => 'nullable|string|max:60',
            'raw_payload' => 'nullable|json',
        ]);

        try {
            $payload = $this->buildFacturaComPayload($validated, $billing);
            $response = $facturaCom->createCfdi40($payload);

            $this->applyFacturaComResponse($billing, $response);

            $isSuccess = ($response['response'] ?? null) === 'success' || ($response['status'] ?? null) === 'success';

            if (! $isSuccess) {
                $message = is_array($response['message'] ?? null)
                    ? (($response['message']['message'] ?? null) ?: 'Factura.com devolvio un error al timbrar.')
                    : ($response['message'] ?? 'Factura.com devolvio un error al timbrar.');

                return back()->withErrors(['facturacom' => $message]);
            }

            return back()->with('success', 'CFDI timbrado correctamente en Factura.com.');
        } catch (Throwable $e) {
            return back()->withErrors(['facturacom' => 'No se pudo timbrar en Factura.com: ' . $e->getMessage()]);
        }
    }

    public function syncFacturaCom(Request $request, Billing $billing, FacturaComService $facturaCom)
    {
        if (! $facturaCom->isConfigured()) {
            return back()->withErrors([
                'facturacom' => 'Configura FACTURACOM_API_KEY y FACTURACOM_SECRET_KEY en el .env',
            ]);
        }

        $validated = $request->validate([
            'search_mode' => 'nullable|in:uid,uuid,order',
            'search_value' => 'nullable|string|max:80',
        ]);

        $mode = $validated['search_mode'] ?? null;
        $value = trim((string) ($validated['search_value'] ?? ''));

        if ($mode === null || $value === '') {
            if (! empty($billing->facturacom_uuid)) {
                $mode = 'uuid';
                $value = $billing->facturacom_uuid;
            } elseif (! empty($billing->facturacom_uid)) {
                $mode = 'uid';
                $value = $billing->facturacom_uid;
            } else {
                $mode = 'order';
                $value = 'BILLING-' . $billing->id;
            }
        }

        try {
            $response = match ($mode) {
                'uid' => $facturaCom->getCfdiByUid($value),
                'uuid' => $facturaCom->getCfdiByUuid($value),
                default => $facturaCom->getCfdiByOrder($value),
            };

            $this->applyFacturaComResponse($billing, $response);

            $isSuccess = ($response['response'] ?? null) === 'success' || ($response['status'] ?? null) === 'success';

            if (! $isSuccess) {
                $message = is_array($response['message'] ?? null)
                    ? (($response['message']['message'] ?? null) ?: 'No fue posible sincronizar el CFDI.')
                    : ($response['message'] ?? 'No fue posible sincronizar el CFDI.');

                return back()->withErrors(['facturacom' => $message]);
            }

            return back()->with('success', 'CFDI sincronizado correctamente desde Factura.com.');
        } catch (Throwable $e) {
            return back()->withErrors(['facturacom' => 'No se pudo consultar Factura.com: ' . $e->getMessage()]);
        }
    }

    private function buildFacturaComPayload(array $validated, Billing $billing): array
    {
        if (! empty($validated['raw_payload'])) {
            $decoded = json_decode((string) $validated['raw_payload'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $descripcion = $validated['descripcion'] ?? ('Servicio ERP - Folio ' . $billing->id);
        $claveProdServ = $validated['clave_prod_serv'] ?? '81112101';
        $claveUnidad = $validated['clave_unidad'] ?? 'E48';
        $unidad = $validated['unidad'] ?? 'Unidad de servicio';
        $objetoImp = $validated['objeto_imp'] ?? '01';
        $monto = number_format((float) $billing->amount, 6, '.', '');

        $payload = [
            'Receptor' => [
                'UID' => $validated['receptor_uid'],
            ],
            'TipoDocumento' => 'factura',
            'Conceptos' => [[
                'ClaveProdServ' => $claveProdServ,
                'Cantidad' => '1.000000',
                'ClaveUnidad' => $claveUnidad,
                'Unidad' => $unidad,
                'ValorUnitario' => $monto,
                'Descripcion' => $descripcion,
                'ObjetoImp' => $objetoImp,
            ]],
            'UsoCFDI' => $validated['uso_cfdi'],
            'Serie' => (int) $validated['serie_id'],
            'FormaPago' => $validated['forma_pago'],
            'MetodoPago' => $validated['metodo_pago'],
            'Moneda' => $validated['moneda'] ?? 'MXN',
            'NumOrder' => $validated['num_order'] ?? ('BILLING-' . $billing->id),
            'EnviarCorreo' => (bool) ($validated['enviar_correo'] ?? false),
        ];

        return $payload;
    }

    private function applyFacturaComResponse(Billing $billing, array $response): void
    {
        $data = $response['data'] ?? [];
        $inv = $response['INV'] ?? [];

        $folio = null;
        if (! empty($inv['Serie']) || ! empty($inv['Folio'])) {
            $folio = trim((string) ($inv['Serie'] ?? '') . (string) ($inv['Folio'] ?? ''));
        }
        if (! $folio && ! empty($data['Folio'])) {
            $folio = (string) $data['Folio'];
        }

        $billing->update([
            'facturacom_uid' => $response['uid'] ?? $response['invoice_uid'] ?? $data['UID'] ?? $billing->facturacom_uid,
            'facturacom_uuid' => $response['UUID'] ?? $response['uuid'] ?? $data['UUID'] ?? $billing->facturacom_uuid,
            'facturacom_folio' => $folio ?? $billing->facturacom_folio,
            'facturacom_status' => $response['response'] ?? $response['status'] ?? $data['Status'] ?? $billing->facturacom_status,
            'facturacom_synced_at' => now(),
            'facturacom_last_response' => $response,
        ]);

        if (! $billing->invoice_number && ! empty($folio)) {
            $billing->update(['invoice_number' => $folio]);
        }
    }

    public function destroy(Billing $billing)
    {
        $billing->update(['is_active' => false]);
        return redirect()->route('billing.index')->with('success', 'Factura eliminada.');
    }
}
