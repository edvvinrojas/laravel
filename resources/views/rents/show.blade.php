@extends('layouts.app')
@section('title','Renta')
@section('page-title','Detalle de Renta')

@section('content')
<div class="flex gap-3 mb-4">
    @if(auth()->user()->hasPermission('rentas.edit'))
        <a href="{{ route('rents.edit',$rent) }}" class="btn-primary">Editar</a>
    @endif
    @if(auth()->user()->hasPermission('rentas.view'))
        <a href="{{ route('rents.pdf',$rent) }}" target="_blank" class="btn-secondary">PDF</a>
    @endif
    <a href="{{ route('rents.index') }}" class="btn-secondary">← Volver</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 card">
        <div class="card-header">
            <h3 class="font-semibold">Contrato {{ $rent->contract_number ?? 'Sin número' }}</h3>
            @php $colors=['VIGENTE'=>'badge-green','PENDIENTE'=>'badge-yellow','SIN_FIRMAR'=>'badge-blue','FINALIZADO'=>'badge-gray','CANCELADO'=>'badge-red']; @endphp
            <span class="{{ $colors[$rent->contract_status]??'badge-gray' }}">{{ $rent->contract_status }}</span>
        </div>
        <div class="card-body grid grid-cols-2 gap-4 text-sm">
            <div><p class="text-gray-500">Cliente</p><p class="font-medium">{{ $rent->client->name }}</p></div>
            <div><p class="text-gray-500">Equipos</p><p class="font-medium">{{ $rent->items->count() ?: 1 }}</p></div>
            <div><p class="text-gray-500">Renta mensual</p><p class="font-bold text-lg">${{ number_format($rent->rent,2) }}</p></div>
            <div><p class="text-gray-500">Inicio</p><p>{{ $rent->start_date->format('d/m/Y') }}</p></div>
            <div><p class="text-gray-500">Fin</p><p>{{ $rent->end_date?->format('d/m/Y') ?? '—' }}</p></div>
            <div>
                <p class="text-gray-500">Foráneo</p>
                <p>{{ $rent->is_foreign ? 'Sí' : 'No' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Creado por</p>
                <p>{{ $rent->creator?->full_name ?? '—' }}</p>
            </div>
            <div class="col-span-2 border-t pt-3">
                <p class="text-gray-500 mb-2">Asignacion por equipo</p>
                <div class="space-y-2">
                    @foreach(($rent->items->count() ? $rent->items : collect([$rent->item])) as $eq)
                        @php
                            $branchId = $eq->pivot->branch_id ?? $rent->branch_id;
                            $areaId = $eq->pivot->area_id ?? $rent->area_id;
                            $branchName = optional($rent->client->branches->firstWhere('id', $branchId))->name;
                            $branchModel = $rent->client->branches->firstWhere('id', $branchId);
                            $areaName = $branchModel ? optional($branchModel->areas->firstWhere('id', $areaId))->name : null;
                        @endphp
                        <div class="rounded border border-gray-200 p-2">
                            <p class="font-medium">{{ $eq->brand->name ?? '' }} {{ $eq->model }} <span class="font-mono text-xs text-gray-500">{{ $eq->serie }}</span></p>
                            <p class="text-xs text-gray-600">Sucursal: {{ $branchName ?: 'Sin sucursal' }} | Area: {{ $areaName ?: 'Sin area' }}</p>
                            <p class="text-xs text-gray-600">Renta mensual: ${{ number_format($eq->pivot->rent ?? 0, 2) }}</p>
                            <p class="text-xs text-gray-600">Contador BN: {{ (int) ($eq->pivot->contador_inicial_bn ?? $rent->contador_inicial_bn ?? 0) }} | Contador Color: {{ (int) ($eq->pivot->contador_inicial_color ?? $rent->contador_inicial_color ?? 0) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            @if($rent->has_print_service)
            <div class="col-span-2 border-t pt-3 grid grid-cols-2 gap-3">
                <div><p class="text-gray-500">BN incluidas</p><p>{{ number_format($rent->bn_included) }}</p></div>
                <div><p class="text-gray-500">Costo exceso BN</p><p>${{ $rent->bn_cost_per_excess }}</p></div>
                <div><p class="text-gray-500">Color incluidas</p><p>{{ number_format($rent->color_included) }}</p></div>
                <div><p class="text-gray-500">Costo exceso Color</p><p>${{ $rent->color_cost_per_excess }}</p></div>
                @if($rent->print_notes)
                <div class="col-span-2"><p class="text-gray-500">Notas</p><p class="text-sm">{{ $rent->print_notes }}</p></div>
                @endif
            </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-sm">Últimas facturas</h3>
            @if(auth()->user()->hasPermission('facturacion.create'))
                <a href="{{ route('billing.create') }}?rent_id={{ $rent->id }}" class="btn btn-sm btn-primary">+ Factura</a>
            @endif
        </div>
        <ul class="divide-y divide-gray-100">
            @forelse($rent->billings->take(5) as $b)
            <li class="px-4 py-2.5 flex justify-between text-sm">
                <span>{{ $b->target_date->format('M Y') }}</span>
                <span class="font-medium">${{ number_format($b->amount,2) }}</span>
                <span class="badge {{ $b->status==='PAGADO'?'badge-green':($b->status==='VENCIDO'?'badge-red':'badge-yellow') }}">{{ $b->status }}</span>
            </li>
            @empty
            <li class="px-4 py-6 text-center text-sm text-gray-400">Sin facturas</li>
            @endforelse
        </ul>
    </div>

    @if($rent->has_print_service)
    <div class="card lg:col-span-3">
        <div class="card-header"><h3 class="font-semibold text-sm">Contadores de impresión</h3>
            @if(auth()->user()->hasPermission('facturacion.create'))
                <a href="{{ route('print-counters.create') }}?rent_id={{ $rent->id }}" class="btn btn-sm btn-primary">+ Contador</a>
            @endif
        </div>
        <div class="table-wrap rounded-none border-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Período</th>
                        <th>BN impreso</th>
                        <th>Color impreso</th>
                        <th>Exceso BN</th>
                        <th>Exceso Color</th>
                        <th>Total exceso</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rent->printCounters as $pc)
                <tr>
                    <td class="font-mono text-xs">{{ sprintf('%02d',$pc->period_month) }}/{{ $pc->period_year }}</td>
                    <td>{{ number_format($pc->bn_printed) }}</td>
                    <td>{{ number_format($pc->color_printed) }}</td>
                    <td>{{ number_format($pc->bn_excess) }}</td>
                    <td>{{ number_format($pc->color_excess) }}</td>
                    <td class="font-medium">${{ number_format($pc->total_excess_amount,2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-gray-400 py-4">Sin contadores</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
