@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Stat cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <x-stat-card label="Clientes activos"   value="{{ $stats['clients'] }}"         color="blue"   icon="users"/>
    <x-stat-card label="Rentas vigentes"    value="{{ $stats['rents_active'] }}"     color="green"  icon="document"/>
    <x-stat-card label="Facturas pendientes" value="{{ $stats['billing_pending'] }}" color="yellow" icon="cash"/>
    <x-stat-card label="Facturas vencidas"  value="{{ $stats['billing_overdue'] }}"  color="red"    icon="exclamation"/>
    <x-stat-card label="Tickets pendientes" value="{{ $stats['tickets_pending'] }}"  color="orange" icon="ticket"/>
    <x-stat-card label="Tickets urgentes"   value="{{ $stats['tickets_urgent'] }}"   color="red"    icon="bell"/>
    <x-stat-card label="Equipos en sistema" value="{{ $stats['items_total'] }}"      color="purple" icon="computer"/>
    <x-stat-card label="Empleados activos"  value="{{ $stats['employees'] }}"        color="teal"   icon="person"/>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Facturas vencidas --}}
    <div class="card lg:col-span-2">
        <div class="card-header">
            <h3 class="font-semibold text-gray-800 text-sm">Facturas vencidas</h3>
            <a href="{{ route('billing.index', ['status' => 'VENCIDO']) }}" class="text-xs text-blue-600 hover:underline">Ver todas</a>
        </div>
        <div class="table-wrap rounded-none border-0">
            <table class="table">
                <thead>
                    <tr><th>Cliente</th><th>Monto</th><th>Vencimiento</th><th>Días</th></tr>
                </thead>
                <tbody>
                    @forelse($overdue_billings as $b)
                    <tr>
                        <td>
                            <a href="{{ route('billing.show', $b) }}" class="text-blue-600 hover:underline font-medium">
                                {{ $b->client->name }}
                            </a>
                        </td>
                        <td class="font-medium">${{ number_format($b->amount, 2) }}</td>
                        <td>{{ $b->due_date->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge-red">
                                {{ $b->due_date->diffInDays(now()) }} días
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-gray-400 py-6">Sin facturas vencidas</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tickets urgentes --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-gray-800 text-sm">Tickets abiertos</h3>
            <a href="{{ route('tickets.index') }}" class="text-xs text-blue-600 hover:underline">Ver todos</a>
        </div>
        <ul class="divide-y divide-gray-100">
            @forelse($recent_tickets as $t)
            <li class="px-4 py-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $t->client->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $t->description }}</p>
                    </div>
                    @if($t->report_status === 'URGENTE')
                    <span class="badge-red flex-shrink-0">Urgente</span>
                    @else
                    <span class="badge-yellow flex-shrink-0">Pendiente</span>
                    @endif
                </div>
            </li>
            @empty
            <li class="px-4 py-8 text-center text-sm text-gray-400">Sin tickets abiertos</li>
            @endforelse
        </ul>
    </div>

    {{-- Por vencer esta semana --}}
    <div class="card lg:col-span-3">
        <div class="card-header">
            <h3 class="font-semibold text-gray-800 text-sm">Facturas por vencer esta semana</h3>
        </div>
        <div class="table-wrap rounded-none border-0">
            <table class="table">
                <thead>
                    <tr><th>Cliente</th><th>No. Factura</th><th>Monto</th><th>Vence</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    @forelse($upcoming_billings as $b)
                    <tr>
                        <td>{{ $b->client->name }}</td>
                        <td class="text-gray-500">{{ $b->invoice_number ?? '—' }}</td>
                        <td class="font-medium">${{ number_format($b->amount, 2) }}</td>
                        <td>
                            <span class="badge-yellow">{{ $b->due_date->format('d/m/Y') }}</span>
                        </td>
                        <td>
                            <a href="{{ route('billing.show', $b) }}" class="btn btn-sm btn-secondary">Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-gray-400 py-6">Sin vencimientos próximos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
