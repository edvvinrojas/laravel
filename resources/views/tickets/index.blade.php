@extends('layouts.app')
@section('title','Atención a Clientes')
@section('page-title','Atención a Clientes — Tickets')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2 flex-wrap">
            <input name="search" value="{{ request('search') }}" class="form-input w-52" placeholder="Buscar ID o cliente…">
            <select name="status" class="form-select w-44">
                <option value="">Estado</option>
                @foreach($statuses as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
            <select name="priority" class="form-select w-36">
                <option value="">Prioridad</option>
                @foreach($priorities as $p)
                <option value="{{ $p }}" @selected(request('priority')===$p)>{{ $p }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select w-40">
                <option value="">Tipo de falla</option>
                @foreach($reportTypes as $t)
                <option value="{{ $t }}" @selected(request('type')===$t)>{{ $t }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Buscar</button>
        </form>
        <a href="{{ route('tickets.create') }}" class="btn-primary">+ Levantar ticket</a>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr>
                <th>ID</th><th>Cliente</th><th>Equipo</th><th>Tipo</th>
                <th>Prioridad</th><th>Estado</th><th>Levantado</th><th>Acciones</th>
            </tr></thead>
            <tbody>
            @forelse($tickets as $t)
            @php
                $sc = ['URGENTE'=>'badge-red','PENDIENTE'=>'badge-yellow','ATENCION'=>'badge-orange','PROGRAMADO'=>'badge-blue','INFORMATIVO'=>'badge-gray','NO_QUEDO_EN_LA_VISITA'=>'badge-purple','LISTO'=>'badge-green'];
                $pc = ['URGENTE'=>'badge-red','NORMAL'=>'badge-blue','BAJA'=>'badge-gray'];
            @endphp
            <tr>
                <td class="font-mono text-sm text-blue-700">{{ $t->ticket_code ?? '—' }}</td>
                <td class="font-medium">{{ $t->client->name }}<br><span class="text-xs text-gray-400">{{ $t->branch->name }}</span></td>
                <td class="text-xs text-gray-600">
                    @if($t->item)
                        {{ $t->item->model }}<br><span class="text-gray-400">{{ $t->item->serie ?? $t->item->sku }}</span>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                <td><span class="badge-blue text-xs">{{ $t->report_type }}</span></td>
                <td><span class="{{ $pc[$t->priority]??'badge-gray' }}">{{ $t->priority ?? 'NORMAL' }}</span></td>
                <td><span class="{{ $sc[$t->report_status]??'badge-gray' }}">{{ str_replace('_',' ',$t->report_status) }}</span></td>
                <td class="text-xs text-gray-500">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                <td class="flex gap-1">
                    <a href="{{ route('tickets.show',$t) }}" class="btn btn-sm btn-secondary">Ver</a>
                    @if($t->report_status !== 'LISTO')
                    <form method="POST" action="{{ route('tickets.close',$t) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-success">Cerrar</button></form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-8 text-gray-400">Sin tickets registrados</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $tickets->links() }}</div>
</div>
@endsection
