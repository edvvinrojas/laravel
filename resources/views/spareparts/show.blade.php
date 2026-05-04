@extends('layouts.app')
@section('title','Refacción')
@section('page-title','Detalle de Refacción')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('spareparts.edit',$sparepart) }}" class="btn-primary">Editar</a>
    <a href="{{ route('spareparts.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="card max-w-xl">
    <div class="card-header"><h3 class="font-semibold">{{ $sparepart->name }}</h3></div>
    <div class="card-body grid grid-cols-2 gap-3 text-sm">
        <div><p class="text-gray-500">Código</p><p class="font-mono">{{ $sparepart->code ?? '—' }}</p></div>
        <div><p class="text-gray-500">Marca</p><p>{{ $sparepart->brand_name ?? '—' }}</p></div>
        <div><p class="text-gray-500">Color</p><p>{{ $sparepart->color ?? '—' }}</p></div>
        <div><p class="text-gray-500">Proveedor</p><p>{{ $sparepart->supplier_name ?? '—' }}</p></div>
        <div><p class="text-gray-500">Creado</p><p>{{ $sparepart->created_at?->format('d/m/Y') ?? '—' }}</p></div>
        <div class="col-span-2"><p class="text-gray-500">Equipo compatible</p><p>{{ $sparepart->equipment ?? '—' }}</p></div>
        <div><p class="text-gray-500">Precio unitario</p><p>{{ $sparepart->unit_price !== null ? '$'.number_format($sparepart->unit_price,2) : '—' }}</p></div>
        <div><p class="text-gray-500">Precio total</p><p>{{ $sparepart->total_price !== null ? '$'.number_format($sparepart->total_price,2) : '—' }}</p></div>
        <div class="col-span-2"><p class="text-gray-500">No. de factura</p><p class="font-mono">{{ $sparepart->invoice_number ?? '—' }}</p></div>
        @if($sparepart->description)
        <div class="col-span-2"><p class="text-gray-500">Descripción</p><p>{{ $sparepart->description }}</p></div>
        @endif
    </div>
</div>
@endsection
