@extends('layouts.app')
@section('title','Auditoría')
@section('page-title','Registros de Auditoría')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2 flex-wrap">
            <select name="module" class="form-select w-40"><option value="">Módulo</option>@foreach($modules as $m)<option value="{{ $m }}" @selected(request('module')===$m)>{{ $m }}</option>@endforeach</select>
            <select name="action" class="form-select w-36"><option value="">Acción</option>@foreach($actions as $a)<option value="{{ $a }}" @selected(request('action')===$a)>{{ $a }}</option>@endforeach</select>
            <input name="date_from" type="date" value="{{ request('date_from') }}" class="form-input w-40">
            <input name="date_to" type="date" value="{{ request('date_to') }}" class="form-input w-40">
            <button class="btn-secondary">Filtrar</button>
            @if(request()->anyFilled(['module','action','date_from','date_to']))<a href="{{ route('audit.index') }}" class="btn-secondary">Limpiar</a>@endif
        </form>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Módulo</th><th>ID Registro</th><th>IP</th><th>Detalle</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $log->user?->full_name ?? 'Sistema' }}</td>
                <td>
                    @php $ac=['CREATE'=>'badge-green','UPDATE'=>'badge-blue','DELETE'=>'badge-red','LOGIN'=>'badge-purple']; @endphp
                    <span class="{{ $ac[strtoupper($log->action)]??'badge-gray' }} text-xs">{{ $log->action }}</span>
                </td>
                <td class="text-sm">{{ $log->module }}</td>
                <td class="text-xs text-gray-500">#{{ $log->record_id ?? '—' }}</td>
                <td class="font-mono text-xs text-gray-400">{{ $log->ip_address }}</td>
                <td class="max-w-xs"><p class="text-xs text-gray-600 truncate">{{ $log->detail }}</p></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-8 text-gray-400">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
</div>
@endsection
