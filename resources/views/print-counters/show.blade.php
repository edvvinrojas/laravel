@extends('layouts.app')
@section('title','Contador')
@section('page-title','Detalle Contador')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('print-counters.edit',$printCounter) }}" class="btn-primary">Editar</a>

    @if(!$printCounter->is_billed && $printCounter->total_excess_amount > 0)
    <form method="POST" action="{{ route('print-counters.bill-excess',$printCounter) }}"
          onsubmit="return confirm('¿Generar cobro de exceso por ${{ number_format($printCounter->total_excess_amount,2) }}?')">
        @csrf
        <button type="submit" class="btn-danger">
            Facturar exceso — ${{ number_format($printCounter->total_excess_amount,2) }}
        </button>
    </form>
    @elseif($printCounter->is_billed && $printCounter->billing)
    <a href="{{ route('billing.show',$printCounter->billing) }}" class="btn-secondary">
        Ver factura de exceso →
    </a>
    @endif

    <a href="{{ route('print-counters.index') }}" class="btn-secondary">← Volver</a>
</div>

<div class="card max-w-2xl">
    <div class="card-header">
        <h3 class="font-semibold">{{ $printCounter->rent->client->name }} — {{ sprintf('%02d',$printCounter->period_month) }}/{{ $printCounter->period_year }}</h3>
        @if($printCounter->is_billed)<span class="badge-green">Facturado</span>@else<span class="badge-gray">Sin facturar</span>@endif
    </div>
    <div class="card-body">
        {{-- Contadores --}}
        <div class="grid grid-cols-3 gap-4 text-sm mb-4">
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-gray-500 text-xs">BN Anterior</p>
                <p class="text-lg font-bold">{{ number_format($printCounter->bn_previous) }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-gray-500 text-xs">BN Actual</p>
                <p class="text-lg font-bold">{{ number_format($printCounter->bn_current) }}</p>
            </div>
            <div class="text-center p-3 bg-blue-50 rounded-lg">
                <p class="text-blue-600 text-xs">BN Impreso</p>
                <p class="text-lg font-bold text-blue-700">{{ number_format($printCounter->bn_printed) }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-gray-500 text-xs">Color Anterior</p>
                <p class="text-lg font-bold">{{ number_format($printCounter->color_previous) }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-gray-500 text-xs">Color Actual</p>
                <p class="text-lg font-bold">{{ number_format($printCounter->color_current) }}</p>
            </div>
            <div class="text-center p-3 bg-purple-50 rounded-lg">
                <p class="text-purple-600 text-xs">Color Impreso</p>
                <p class="text-lg font-bold text-purple-700">{{ number_format($printCounter->color_printed) }}</p>
            </div>
        </div>

        {{-- Exceso --}}
        <div class="border-t pt-4 space-y-3 text-sm">

            {{-- BN row --}}
            @php $bnOver = $printCounter->bn_excess > 0; @endphp
            <div class="flex items-center gap-3 p-3 rounded-lg {{ $bnOver ? 'bg-red-50 border border-red-200' : 'bg-gray-50' }}">
                <div class="flex-1">
                    <p class="text-xs text-gray-500 mb-1">BN impreso vs incluidas</p>
                    <p class="font-semibold {{ $bnOver ? 'text-red-700' : 'text-gray-700' }}">
                        {{ number_format($printCounter->bn_printed) }} impresas
                        / {{ number_format($printCounter->bn_included) }} incluidas
                        @if($bnOver)
                            → <span class="text-red-600">{{ number_format($printCounter->bn_excess) }} de exceso</span>
                        @else
                            → <span class="text-green-600">sin exceso</span>
                        @endif
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">{{ number_format($printCounter->bn_excess) }} × ${{ $printCounter->bn_cost_per_page }}</p>
                    <p class="font-bold {{ $bnOver ? 'text-red-600' : 'text-gray-400' }}">${{ number_format($printCounter->bn_excess_amount,2) }}</p>
                </div>
            </div>

            {{-- Color row --}}
            @php $colorOver = $printCounter->color_excess > 0; @endphp
            <div class="flex items-center gap-3 p-3 rounded-lg {{ $colorOver ? 'bg-red-50 border border-red-200' : 'bg-gray-50' }}">
                <div class="flex-1">
                    <p class="text-xs text-gray-500 mb-1">Color impreso vs incluidas</p>
                    <p class="font-semibold {{ $colorOver ? 'text-red-700' : 'text-gray-700' }}">
                        {{ number_format($printCounter->color_printed) }} impresas
                        / {{ number_format($printCounter->color_included) }} incluidas
                        @if($colorOver)
                            → <span class="text-red-600">{{ number_format($printCounter->color_excess) }} de exceso</span>
                        @else
                            → <span class="text-green-600">sin exceso</span>
                        @endif
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">{{ number_format($printCounter->color_excess) }} × ${{ $printCounter->color_cost_per_page }}</p>
                    <p class="font-bold {{ $colorOver ? 'text-red-600' : 'text-gray-400' }}">${{ number_format($printCounter->color_excess_amount,2) }}</p>
                </div>
            </div>

            {{-- Total --}}
            <div class="text-center border-t pt-3">
                <p class="text-gray-500 text-xs mb-1">Total exceso a cobrar</p>
                <p class="text-3xl font-bold {{ $printCounter->total_excess_amount > 0 ? 'text-red-600' : 'text-gray-400' }}">
                    ${{ number_format($printCounter->total_excess_amount,2) }}
                </p>
                @if($printCounter->total_excess_amount == 0)
                <p class="text-xs text-green-600 mt-1">✓ Dentro del límite incluido — sin cobro adicional</p>
                @else
                <p class="text-xs text-red-500 mt-1">⚠ Se pasaron del límite incluido — hay cobro adicional</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
