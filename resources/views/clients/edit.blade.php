@extends('layouts.app')
@section('title', 'Editar cliente')
@section('page-title', 'Editar cliente')
@section('breadcrumb', 'Clientes / ' . $client->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    {{-- Datos del cliente --}}
    <div class="card">
        <div class="card-header">
            <h2 class="text-base font-semibold text-gray-800">{{ $client->name }}</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('clients.update', $client) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="form-label">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $client->name) }}"
                           class="form-input @error('name') border-red-400 @enderror" required autofocus>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Nombre comercial</label>
                    <input type="text" name="comercial_name" value="{{ old('comercial_name', $client->comercial_name) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">RFC</label>
                    <input type="text" name="rfc" value="{{ old('rfc', $client->rfc) }}" class="form-input" maxlength="20">
                </div>

                <div>
                    <label class="form-label">Dirección</label>
                    <input type="text" name="address" value="{{ old('address', $client->address) }}" class="form-input">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Colonia</label>
                        <input type="text" name="colonia" value="{{ old('colonia', $client->colonia) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Código postal</label>
                        <input type="text" name="zip_code" value="{{ old('zip_code', $client->zip_code) }}" class="form-input" maxlength="10">
                    </div>
                </div>

                <div>
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="city" value="{{ old('city', $client->city) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Ejecutivo asignado</label>
                    <select name="user_id" class="form-select">
                        <option value="">— Sin asignar —</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $client->user_id) == $user->id ? 'selected' : '' }}>
                            {{ $user->full_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           {{ old('is_active', $client->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="is_active" class="form-label mb-0">Cliente activo</label>
                </div>

                {{-- Agregar nuevo contacto --}}
                @include('clients._contact_form')

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                    <a href="{{ route('clients.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Contactos existentes --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-gray-700">Contactos del cliente</h3>
        </div>
        @if($client->contacts->isEmpty())
        <div class="card-body text-sm text-gray-400">Sin contactos registrados.</div>
        @else
        <div class="card-body p-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th class="text-right">Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($client->contacts as $contact)
                    <tr>
                        <td class="font-medium">{{ $contact->name }}</td>
                        <td class="text-sm text-gray-600">{{ $contact->phone ?? '—' }}</td>
                        <td class="text-sm text-gray-600">{{ $contact->email ?? '—' }}</td>
                        <td class="text-sm text-gray-500">{{ $contact->rol ?? '—' }}</td>
                        <td class="text-right">
                            <form action="{{ route('clients.contacts.destroy', [$client, $contact]) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar contacto?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection
