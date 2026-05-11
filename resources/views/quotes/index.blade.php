@extends('layouts.app')
@section('title','Cotizaciones')
@section('page-title','Cotizaciones')

@section('content')
<div class="space-y-5">

{{-- ── SECCIÓN: PENDIENTES POR REVISAR (solo visible para jefes) ──────────── --}}
@if($pendingReview->isNotEmpty())
<div class="card border-amber-200">
    <div class="card-header bg-amber-50 border-b border-amber-200">
        <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <h3 class="font-semibold text-amber-800 text-sm">Cotizaciones pendientes de revisión</h3>
            <span class="badge bg-amber-100 text-amber-800">{{ $pendingReview->count() }}</span>
        </div>
        <p class="text-xs text-amber-600">Cotizaciones de tus colaboradores pendientes de revisión (BORRADOR / ENVIADA)</p>
    </div>
    <div class="divide-y divide-amber-100">
        @foreach($pendingReview as $q)
        <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3 hover:bg-amber-50/50 transition">
            <div class="flex-1 min-w-0 grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-1 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Número</p>
                    <p class="font-mono font-semibold text-gray-800">{{ $q->quote_number }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Cliente</p>
                    <p class="font-medium text-gray-800 truncate">{{ $q->client->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Total</p>
                    <p class="font-bold text-gray-900">${{ number_format($q->total, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Generada por</p>
                    <p class="text-gray-700">{{ $q->creator->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $q->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('quotes.show', $q) }}" class="btn btn-sm btn-secondary">Ver detalle</a>
                <form method="POST" action="{{ route('quotes.approve', $q) }}" id="approve-form-{{ $q->id }}">
                    @csrf @method('PATCH')
                </form>
                <form method="POST" action="{{ route('quotes.reject', $q) }}" id="reject-form-{{ $q->id }}">
                    @csrf @method('PATCH')
                </form>
                <button type="button" class="btn btn-sm btn-success"
                    onclick="openConfirmModal('approve', {{ $q->id }}, '{{ addslashes($q->quote_number) }}', '{{ addslashes($q->client->name) }}', '{{ number_format($q->total, 2) }}', '{{ addslashes($q->creator->full_name ?? '') }}')">
                    ✓ Aprobar
                </button>
                <button type="button" class="btn btn-sm btn-danger"
                    onclick="openConfirmModal('reject', {{ $q->id }}, '{{ addslashes($q->quote_number) }}', '{{ addslashes($q->client->name) }}', '{{ number_format($q->total, 2) }}', '{{ addslashes($q->creator->full_name ?? '') }}')">
                    ✗ Rechazar
                </button>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── LISTADO GENERAL ─────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2 flex-wrap">
            <input name="search" value="{{ request('search') }}" class="form-input w-56" placeholder="Cliente / número…">
            <select name="status" class="form-select w-44">
                <option value="">Todos los estados</option>
                @foreach(['BORRADOR','ENVIADA','APROBADA','RECHAZADA'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Buscar</button>
        </form>
        @if(auth()->user()->hasPermission('cotizaciones.create'))
        <a href="{{ route('quotes.create') }}" class="btn-primary">+ Nueva cotización</a>
        @endif
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Válida hasta</th>
                    <th>Creada por</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($quotes as $q)
            @php
                $colors = [
                    'BORRADOR'  => 'badge-gray',
                    'ENVIADA'   => 'badge-yellow',
                    'APROBADA'  => 'badge-green',
                    'RECHAZADA' => 'badge-red',
                ];
                $isManager = $pendingReview->contains('id', $q->id) ||
                    \App\Models\Employee::where('user_id', $q->created_by)
                        ->where('direct_manager_user_id', auth()->id())
                        ->exists();
            @endphp
            <tr>
                <td class="font-mono text-xs">{{ $q->quote_number }}</td>
                <td>{{ $q->client->name }}</td>
                <td class="font-semibold">${{ number_format($q->total, 2) }}</td>
                <td><span class="{{ $colors[$q->status] ?? 'badge-gray' }}">{{ $q->status }}</span></td>
                <td>{{ $q->valid_until ? $q->valid_until->format('d/m/Y') : '—' }}</td>
                <td>{{ $q->creator->full_name ?? '—' }}</td>
                <td class="text-xs text-gray-500">{{ $q->created_at->format('d/m/Y') }}</td>
                <td>
                    <div class="flex gap-1 flex-wrap">
                        @if(auth()->user()->hasPermission('cotizaciones.view'))
                        <a href="{{ route('quotes.show', $q) }}" class="btn btn-sm btn-secondary">Ver</a>
                        @endif
                        @if(auth()->user()->hasPermission('cotizaciones.edit') && !in_array($q->status, ['APROBADA','RECHAZADA']))
                        <a href="{{ route('quotes.edit', $q) }}" class="btn btn-sm btn-primary">Editar</a>
                        @endif
                        @if(!in_array($q->status, ['APROBADA','RECHAZADA']) && (auth()->user()->isGerencia() || $isManager))
                        {{-- Formularios ocultos para la tabla --}}
                        <form method="POST" action="{{ route('quotes.approve', $q) }}" id="approve-form-tbl-{{ $q->id }}">
                            @csrf @method('PATCH')
                        </form>
                        <form method="POST" action="{{ route('quotes.reject', $q) }}" id="reject-form-tbl-{{ $q->id }}">
                            @csrf @method('PATCH')
                        </form>
                        <button type="button" class="btn btn-sm btn-success"
                            onclick="openConfirmModal('approve-tbl', {{ $q->id }}, '{{ addslashes($q->quote_number) }}', '{{ addslashes($q->client->name) }}', '{{ number_format($q->total, 2) }}', '{{ addslashes($q->creator->full_name ?? '') }}')">✓</button>
                        <button type="button" class="btn btn-sm btn-danger"
                            onclick="openConfirmModal('reject-tbl', {{ $q->id }}, '{{ addslashes($q->quote_number) }}', '{{ addslashes($q->client->name) }}', '{{ number_format($q->total, 2) }}', '{{ addslashes($q->creator->full_name ?? '') }}')">✗</button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-8 text-gray-400">Sin cotizaciones registradas</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $quotes->links() }}</div>
</div>

</div>

{{-- ── MODAL DE CONFIRMACIÓN ───────────────────────────────────────────────── --}}
<div id="confirmModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    {{-- Overlay --}}
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeConfirmModal()"></div>

    {{-- Panel --}}
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">

            {{-- Header con ícono dinámico --}}
            <div id="modalHeader" class="px-6 pt-6 pb-4 flex items-start gap-4">
                <div id="modalIconWrap" class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full">
                    <svg id="modalIcon" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 id="modalTitle" class="text-base font-bold text-gray-900"></h3>
                    <p id="modalSubtitle" class="text-sm text-gray-500 mt-0.5"></p>
                </div>
            </div>

            {{-- Datos de la cotización --}}
            <div class="mx-6 mb-4 rounded-xl border border-gray-100 bg-gray-50 divide-y divide-gray-100">
                <div class="px-4 py-2.5 flex justify-between items-center gap-4">
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Cotización</span>
                    <span id="modalNumber" class="font-mono font-semibold text-gray-800 text-sm"></span>
                </div>
                <div class="px-4 py-2.5 flex justify-between items-center gap-4">
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Cliente</span>
                    <span id="modalClient" class="font-medium text-gray-800 text-sm text-right truncate max-w-[200px]"></span>
                </div>
                <div class="px-4 py-2.5 flex justify-between items-center gap-4">
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Monto total</span>
                    <span id="modalTotal" class="font-bold text-gray-900 text-sm"></span>
                </div>
                <div class="px-4 py-2.5 flex justify-between items-center gap-4">
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Elaborada por</span>
                    <span id="modalCreator" class="text-gray-700 text-sm text-right"></span>
                </div>
            </div>

            {{-- Mensaje de consecuencia --}}
            <p id="modalWarning" class="mx-6 mb-5 text-xs text-gray-500 leading-relaxed"></p>

            {{-- Botones --}}
            <div class="px-6 pb-6 flex gap-3 justify-end">
                <button type="button" onclick="closeConfirmModal()"
                    class="btn btn-secondary px-5">
                    Cancelar
                </button>
                <button type="button" id="modalConfirmBtn" onclick="submitModalForm()"
                    class="btn px-5 font-semibold">
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let _modalFormId = null;

function openConfirmModal(action, id, number, client, total, creator) {
    const isApprove = action.startsWith('approve');
    const formSuffix = action.includes('tbl') ? '-tbl-' : '-';

    _modalFormId = (isApprove ? 'approve' : 'reject') + '-form' + formSuffix + id;

    const modal    = document.getElementById('confirmModal');
    const iconWrap = document.getElementById('modalIconWrap');
    const icon     = document.getElementById('modalIcon');
    const title    = document.getElementById('modalTitle');
    const subtitle = document.getElementById('modalSubtitle');
    const warning  = document.getElementById('modalWarning');
    const btn      = document.getElementById('modalConfirmBtn');

    document.getElementById('modalNumber').textContent  = number;
    document.getElementById('modalClient').textContent  = client;
    document.getElementById('modalTotal').textContent   = '$' + total;
    document.getElementById('modalCreator').textContent = creator || '—';

    if (isApprove) {
        iconWrap.className = 'flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100';
        icon.className     = 'h-6 w-6 text-green-600';
        icon.innerHTML     = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
        title.textContent  = 'Aprobar cotización';
        subtitle.textContent = '¿Estás seguro de que deseas aprobar esta cotización?';
        warning.textContent  = 'Al aprobar, el estado cambiará a APROBADA y se notificará al colaborador. Esta acción no se puede deshacer directamente.';
        btn.textContent    = '✓ Sí, aprobar';
        btn.className      = 'btn px-5 font-semibold bg-green-600 hover:bg-green-700 text-white rounded-lg transition';
    } else {
        iconWrap.className = 'flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100';
        icon.className     = 'h-6 w-6 text-red-600';
        icon.innerHTML     = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
        title.textContent  = 'Rechazar cotización';
        subtitle.textContent = '¿Estás seguro de que deseas rechazar esta cotización?';
        warning.textContent  = 'Al rechazar, el estado cambiará a RECHAZADA y se notificará al colaborador para que la revise o corrija.';
        btn.textContent    = '✗ Sí, rechazar';
        btn.className      = 'btn px-5 font-semibold bg-red-600 hover:bg-red-700 text-white rounded-lg transition';
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    document.body.style.overflow = '';
    _modalFormId = null;
}

function submitModalForm() {
    if (_modalFormId) {
        document.getElementById(_modalFormId).submit();
    }
}

// Cerrar con Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeConfirmModal();
});
</script>
@endpush

