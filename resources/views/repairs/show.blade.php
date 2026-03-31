@extends('layouts.app')
@section('title','Reparación')
@section('page-title','Detalle de Reparación')

@section('content')
<div class="flex gap-3 mb-4 flex-wrap">
    <a href="{{ route('repairs.edit',$repair) }}" class="btn-primary">Editar</a>
    <a href="{{ route('repairs.index') }}" class="btn-secondary">← Volver</a>
    @if($repair->estado_taller !== 'LISTO')
    <form action="{{ route('repairs.listo', $repair) }}" method="POST"
          onsubmit="return confirm('¿Marcar como listo y pasar a bodega?')">
        @csrf @method('PATCH')
        <button class="btn-success">Marcar Listo → Bodega</button>
    </form>
    @endif
</div>
<div class="card max-w-2xl">
    <div class="card-header">
        <div>
            <h3 class="font-semibold">{{ $repair->item->brand->name ?? '' }} {{ $repair->item->model }}</h3>
            <p class="text-xs text-gray-500 font-mono">{{ $repair->item->serie }}</p>
        </div>
        @php $sc=['PENDIENTE'=>'badge-yellow','PAUSADO'=>'badge-gray','LISTO'=>'badge-green']; @endphp
        <span class="{{ $sc[$repair->estado_taller]??'badge-gray' }}">{{ $repair->estado_taller }}</span>
    </div>
    <div class="card-body grid grid-cols-2 gap-4 text-sm">
        <div><p class="text-gray-500">Procedencia</p><p>{{ $repair->procedencia }}</p></div>
        <div><p class="text-gray-500">Ubicación</p><p>{{ $repair->ubicacion ?? '—' }}</p></div>
        <div><p class="text-gray-500">Proceso</p><p>{{ str_replace('_',' ',$repair->proceso) }}</p></div>
        <div><p class="text-gray-500">Estatus</p><p>{{ str_replace('_',' ',$repair->estatus) }}</p></div>
        <div><p class="text-gray-500">Fecha alta</p><p>{{ $repair->fecha_alta->format('d/m/Y') }}</p></div>
        <div><p class="text-gray-500">Fecha conclusión</p><p>{{ $repair->fecha_conclusion?->format('d/m/Y') ?? '—' }}</p></div>
        @if($repair->diagnostico_inicial)
        <div class="col-span-2"><p class="text-gray-500">Diagnóstico</p><p>{{ $repair->diagnostico_inicial }}</p></div>
        @endif
        @if($repair->comments)
        <div class="col-span-2"><p class="text-gray-500">Comentarios</p><p>{{ $repair->comments }}</p></div>
        @endif
    </div>
</div>
@endsection
