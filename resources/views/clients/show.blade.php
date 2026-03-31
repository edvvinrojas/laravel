{{-- resources/views/clients/show.blade.php --}}
@extends('layouts.app')

@section('title', $client->name)
@section('page-title', $client->name)
@section('breadcrumb', 'Clientes / Detalle')

@section('content')
<div class="space-y-6">

    {{-- Header actions --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            @if($client->is_active)
                <span class="badge-green">Activo</span>
            @else
                <span class="badge-gray">Inactivo</span>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{-- Portal de contadores --}}
            @if($client->share_token)
            <button onclick="copyPortalLink()"
                    class="btn-success btn-sm flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span id="copyBtnText">Copiar enlace portal</span>
            </button>
            <a href="{{ route('portal.counters', $client->share_token) }}" target="_blank"
               class="btn-secondary btn-sm">Ver portal</a>
            <form action="{{ route('clients.portal.revoke', $client) }}" method="POST"
                  onsubmit="return confirm('¿Revocar acceso? El cliente ya no podrá ver sus contadores.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger btn-sm">Revocar acceso</button>
            </form>
            @else
            <form action="{{ route('clients.portal.generate', $client) }}" method="POST">
                @csrf
                <button type="submit" class="btn-secondary btn-sm flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Generar portal contadores
                </button>
            </form>
            @endif
            <a href="{{ route('clients.edit', $client) }}" class="btn-secondary btn-sm">Editar</a>
            <a href="{{ route('clients.index') }}" class="btn-secondary btn-sm">Volver</a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Client details --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-gray-800">Información general</h2>
            </div>
            <div class="card-body space-y-3 text-sm">
                <div class="grid grid-cols-2 gap-x-4 gap-y-3">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Nombre</p>
                        <p class="text-gray-800 font-medium mt-0.5">{{ $client->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Nombre comercial</p>
                        <p class="text-gray-700 mt-0.5">{{ $client->comercial_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">RFC</p>
                        <p class="font-mono text-gray-700 mt-0.5">{{ $client->rfc ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Ciudad</p>
                        <p class="text-gray-700 mt-0.5">{{ $client->city ?? '—' }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Dirección</p>
                        <p class="text-gray-700 mt-0.5">
                            @if($client->address)
                                {{ $client->address }}
                                @if($client->colonia), {{ $client->colonia }}@endif
                                @if($client->zip_code) C.P. {{ $client->zip_code }}@endif
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Contacto principal</p>
                        <p class="text-gray-700 mt-0.5">
                            @php $firstContact = $client->contacts->first() @endphp
                            @if($firstContact)
                                <span class="font-medium">{{ $firstContact->name }}</span>
                                @if($firstContact->phone)
                                    <br><span class="text-gray-500">{{ $firstContact->phone }}</span>
                                @endif
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Ejecutivo</p>
                        <p class="text-gray-700 mt-0.5">{{ $client->creator?->full_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Registrado</p>
                        <p class="text-gray-700 mt-0.5">{{ $client->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="space-y-4">
            <div class="card">
                <div class="card-body">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-blue-600">{{ $client->branches->count() }}</p>
                            <p class="text-xs text-gray-500 mt-1">Sucursales</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600">{{ $client->rents->count() }}</p>
                            <p class="text-xs text-gray-500 mt-1">Rentas</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-purple-600">{{ $client->sales->count() }}</p>
                            <p class="text-xs text-gray-500 mt-1">Ventas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contacts --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Contactos</h2>
            <a href="{{ route('clients.edit', $client) }}" class="btn-primary btn-sm">+ Agregar contacto</a>
        </div>
        <div class="card-body p-0">
            @if($client->contacts->isEmpty())
                <p class="text-center text-gray-400 py-8 text-sm">No hay contactos registrados.</p>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($client->contacts as $contact)
                    <div class="px-6 py-4 flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 text-sm">{{ $contact->name }}</p>
                            <div class="flex flex-wrap gap-x-4 gap-y-0.5 mt-0.5">
                                @if($contact->rol)
                                    <span class="text-xs text-gray-500">{{ $contact->rol }}</span>
                                @endif
                                @if($contact->phone)
                                    <span class="text-xs text-gray-500">{{ $contact->phone }}</span>
                                @endif
                                @if($contact->email)
                                    <span class="text-xs text-gray-500">{{ $contact->email }}</span>
                                @endif
                            </div>
                        </div>
                        <form action="{{ route('clients.contacts.destroy', [$client, $contact]) }}"
                              method="POST"
                              onsubmit="return confirm('¿Eliminar contacto «{{ addslashes($contact->name) }}»?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Branches --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Sucursales</h2>
            <button
                onclick="document.getElementById('branch-form').classList.toggle('hidden')"
                class="btn-primary btn-sm">
                + Agregar sucursal
            </button>
        </div>

        {{-- Inline branch form --}}
        <div id="branch-form" class="hidden border-b border-gray-100 bg-gray-50 px-6 py-4">
            <form action="{{ route('branches.store', $client) }}" method="POST">
                @csrf
                <p class="text-sm font-medium text-gray-700 mb-3">Nueva sucursal</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="form-input" required placeholder="Ej. Matriz, Sucursal Norte…">
                    </div>
                    <div>
                        <label class="form-label">Ciudad</label>
                        <input type="text" name="city" class="form-input" placeholder="Ciudad">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="address" class="form-input" placeholder="Calle, número">
                    </div>
                    <div>
                        <label class="form-label">Colonia</label>
                        <input type="text" name="colonia" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Código postal</label>
                        <input type="text" name="zip_code" class="form-input" maxlength="10">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_main" name="is_main" value="1"
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_main" class="form-label mb-0">Sucursal principal</label>
                    </div>
                </div>
                <div class="flex gap-2 mt-4">
                    <button type="submit" class="btn-primary btn-sm">Guardar sucursal</button>
                    <button type="button"
                            onclick="document.getElementById('branch-form').classList.add('hidden')"
                            class="btn-secondary btn-sm">Cancelar</button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            @if($client->branches->isEmpty())
                <p class="text-center text-gray-400 py-10 text-sm">No hay sucursales registradas.</p>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($client->branches as $branch)
                    <div class="px-6 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-800 text-sm">{{ $branch->name }}</span>
                                    @if($branch->is_main)
                                        <span class="badge-blue">Principal</span>
                                    @endif
                                </div>
                                @if($branch->address)
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $branch->address }}
                                        @if($branch->colonia), {{ $branch->colonia }}@endif
                                        @if($branch->city), {{ $branch->city }}@endif
                                        @if($branch->zip_code) C.P. {{ $branch->zip_code }}@endif
                                    </p>
                                @endif

                                {{-- Areas --}}
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    @foreach($branch->areas as $area)
                                        <span class="inline-flex items-center gap-1 pl-2 pr-1 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                                            {{ $area->name }}
                                            <form action="{{ route('branches.areas.destroy', [$branch, $area]) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('¿Eliminar área «{{ addslashes($area->name) }}»?')"
                                                  class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-red-500 leading-none">✕</button>
                                            </form>
                                        </span>
                                    @endforeach
                                    <button type="button"
                                            onclick="document.getElementById('area-form-{{ $branch->id }}').classList.toggle('hidden')"
                                            class="text-xs text-blue-600 hover:underline">+ Área</button>
                                </div>
                                {{-- Inline area form --}}
                                <form id="area-form-{{ $branch->id }}"
                                      action="{{ route('branches.areas.store', $branch) }}"
                                      method="POST"
                                      class="hidden flex items-center gap-2 mt-2">
                                    @csrf
                                    <input name="name" type="text" placeholder="Nombre del área" class="form-input text-xs py-1" required>
                                    <button type="submit" class="btn-primary btn-sm">Guardar</button>
                                    <button type="button"
                                            onclick="document.getElementById('area-form-{{ $branch->id }}').classList.add('hidden')"
                                            class="btn-secondary btn-sm">Cancelar</button>
                                </form>
                            </div>
                            <form action="{{ route('branches.destroy', $branch) }}"
                                  method="POST"
                                  onsubmit="return confirm('¿Eliminar sucursal «{{ addslashes($branch->name) }}»?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Documentos --}}
    @php
        $docLabels = [
            'acta_constitutiva'     => 'Acta constitutiva',
            'constancia_fiscal'     => 'Constancia de situación fiscal (CSF)',
            'comprobante_domicilio' => 'Comprobante de domicilio',
            'deposito_garantia'     => 'Comprobante de depósito en garantía',
            'ine_representante'     => 'INE del representante legal',
            'carta_poder'           => 'Carta poder',
            'estado_cuenta'         => 'Carátula de estado de cuenta',
            'alta_cliente'          => 'Alta cliente',
            'cotizacion'            => 'Cotización autorizada',
            'contrato'              => 'Contrato',
        ];
        $docs = $client->documents ?? [];
    @endphp
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Documentos del cliente</h2>
            <button onclick="document.getElementById('doc-form').classList.toggle('hidden')" class="btn-primary btn-sm">+ Subir documento</button>
        </div>

        <div id="doc-form" class="hidden border-b border-gray-100 bg-gray-50 px-6 py-4">
            <form action="{{ route('clients.documents.upload', $client) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    <div class="sm:col-span-1">
                        <label class="form-label">Tipo de documento</label>
                        <select name="doc_type" class="form-select" required>
                            <option value="">Seleccionar…</option>
                            @foreach($docLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-1">
                        <label class="form-label">Archivo PDF (máx. 10 MB)</label>
                        <input type="file" name="archivo" accept=".pdf" class="form-input" required>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary btn-sm">Subir</button>
                        <button type="button" onclick="document.getElementById('doc-form').classList.add('hidden')" class="btn-secondary btn-sm">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="divide-y divide-gray-100">
                @foreach($docLabels as $key => $label)
                <div class="px-6 py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        @if(isset($docs[$key]))
                            <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-gray-200 flex-shrink-0"></span>
                        @endif
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(isset($docs[$key]))
                            <a href="{{ Storage::url($docs[$key]) }}" target="_blank" class="btn-secondary btn-sm">Ver PDF</a>
                            <form action="{{ route('clients.documents.destroy', [$client, $key]) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar este documento?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400">Sin documento</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

@push('scripts')
@if($client->share_token)
<script>
function copyPortalLink() {
    const url = '{{ route('portal.counters', $client->share_token) }}';
    navigator.clipboard.writeText(url).then(() => {
        const btn = document.getElementById('copyBtnText');
        btn.textContent = '¡Copiado!';
        setTimeout(() => btn.textContent = 'Copiar enlace portal', 2000);
    });
}
</script>
@endif
@endpush
@endsection
