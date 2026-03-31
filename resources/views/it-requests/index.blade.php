@extends('layouts.app')
@section('title','Mesa de Ayuda TI')
@section('page-title','Mesa de Ayuda — Tickets TI')

@section('content')
<div class="space-y-4">

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <a href="{{ route('it-requests.create') }}" class="btn-primary">+ Nuevo ticket</a>

        <form method="GET" class="flex flex-wrap gap-2">
            <input name="search" value="{{ request('search') }}" placeholder="Folio o título…"
                   class="form-input w-44 text-sm">
            <select name="status" class="form-select w-36 text-sm">
                <option value="">Todos los estados</option>
                @foreach(['ABIERTO','EN_PROCESO','RESUELTO','CERRADO'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
            <select name="priority" class="form-select w-32 text-sm">
                <option value="">Prioridad</option>
                @foreach(['URGENTE','ALTA','MEDIA','BAJA'] as $p)
                <option value="{{ $p }}" @selected(request('priority')===$p)>{{ $p }}</option>
                @endforeach
            </select>
            <select name="category" class="form-select w-36 text-sm">
                <option value="">Categoría</option>
                @foreach(['EMAIL','INTERNET','HARDWARE','SOFTWARE','IMPRESORA','TELEFONO','ACCESOS','OTRO'] as $c)
                <option value="{{ $c }}" @selected(request('category')===$c)>{{ $c }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary text-sm">Filtrar</button>
            @if(request()->hasAny(['search','status','priority','category']))
            <a href="{{ route('it-requests.index') }}" class="btn-secondary text-sm">Limpiar</a>
            @endif
        </form>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Table --}}
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Folio</th>
                    @if($isTi) <th>Solicitante</th> @endif
                    <th>Categoría</th>
                    <th>Título</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Asignado a</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($query as $t)
                <tr>
                    <td class="font-mono text-xs font-semibold">{{ $t->folio }}</td>
                    @if($isTi) <td>{{ $t->user->full_name }}</td> @endif
                    <td><span class="badge badge-blue">{{ $t->category }}</span></td>
                    <td class="max-w-xs truncate">{{ $t->title }}</td>
                    <td>
                        @php
                            $pc = match($t->priority) {
                                'URGENTE' => 'badge-red',
                                'ALTA'    => 'badge-yellow',
                                'MEDIA'   => 'badge-blue',
                                default   => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $pc }}">{{ $t->priority }}</span>
                    </td>
                    <td>
                        @php
                            $sc = match($t->status) {
                                'ABIERTO'    => 'badge-yellow',
                                'EN_PROCESO' => 'badge-blue',
                                'RESUELTO'   => 'badge-green',
                                'CERRADO'    => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $sc }}">{{ str_replace('_',' ',$t->status) }}</span>
                    </td>
                    <td>{{ $t->assignedUser?->full_name ?? '—' }}</td>
                    <td class="text-xs text-gray-500">{{ $t->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('it-requests.show', $t) }}" class="text-blue-600 hover:underline text-xs">Ver</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-8 text-gray-400">Sin tickets registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $query->links() }}
</div>
@endsection
