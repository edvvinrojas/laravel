@extends('layouts.app')
@section('title','Recursos Humanos')
@section('page-title','Recursos Humanos')

@section('content')
<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4">
            <p class="text-2xl font-bold text-teal-600">{{ $stats['employees_active'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Empleados activos</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['payrolls_pending'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Nóminas pendientes</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['vacations_pending'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Vacaciones por aprobar</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold text-red-600">{{ $stats['absences_month'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Ausencias este mes</p>
        </div>
    </div>

    {{-- Módulos --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        <a href="{{ route('employees.index') }}"
           class="card p-5 hover:shadow-md transition-shadow flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-teal-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">Empleados</p>
                <p class="text-xs text-gray-500 mt-0.5">Expedientes, altas y bajas</p>
                <p class="text-sm font-bold text-teal-600 mt-2">{{ $stats['employees_active'] }} activos</p>
            </div>
        </a>

        <a href="{{ route('payrolls.index') }}"
           class="card p-5 hover:shadow-md transition-shadow flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">Nóminas</p>
                <p class="text-xs text-gray-500 mt-0.5">Pagos, bonos y comisiones</p>
                <p class="text-sm font-bold text-green-600 mt-2">${{ number_format($stats['payroll_month'], 2) }} este mes</p>
            </div>
        </a>

        <a href="{{ route('vacations.index') }}"
           class="card p-5 hover:shadow-md transition-shadow flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">Vacaciones</p>
                <p class="text-xs text-gray-500 mt-0.5">Solicitudes y aprobaciones</p>
                @if($stats['vacations_pending'] > 0)
                <p class="text-sm font-bold text-yellow-600 mt-2">{{ $stats['vacations_pending'] }} por aprobar</p>
                @else
                <p class="text-sm text-gray-400 mt-2">Sin pendientes</p>
                @endif
            </div>
        </a>

        <a href="{{ route('absences.index') }}"
           class="card p-5 hover:shadow-md transition-shadow flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">Ausentismo</p>
                <p class="text-xs text-gray-500 mt-0.5">Faltas, retardos e incapacidades</p>
                <p class="text-sm font-bold text-red-600 mt-2">{{ $stats['absences_month'] }} este mes</p>
            </div>
        </a>

        <a href="{{ route('administrative-records.index') }}"
           class="card p-5 hover:shadow-md transition-shadow flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">Registros Administrativos</p>
                <p class="text-xs text-gray-500 mt-0.5">Amonestaciones, reconocimientos</p>
            </div>
        </a>

        <a href="{{ route('credits.index') }}"
           class="card p-5 hover:shadow-md transition-shadow flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">Créditos</p>
                <p class="text-xs text-gray-500 mt-0.5">Préstamos y compras a descuento</p>
                <p class="text-sm font-bold text-amber-700 mt-2">{{ $stats['credits_authorized'] }} autorizados</p>
            </div>
        </a>

        @if(auth()->user()->isGerencia())
        <a href="{{ route('supervision.requests') }}"
           class="card p-5 hover:shadow-md transition-shadow flex items-start gap-4 border border-blue-200 bg-blue-50/40">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M4 6h16M4 18h16"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">Supervisión de Peticiones</p>
                <p class="text-xs text-gray-500 mt-0.5">Bandeja única para atender autorizaciones de jefatura</p>
                <p class="text-sm font-bold text-blue-700 mt-2">Vacaciones, ausentismo y tickets</p>
            </div>
        </a>
        @endif

    </div>

    {{-- Empleados recientes --}}
    @if($recent_employees->isNotEmpty())
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-gray-700">Empleados activos recientes</h3>
            <a href="{{ route('employees.create') }}" class="btn-primary btn-sm">+ Nuevo empleado</a>
        </div>
        <div class="card-body p-0">
            <table class="table">
                <thead>
                    <tr><th>Nombre</th><th>NSS</th><th>RFC</th><th>Ingreso</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($recent_employees as $e)
                    <tr>
                        <td class="font-medium">{{ $e->nombre }}</td>
                        <td class="font-mono text-xs">{{ $e->nss }}</td>
                        <td class="font-mono text-xs">{{ $e->rfc }}</td>
                        <td class="text-sm">{{ $e->hire_date->format('d/m/Y') }}</td>
                        <td class="text-right"><a href="{{ route('employees.show', $e) }}" class="btn-secondary btn-sm">Ver</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
