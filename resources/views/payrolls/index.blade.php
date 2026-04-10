@extends('layouts.app')
@section('title','Nóminas')
@section('page-title','Nóminas')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="flex gap-2">
            <input name="search" value="{{ request('search') }}" class="form-input w-48" placeholder="Empleado…">
            <select name="status" class="form-select w-36">
                <option value="">Estado</option>
                @foreach(['PENDIENTE','APROBADO','RECHAZADO','PAGADO'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn-secondary">Buscar</button>
        </form>
        <div class="flex gap-2"><a href="{{ route('rh.index') }}" class="btn-secondary">&larr; RH</a><a href="{{ route('payrolls.create') }}" class="btn-primary">+ Nueva nómina</a></div>
    </div>
    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead><tr><th>Empleado</th><th>Fecha pago</th><th>Salario</th><th>Bono</th><th>Comisión</th><th>Desc. crédito</th><th>Total</th><th>Neto</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($payrolls as $p)
            @php $sc=['PENDIENTE'=>'badge-yellow','APROBADO'=>'badge-blue','RECHAZADO'=>'badge-red','ACTIVO'=>'badge-purple','PAGADO'=>'badge-green']; @endphp
            <tr>
                <td class="font-medium">{{ $p->employee->nombre }}</td>
                <td>{{ $p->pay_day->format('d/m/Y') }}</td>
                <td>${{ number_format($p->salary,2) }}</td>
                <td>${{ number_format($p->bonus,2) }}</td>
                <td>${{ number_format($p->commission,2) }}</td>
                <td class="text-red-600">-${{ number_format($p->credit_discount ?? 0,2) }}</td>
                <td class="font-bold">${{ number_format($p->total_pay,2) }}</td>
                <td class="font-bold text-green-700">${{ number_format($p->net_pay ?? $p->total_pay,2) }}</td>
                <td><span class="{{ $sc[$p->status]??'badge-gray' }}">{{ $p->status }}</span></td>
                <td class="flex gap-1">
                    <a href="{{ route('payrolls.show',$p) }}" class="btn btn-sm btn-secondary">Ver</a>
                    <a href="{{ route('payrolls.edit',$p) }}" class="btn btn-sm btn-primary">Editar</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center py-8 text-gray-400">Sin registros</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $payrolls->links() }}</div>
</div>
@endsection
