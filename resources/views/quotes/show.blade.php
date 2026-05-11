@extends('layouts.app')
@section('title','Cotización '.$quote->quote_number)
@section('page-title','Cotización '.$quote->quote_number)
@section('breadcrumb','Detalle')

@section('content')
@php
    $statusColors = [
        'BORRADOR'  => 'badge-gray',
        'ENVIADA'   => 'badge-blue',
        'APROBADA'  => 'badge-green',
        'RECHAZADA' => 'badge-red',
    ];
@endphp

<div class="w-full max-w-4xl mx-auto space-y-5">

    {{-- Cabecera con acciones --}}
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <span class="font-mono text-base font-bold">{{ $quote->quote_number }}</span>
                <span class="{{ $statusColors[$quote->status] ?? 'badge-gray' }}">{{ $quote->status }}</span>
            </div>
            <div class="flex gap-2">
                @if(auth()->user()->hasPermission('cotizaciones.edit') && !in_array($quote->status, ['APROBADA','RECHAZADA']))
                <a href="{{ route('quotes.edit', $quote) }}" class="btn-primary btn-sm">Editar</a>
                @endif
                @if(auth()->user()->hasPermission('cotizaciones.delete'))
                <form method="POST" action="{{ route('quotes.destroy', $quote) }}"
                    onsubmit="return confirm('¿Eliminar cotización {{ $quote->quote_number }}? Esta acción no se puede deshacer.')">
                    @csrf @method('DELETE')
                    <button class="btn-danger btn-sm">Eliminar</button>
                </form>
                @endif
                <a href="{{ route('quotes.index') }}" class="btn-secondary btn-sm">← Volver</a>
            </div>
        </div>
        <div class="card-body grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Cliente</p>
                <a href="{{ route('clients.show', $quote->client) }}" class="font-semibold text-blue-600 hover:underline">
                    {{ $quote->client->name }}
                </a>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Válida hasta</p>
                <p class="font-semibold {{ $quote->valid_until && $quote->valid_until->isPast() && $quote->status !== 'APROBADA' ? 'text-red-600' : '' }}">
                    {{ $quote->valid_until ? $quote->valid_until->format('d/m/Y') : '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Creada por</p>
                <p class="font-semibold">{{ $quote->creator->full_name ?? '—' }}</p>
                <p class="text-xs text-gray-400">{{ $quote->created_at->format('d/m/Y H:i') }}</p>
            </div>
            @if($quote->notes)
            <div class="sm:col-span-2 md:col-span-3">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Notas / Condiciones</p>
                <p class="text-gray-700 whitespace-pre-line">{{ $quote->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Líneas --}}
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-sm">Líneas de cotización</h3>
            <span class="badge-gray">{{ $quote->lines->count() }} {{ $quote->lines->count() === 1 ? 'línea' : 'líneas' }}</span>
        </div>
        <div class="table-wrap rounded-none border-0">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-8">#</th>
                        <th>Descripción</th>
                        <th class="w-20 text-center">Cant.</th>
                        <th class="w-36 text-right">Precio unit.</th>
                        <th class="w-36 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($quote->lines as $i => $line)
                <tr>
                    <td class="text-xs text-gray-400">{{ $i + 1 }}</td>
                    <td>
                        <span class="font-medium text-gray-900">{{ $line->description }}</span>
                        @if($line->product_type)
                        @php
                            $typeLabel = ['item' => 'Equipo', 'sparepart' => 'Refacción', 'inventory' => 'Inventario'];
                            $typeColor = ['item' => 'badge-blue', 'sparepart' => 'badge-green', 'inventory' => 'badge-purple'];
                        @endphp
                        <span class="{{ $typeColor[$line->product_type] ?? 'badge-gray' }} ml-2">{{ $typeLabel[$line->product_type] ?? $line->product_type }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $line->quantity }}</td>
                    <td class="text-right">${{ number_format($line->unit_price, 2) }}</td>
                    <td class="text-right font-semibold">${{ number_format($line->total, 2) }}</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50">
                        <td colspan="4" class="px-4 py-3 text-right font-bold text-gray-700">TOTAL</td>
                        <td class="px-4 py-3 text-right font-bold text-blue-700 text-lg">${{ number_format($quote->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
