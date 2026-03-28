@extends('layouts.app')
@section('title','Tickets')
@section('page-title','Tickets de Servicio')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2 flex-wrap">
            <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Buscar cliente…">
            <select name="status" class="form-select w-44">
                <option value="">Estado</option>
                @foreach(['PENDIENTE','URGENTE','ATENCION','PROGRAMADO','INFORMATIVO','NO_QUEDO_EN_LA_VISITA','LISTO'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select w-40">
                <option value="">Tipo</option>
                @foreach(['CONECTIVIDAD','ATASCO','TONER','QUEJAS','COPIA','RUIDOS','IMPRESION','OTROS'] as $t)
                <option value="{{ $t }}" @selected(request('type')===$t)>{{ $t }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Buscar</button>
        </form>
        <a href="{{ route('tickets.create') }}" class="btn-primary">+ Nuevo ticket</a>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Cliente</th><th>Tipo</th><th>Descripción</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($tickets as $t)
            @php
            $sc=['URGENTE'=>'badge-red','PENDIENTE'=>'badge-yellow','ATENCION'=>'badge-orange','PROGRAMADO'=>'badge-blue','INFORMATIVO'=>'badge-gray','NO_QUEDO_EN_LA_VISITA'=>'badge-purple','LISTO'=>'badge-green'];
            @endphp
            <tr>
                <td class="font-medium">{{ $t->client->name }}<br><span class="text-xs text-gray-400">{{ $t->branch->name }}</span></td>
                <td><span class="badge-blue text-xs">{{ $t->report_type }}</span></td>
                <td class="max-w-xs"><p class="truncate text-sm">{{ $t->description }}</p></td>
                <td><span class="{{ $sc[$t->report_status]??'badge-gray' }}">{{ str_replace('_',' ',$t->report_status) }}</span></td>
                <td class="text-xs text-gray-500">{{ $t->created_at->format('d/m/Y') }}</td>
                <td class="flex gap-1">
                    <a href="{{ route('tickets.show',$t) }}" class="btn btn-sm btn-secondary">Ver</a>
                    @if($t->report_status !== 'LISTO')
                    <form method="POST" action="{{ route('tickets.close',$t) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-success">Cerrar</button></form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-8 text-gray-400">Sin tickets</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $tickets->links() }}</div>
</div>
@endsection
