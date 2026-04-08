@extends('layouts.app')
@section('title','TI – Inventario')
@section('page-title','Inventario TI')

@section('content')
<div class="flex gap-3 mb-4 flex-wrap items-center">
    <a href="{{ route('ti-equipment.create') }}" class="btn-primary">+ Nuevo equipo</a>
    <a href="{{ route('ti-equipment.licenses') }}" class="btn-secondary">Licencias</a>
</div>

<form method="GET" class="flex gap-2 mb-4 flex-wrap">
    <input name="search" value="{{ request('search') }}" class="form-input w-56" placeholder="Buscar código/marca/modelo…">
    <select name="tipo" class="form-select w-40">
        <option value="">Todos los tipos</option>
        @foreach(['PC','LAPTOP','SERVIDOR','IMPRESORA','TELEFONO','TABLET','SWITCH','ROUTER','OTRO'] as $t)
        <option value="{{ $t }}" @selected(request('tipo')===$t)>{{ $t }}</option>
        @endforeach
    </select>
    <select name="status" class="form-select w-36">
        <option value="">Todos los estatus</option>
        @foreach(['ACTIVO','BAJA','REPARACION','BODEGA'] as $s)
        <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
        @endforeach
    </select>
    <button class="btn-secondary">Filtrar</button>
    <a href="{{ route('ti-equipment.index') }}" class="btn-secondary">Limpiar</a>
</form>

<div class="card">
<table class="w-full text-sm">
    <thead class="table-head">
        <tr>
            <th class="px-4 py-2 text-left">Código</th>
            <th class="px-4 py-2 text-left">Tipo</th>
            <th class="px-4 py-2 text-left">Marca / Modelo</th>
            <th class="px-4 py-2 text-left">Asignado a</th>
            <th class="px-4 py-2 text-left">Ubicación</th>
            <th class="px-4 py-2 text-left">Estatus</th>
            <th class="px-4 py-2"></th>
        </tr>
    </thead>
    <tbody>
    @forelse($equipment as $eq)
    @php $sc=['ACTIVO'=>'badge-green','BAJA'=>'badge-red','REPARACION'=>'badge-yellow','BODEGA'=>'badge-gray']; @endphp
    <tr class="table-row">
        <td class="px-4 py-2 font-mono font-semibold">{{ $eq->codigo_interno }}</td>
        <td class="px-4 py-2">{{ $eq->tipo }}</td>
        <td class="px-4 py-2">{{ $eq->marca }} {{ $eq->modelo }}</td>
        <td class="px-4 py-2">{{ $eq->assignedUser ? ($eq->assignedUser->full_name ?: $eq->assignedUser->username) : '—' }}</td>
        <td class="px-4 py-2 text-gray-500">{{ $eq->ubicacion ?? '—' }}</td>
        <td class="px-4 py-2"><span class="{{ $sc[$eq->status]??'badge-gray' }}">{{ $eq->status }}</span></td>
        <td class="px-4 py-2 text-right">
            <a href="{{ route('ti-equipment.show', $eq) }}" class="text-blue-600 hover:underline text-xs">Ver</a>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">Sin registros</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-4">{{ $equipment->links() }}</div>
@endsection
