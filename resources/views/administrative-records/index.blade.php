@extends('layouts.app')
@section('title','Registros Administrativos')
@section('page-title','Registros Administrativos')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Empleado…">
            <select name="type" class="form-select w-56"><option value="">Tipo</option>@foreach(['RETROALIMENTACION_ESCRITA','AMONESTACION','ACTA_ADMINISTRATIVA','ENTREVISTA_AUSENTISMO'] as $t)<option value="{{ $t }}" @selected(request('type')===$t)>{{ str_replace('_',' ',$t) }}</option>@endforeach</select>
            <button class="btn-secondary">Buscar</button>
        </form>
        <a href="{{ route('administrative-records.create') }}" class="btn-primary">+ Nuevo registro</a>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Empleado</th><th>Tipo</th><th>Días suspensión</th><th>Emitido por</th><th>Fecha</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($records as $r)
            <tr>
                <td class="font-medium">{{ $r->employee->nombre }}</td>
                <td><span class="badge-yellow text-xs">{{ str_replace('_',' ',$r->type_administrative) }}</span></td>
                <td>{{ $r->suspended_days > 0 ? $r->suspended_days.' días' : '—' }}</td>
                <td>{{ $r->issuedBy->full_name }}</td>
                <td>{{ $r->created_at->format('d/m/Y') }}</td>
                <td class="flex gap-1">
                    <a href="{{ route('administrative-records.show',$r) }}" class="btn btn-sm btn-secondary">Ver</a>
                    <a href="{{ route('administrative-records.edit',$r) }}" class="btn btn-sm btn-primary">Editar</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-8 text-gray-400">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $records->links() }}</div>
</div>
@endsection
