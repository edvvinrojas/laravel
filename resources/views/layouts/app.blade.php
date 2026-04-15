<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CopyMart ERP') — CopyMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style type="text/tailwindcss">
        @layer components {
            .nav-link {
                @apply flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-800 transition-colors duration-150;
            }

            .nav-link.active {
                @apply bg-blue-600 text-white;
            }

            .btn {
                @apply inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed;
            }

            .btn-primary {
                @apply btn bg-blue-600 text-white hover:bg-blue-700;
            }

            .btn-secondary {
                @apply btn bg-gray-100 text-gray-700 hover:bg-gray-200;
            }

            .btn-danger {
                @apply btn bg-red-600 text-white hover:bg-red-700;
            }

            .btn-success {
                @apply btn bg-green-600 text-white hover:bg-green-700;
            }

            .btn-sm {
                @apply !px-2.5 !py-1.5 !text-xs;
            }

            .card {
                @apply bg-white rounded-xl border border-gray-200 shadow-sm;
            }

            .card-header {
                @apply px-5 py-4 border-b border-gray-100 flex items-center justify-between;
            }

            .card-body {
                @apply p-5;
            }

            .form-label {
                @apply block text-sm font-medium text-gray-700 mb-1;
            }

            .form-input {
                @apply w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-50 disabled:text-gray-500;
            }

            .form-select {
                @apply form-input appearance-none bg-white;
            }

            .form-error {
                @apply text-xs text-red-600 mt-1;
            }

            .form-checkbox {
                @apply rounded border-gray-300 text-blue-600 focus:ring-blue-500;
            }

            .badge {
                @apply inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium;
            }

            .badge-blue {
                @apply badge bg-blue-100 text-blue-800;
            }

            .badge-green {
                @apply badge bg-green-100 text-green-800;
            }

            .badge-red {
                @apply badge bg-red-100 text-red-800;
            }

            .badge-yellow {
                @apply badge bg-yellow-100 text-yellow-800;
            }

            .badge-gray {
                @apply badge bg-gray-100 text-gray-700;
            }

            .badge-purple {
                @apply badge bg-purple-100 text-purple-800;
            }

            .table-wrap {
                @apply overflow-x-auto rounded-lg border border-gray-200;
            }

            .table {
                @apply w-full text-sm text-left;
            }

            .table thead {
                @apply bg-gray-50 text-xs text-gray-500 uppercase tracking-wider;
            }

            .table th {
                @apply px-4 py-3 font-semibold;
            }

            .table td {
                @apply px-4 py-3 border-t border-gray-100 text-gray-800;
            }

            .table tbody tr {
                @apply hover:bg-gray-50 transition-colors;
            }

            .table-sort-header {
                @apply inline-flex items-center gap-2 select-none;
            }

            .table-sort-header-button {
                @apply inline-flex items-center gap-2 bg-transparent p-0 text-inherit hover:text-gray-700;
            }

            .table-sort-arrows {
                @apply inline-flex flex-col leading-none;
            }

            .table-sort-arrow {
                @apply text-[10px] text-gray-300 transition-colors;
            }

            .table-sort-arrow.active {
                @apply text-blue-600;
            }
        }
    </style>
    @stack('head')
</head>

