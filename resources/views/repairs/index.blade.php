@extends('layouts.app')
@section('title','Taller')
@section('page-title','Taller / Reparaciones')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Serie / modelo…">
            <select name="status" class="form-select w-36">
                <option value="">Estado</option>
                @foreach(['PENDIENTE','PAUSADO','LISTO'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Buscar</button>
        </form>
        <a href="{{ route('repairs.create') }}" class="btn-primary">+ Ingreso a taller</a>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Equipo</th><th>Serie</th><th>Procedencia</th><th>Estatus</th><th>Ubicación</th><th>Fecha alta</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($repairs as $r)
            @php $sc=['PENDIENTE'=>'badge-yellow','PAUSADO'=>'badge-gray','LISTO'=>'badge-green']; @endphp
            <tr>
                <td>{{ $r->item->brand->name ?? '' }} {{ $r->item->model }}</td>
                <td class="font-mono text-xs">{{ $r->item->serie }}</td>
                <td><span class="badge-blue text-xs">{{ $r->procedencia }}</span></td>
                <td><span class="{{ $sc[$r->estado_taller]??'badge-gray' }}">{{ $r->estado_taller }}</span></td>
                <td>{{ $r->ubicacion ?? '—' }}</td>
                <td class="text-xs text-gray-500">{{ $r->fecha_alta->format('d/m/Y') }}</td>
                <td class="flex gap-1">
                    @if(auth()->user()->hasPermission('taller.view'))
                        <a href="{{ route('repairs.show',$r) }}" class="btn btn-sm btn-secondary">Ver</a>
                    @endif
                    @if(auth()->user()->hasPermission('taller.edit'))
                        <a href="{{ route('repairs.edit',$r) }}" class="btn btn-sm btn-primary">Editar</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-8 text-gray-400">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $repairs->links() }}</div>
</div>
@endsection
