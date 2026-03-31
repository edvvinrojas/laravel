@extends('layouts.app')
@section('title','Renta')
@section('page-title','Detalle de Renta')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('rents.edit',$rent) }}" class="btn-primary">Editar</a>
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
            <div><p class="text-gray-500">Equipo</p><p class="font-medium">{{ $rent->item->brand->name ?? '' }} {{ $rent->item->model }}</p></div>
            <div><p class="text-gray-500">Serie</p><p class="font-mono">{{ $rent->item->serie }}</p></div>
            <div><p class="text-gray-500">Renta mensual</p><p class="font-bold text-lg">${{ number_format($rent->rent,2) }}</p></div>
            <div><p class="text-gray-500">Inicio</p><p>{{ $rent->start_date->format('d/m/Y') }}</p></div>
            <div><p class="text-gray-500">Fin</p><p>{{ $rent->end_date?->format('d/m/Y') ?? '—' }}</p></div>
            @if($rent->has_print_service)
            <div class="col-span-2 border-t pt-3 grid grid-cols-2 gap-3">
                <div><p class="text-gray-500">BN incluidas</p><p>{{ number_format($rent->bn_included) }}</p></div>
                <div><p class="text-gray-500">Costo exceso BN</p><p>${{ $rent->bn_cost_per_excess }}</p></div>
                <div><p class="text-gray-500">Color incluidas</p><p>{{ number_format($rent->color_included) }}</p></div>
                <div><p class="text-gray-500">Costo exceso Color</p><p>${{ $rent->color_cost_per_excess }}</p></div>
            </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="font-semibold text-sm">Últimas facturas</h3><a href="{{ route('billing.create') }}?rent_id={{ $rent->id }}" class="btn btn-sm btn-primary">+ Factura</a></div>
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

    {{-- Accesorios y consumibles incluidos --}}
    @if($rent->accesorios->count() || $rent->consumibles->count())
    <div class="card lg:col-span-3">
        <div class="card-header">
            <h3 class="font-semibold text-sm">Accesorios y consumibles del contrato</h3>
        </div>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-6">

            @if($rent->accesorios->count())
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Accesorios</p>
                <div class="space-y-1.5">
                    @foreach($rent->accesorios as $acc)
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="font-medium">{{ $acc->nombre }}</span>
                        @if($acc->codigo)<span class="text-gray-400 text-xs">{{ $acc->codigo }}</span>@endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($rent->consumibles->count())
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Consumibles / Tóner</p>
                <div class="space-y-1.5">
                    @foreach($rent->consumibles as $con)
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>
                            <span class="font-medium">{{ $con->nombre }}</span>
                            <span class="text-gray-400 text-xs ml-1">
                                {{ $con->tipo }}{{ $con->color ? ' · '.$con->color : '' }}
                            </span>
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
    @endif

    @if($rent->has_print_service)
    <div class="card lg:col-span-3">
        <div class="card-header"><h3 class="font-semibold text-sm">Contadores de impresión</h3><a href="{{ route('print-counters.create') }}?rent_id={{ $rent->id }}" class="btn btn-sm btn-primary">+ Contador</a></div>
        <div class="table-wrap rounded-none border-0">
            <table class="table">
                <thead><tr><th>Período</th><th>BN impreso</th><th>Color impreso</th><th>Exceso BN</th><th>Exceso Color</th><th>Total exceso</th></tr></thead>
                <tbody>
                @forelse($rent->printCounters as $pc)
                <tr>
                    <td>{{ sprintf('%02d',$pc->period_month) }}/{{ $pc->period_year }}</td>
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
