{{-- resources/views/clients/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar cliente')
@section('page-title', 'Editar cliente')
@section('breadcrumb', 'Clientes / ' . $client->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card">
        <div class="card-header">
            <h2 class="text-base font-semibold text-gray-800">Editar: {{ $client->name }}</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('clients.update', $client) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Nombre --}}
                <div>
                    <label for="name" class="form-label">Nombre <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $client->name) }}"
                        class="form-input @error('name') border-red-400 @enderror"
                        required
                        autofocus
                    >
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nombre comercial --}}
                <div>
                    <label for="comercial_name" class="form-label">Nombre comercial</label>
                    <input
                        type="text"
                        id="comercial_name"
                        name="comercial_name"
                        value="{{ old('comercial_name', $client->comercial_name) }}"
                        class="form-input @error('comercial_name') border-red-400 @enderror"
                    >
                    @error('comercial_name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- RFC --}}
                <div>
                    <label for="rfc" class="form-label">RFC</label>
                    <input
                        type="text"
                        id="rfc"
                        name="rfc"
                        value="{{ old('rfc', $client->rfc) }}"
                        class="form-input @error('rfc') border-red-400 @enderror"
                        maxlength="20"
                    >
                    @error('rfc')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dirección --}}
                <div>
                    <label for="address" class="form-label">Dirección</label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        value="{{ old('address', $client->address) }}"
                        class="form-input @error('address') border-red-400 @enderror"
                    >
                    @error('address')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Colonia / CP --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="colonia" class="form-label">Colonia</label>
                        <input
                            type="text"
                            id="colonia"
                            name="colonia"
                            value="{{ old('colonia', $client->colonia) }}"
                            class="form-input @error('colonia') border-red-400 @enderror"
                        >
                        @error('colonia')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="zip_code" class="form-label">Código postal</label>
                        <input
                            type="text"
                            id="zip_code"
                            name="zip_code"
                            value="{{ old('zip_code', $client->zip_code) }}"
                            class="form-input @error('zip_code') border-red-400 @enderror"
                            maxlength="10"
                        >
                        @error('zip_code')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Ciudad --}}
                <div>
                    <label for="city" class="form-label">Ciudad</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="{{ old('city', $client->city) }}"
                        class="form-input @error('city') border-red-400 @enderror"
                    >
                    @error('city')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contacto --}}
                <div>
                    <label for="contact_id" class="form-label">Contacto</label>
                    <select id="contact_id" name="contact_id"
                            class="form-select @error('contact_id') border-red-400 @enderror">
                        <option value="">— Sin contacto —</option>
                        @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}"
                                {{ old('contact_id', $client->contact_id) == $contact->id ? 'selected' : '' }}>
                                {{ $contact->name }}{{ $contact->phone ? ' — ' . $contact->phone : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('contact_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Ejecutivo --}}
                <div>
                    <label for="user_id" class="form-label">Ejecutivo asignado</label>
                    <select id="user_id" name="user_id"
                            class="form-select @error('user_id') border-red-400 @enderror">
                        <option value="">— Sin asignar —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ old('user_id', $client->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Activo --}}
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="is_active"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $client->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <label for="is_active" class="form-label mb-0">Cliente activo</label>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                    <a href="{{ route('clients.index') }}" class="btn-secondary">Cancelar</a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
