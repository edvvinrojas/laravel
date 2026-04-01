@extends('layouts.app')
@section('title','Reportes')
@section('page-title','Reportes')

@section('content')

{{-- ── KPIs ── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="card p-4 flex flex-col">
        <span class="text-xs text-gray-500 uppercase tracking-wide">Clientes</span>
        <span class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['clients']) }}</span>
    </div>
    <div class="card p-4 flex flex-col">
        <span class="text-xs text-gray-500 uppercase tracking-wide">Ventas del Mes</span>
        <span class="text-2xl font-bold text-blue-700 mt-1">{{ $summary['sales_this_month'] }}</span>
        <span class="text-xs text-gray-400">{{ $summary['sales_total'] }} total</span>
    </div>
    <div class="card p-4 flex flex-col">
        <span class="text-xs text-gray-500 uppercase tracking-wide">Rentas Activas</span>
        <span class="text-2xl font-bold text-green-700 mt-1">{{ $summary['rents_active'] }}</span>
        <span class="text-xs text-gray-400">{{ $summary['rents_total'] }} total</span>
    </div>
    <div class="card p-4 flex flex-col">
        <span class="text-xs text-gray-500 uppercase tracking-wide">Facturas Vencidas</span>
        <span class="text-2xl font-bold text-red-600 mt-1">{{ $summary['billing_overdue'] }}</span>
        <span class="text-xs text-gray-400">{{ $summary['billing_pending'] }} pendientes</span>
    </div>
    <div class="card p-4 flex flex-col">
        <span class="text-xs text-gray-500 uppercase tracking-wide">Tickets Abiertos</span>
        <span class="text-2xl font-bold text-yellow-600 mt-1">{{ $summary['tickets_open'] }}</span>
    </div>
    <div class="card p-4 flex flex-col">
        <span class="text-xs text-gray-500 uppercase tracking-wide">Equipos</span>
        <span class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['equipment_total']) }}</span>
    </div>
    <div class="card p-4 flex flex-col">
        <span class="text-xs text-gray-500 uppercase tracking-wide">Reparaciones Pendientes</span>
        <span class="text-2xl font-bold text-orange-600 mt-1">{{ $summary['repairs_pending'] }}</span>
    </div>
    <div class="card p-4 flex flex-col">
        <span class="text-xs text-gray-500 uppercase tracking-wide">Compras Activas</span>
        <span class="text-2xl font-bold text-purple-600 mt-1">{{ $summary['purchases_active'] }}</span>
    </div>
</div>

{{-- ── Gráficos ── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Ventas por Mes --}}
    <div class="card">
        <div class="card-header font-semibold text-sm">Ventas por Mes (últimos 12)</div>
        <div class="p-5 space-y-2">
            @php
                $maxSales = $salesByMonth->max('count') ?: 1;
            @endphp
            @forelse($salesByMonth as $s)
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500 w-16 text-right font-mono">{{ $s->month }}</span>
                <div class="flex-1 bg-gray-100 rounded-full h-5 overflow-hidden">
                    <div class="bg-blue-500 h-5 rounded-full flex items-center justify-end pr-2 text-xs text-white font-semibold transition-all"
                         style="width: {{ max(($s->count / $maxSales) * 100, 8) }}%">
                        {{ $s->count }}
                    </div>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">Sin datos de ventas</p>
            @endforelse
        </div>
    </div>

    {{-- Rentas por Estado --}}
    <div class="card">
        <div class="card-header font-semibold text-sm">Rentas por Estado</div>
        <div class="p-5 space-y-2">
            @php
                $maxRents = $rentsByStatus->max('count') ?: 1;
                $rentColors = [
                    'VIGENTE' => 'bg-green-500', 'PENDIENTE' => 'bg-yellow-500',
                    'FINALIZADO' => 'bg-gray-400', 'CANCELADO' => 'bg-red-500',
                    'SIN_FIRMAR' => 'bg-blue-500',
                ];
            @endphp
            @forelse($rentsByStatus as $r)
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500 w-24 text-right">{{ str_replace('_',' ',$r->status) }}</span>
                <div class="flex-1 bg-gray-100 rounded-full h-5 overflow-hidden">
                    <div class="{{ $rentColors[$r->status] ?? 'bg-gray-500' }} h-5 rounded-full flex items-center justify-end pr-2 text-xs text-white font-semibold transition-all"
                         style="width: {{ max(($r->count / $maxRents) * 100, 8) }}%">
                        {{ $r->count }}
                    </div>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">Sin datos de rentas</p>
            @endforelse
        </div>
    </div>

    {{-- Estado de Cobranza --}}
    <div class="card">
        <div class="card-header font-semibold text-sm">Estado de Cobranza</div>
        <div class="p-5 grid grid-cols-3 gap-4">
            <div class="rounded-lg bg-green-50 border border-green-200 p-4 text-center">
                <span class="text-2xl font-bold text-green-700">{{ $billingAging['pagado'] }}</span>
                <p class="text-xs text-green-600 mt-1">Pagado</p>
            </div>
            <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4 text-center">
                <span class="text-2xl font-bold text-yellow-700">{{ $billingAging['vigente'] }}</span>
                <p class="text-xs text-yellow-600 mt-1">Vigente</p>
            </div>
            <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-center">
                <span class="text-2xl font-bold text-red-700">{{ $billingAging['vencido'] }}</span>
                <p class="text-xs text-red-600 mt-1">Vencido</p>
            </div>
        </div>
    </div>

    {{-- Tickets por Tipo --}}
    <div class="card">
        <div class="card-header font-semibold text-sm">Tickets por Tipo</div>
        <div class="p-5 space-y-2">
            @php
                $maxTickets = $ticketsByType->max('count') ?: 1;
            @endphp
            @forelse($ticketsByType as $t)
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500 w-28 text-right">{{ str_replace('_',' ',$t->type) }}</span>
                <div class="flex-1 bg-gray-100 rounded-full h-5 overflow-hidden">
                    <div class="bg-indigo-500 h-5 rounded-full flex items-center justify-end pr-2 text-xs text-white font-semibold transition-all"
                         style="width: {{ max(($t->count / $maxTickets) * 100, 8) }}%">
                        {{ $t->count }}
                    </div>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">Sin tickets registrados</p>
            @endforelse
        </div>
    </div>

    {{-- Equipos por Ubicación --}}
    <div class="card lg:col-span-2">
        <div class="card-header font-semibold text-sm">Equipos por Ubicación</div>
        <div class="p-5 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            @php
                $locColors = [
                    'BODEGA' => ['bg-blue-50','border-blue-200','text-blue-700','text-blue-600'],
                    'ASIGNADO' => ['bg-green-50','border-green-200','text-green-700','text-green-600'],
                    'VENDIDO' => ['bg-gray-50','border-gray-200','text-gray-700','text-gray-500'],
                    'TALLER' => ['bg-orange-50','border-orange-200','text-orange-700','text-orange-600'],
                    'DESCONOCIDO' => ['bg-red-50','border-red-200','text-red-700','text-red-600'],
                ];
            @endphp
            @forelse($equipmentByLocation as $eq)
            @php $c = $locColors[$eq->location] ?? ['bg-gray-50','border-gray-200','text-gray-700','text-gray-500']; @endphp
            <div class="rounded-lg {{ $c[0] }} border {{ $c[1] }} p-4 text-center">
                <span class="text-2xl font-bold {{ $c[2] }}">{{ $eq->count }}</span>
                <p class="text-xs {{ $c[3] }} mt-1">{{ $eq->location }}</p>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4 col-span-full">Sin equipos registrados</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
