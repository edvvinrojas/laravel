@extends('layouts.app')
@section('title','Licencias TI')
@section('page-title','Licencias de Software')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('ti-equipment.index') }}" class="btn-secondary">← Equipos TI</a>
    <button onclick="document.getElementById('newLicForm').classList.toggle('hidden')" class="btn-primary">+ Nueva licencia</button>
</div>

<div id="newLicForm" class="hidden card mb-4">
    <div class="card-header font-semibold">Registrar licencia</div>
    <form method="POST" action="{{ route('ti-equipment.licenses.store') }}">
    @csrf
    <div class="card-body grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="form-label">Software *</label>
            <input name="software" type="text" value="{{ old('software') }}" class="form-input" required placeholder="Microsoft Office 365…">
        </div>
        <div>
            <label class="form-label">Tipo *</label>
            <input name="tipo" type="text" value="{{ old('tipo') }}" class="form-input" required placeholder="Ej: OFFICE, ANTIVIRUS, OS…">
        </div>
        <div>
            <label class="form-label">Cantidad de licencias *</label>
            <input name="cantidad_licencias" type="number" min="1" value="{{ old('cantidad_licencias',1) }}" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Clave / Product Key</label>
            <input name="clave_licencia" type="text" value="{{ old('clave_licencia') }}" class="form-input font-mono">
        </div>
        <div>
            <label class="form-label">Proveedor</label>
            <input name="proveedor" type="text" value="{{ old('proveedor') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Fecha de vencimiento</label>
            <input name="fecha_vencimiento" type="date" value="{{ old('fecha_vencimiento') }}" class="form-input">
        </div>
        <div class="col-span-3">
            <label class="form-label">Notas</label>
            <textarea name="notas" class="form-input" rows="2">{{ old('notas') }}</textarea>
        </div>
    </div>
    <div class="px-5 py-3 border-t border-gray-100 flex gap-3">
        <button type="submit" class="btn-primary">Guardar</button>
    </div>
    </form>
</div>

<div class="card">
<table class="w-full text-sm">
    <thead class="table-head">
        <tr>
            <th class="px-4 py-2 text-left">Software</th>
            <th class="px-4 py-2 text-left">Tipo</th>
            <th class="px-4 py-2 text-left">Equipos</th>
            <th class="px-4 py-2 text-left">Cantidad</th>
            <th class="px-4 py-2 text-left">Vencimiento</th>
            <th class="px-4 py-2"></th>
        </tr>
    </thead>
    <tbody>
    @forelse($licenses as $lic)
    @php $lsc=['OFFICE'=>'badge-blue','ANTIVIRUS'=>'badge-green','OS'=>'badge-purple','OTRO'=>'badge-gray']; @endphp
    <tr class="table-row">
        <td class="px-4 py-2 font-medium">{{ $lic->software }}</td>
        <td class="px-4 py-2"><span class="{{ $lsc[$lic->tipo]??'badge-gray' }}">{{ $lic->tipo }}</span></td>
        <td class="px-4 py-2">
            <span class="{{ $lic->equipment_count >= $lic->cantidad_licencias ? 'text-red-600 font-semibold' : '' }}">
                {{ $lic->equipment_count }} / {{ $lic->cantidad_licencias }}
            </span>
        </td>
        <td class="px-4 py-2">{{ $lic->cantidad_licencias }}</td>
        <td class="px-4 py-2">
            @if($lic->fecha_vencimiento)
                @php $expired = $lic->fecha_vencimiento->isPast(); @endphp
                <span class="{{ $expired ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                    {{ $lic->fecha_vencimiento->format('d/m/Y') }}
                    @if($expired) ⚠ Vencida @endif
                </span>
            @else
                <span class="text-gray-400">Sin vencimiento</span>
            @endif
        </td>
        <td class="px-4 py-2 text-right">
            <div class="flex justify-end gap-3">
                <a href="{{ route('ti-equipment.licenses.edit', $lic) }}" class="text-blue-600 text-xs hover:underline">Editar</a>
                <form method="POST" action="{{ route('ti-equipment.licenses.destroy', $lic) }}"
                      onsubmit="return confirm('¿Eliminar esta licencia?')">
                    @csrf @method('DELETE')
                    <button class="text-red-500 text-xs hover:underline">Eliminar</button>
                </form>
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Sin licencias registradas</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-4">{{ $licenses->links() }}</div>
@endsection