<body class="flex h-full">

    {{-- Sidebar --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-gray-900 text-white transition-transform duration-300 ease-in-out lg:static lg:flex lg:translate-x-0">

        {{-- Logo --}}
        <div class="flex items-center justify-center border-b border-gray-700 px-4 py-4">
            <img src="{{ asset('img/logo.svg') }}" alt="CopyMart" class="h-11 w-auto rounded-md bg-white p-1">
        </div>

        {{-- Nav --}}
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm">

            <a href="{{ route('dashboard') }}" class="nav-link @activeRoute('dashboard')">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
            {{-- <a href="{{ route('it-requests.index') }}" class="nav-link @activeRoute('it-requests.*')">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Mesa de Ayuda
            </a> --}}

            @php
                $user = auth()->user();

                $canVentas = $user->hasPermission('ventas.view');
                $canRentas = $user->hasPermission('rentas.view');
                $canClientes = $user->hasPermission('clientes.view');
                $canProduccion = $user->hasPermission('produccion.view');

                $canCompras = $user->hasPermission('compras.view');
                $canAlmacen = $user->hasPermission('almacen.view');
                $canFacturacion = $user->hasPermission('facturacion.view') || $user->hasPermission('cobranza.view');
                $canReportes = $user->hasPermission('reportes.view');

                $canRutas = $user->hasPermission('rutas.view');
                $canOrdenes = $user->hasPermission('ordenes_servicio.view');
                $canTaller = $user->hasPermission('taller.view');

                $canRH = $user->hasPermission('recursos_humanos.view');

                $canUsuarios = $user->hasPermission('usuarios.view');
                $canTiEquip = $user->hasPermission('ti.view');
                $canSku = $user->hasPermission('configuracion.view') || $user->hasPermission('migraciones.view');
                $canMesaAyuda = $user->hasPermission('ti.view');
                $canAuditoria = $user->hasPermission('auditoria.view');
            @endphp

            {{-- COMERCIAL --}}
            @if ($canVentas || $canRentas || $canClientes || $canProduccion)
                <div class="pt-3">
                    <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Comercial</p>
                    @if ($canVentas)
                        <a href="{{ route('sales.index') }}" class="nav-link @activeRoute('sales.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            Ventas
                        </a>
                    @endif
                    @if ($canRentas)
                        <a href="{{ route('rents.index') }}" class="nav-link @activeRoute('rents.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Rentas
                        </a>
                    @endif
                    @if ($canClientes)
                        <a href="{{ route('clients.index') }}" class="nav-link @activeRoute('clients.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Clientes
                        </a>
                    @endif
                    @if ($canProduccion)
                        <a href="{{ route('production.index') }}" class="nav-link @activeRoute('production.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Producción
                        </a>
                    @endif
                </div>
            @endif

            {{-- ADMINISTRACIÓN --}}
            @if ($canCompras || $canAlmacen || $canFacturacion || $canReportes)
                <div class="pt-3">
                    <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Administración</p>
                    @if ($canCompras)
                        <a href="{{ route('purchases.index') }}" class="nav-link @activeRoute('purchases.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Compras
                        </a>
                    @endif
                    @if ($canAlmacen)
                        <a href="{{ route('almacen.index') }}" class="nav-link @activeRoute('almacen.*') @activeRoute('equipment.*') @activeRoute('inventory.*') @activeRoute('spareparts.*') @activeRoute('item-catalog.*') @activeRoute('shelves.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Almacén
                        </a>
                    @endif
                    @if ($canFacturacion)
                        <a href="{{ route('billing.index', ['tab' => 'cobranza']) }}" class="nav-link @activeRoute('billing.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Cobranza / Facturación
                        </a>
                    @endif
                    @if ($canReportes)
                        <a href="{{ route('reports.index') }}" class="nav-link @activeRoute('reports.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Reportes
                        </a>
                    @endif
                </div>
            @endif

            {{-- OPERACIONES --}}
            @if ($canRutas || $canOrdenes || $canTaller)
                <div class="pt-3">
                    <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Operaciones</p>
                    @if ($canRutas)
                        <a href="{{ route('routes.index') }}" class="nav-link @activeRoute('routes.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            Rutas
                        </a>
                    @endif
                    @if ($canOrdenes)
                        <a href="{{ route('service-orders.index') }}" class="nav-link @activeRoute('service-orders.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Órdenes de Servicio
                        </a>
                    @endif
                    @if ($canTaller)
                        <a href="{{ route('repairs.index') }}" class="nav-link @activeRoute('repairs.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 11-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                            </svg>
                            Taller
                        </a>
                    @endif
                </div>
            @endif

            {{-- RECURSOS HUMANOS --}}
            @if ($canRH)
                <div class="pt-3">
                    <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">RH</p>
                    @if ($canRH)
                        <a href="{{ route('rh.index') }}" class="nav-link @activeRoute('rh.*') @activeRoute('employees.*') @activeRoute('payrolls.*') @activeRoute('vacations.*') @activeRoute('absences.*') @activeRoute('administrative-records.*') @activeRoute('credits.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Recursos Humanos
                        </a>
                    @endif
                </div>
            @endif

            {{-- TI --}}
            @if ($canUsuarios || $canTiEquip || $canSku || $canMesaAyuda || $canAuditoria)
                <div class="pt-3">
                    <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">TI</p>
                    @if ($canUsuarios)
                        <a href="{{ route('users.index') }}" class="nav-link @activeRoute('users.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Usuarios
                        </a>
                    @endif
                    @if ($canTiEquip)
                        <a href="{{ route('ti-equipment.index') }}" class="nav-link @activeRoute('ti-equipment.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Inventario TI
                        </a>
                    @endif
                    @if ($canSku)
                        <a href="{{ route('sku.index') }}" class="nav-link @activeRoute('sku.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            Catálogo SKU
                        </a>
                    @endif
                    @if ($canMesaAyuda)
                        <a href="{{ route('it-requests.index') }}" class="nav-link @activeRoute('it-requests.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Mesa de Ayuda
                        </a>
                    @endif
                    @if ($canAuditoria)
                        <a href="{{ route('audit.index') }}" class="nav-link @activeRoute('audit.*')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Auditoría
                        </a>
                    @endif
                </div>
            @endif

        </nav>

        {{-- User footer --}}
        <div class="flex items-center gap-3 border-t border-gray-700 px-4 py-3">
            <a href="{{ route('profile.show') }}" class="flex min-w-0 flex-1 items-center gap-3 transition hover:opacity-80">
                @if (auth()->user()->avatar)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->avatar) }}" alt="Foto" class="h-8 w-8 flex-shrink-0 rounded-full border border-gray-600 object-cover">
                @else
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-500 text-sm font-semibold">
                        {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium">{{ auth()->user()->full_name }}</p>
                    <p class="text-xs capitalize text-gray-400">{{ auth()->user()->rol }}</p>
                </div>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-gray-400 transition hover:text-white" title="Cerrar sesión">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-black/50 lg:hidden" onclick="closeSidebar()"></div>

    {{-- Main content --}}
    <div class="flex min-h-screen flex-1 flex-col overflow-x-hidden">

        {{-- Top bar --}}
        <header class="sticky top-0 z-30 flex items-center gap-3 border-b border-gray-200 bg-white px-4 py-3">
            <button onclick="toggleSidebar()" class="rounded-md p-1.5 hover:bg-gray-100 lg:hidden">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div class="flex-1 flex items-center gap-3">
                <h1 class="text-base font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                @hasSection('breadcrumb')
                    <p class="text-xs text-gray-500">@yield('breadcrumb')</p>
                @endif
                {{-- Global search trigger --}}
                <button onclick="openGlobalSearch()" class="ml-auto hidden sm:flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm text-gray-400 hover:bg-gray-100 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Buscar… <kbd class="ml-2 rounded border border-gray-300 bg-white px-1.5 py-0.5 text-xs font-mono text-gray-400">Ctrl+K</kbd>
                </button>
            </div>
            <div class="flex items-center gap-2">
                {{-- Notifications --}}
                @php
                    $unread = \App\Models\Notification::where('user_id', auth()->id())
                        ->where('is_read', false)
                        ->count();
                @endphp
                <a href="{{ route('notifications.index') }}" class="relative rounded-md p-1.5 hover:bg-gray-100">
                    <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if ($unread > 0)
                        <span class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-xs text-white">{{ $unread }}</span>
                    @endif
                </a>
            </div>
        </header>

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="mx-6 mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                <p class="font-semibold mb-1">Por favor corrige los siguientes errores:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mx-6 mt-4 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mx-6 mt-4 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>

    </div>

    <script>
        function toggleSidebar() {
            const s = document.getElementById('sidebar');
            const o = document.getElementById('sidebar-overlay');
            s.classList.toggle('-translate-x-full');
            o.classList.toggle('hidden');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.add('hidden');
        }
    </script>

    {{-- Global Search Modal --}}
    <div id="searchModal" class="fixed inset-0 z-50 hidden" role="dialog">
        <div class="fixed inset-0 bg-black/40" onclick="closeGlobalSearch()"></div>
        <div class="relative mx-auto mt-20 w-full max-w-lg rounded-xl bg-white shadow-2xl ring-1 ring-gray-200">
            <div class="flex items-center gap-3 border-b px-4 py-3">
                <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input id="searchInput" type="text" class="flex-1 border-0 bg-transparent text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-0" placeholder="Buscar clientes, equipos, rentas, ventas, tickets…" autocomplete="off">
                <kbd class="rounded border border-gray-300 bg-gray-50 px-1.5 py-0.5 text-xs font-mono text-gray-400 cursor-pointer" onclick="closeGlobalSearch()">ESC</kbd>
            </div>
            <div id="searchResults" class="max-h-80 overflow-y-auto p-2">
                <p class="px-3 py-6 text-center text-sm text-gray-400">Escribe al menos 2 caracteres para buscar</p>
            </div>
        </div>
    </div>

    <script>
        let searchTimer = null;
        function openGlobalSearch() {
            document.getElementById('searchModal').classList.remove('hidden');
            document.getElementById('searchInput').value = '';
            document.getElementById('searchResults').innerHTML = '<p class="px-3 py-6 text-center text-sm text-gray-400">Escribe al menos 2 caracteres para buscar</p>';
            setTimeout(() => document.getElementById('searchInput').focus(), 50);
        }
        function closeGlobalSearch() {
            document.getElementById('searchModal').classList.add('hidden');
        }
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); openGlobalSearch(); }
            if (e.key === 'Escape') closeGlobalSearch();
        });
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimer);
            const q = this.value.trim();
            if (q.length < 2) {
                document.getElementById('searchResults').innerHTML = '<p class="px-3 py-6 text-center text-sm text-gray-400">Escribe al menos 2 caracteres para buscar</p>';
                return;
            }
            searchTimer = setTimeout(() => {
                fetch('{{ route("search") }}?q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    const box = document.getElementById('searchResults');
                    if (!data.length) { box.innerHTML = '<p class="px-3 py-6 text-center text-sm text-gray-400">Sin resultados</p>'; return; }
                    const typeColors = { Cliente:'bg-blue-100 text-blue-700', Equipo:'bg-green-100 text-green-700', Renta:'bg-purple-100 text-purple-700', Venta:'bg-yellow-100 text-yellow-700', Ticket:'bg-red-100 text-red-700', Compra:'bg-orange-100 text-orange-700', Empleado:'bg-indigo-100 text-indigo-700' };
                    box.innerHTML = data.map(r =>
                        `<a href="${r.url}" class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-50 transition">` +
                        `<span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium ${typeColors[r.type]||'bg-gray-100 text-gray-700'}">${r.type}</span>` +
                        `<span class="flex-1 text-sm text-gray-800 truncate">${r.label}</span>` +
                        `<span class="text-xs text-gray-400 truncate max-w-[120px]">${r.sub||''}</span>` +
                        `</a>`
                    ).join('');
                });
            }, 250);
        });
    </script>

    <script>
        function getCellValue(row, columnIndex) {
            const cell = row.children[columnIndex];
            return (cell ? cell.innerText : '').trim();
        }

        function toSortableNumber(value) {
            // Normaliza valores como "$1,234.50" o "12%" a número.
            const normalized = value
                .replace(/\s+/g, '')
                .replace(/[^0-9.,]/g, '')
                .replace(/,(?=\d{3}(\D|$))/g, '');

            const parsed = parseFloat(normalized.replace(',', '.'));
            return Number.isFinite(parsed) ? parsed : null;
        }

        function sortTableRows(table, columnIndex, direction) {
            const tbody = table.tBodies[0];
            if (!tbody) return;

            const rows = Array.from(tbody.rows).filter((row) => {
                return !(row.cells.length === 1 && row.cells[0].hasAttribute('colspan'));
            });

            if (rows.length < 2) return;

            const compare = (a, b) => {
                const aText = getCellValue(a, columnIndex);
                const bText = getCellValue(b, columnIndex);

                const aNum = toSortableNumber(aText);
                const bNum = toSortableNumber(bText);

                if (aNum !== null && bNum !== null) {
                    return direction === 'asc' ? aNum - bNum : bNum - aNum;
                }

                const textCompare = aText.localeCompare(bText, 'es', { sensitivity: 'base' });
                return direction === 'asc' ? textCompare : -textCompare;
            };

            rows.sort(compare).forEach((row) => tbody.appendChild(row));
        }

        function updateSortArrows(table, columnIndex, direction) {
            table.querySelectorAll('th[data-sort-index]').forEach((th) => {
                const up = th.querySelector('[data-sort-arrow="asc"]');
                const down = th.querySelector('[data-sort-arrow="desc"]');
                if (!up || !down) return;

                const isActiveColumn = Number(th.dataset.sortIndex) === columnIndex;
                up.classList.toggle('active', isActiveColumn && direction === 'asc');
                down.classList.toggle('active', isActiveColumn && direction === 'desc');
            });
        }

        function enhanceSortableHeaders(table) {
            const headers = Array.from(table.tHead?.rows?.[0]?.cells || []);
            if (!headers.length) return;

            headers.forEach((th, index) => {
                const label = (th.innerText || '').trim();
                if (!label) return;
                if (th.dataset.sortIndex) return;

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'table-sort-header-button';
                button.setAttribute('aria-label', `Ordenar ${label}`);

                const text = document.createElement('span');
                text.textContent = label;

                const arrows = document.createElement('span');
                arrows.className = 'table-sort-arrows';
                arrows.innerHTML = '<span class="table-sort-arrow" data-sort-arrow="asc">▲</span><span class="table-sort-arrow" data-sort-arrow="desc">▼</span>';

                button.appendChild(text);
                button.appendChild(arrows);

                th.textContent = '';
                th.dataset.sortIndex = String(index);
                th.appendChild(button);

                button.addEventListener('click', () => {
                    const nextDirection = th.dataset.sortDirection === 'asc' ? 'desc' : 'asc';

                    table.querySelectorAll('th[data-sort-index]').forEach((header) => {
                        if (header !== th) {
                            delete header.dataset.sortDirection;
                        }
                    });

                    th.dataset.sortDirection = nextDirection;
                    sortTableRows(table, index, nextDirection);
                    updateSortArrows(table, index, nextDirection);
                });
            });
        }

        function initTableSortToolbars() {
            const tables = Array.from(document.querySelectorAll('table'));

            tables.forEach((table) => {
            if (table.dataset.sortEnhanced === '1') return;
                if (table.dataset.disableSortToolbar === '1') return;
                if (!table.tHead || !table.tBodies || !table.tBodies.length) return;

                const tbody = table.tBodies[0];
                if (!tbody) return;

                const dataRows = Array.from(tbody.rows).filter((row) => {
                    return !(row.cells.length === 1 && row.cells[0].hasAttribute('colspan'));
                });

                if (dataRows.length < 2) return;

                dataRows.forEach((row, index) => {
                    row.dataset.sortOriginalIndex = String(index);
                });

                enhanceSortableHeaders(table);
                table.dataset.sortEnhanced = '1';
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTableSortToolbars);
        } else {
            initTableSortToolbars();
        }
    </script>

    @stack('scripts')
</body>

</html>
