{{-- resources/views/clients/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nuevo cliente')
@section('page-title', 'Nuevo cliente')
@section('breadcrumb', 'Clientes / Nuevo')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card">
        <div class="card-header">
            <h2 class="text-base font-semibold text-gray-800">Datos del cliente</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('clients.store') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Nombre --}}
                <div>
                    <label for="name" class="form-label">Nombre <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
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
                        value="{{ old('comercial_name') }}"
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
                        value="{{ old('rfc') }}"
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
                        value="{{ old('address') }}"
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
                            value="{{ old('colonia') }}"
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
                            value="{{ old('zip_code') }}"
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
                        value="{{ old('city') }}"
                        class="form-input @error('city') border-red-400 @enderror"
                    >
                    @error('city')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contacto --}}
                <div>
                    <label for="contact_id" class="form-label">Contacto existente</label>
                    <select id="contact_id" name="contact_id"
                            class="form-select @error('contact_id') border-red-400 @enderror">
                        <option value="">— Sin contacto —</option>
                        @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}"
                                {{ old('contact_id') == $contact->id ? 'selected' : '' }}>
                                {{ $contact->name }}{{ $contact->phone ? ' — ' . $contact->phone : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('contact_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nuevo contacto inline --}}
                <div class="border border-dashed border-gray-300 rounded-xl p-4 bg-gray-50">
                    <button type="button" onclick="toggleNewContact()"
                        class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1 mb-3">
                        <span id="toggle-icon">＋</span> Registrar nuevo contacto
                    </button>
                    <div id="new-contact-fields" class="{{ old('new_contact_name') ? '' : 'hidden' }} grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="form-label">Nombre del contacto</label>
                            <input name="new_contact_name" value="{{ old('new_contact_name') }}" class="form-input" placeholder="Nombre completo">
                        </div>
                        <div>
                            <label class="form-label">Teléfono</label>
                            <input name="new_contact_phone" value="{{ old('new_contact_phone') }}" class="form-input" placeholder="55 1234 5678">
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <input name="new_contact_email" type="email" value="{{ old('new_contact_email') }}" class="form-input" placeholder="correo@empresa.com">
                        </div>
                        <div>
                            <label class="form-label">Empresa</label>
                            <input name="new_contact_company" value="{{ old('new_contact_company') }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Cargo / Rol</label>
                            <input name="new_contact_rol" value="{{ old('new_contact_rol') }}" class="form-input" placeholder="Gerente, Compras…">
                        </div>
                        <p class="sm:col-span-2 text-xs text-gray-500">Si llenas el nombre, se creará un nuevo contacto y se asignará al cliente automáticamente.</p>
                    </div>
                </div>

                {{-- Ejecutivo --}}
                <div>
                    <label for="user_id" class="form-label">Ejecutivo asignado</label>
                    <select id="user_id" name="user_id"
                            class="form-select @error('user_id') border-red-400 @enderror">
                        <option value="">— Sin asignar —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ old('user_id') == $user->id ? 'selected' : '' }}>
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
                        {{ old('is_active', '1') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <label for="is_active" class="form-label mb-0">Cliente activo</label>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Guardar</button>
                    <a href="{{ route('clients.index') }}" class="btn-secondary">Cancelar</a>
                </div>

            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
function toggleNewContact() {
    const f = document.getElementById('new-contact-fields');
    const i = document.getElementById('toggle-icon');
    const hidden = f.classList.toggle('hidden');
    i.textContent = hidden ? '＋' : '－';
}
</script>
@endpush
@endsection
