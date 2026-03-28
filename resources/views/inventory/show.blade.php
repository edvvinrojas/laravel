@extends('layouts.app')
@section('title','Artículo')
@section('page-title','Detalle de Artículo')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('inventory.edit',$inventory) }}" class="btn-primary">Editar</a>
    <a href="{{ route('inventory.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="card max-w-2xl mb-6">
    <div class="card-header">
        <h3 class="font-semibold">{{ $inventory->catalog?->item_name ?? $inventory->item_code }}</h3>
        @if($inventory->is_available)
        <span class="badge-green">Disponible</span>
        @else
        <span class="badge-gray">No disponible</span>
        @endif
    </div>
    <div class="card-body grid grid-cols-2 gap-4 text-sm">
        <div><p class="text-gray-500">Código</p><p class="font-mono">{{ $inventory->item_code }}</p></div>
        <div><p class="text-gray-500">Tipo</p><p>{{ $inventory->catalog?->item_type ?? '—' }}</p></div>
        <div><p class="text-gray-500">Repisa</p><p>{{ $inventory->shelf?->name ?? '—' }}</p></div>
        <div><p class="text-gray-500">Sección</p><p>{{ $inventory->section ?? '—' }}</p></div>
        <div><p class="text-gray-500">Calidad</p><p>{{ $inventory->quality ?? '—' }}</p></div>
        <div><p class="text-gray-500">Proveedor</p><p>{{ $inventory->supplier?->name ?? '—' }}</p></div>
        <div><p class="text-gray-500">Factura</p><p>{{ $inventory->invoice ?? '—' }}</p></div>
        <div><p class="text-gray-500">Fecha entrada</p><p>{{ $inventory->entry_date?->format('d/m/Y') ?? '—' }}</p></div>
        <div><p class="text-gray-500">Costo</p><p>${{ $inventory->cost ? number_format($inventory->cost,2) : '—' }}</p></div>
        <div><p class="text-gray-500">Activo</p>
            @if($inventory->is_active)<span class="badge-green">Sí</span>@else<span class="badge-gray">No</span>@endif
        </div>
        @if($inventory->comments)
        <div class="col-span-2"><p class="text-gray-500">Comentarios</p><p>{{ $inventory->comments }}</p></div>
        @endif
    </div>
</div>

{{-- Equipos relacionados --}}
@if($inventory->items->count())
<div class="card max-w-2xl">
    <div class="card-header"><h3 class="font-semibold text-sm">Equipos asociados ({{ $inventory->items->count() }})</h3></div>
    <div class="card-body p-0">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Modelo</th><th>Serie</th><th>Estado</th></tr></thead>
                <tbody>
                @foreach($inventory->items as $equip)
                <tr>
                    <td><a href="{{ route('equipment.show',$equip) }}" class="text-blue-600 hover:underline">{{ $equip->model }}</a></td>
                    <td class="font-mono text-sm">{{ $equip->serie }}</td>
                    <td><span class="badge-gray text-xs">{{ $equip->location_status ?? '—' }}</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
