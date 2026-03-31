<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseQuote;
use App\Models\Sparepart;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PurchaseController extends Controller
{
    private function notify(int $userId, string $title, string $message, string $link): void
    {
        Notification::create([
            'user_id'    => $userId,
            'type'       => 'compra',
            'title'      => $title,
            'message'    => $message,
            'link'       => $link,
            'is_read'    => false,
            'created_at' => now(),
        ]);
    }

    private function notifyRole(string $rol, string $title, string $message, string $link): void
    {
        User::where('rol', $rol)->where('is_active', true)->each(
            fn($u) => $this->notify($u->id, $title, $message, $link)
        );
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $purchases = Purchase::with(['sparepart', 'user'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when(
                $user->rol === 'usuario',
                fn($q) => $q->where('user_id', $user->id)
            )
            ->orderBy('created_at', 'desc')
            ->paginate(15)->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $spareparts = Sparepart::orderBy('name')->get();
        return view('purchases.create', compact('spareparts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sparepart_id'  => 'nullable|exists:spareparts,id',
            'name'          => 'required|string|max:255',
            'amount'        => 'required|integer|min:1',
            'quality'       => 'nullable|string|max:100',
            'justification' => 'nullable|string',
            'type'          => 'required|in:INTERNA,VENTA',
            'comments'      => 'nullable|string',
        ]);

        $data['user_id'] = auth()->id();
        $data['status']  = 'SOLICITADO';

        $purchase = Purchase::create($data);

        $link    = route('purchases.show', $purchase);
        $solicit = auth()->user()->full_name;
        $this->notifyRole('gerencia', "Nueva solicitud de compra",
            "{$solicit} solicitó: {$purchase->name} (x{$purchase->amount})", $link);
        $this->notifyRole('administrador', "Nueva solicitud de compra",
            "{$solicit} solicitó: {$purchase->name} (x{$purchase->amount})", $link);

        return redirect()->route('purchases.show', $purchase)->with('success', 'Solicitud enviada. El gerente recibirá una notificación.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['sparepart', 'user', 'areaChief', 'admin', 'quotes']);
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $spareparts = Sparepart::orderBy('name')->get();
        return view('purchases.edit', compact('purchase', 'spareparts'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $user = auth()->user();
        $isCompras = in_array($user->rol, ['administrador', 'gerencia']) || $user->department === 'administracion';

        $rules = [
            'name'              => 'required|string|max:255',
            'amount'            => 'required|integer|min:1',
            'authorized_amount' => 'nullable|integer|min:0',
            'quality'           => 'nullable|string|max:100',
            'justification'     => 'nullable|string',
            'type'              => 'required|in:INTERNA,VENTA',
            'comments'          => 'nullable|string',
        ];

        if ($isCompras) {
            $rules += [
                'shipping_method' => 'nullable|string|max:100',
                'shipping_cost'   => 'nullable|numeric|min:0',
                'shipping_code'   => 'nullable|string|max:100',
                'quotes'          => 'nullable|array',
                'quotes.*.supplier_name' => 'required_with:quotes.*|string|max:255',
                'quotes.*.cost'          => 'required_with:quotes.*|numeric|min:0',
                'quotes.*.notes'         => 'nullable|string',
            ];
        }

        $data = $request->validate($rules);

        // Sync quotes (delete old, insert new) — only for compras/admin
        if ($isCompras) {
            $purchase->quotes()->delete();
            foreach ($request->input('quotes', []) as $q) {
                if (!empty($q['supplier_name'])) {
                    $purchase->quotes()->create([
                        'supplier_name' => $q['supplier_name'],
                        'cost'          => $q['cost'],
                        'notes'         => $q['notes'] ?? null,
                    ]);
                }
            }
            unset($data['quotes']);
        }

        $purchase->update($data);

        return redirect()->route('purchases.show', $purchase)->with('success', 'Compra actualizada.');
    }

    public function approve(Purchase $purchase)
    {
        $user = auth()->user();
        if (!in_array($user->rol, ['gerencia', 'administrador'])) {
            abort(403);
        }

        $purchase->update([
            'status'                      => 'AUTORIZADO',
            'authorized_by_area_chief_id' => $user->id,
            'authorized_by_area_chief_date' => Carbon::now(),
        ]);

        $link = route('purchases.show', $purchase);
        $this->notify($purchase->user_id, "Compra autorizada",
            "Tu solicitud «{$purchase->name}» fue autorizada. Pasa a compras para seguimiento.", $link);

        User::where('department', 'administracion')->where('is_active', true)->each(
            fn($u) => $this->notify($u->id, "Compra autorizada para procesar",
                "Solicitud «{$purchase->name}» lista para cotizar proveedores.", $link)
        );

        return back()->with('success', 'Compra autorizada correctamente.');
    }

    public function updateStatus(Request $request, Purchase $purchase)
    {
        $user = auth()->user();
        $isCompras = $user->rol === 'administrador' || $user->department === 'administracion';
        if (!$isCompras) abort(403);

        $request->validate(['status' => 'required|in:PEDIDO,LLEGO,ENTREGADO']);

        $purchase->update(['status' => $request->status]);

        $link = route('purchases.show', $purchase);
        $this->notify($purchase->user_id, "Actualización de compra",
            "Tu solicitud «{$purchase->name}» cambió a: {$request->status}", $link);

        return back()->with('success', "Estatus actualizado a {$request->status}.");
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Compra eliminada.');
    }
}
