@extends('layouts.app')
@section('title','Nómina')
@section('page-title','Detalle de Nómina')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('payrolls.edit',$payroll) }}" class="btn-primary">Editar</a>
    <a href="{{ route('payrolls.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="card max-w-md">
    <div class="card-header"><h3 class="font-semibold">{{ $payroll->employee->nombre }}</h3>
        @php $sc=['PENDIENTE'=>'badge-yellow','APROBADO'=>'badge-blue','RECHAZADO'=>'badge-red','ACTIVO'=>'badge-purple','PAGADO'=>'badge-green']; @endphp
        <span class="{{ $sc[$payroll->status]??'badge-gray' }}">{{ $payroll->status }}</span>
    </div>
    <div class="card-body text-sm space-y-3">
        <div class="flex justify-between"><span class="text-gray-500">Fecha pago</span><span>{{ $payroll->pay_day->format('d/m/Y') }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Salario base</span><span>${{ number_format($payroll->salary,2) }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Bono</span><span>${{ number_format($payroll->bonus,2) }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Comisión</span><span>${{ number_format($payroll->commission,2) }}</span></div>
        <div class="flex justify-between border-t pt-2 font-bold text-base"><span>Total</span><span>${{ number_format($payroll->total_pay,2) }}</span></div>
    </div>
</div>
@endsection
