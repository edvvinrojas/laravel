<?php
// app/Http/Controllers/ClientController.php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with('contact')
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
        $contacts = Contact::orderBy('name')->get();
        $users    = User::where('is_active', true)->orderBy('full_name')->get();

        return view('clients.create', compact('contacts', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'comercial_name' => 'nullable|string|max:255',
            'rfc'            => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'colonia'        => 'nullable|string|max:255',
            'zip_code'       => 'nullable|string|max:10',
            'city'           => 'nullable|string|max:100',
            'contact_id'     => 'nullable|exists:contacts,id',
            'user_id'        => 'nullable|exists:users,id',
            'is_active'      => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    public function show(Client $client)
    {
        $client->load('contact', 'creator', 'branches.areas');

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $contacts = Contact::orderBy('name')->get();
        $users    = User::where('is_active', true)->orderBy('full_name')->get();

        return view('clients.edit', compact('client', 'contacts', 'users'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'comercial_name' => 'nullable|string|max:255',
            'rfc'            => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'colonia'        => 'nullable|string|max:255',
            'zip_code'       => 'nullable|string|max:10',
            'city'           => 'nullable|string|max:100',
            'contact_id'     => 'nullable|exists:contacts,id',
            'user_id'        => 'nullable|exists:users,id',
            'is_active'      => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }
}
