@extends('layouts.app')
@section('title','Contador')
@section('page-title','Detalle Contador')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('print-counters.edit',$printCounter) }}" class="btn-primary">Editar</a>
    <a href="{{ route('print-counters.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="card max-w-2xl">
    <div class="card-header">
        <h3 class="font-semibold">{{ $printCounter->rent->client->name }} — {{ sprintf('%02d',$printCounter->period_month) }}/{{ $printCounter->period_year }}</h3>
        @if($printCounter->is_billed)<span class="badge-green">Facturado</span>@else<span class="badge-gray">Sin facturar</span>@endif
    </div>
    <div class="card-body">
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
        <div class="grid grid-cols-2 gap-4 text-sm border-t pt-4">
            <div><p class="text-gray-500">Exceso BN</p><p>{{ number_format($printCounter->bn_excess) }} × ${{ $printCounter->bn_cost_per_page }} = <strong>${{ number_format($printCounter->bn_excess_amount,2) }}</strong></p></div>
            <div><p class="text-gray-500">Exceso Color</p><p>{{ number_format($printCounter->color_excess) }} × ${{ $printCounter->color_cost_per_page }} = <strong>${{ number_format($printCounter->color_excess_amount,2) }}</strong></p></div>
            <div class="col-span-2 text-center border-t pt-3">
                <p class="text-gray-500 text-xs">Total exceso</p>
                <p class="text-2xl font-bold text-red-600">${{ number_format($printCounter->total_excess_amount,2) }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
