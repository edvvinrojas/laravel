@extends('layouts.app')
@section('title','Créditos a Empleados')
@section('page-title','Créditos')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Empleado...">
            <select name="status" class="form-select w-40">
                <option value="">Estado</option>
                @foreach(['SOLICITADO','AUTORIZADO','LIQUIDADO','CANCELADO'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Buscar</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('rh.index') }}" class="btn-secondary">&larr; RH</a>
            <a href="{{ route('credits.create') }}" class="btn-primary">+ Nuevo crédito</a>
        </div>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Monto crédito</th>
                    <th>Descuento quincenal</th>
                    <th>Monto pendiente</th>
                    <th>Quincenas pendientes</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($credits as $c)
            @php $sc=['SOLICITADO'=>'badge-yellow','AUTORIZADO'=>'badge-blue','LIQUIDADO'=>'badge-green','CANCELADO'=>'badge-red']; @endphp
            @php
                $u = auth()->user();
                $canApprove = $u->hasFullRhAccess()
                    || $u->department === 'rh'
                    || $c->employee?->direct_manager_user_id === $u->id;
            @endphp
            <tr>
                <td class="font-medium">{{ $c->employee->nombre }}</td>
                <td>${{ number_format($c->credit_amount,2) }}</td>
                <td>${{ number_format($c->biweekly_discount,2) }}</td>
                <td>${{ number_format($c->pending_amount,2) }}</td>
                <td>{{ $c->pending_biweeks }}</td>
                <td><span class="{{ $sc[$c->status] ?? 'badge-gray' }}">{{ $c->status }}</span></td>
                <td class="flex gap-1 flex-wrap">
                    <a href="{{ route('credits.show',$c) }}" class="btn btn-sm btn-secondary">Ver</a>
                    <a href="{{ route('credits.edit',$c) }}" class="btn btn-sm btn-primary">Editar</a>
                    @if($canApprove && $c->status === 'SOLICITADO')
                    <form action="{{ route('credits.approve',$c) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-success">Autorizar</button>
                    </form>
                    <form action="{{ route('credits.reject',$c) }}" method="POST" onsubmit="return confirm('¿No autorizar esta solicitud?')">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-danger">No autorizar</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-8 text-gray-400">Sin créditos registrados</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $credits->links() }}</div>
</div>
@endsection
