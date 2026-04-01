@extends('layouts.app')
@section('title','Contadores')
@section('page-title','Contadores de Impresión')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Buscar cliente…">
            <select name="rent_id" class="form-select w-48">
                <option value="">Todas las rentas</option>
                @foreach($rents as $r)
                <option value="{{ $r->id }}" @selected(request('rent_id')==$r->id)>{{ $r->client->name }} — {{ $r->contract_number }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Buscar</button>
        </form>
        <a href="{{ route('print-counters.create') }}" class="btn-primary">+ Nuevo contador</a>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Cliente</th><th>Contrato</th><th>Período</th><th>BN Impreso</th><th>Color Impreso</th><th>Exceso total</th><th>Facturado</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($counters as $c)
            <tr>
                <td>{{ $c->rent->client->name }}</td>
                <td class="font-mono text-xs">{{ $c->rent->contract_number }}</td>
                <td>{{ sprintf('%02d',$c->period_month) }}/{{ $c->period_year }}</td>
                <td>{{ number_format($c->bn_printed) }}</td>
                <td>{{ number_format($c->color_printed) }}</td>
                <td class="font-medium">${{ number_format($c->total_excess_amount,2) }}</td>
                <td>{!! $c->is_billed ? '<span class="badge-green">Sí</span>' : '<span class="badge-gray">No</span>' !!}</td>
                <td>
                    <a href="{{ route('print-counters.show',$c) }}" class="btn btn-sm btn-secondary">Ver</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-8 text-gray-400">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $counters->links() }}</div>
</div>
@endsection
