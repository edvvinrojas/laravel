@extends('layouts.app')
@section('title','Configuración de SKU')
@section('page-title','Configuración de SKU')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-gray-700">Formato por categoría</h3>
        <p class="text-xs text-gray-400">Define el prefijo y dígitos de cada categoría. El SKU se genera automáticamente al crear registros.</p>
    </div>

    <div class="table-wrap rounded-none border-0">
        <table class="table">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th class="w-36">Prefijo</th>
                    <th class="w-28">Dígitos</th>
                    <th class="w-28">Último #</th>
                    <th class="w-44">Siguiente SKU</th>
                    <th class="w-40"></th>
                </tr>
            </thead>
            <tbody>
            @foreach($formats as $fmt)
            <tr>
                <td class="font-medium">{{ $fmt->label }}</td>
                <td>
                    <form action="{{ route('sku.update', $fmt) }}" method="POST" class="flex gap-1" id="form-{{ $fmt->id }}">
                        @csrf @method('PUT')
                        <input name="prefix" value="{{ $fmt->prefix }}" class="form-input font-mono text-sm w-full" required>
                </td>
                <td>
                        <select name="pad" class="form-select text-sm w-full">
                            @for($i = 1; $i <= 6; $i++)
                            <option value="{{ $i }}" @selected($fmt->pad === $i)>{{ $i }} ({{ str_pad('1', $i, '0', STR_PAD_LEFT) }})</option>
                            @endfor
                        </select>
                    </form>
                </td>
                <td class="text-center text-gray-500">{{ $fmt->last_number }}</td>
                <td>
                    <span class="font-mono font-semibold text-indigo-600">{{ $fmt->preview() }}</span>
                </td>
                <td class="flex gap-1">
                    <button type="submit" form="form-{{ $fmt->id }}" class="btn-primary btn-sm">Guardar</button>
                    @if($fmt->last_number > 0)
                    <form action="{{ route('sku.reset', $fmt) }}" method="POST"
                          onsubmit="return confirm('¿Reiniciar contador de {{ $fmt->label }} a 0?')">
                        @csrf
                        <button class="btn-secondary btn-sm" title="Reiniciar contador">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
    <strong>¿Cómo funciona?</strong> — Cuando creas un equipo, producto, accesorio, etc., el sistema toma el prefijo + último número + 1 y genera el SKU automáticamente. Por ejemplo, si Equipos tiene prefijo <code class="font-mono bg-blue-100 px-1 rounded">EQ-</code> y el último número es 5, el siguiente equipo recibirá <code class="font-mono bg-blue-100 px-1 rounded">EQ-006</code>.
</div>

@endsection
