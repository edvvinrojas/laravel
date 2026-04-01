@extends('layouts.app')
@section('title', $tab === 'facturacion' ? 'Facturación' : 'Cobranza')
@section('page-title', $tab === 'facturacion' ? 'Facturación' : 'Cobranza')

@section('content')
<div class="space-y-4">

    {{-- Pestañas --}}
    <div class="flex border-b border-gray-200 gap-1">
        <a href="{{ route('billing.index', ['tab' => 'cobranza']) }}"
           class="px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 transition-colors
                  {{ $tab === 'cobranza' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Cobranza
        </a>
        <a href="{{ route('billing.index', ['tab' => 'facturacion']) }}"
           class="px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 transition-colors
                  {{ $tab === 'facturacion' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Facturación
        </a>
        <div class="flex-1"></div>
        <div class="flex items-center pb-1">
            <a href="{{ route('billing.create') }}" class="btn-primary btn-sm">+ Nueva factura</a>
        </div>
    </div>

    {{-- Totales --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">${{ number_format($totals['pending'],2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Pendiente de cobro</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-red-600">${{ number_format($totals['overdue'],2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Vencido</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-green-600">${{ number_format($totals['paid'],2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Cobrado este mes</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card">
        <div class="card-header">
            <form method="GET" class="flex gap-2 flex-wrap">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Cliente / factura…">
                @if($tab === 'cobranza')
                <select name="status" class="form-select w-36">
                    <option value="">Estado</option>
                    @foreach(['PENDIENTE','VENCIDO','PAGADO'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                    @endforeach
                </select>
                @else
                <select name="type" class="form-select w-32">
                    <option value="">Tipo</option>
                    <option value="RENTA" @selected(request('type')==='RENTA')>RENTA</option>
                    <option value="VENTA" @selected(request('type')==='VENTA')>VENTA</option>
                    <option value="EXCESO" @selected(request('type')==='EXCESO')>EXCESO</option>
                </select>
                <input name="date_from" type="date" value="{{ request('date_from') }}" class="form-input w-36" title="Desde">
                <input name="date_to" type="date" value="{{ request('date_to') }}" class="form-input w-36" title="Hasta">
                @endif
                <button class="btn-secondary btn-sm">Filtrar</button>
                @if(request()->anyFilled(['search','status','type','date_from','date_to']))
                <a href="{{ route('billing.index', ['tab' => $tab]) }}" class="btn-secondary btn-sm">Limpiar</a>
                @endif
            </form>
        </div>
        <div class="table-wrap rounded-none border-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($billings as $b)
                @php $c=['PENDIENTE'=>'badge-yellow','PAGADO'=>'badge-green','VENCIDO'=>'badge-red']; @endphp
                <tr>
                    <td class="font-mono text-xs">{{ $b->invoice_number ?? '—' }}</td>
                    <td class="font-medium">{{ $b->client->name }}</td>
                    <td><span class="{{ $b->billing_type==='RENTA'?'badge-blue':($b->billing_type==='EXCESO'?'badge-red':'badge-purple') }}">{{ $b->billing_type }}</span></td>
                    <td class="font-medium">${{ number_format($b->amount,2) }}</td>
                    <td class="{{ $b->status==='VENCIDO'?'text-red-600 font-medium':'' }}">{{ $b->due_date->format('d/m/Y') }}</td>
                    <td><span class="{{ $c[$b->status]??'badge-gray' }}">{{ $b->status }}</span></td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('billing.show',$b) }}" class="btn-secondary btn-sm">Ver</a>
                            @if($b->status !== 'PAGADO')
                            <a href="{{ route('billing.edit',$b) }}" class="btn-secondary btn-sm">Editar</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-8 text-gray-400">Sin registros</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $billings->appends(request()->query())->links() }}</div>
    </div>

</div>
@endsection
