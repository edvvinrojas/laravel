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
            .nav-link { @apply flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-800 transition-colors duration-150; }
            .nav-link.active { @apply bg-blue-600 text-white; }
            .btn { @apply inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed; }
            .btn-primary { @apply btn bg-blue-600 text-white hover:bg-blue-700; }
            .btn-secondary { @apply btn bg-gray-100 text-gray-700 hover:bg-gray-200; }
            .btn-danger { @apply btn bg-red-600 text-white hover:bg-red-700; }
            .btn-success { @apply btn bg-green-600 text-white hover:bg-green-700; }
            .btn-sm { @apply !px-2.5 !py-1.5 !text-xs; }
            .card { @apply bg-white rounded-xl border border-gray-200 shadow-sm; }
            .card-header { @apply px-5 py-4 border-b border-gray-100 flex items-center justify-between; }
            .card-body { @apply p-5; }
            .form-label { @apply block text-sm font-medium text-gray-700 mb-1; }
            .form-input { @apply w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-50 disabled:text-gray-500; }
            .form-select { @apply form-input appearance-none bg-white; }
            .form-error { @apply text-xs text-red-600 mt-1; }
            .form-checkbox { @apply rounded border-gray-300 text-blue-600 focus:ring-blue-500; }
            .badge { @apply inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium; }
            .badge-blue   { @apply badge bg-blue-100 text-blue-800; }
            .badge-green  { @apply badge bg-green-100 text-green-800; }
            .badge-red    { @apply badge bg-red-100 text-red-800; }
            .badge-yellow { @apply badge bg-yellow-100 text-yellow-800; }
            .badge-gray   { @apply badge bg-gray-100 text-gray-700; }
            .badge-purple { @apply badge bg-purple-100 text-purple-800; }
            .table-wrap { @apply overflow-x-auto rounded-lg border border-gray-200; }
            .table { @apply w-full text-sm text-left; }
            .table thead { @apply bg-gray-50 text-xs text-gray-500 uppercase tracking-wider; }
            .table th { @apply px-4 py-3 font-semibold; }
            .table td { @apply px-4 py-3 border-t border-gray-100 text-gray-800; }
            .table tbody tr { @apply hover:bg-gray-50 transition-colors; }
        }
    </style>
    @stack('head')
</head>
<body class="h-full flex">

{{-- Sidebar --}}
<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white flex flex-col transition-transform duration-300 ease-in-out
           -translate-x-full lg:translate-x-0 lg:static lg:flex">

    {{-- Logo --}}
    <div class="flex items-center justify-center px-4 py-4 border-b border-gray-700">
        <img src="{{ asset('img/logo.svg') }}" alt="CopyMart" class="h-11 w-auto bg-white rounded-md p-1">
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-sm">

        <a href="{{ route('dashboard') }}"
           class="nav-link @activeRoute('dashboard')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>

        @php $dept = auth()->user()->department; $rol = auth()->user()->rol; @endphp

        {{-- COMERCIAL --}}
        @if($rol === 'administrador' || in_array($dept, ['comercial', 'administracion', 'operaciones']))
        <div class="pt-3">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Comercial</p>
            <a href="{{ route('sales.index') }}" class="nav-link @activeRoute('sales.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Ventas
            </a>
            <a href="{{ route('rents.index') }}" class="nav-link @activeRoute('rents.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Rentas
            </a>
            <a href="{{ route('clients.index') }}" class="nav-link @activeRoute('clients.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Clientes
            </a>
            <a href="{{ route('production.index') }}" class="nav-link @activeRoute('production.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Producción
            </a>
        </div>
        @endif

        {{-- ADMINISTRACIÓN --}}
        @if($rol === 'administrador' || $dept === 'administracion')
        <div class="pt-3">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Administración</p>
            <a href="{{ route('purchases.index') }}" class="nav-link @activeRoute('purchases.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Compras
            </a>
            <a href="{{ route('almacen.index') }}" class="nav-link @activeRoute('almacen.*') @activeRoute('equipment.*') @activeRoute('inventory.*') @activeRoute('productos.*') @activeRoute('accesorios.*') @activeRoute('consumibles.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Almacén
            </a>
<a href="{{ route('billing.index', ['tab' => 'cobranza']) }}" class="nav-link @activeRoute('billing.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Cobranza / Facturación
            </a>
        </div>
        @endif

        {{-- OPERACIONES --}}
        @if($rol === 'administrador' || in_array($dept, ['operaciones', 'administracion', 'rh']))
        <div class="pt-3">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Operaciones</p>
            <a href="{{ route('routes.index') }}" class="nav-link @activeRoute('routes.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                Rutas
            </a>
            <a href="{{ route('tickets.index') }}" class="nav-link @activeRoute('tickets.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Órdenes de Servicio
            </a>
            <a href="{{ route('repairs.index') }}" class="nav-link @activeRoute('repairs.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 11-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                Taller
            </a>
            <a href="{{ route('rh.index') }}" class="nav-link @activeRoute('rh.*') @activeRoute('employees.*') @activeRoute('payrolls.*') @activeRoute('vacations.*') @activeRoute('absences.*') @activeRoute('administrative-records.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Recursos Humanos
            </a>
        </div>
        @endif

        {{-- TI --}}
        @if($rol === 'administrador' || $dept === 'ti')
        <div class="pt-3">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">TI</p>
            <a href="{{ route('users.index') }}" class="nav-link @activeRoute('users.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Usuarios
            </a>
            <a href="{{ route('ti-equipment.index') }}" class="nav-link @activeRoute('ti-equipment.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Inventario TI
            </a>
            @if($rol === 'administrador')
            <a href="{{ route('audit.index') }}" class="nav-link @activeRoute('audit.*')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Auditoría
            </a>
            @endif
        </div>
        @endif

    </nav>

    {{-- User footer --}}
    <div class="border-t border-gray-700 px-4 py-3 flex items-center gap-3">
        <a href="{{ route('profile.show') }}" class="flex items-center gap-3 flex-1 min-w-0 hover:opacity-80 transition">
            @if(auth()->user()->avatar)
                <img src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->avatar) }}"
                     alt="Foto"
                     class="w-8 h-8 rounded-full object-cover flex-shrink-0 border border-gray-600">
            @else
                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-sm font-semibold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ auth()->user()->full_name }}</p>
                <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->rol }}</p>
            </div>
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="text-gray-400 hover:text-white transition" title="Cerrar sesión">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </button>
        </form>
    </div>
</aside>

{{-- Overlay mobile --}}
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>

{{-- Main content --}}
<div class="flex-1 flex flex-col min-h-screen overflow-x-hidden">

    {{-- Top bar --}}
    <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3 sticky top-0 z-30">
        <button onclick="toggleSidebar()" class="lg:hidden p-1.5 rounded-md hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div class="flex-1">
            <h1 class="text-base font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
            @hasSection('breadcrumb')
            <p class="text-xs text-gray-500">@yield('breadcrumb')</p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            {{-- Notifications --}}
            @php
                $unread = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count();
            @endphp
            <a href="{{ route('notifications.index') }}" class="relative p-1.5 rounded-md hover:bg-gray-100">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                @if($unread > 0)
                <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 rounded-full text-white text-xs flex items-center justify-center">{{ $unread }}</span>
                @endif
            </a>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mx-6 mt-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mx-6 mt-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
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

@stack('scripts')
</body>
</html>
