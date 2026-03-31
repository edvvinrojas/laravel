@extends('layouts.app')
@section('title','Equipo TI')
@section('page-title','Detalle de Equipo TI')

@section('content')
<div class="flex gap-3 mb-4 flex-wrap">
    <a href="{{ route('ti-equipment.edit', $tiEquipment) }}" class="btn-secondary">Editar</a>
    <a href="{{ route('ti-equipment.index') }}" class="btn-secondary">← Volver</a>
    <form action="{{ route('ti-equipment.destroy', $tiEquipment) }}" method="POST"
          onsubmit="return confirm('¿Eliminar este equipo?')" class="ml-auto">
        @csrf @method('DELETE')
        <button class="btn-danger">Eliminar</button>
    </form>
</div>

@php $sc=['ACTIVO'=>'badge-green','BAJA'=>'badge-red','REPARACION'=>'badge-yellow','BODEGA'=>'badge-gray']; @endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

{{-- Info principal --}}
<div class="card">
    <div class="card-header flex items-center justify-between">
        <h3 class="font-semibold">{{ $tiEquipment->codigo_interno }} — {{ $tiEquipment->marca }} {{ $tiEquipment->modelo }}</h3>
        <span class="{{ $sc[$tiEquipment->status]??'badge-gray' }}">{{ $tiEquipment->status }}</span>
    </div>
    <div class="card-body text-sm space-y-2">
        <div class="flex justify-between"><span class="text-gray-500">Tipo</span><span>{{ $tiEquipment->tipo }}</span></div>
        @if($tiEquipment->numero_serie)
        <div class="flex justify-between"><span class="text-gray-500">No. serie</span><span class="font-mono">{{ $tiEquipment->numero_serie }}</span></div>
        @endif
        @if($tiEquipment->procesador)
        <div class="flex justify-between"><span class="text-gray-500">Procesador</span><span>{{ $tiEquipment->procesador }}</span></div>
        @endif
        @if($tiEquipment->ram)
        <div class="flex justify-between"><span class="text-gray-500">RAM</span><span>{{ $tiEquipment->ram }}</span></div>
        @endif
        @if($tiEquipment->almacenamiento)
        <div class="flex justify-between"><span class="text-gray-500">Almacenamiento</span><span>{{ $tiEquipment->almacenamiento }}</span></div>
        @endif
        @if($tiEquipment->sistema_operativo)
        <div class="flex justify-between"><span class="text-gray-500">S.O.</span><span>{{ $tiEquipment->sistema_operativo }}</span></div>
        @endif
        <div class="flex justify-between"><span class="text-gray-500">Asignado a</span>
            <span>{{ $tiEquipment->assignedUser?->name ?? '—' }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Ubicación</span>
            <span>{{ $tiEquipment->ubicacion ?? '—' }}</span></div>
        @if($tiEquipment->fecha_compra)
        <div class="flex justify-between"><span class="text-gray-500">Fecha de compra</span>
            <span>{{ $tiEquipment->fecha_compra->format('d/m/Y') }}</span></div>
        @endif
        @if($tiEquipment->notas)
        <div><p class="text-gray-500">Notas</p><p>{{ $tiEquipment->notas }}</p></div>
        @endif
    </div>
</div>

{{-- Licencias --}}
<div class="card">
    <div class="card-header font-semibold">Licencias</div>
    <div class="card-body text-sm">
        @forelse($tiEquipment->licenses as $lic)
        @php $lsc=['OFFICE'=>'badge-blue','ANTIVIRUS'=>'badge-green','OS'=>'badge-purple','OTRO'=>'badge-gray']; @endphp
        <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
            <div>
                <span class="font-medium">{{ $lic->software }}</span>
                <span class="{{ $lsc[$lic->tipo]??'badge-gray' }} ml-2">{{ $lic->tipo }}</span>
            </div>
            @if($lic->fecha_vencimiento)
            <span class="text-xs text-gray-500">Vence: {{ $lic->fecha_vencimiento->format('d/m/Y') }}</span>
            @endif
        </div>
        @empty
        <p class="text-gray-400">Sin licencias</p>
        @endforelse
    </div>
</div>

</div>

{{-- Periféricos --}}
<div class="card mt-6">
    <div class="card-header font-semibold flex items-center justify-between">
        Periféricos
        <button onclick="document.getElementById('addPeriForm').classList.toggle('hidden')" class="text-blue-600 text-sm">+ Agregar</button>
    </div>

    <div id="addPeriForm" class="hidden px-5 py-4 border-b border-gray-100 bg-gray-50">
        <form method="POST" action="{{ route('ti-equipment.peripherals.store', $tiEquipment) }}">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="form-label text-xs">Tipo *</label>
                    <select name="tipo" class="form-select text-sm" required>
                        @foreach(['MONITOR','TECLADO','MOUSE','CARGADOR','DOCKING','HEADSET','CAMARA','OTRO'] as $pt)
                        <option>{{ $pt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs">Marca</label>
                    <input name="marca" class="form-input text-sm" type="text">
                </div>
                <div>
                    <label class="form-label text-xs">Modelo</label>
                    <input name="modelo" class="form-input text-sm" type="text">
                </div>
                <div>
                    <label class="form-label text-xs">No. serie</label>
                    <input name="numero_serie" class="form-input text-sm" type="text">
                </div>
                <div>
                    <button type="submit" class="btn-primary text-sm w-full">Guardar</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body text-sm">
        @forelse($tiEquipment->peripherals as $p)
        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
            <div>
                <span class="font-medium">{{ $p->tipo }}</span>
                @if($p->marca) <span class="text-gray-500">{{ $p->marca }}</span> @endif
                @if($p->modelo) <span class="text-gray-600">{{ $p->modelo }}</span> @endif
                @if($p->numero_serie) <span class="font-mono text-xs text-gray-400 ml-2">{{ $p->numero_serie }}</span> @endif
            </div>
            <form method="POST" action="{{ route('ti-equipment.peripherals.destroy', [$tiEquipment, $p]) }}"
                  onsubmit="return confirm('¿Eliminar periférico?')">
                @csrf @method('DELETE')
                <button class="text-red-500 text-xs hover:underline">Eliminar</button>
            </form>
        </div>
        @empty
        <p class="text-gray-400">Sin periféricos registrados.</p>
        @endforelse
    </div>
</div>
@endsection
