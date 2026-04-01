<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Item;
use App\Models\Rent;
use App\Models\Sale;
use App\Models\Ticket;
use App\Models\Purchase;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $like = '%' . $q . '%';
        $results = collect();

        // Clientes
        Client::where('name', 'like', $like)
            ->orWhere('rfc', 'like', $like)
            ->limit(5)->get()
            ->each(fn ($c) => $results->push([
                'type'  => 'Cliente',
                'label' => $c->name,
                'sub'   => $c->rfc,
                'url'   => route('clients.show', $c),
            ]));

        // Equipos (Items)
        Item::where('sku', 'like', $like)
            ->orWhere('serial_number', 'like', $like)
            ->orWhere('model', 'like', $like)
            ->limit(5)->get()
            ->each(fn ($i) => $results->push([
                'type'  => 'Equipo',
                'label' => $i->sku . ' — ' . $i->model,
                'sub'   => $i->serial_number ?? $i->location_status,
                'url'   => route('equipment.show', $i),
            ]));

        // Rentas
        Rent::where('contract_number', 'like', $like)
            ->limit(5)->get()
            ->each(fn ($r) => $results->push([
                'type'  => 'Renta',
                'label' => $r->contract_number,
                'sub'   => $r->contract_status,
                'url'   => route('rents.show', $r),
            ]));

        // Ventas
        Sale::where('invoice_number', 'like', $like)
            ->limit(5)->get()
            ->each(fn ($s) => $results->push([
                'type'  => 'Venta',
                'label' => $s->invoice_number ?? "Venta #{$s->id}",
                'sub'   => $s->sale_status,
                'url'   => route('sales.show', $s),
            ]));

        // Tickets
        Ticket::where('description', 'like', $like)
            ->orWhere('id', $q)
            ->limit(5)->get()
            ->each(fn ($t) => $results->push([
                'type'  => 'Ticket',
                'label' => "Ticket #{$t->id}",
                'sub'   => $t->report_status,
                'url'   => route('tickets.show', $t),
            ]));

        // Compras
        Purchase::where('name', 'like', $like)
            ->limit(5)->get()
            ->each(fn ($p) => $results->push([
                'type'  => 'Compra',
                'label' => $p->name,
                'sub'   => $p->status,
                'url'   => route('purchases.show', $p),
            ]));

        // Empleados
        Employee::where('nombre', 'like', $like)
            ->orWhere('nss', 'like', $like)
            ->orWhere('rfc', 'like', $like)
            ->limit(5)->get()
            ->each(fn ($e) => $results->push([
                'type'  => 'Empleado',
                'label' => $e->nombre,
                'sub'   => $e->rfc,
                'url'   => route('employees.show', $e),
            ]));

        return response()->json($results->take(25)->values());
    }
}
