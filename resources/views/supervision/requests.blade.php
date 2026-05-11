@extends('layouts.app')
@section('title', 'Supervision de Peticiones')
@section('page-title', 'Supervision de Peticiones')

@section('content')
<div class="space-y-6">
    <div class="card">
        <div class="card-body flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Bandeja de jefes y administracion</h2>
                <p class="text-sm text-gray-500">Atiende solicitudes pendientes de vacaciones, ausentismo, tickets y cotizaciones desde un solo lugar.</p>
            </div>
            <form method="GET" class="flex w-full gap-2 md:w-auto">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar empleado, cliente o folio" class="form-input md:w-80">
                <button class="btn-primary" type="submit">Filtrar</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Vacaciones</p>
            <p class="mt-2 text-2xl font-bold text-blue-600">{{ $stats['vacations_pending'] }}</p>
            <p class="text-sm text-gray-500">Pendientes de aprobacion</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Ausentismo</p>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ $stats['absences_pending'] }}</p>
            <p class="text-sm text-gray-500">Pendientes de aprobacion</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tickets</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['tickets_open'] }}</p>
            <p class="text-sm text-gray-500">Pendientes de atencion</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cotizaciones</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $stats['quotes_pending'] }}</p>
            <p class="text-sm text-gray-500">Pendientes de revision</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-gray-700">Empleados a tu cargo</h3>
            <span class="badge badge-blue">{{ $managedEmployees->count() }}</span>
        </div>
        <div class="card-body">
            <p class="mb-3 text-sm text-gray-500">Solo puedes atender solicitudes y tickets de los empleados listados aqui.</p>
            @if($managedEmployees->isEmpty())
                <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">No tienes empleados asignados actualmente. Contacta a RH para asignaciones.</p>
            @else
                <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($managedEmployees as $employee)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                            <p class="text-sm font-semibold text-gray-800">{{ $employee->nombre }}</p>
                            <p class="text-xs text-gray-500">{{ $employee->puesto ?: 'Sin puesto' }} · {{ strtoupper($employee->departamento ?: 'SIN DEPTO') }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-gray-700">Solicitudes de vacaciones por autorizar</h3>
            <a href="{{ route('vacations.index') }}" class="btn-secondary btn-sm">Ver modulo completo</a>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap rounded-none border-0 border-t border-gray-100">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Fechas</th>
                            <th>Dias</th>
                            <th>Estatus</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vacations as $vacation)
                            <tr>
                                <td>
                                    <p class="font-medium">{{ $vacation->employee?->nombre ?? 'Sin empleado' }}</p>
                                    <p class="text-xs text-gray-500">{{ strtoupper($vacation->employee?->user?->department ?? 'SIN DEPTO') }}</p>
                                </td>
                                <td>{{ optional($vacation->start_date)->format('d/m/Y') }} - {{ optional($vacation->end_date)->format('d/m/Y') }}</td>
                                <td>{{ $vacation->vacation_days }}</td>
                                <td><span class="badge badge-yellow">{{ $vacation->status }}</span></td>
                                <td class="text-right">
                                    <div class="inline-flex gap-1">
                                        <form method="POST" action="{{ route('vacations.approve', $vacation) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn-success btn-sm" type="submit">Aprobar</button>
                                        </form>
                                        <form method="POST" action="{{ route('vacations.reject', $vacation) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn-danger btn-sm" type="submit">Rechazar</button>
                                        </form>
                                        <a href="{{ route('vacations.show', $vacation) }}" class="btn-secondary btn-sm">Ver</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-sm text-gray-500">No hay solicitudes de vacaciones pendientes para tu supervision.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-gray-700">Solicitudes de ausentismo por autorizar</h3>
            <a href="{{ route('absences.index') }}" class="btn-secondary btn-sm">Ver modulo completo</a>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap rounded-none border-0 border-t border-gray-100">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Tipo</th>
                            <th>Fechas</th>
                            <th>Estatus</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absences as $absence)
                            <tr>
                                <td>
                                    <p class="font-medium">{{ $absence->employee?->nombre ?? 'Sin empleado' }}</p>
                                    <p class="text-xs text-gray-500">{{ strtoupper($absence->employee?->user?->department ?? 'SIN DEPTO') }}</p>
                                </td>
                                <td>{{ str_replace('_', ' ', $absence->absence_type) }}</td>
                                <td>{{ optional($absence->start_date)->format('d/m/Y') }} - {{ optional($absence->end_date)->format('d/m/Y') }}</td>
                                <td><span class="badge badge-yellow">{{ $absence->status }}</span></td>
                                <td class="text-right">
                                    <div class="inline-flex gap-1">
                                        <form method="POST" action="{{ route('absences.approve', $absence) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn-success btn-sm" type="submit">Aprobar</button>
                                        </form>
                                        <form method="POST" action="{{ route('absences.reject', $absence) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn-danger btn-sm" type="submit">Rechazar</button>
                                        </form>
                                        <a href="{{ route('absences.show', $absence) }}" class="btn-secondary btn-sm">Ver</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-sm text-gray-500">No hay solicitudes de ausentismo pendientes para tu supervision.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-gray-700">Tickets pendientes de atencion</h3>
            <a href="{{ route('tickets.index') }}" class="btn-secondary btn-sm">Ir a tickets</a>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap rounded-none border-0 border-t border-gray-100">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Prioridad</th>
                            <th>Estatus</th>
                            <th>Creado por</th>
                            <th class="text-right">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td class="font-mono text-xs">{{ $ticket->ticket_code }}</td>
                                <td>{{ $ticket->client?->name ?? 'Sin cliente' }}</td>
                                <td>
                                    <span class="badge {{ $ticket->priority === 'URGENTE' ? 'badge-red' : ($ticket->priority === 'NORMAL' ? 'badge-yellow' : 'badge-gray') }}">
                                        {{ $ticket->priority }}
                                    </span>
                                </td>
                                <td><span class="badge badge-blue">{{ $ticket->report_status }}</span></td>
                                <td>{{ $ticket->creator?->full_name ?? 'Sistema' }}</td>
                                <td class="text-right">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="btn-primary btn-sm">Atender</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-gray-500">No hay tickets pendientes para supervision.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-gray-700">Cotizaciones por autorizar</h3>
            <a href="{{ route('quotes.index') }}" class="btn-secondary btn-sm">Ir a cotizaciones</a>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap rounded-none border-0 border-t border-gray-100">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Numero</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estatus</th>
                            <th>Creado por</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotes as $quote)
                            <tr>
                                <td class="font-mono text-xs">{{ $quote->quote_number }}</td>
                                <td>{{ $quote->client?->name ?? 'Sin cliente' }}</td>
                                <td class="font-semibold">${{ number_format((float) $quote->total, 2) }}</td>
                                <td>
                                    <span class="badge {{ $quote->status === 'ENVIADA' ? 'badge-yellow' : 'badge-gray' }}">{{ $quote->status }}</span>
                                </td>
                                <td>{{ $quote->creator?->full_name ?? 'Sistema' }}</td>
                                <td class="text-right">
                                    <div class="inline-flex gap-1">
                                        <form method="POST" action="{{ route('quotes.approve', $quote) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn-success btn-sm" type="submit">Aprobar</button>
                                        </form>
                                        <form method="POST" action="{{ route('quotes.reject', $quote) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn-danger btn-sm" type="submit">Rechazar</button>
                                        </form>
                                        <a href="{{ route('quotes.show', $quote) }}" class="btn-secondary btn-sm">Ver</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-gray-500">No hay cotizaciones pendientes para supervision.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
