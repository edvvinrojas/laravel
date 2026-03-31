<!DOCTYPE html>
<html lang="es" class="bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contadores de Impresión — {{ $client->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-800">

{{-- Header --}}
<header class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-10 no-print">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <img src="{{ asset('img/logo.svg') }}" alt="CopyMart" class="h-9 w-auto bg-gray-900 rounded p-1">
            <div>
                <p class="text-xs text-gray-400 leading-none">Portal de contadores</p>
                <p class="font-semibold text-gray-800 text-sm leading-tight">{{ $client->name }}</p>
            </div>
        </div>
        <button onclick="window.print()"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimir / PDF
        </button>
    </div>
</header>

<main class="max-w-5xl mx-auto px-4 py-6 space-y-8">

    {{-- Print header (only in print) --}}
    <div class="hidden print:block mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xl font-bold">{{ $client->name }}</p>
                @if($client->comercial_name)
                <p class="text-gray-500 text-sm">{{ $client->comercial_name }}</p>
                @endif
            </div>
            <div class="text-right text-sm text-gray-500">
                <p>Reporte de contadores</p>
                <p>Generado: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        <hr class="mt-3">
    </div>

    @if($rents->isEmpty())
    <div class="text-center py-20 text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="font-medium">No hay rentas con servicio de impresión activas.</p>
    </div>
    @endif

    @foreach($rents as $rent)
    @php
        $latest  = $rent->printCounters->first();
        $months  = ['', 'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                    'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    @endphp

    <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        {{-- Equipment header --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-4 text-white">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="font-semibold text-lg leading-tight">
                        {{ $rent->item->brand->name ?? '' }} {{ $rent->item->model ?? '—' }}
                    </p>
                    <p class="text-blue-100 text-sm mt-0.5">
                        No. serie: <span class="font-mono">{{ $rent->item->serie ?? '—' }}</span>
                        &nbsp;·&nbsp; Contrato: <span class="font-mono">{{ $rent->contract_number }}</span>
                    </p>
                    @if($rent->branch || $rent->area)
                    <p class="text-blue-100 text-xs mt-1">
                        Ubicación:
                        @if($rent->branch) {{ $rent->branch->name }} @endif
                        @if($rent->area) / {{ $rent->area->name }} @endif
                    </p>
                    @endif
                </div>
                <div class="text-right text-sm">
                    <p class="text-blue-100 text-xs">Incluidas por mes</p>
                    <p class="font-medium">BN: {{ number_format($rent->bn_included) }} &nbsp;|&nbsp; Color: {{ number_format($rent->color_included) }}</p>
                </div>
            </div>
        </div>

        {{-- Current month summary --}}
        @if($latest)
        <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-y sm:divide-y-0 divide-gray-100 border-b border-gray-100">
            @php
                $bnPct    = $rent->bn_included > 0 ? min(100, round($latest->bn_printed / $rent->bn_included * 100)) : 100;
                $colorPct = $rent->color_included > 0 ? min(100, round($latest->color_printed / $rent->color_included * 100)) : 100;
            @endphp
            <div class="px-5 py-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Período actual</p>
                <p class="font-semibold text-gray-800 mt-1">
                    {{ $months[$latest->period_month] }} {{ $latest->period_year }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Lectura: {{ $latest->reading_date?->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide">BN impreso</p>
                <p class="font-semibold text-gray-800 mt-1">{{ number_format($latest->bn_printed) }}</p>
                <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $bnPct >= 100 ? 'bg-red-500' : ($bnPct >= 80 ? 'bg-yellow-400' : 'bg-blue-500') }}"
                         style="width: {{ $bnPct }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $bnPct }}% de incluidas</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Color impreso</p>
                <p class="font-semibold text-gray-800 mt-1">{{ number_format($latest->color_printed) }}</p>
                <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $colorPct >= 100 ? 'bg-red-500' : ($colorPct >= 80 ? 'bg-yellow-400' : 'bg-green-500') }}"
                         style="width: {{ $colorPct }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $colorPct }}% de incluidas</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Excedente del mes</p>
                @if($latest->total_excess_amount > 0)
                <p class="font-semibold text-red-600 mt-1">${{ number_format($latest->total_excess_amount, 2) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    BN: {{ number_format($latest->bn_excess) }} · Color: {{ number_format($latest->color_excess) }}
                </p>
                @else
                <p class="font-semibold text-green-600 mt-1">Sin excedente</p>
                <p class="text-xs text-gray-400 mt-0.5">Dentro del plan incluido</p>
                @endif
            </div>
        </div>
        @endif

        {{-- History table --}}
        <div class="px-5 py-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Historial de lecturas</p>
            @if($rent->printCounters->isEmpty())
                <p class="text-sm text-gray-400 py-4 text-center">Sin lecturas registradas aún.</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs text-gray-400 uppercase tracking-wide">
                            <th class="pb-2 text-left font-semibold">Período</th>
                            <th class="pb-2 text-right font-semibold">BN anterior</th>
                            <th class="pb-2 text-right font-semibold">BN actual</th>
                            <th class="pb-2 text-right font-semibold">BN impreso</th>
                            <th class="pb-2 text-right font-semibold">Exceso BN</th>
                            <th class="pb-2 text-right font-semibold">Color ant.</th>
                            <th class="pb-2 text-right font-semibold">Color act.</th>
                            <th class="pb-2 text-right font-semibold">Color imp.</th>
                            <th class="pb-2 text-right font-semibold">Exceso Col.</th>
                            <th class="pb-2 text-right font-semibold">Total exceso</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($rent->printCounters as $pc)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-2.5 font-medium">
                                {{ $months[$pc->period_month] }} {{ $pc->period_year }}
                                @if($loop->first)
                                <span class="ml-1 text-xs text-blue-600 font-normal">(último)</span>
                                @endif
                            </td>
                            <td class="py-2.5 text-right tabular-nums text-gray-500">{{ number_format($pc->bn_previous) }}</td>
                            <td class="py-2.5 text-right tabular-nums">{{ number_format($pc->bn_current) }}</td>
                            <td class="py-2.5 text-right tabular-nums font-medium">{{ number_format($pc->bn_printed) }}</td>
                            <td class="py-2.5 text-right tabular-nums {{ $pc->bn_excess > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                                {{ $pc->bn_excess > 0 ? number_format($pc->bn_excess) : '—' }}
                            </td>
                            <td class="py-2.5 text-right tabular-nums text-gray-500">{{ number_format($pc->color_previous) }}</td>
                            <td class="py-2.5 text-right tabular-nums">{{ number_format($pc->color_current) }}</td>
                            <td class="py-2.5 text-right tabular-nums font-medium">{{ number_format($pc->color_printed) }}</td>
                            <td class="py-2.5 text-right tabular-nums {{ $pc->color_excess > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                                {{ $pc->color_excess > 0 ? number_format($pc->color_excess) : '—' }}
                            </td>
                            <td class="py-2.5 text-right tabular-nums {{ $pc->total_excess_amount > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                {{ $pc->total_excess_amount > 0 ? '$'.number_format($pc->total_excess_amount, 2) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </section>
    @endforeach

</main>

{{-- Footer --}}
<footer class="max-w-5xl mx-auto px-4 py-6 text-center text-xs text-gray-400 no-print">
    <p>CopyMart — Portal de consulta exclusivo para <strong>{{ $client->name }}</strong></p>
    <p class="mt-0.5">Esta página es de uso privado. No compartir el enlace con terceros.</p>
</footer>

</body>
</html>
