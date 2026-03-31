<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with('contacts')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('comercial_name', 'like', "%{$search}%")
                       ->orWhere('rfc', 'like', "%{$search}%")
                       ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('clients.index', compact('query'));
    }

    public function create()
    {
        $users = User::where('is_active', true)->orderBy('full_name')->get();
        return view('clients.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'comercial_name'    => 'nullable|string|max:255',
            'rfc'               => 'nullable|string|max:20',
            'address'           => 'nullable|string|max:255',
            'colonia'           => 'nullable|string|max:255',
            'zip_code'          => 'nullable|string|max:10',
            'city'              => 'nullable|string|max:100',
            'user_id'           => 'nullable|exists:users,id',
            // Contacto inline
            'new_contact_name'  => 'nullable|string|max:255',
            'new_contact_phone' => 'nullable|string|max:50',
            'new_contact_email' => 'nullable|email|max:255',
            'new_contact_rol'   => 'nullable|string|max:100',
        ]);

        $client = Client::create([
            'name'           => $validated['name'],
            'comercial_name' => $validated['comercial_name'] ?? null,
            'rfc'            => $validated['rfc'] ?? null,
            'address'        => $validated['address'] ?? null,
            'colonia'        => $validated['colonia'] ?? null,
            'zip_code'       => $validated['zip_code'] ?? null,
            'city'           => $validated['city'] ?? null,
            'user_id'        => $validated['user_id'] ?? null,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        if (!empty($validated['new_contact_name'])) {
            Contact::create([
                'client_id' => $client->id,
                'name'      => $validated['new_contact_name'],
                'phone'     => $validated['new_contact_phone'] ?? null,
                'email'     => $validated['new_contact_email'] ?? null,
                'rol'       => $validated['new_contact_rol'] ?? null,
                'is_active' => true,
            ]);
        }

        return redirect()->route('clients.index')->with('success', 'Cliente creado correctamente.');
    }

    public function show(Client $client)
    {
        $client->load('contacts', 'creator', 'branches.areas');
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $client->load('contacts');
        $users = User::where('is_active', true)->orderBy('full_name')->get();
        return view('clients.edit', compact('client', 'users'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'comercial_name'    => 'nullable|string|max:255',
            'rfc'               => 'nullable|string|max:20',
            'address'           => 'nullable|string|max:255',
            'colonia'           => 'nullable|string|max:255',
            'zip_code'          => 'nullable|string|max:10',
            'city'              => 'nullable|string|max:100',
            'user_id'           => 'nullable|exists:users,id',
            // Nuevo contacto inline
            'new_contact_name'  => 'nullable|string|max:255',
            'new_contact_phone' => 'nullable|string|max:50',
            'new_contact_email' => 'nullable|email|max:255',
            'new_contact_rol'   => 'nullable|string|max:100',
        ]);

        $client->update([
            'name'           => $validated['name'],
            'comercial_name' => $validated['comercial_name'] ?? null,
            'rfc'            => $validated['rfc'] ?? null,
            'address'        => $validated['address'] ?? null,
            'colonia'        => $validated['colonia'] ?? null,
            'zip_code'       => $validated['zip_code'] ?? null,
            'city'           => $validated['city'] ?? null,
            'user_id'        => $validated['user_id'] ?? null,
            'is_active'      => $request->boolean('is_active'),
        ]);

        if (!empty($validated['new_contact_name'])) {
            Contact::create([
                'client_id' => $client->id,
                'name'      => $validated['new_contact_name'],
                'phone'     => $validated['new_contact_phone'] ?? null,
                'email'     => $validated['new_contact_email'] ?? null,
                'rol'       => $validated['new_contact_rol'] ?? null,
                'is_active' => true,
            ]);
        }

        return redirect()->route('clients.edit', $client)->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroyContact(Client $client, Contact $contact)
    {
        $contact->delete();
        return back()->with('success', 'Contacto eliminado.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Cliente eliminado correctamente.');
    }

    public function uploadDocument(Request $request, Client $client)
    {
        $request->validate([
            'doc_type' => 'required|string|in:acta_constitutiva,constancia_fiscal,comprobante_domicilio,deposito_garantia,ine_representante,carta_poder,estado_cuenta,alta_cliente,cotizacion,contrato',
            'archivo'  => 'required|file|mimes:pdf|max:10240',
        ]);

        $docType = $request->doc_type;
        $docs    = $client->documents ?? [];

        if (isset($docs[$docType])) {
            Storage::disk('public')->delete($docs[$docType]);
        }

        $path = $request->file('archivo')->store("clients/{$client->id}", 'public');
        $docs[$docType] = $path;
        $client->update(['documents' => $docs]);

        return back()->with('success', 'Documento subido correctamente.');
    }

    public function destroyDocument(Client $client, string $docType)
    {
        $docs = $client->documents ?? [];
        if (isset($docs[$docType])) {
            Storage::disk('public')->delete($docs[$docType]);
            unset($docs[$docType]);
            $client->update(['documents' => $docs]);
        }
        return back()->with('success', 'Documento eliminado.');
    }
}
