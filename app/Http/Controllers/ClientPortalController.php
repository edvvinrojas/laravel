<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientPortalController extends Controller
{
    /** Public page — no auth required */
    public function show(string $token)
    {
        $client = Client::where('share_token', $token)->firstOrFail();

        $rents = $client->rents()
            ->where('has_print_service', true)
            ->whereIn('contract_status', ['VIGENTE', 'PENDIENTE'])
            ->with([
                'item.brand',
                'branch',
                'area',
                'printCounters' => fn($q) => $q->where('is_active', true)
                    ->orderBy('period_year', 'desc')
                    ->orderBy('period_month', 'desc'),
            ])
            ->orderBy('contract_number')
            ->get();

        return view('portal.counters', compact('client', 'rents'));
    }

    /** Generate (or regenerate) share token — requires auth */
    public function generateToken(Client $client)
    {
        $client->update(['share_token' => Str::random(48)]);

        return back()->with('success', 'Enlace generado. Ya puedes compartirlo con el cliente.');
    }

    /** Revoke share token */
    public function revokeToken(Client $client)
    {
        $client->update(['share_token' => null]);

        return back()->with('success', 'Enlace revocado. El cliente ya no podrá acceder al portal.');
    }
}
