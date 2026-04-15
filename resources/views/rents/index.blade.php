@extends('layouts.app')
@section('title','Rentas')
@section('page-title','Rentas')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="flex items-center gap-3">
            <form method="GET" class="flex gap-2">
                <input name="search" value="{{ request('search') }}" class="form-input w-56" placeholder="Buscar cliente / contrato…">
                <select name="status" class="form-select w-40">
                    <option value="">Todos</option>
                    @foreach(['PENDIENTE','SIN_FIRMAR','VIGENTE','FINALIZADO','CANCELADO'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                    @endforeach
                </select>
                <button class="btn-secondary">Buscar</button>
                <a href="{{ route('print-counters.index') }}" class="btn-secondary">Contadores</a>
                @if(request('search')||request('status'))
                <a href="{{ route('rents.index') }}" class="btn-secondary">Limpiar</a>
                @endif
            </form>
        </div>
        @if(auth()->user()->hasPermission('rentas.create'))
            <a href="{{ route('rents.create') }}" class="btn-primary">+ Nueva renta</a>
        @endif
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr>
                <th>Contrato</th><th>Cliente</th><th>Equipo</th><th>Renta</th><th>Inicio</th><th>Estatus</th><th>Acciones</th>
            </tr></thead>
            <tbody>
            @forelse($rents as $r)
            <tr>
                <td class="font-mono text-xs">{{ $r->contract_number ?? '—' }}</td>
                <td class="font-medium">{{ $r->client->name }}</td>
                <td>{{ $r->item->brand->name ?? '' }} {{ $r->item->model }}</td>
                <td>${{ number_format($r->rent,2) }}</td>
                <td>{{ $r->start_date->format('d/m/Y') }}</td>
                <td>
                    @php $colors=['VIGENTE'=>'badge-green','PENDIENTE'=>'badge-yellow','SIN_FIRMAR'=>'badge-blue','FINALIZADO'=>'badge-gray','CANCELADO'=>'badge-red']; @endphp
                    <span class="{{ $colors[$r->contract_status]??'badge-gray' }}">{{ $r->contract_status }}</span>
                </td>
                <td class="flex gap-1">
                    @if(auth()->user()->hasPermission('rentas.view'))
                        <a href="{{ route('rents.show',$r) }}" class="btn btn-sm btn-secondary">Ver</a>
                    @endif
                    @if(auth()->user()->hasPermission('rentas.edit'))
                        <a href="{{ route('rents.edit',$r) }}" class="btn btn-sm btn-primary">Editar</a>
                    @endif
                    @if(auth()->user()->hasPermission('rentas.delete'))
                        <form method="POST" action="{{ route('rents.destroy',$r) }}" onsubmit="return confirm('¿Desactivar?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-gray-400 py-8">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $rents->links() }}</div>
</div>
@endsection
