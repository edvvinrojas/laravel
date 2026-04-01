@extends('layouts.app')
@section('title','Configuración de SKU')
@section('page-title','Configuración de SKU')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-5">
    @foreach($formats as $fmt)
    @php
        $colors = [
            'EQUIPO'        => ['bg-blue-50 border-blue-200',   'text-blue-700',  'bg-blue-600'],
            'PRODUCTO'      => ['bg-purple-50 border-purple-200','text-purple-700','bg-purple-600'],
            'ACCESORIO'     => ['bg-cyan-50 border-cyan-200',   'text-cyan-700',  'bg-cyan-600'],
            'CONSUMIBLE'    => ['bg-yellow-50 border-yellow-200','text-yellow-700','bg-yellow-600'],
            'REFACCION'     => ['bg-orange-50 border-orange-200','text-orange-700','bg-orange-600'],
            'TI_EQUIPO'     => ['bg-indigo-50 border-indigo-200','text-indigo-700','bg-indigo-600'],
            'TI_PERIFERICO' => ['bg-pink-50 border-pink-200',   'text-pink-700',  'bg-pink-600'],
            'OTRO'          => ['bg-gray-50 border-gray-200',   'text-gray-700',  'bg-gray-600'],
        ];
        [$cardBg, $textColor, $badgeBg] = $colors[$fmt->category] ?? $colors['OTRO'];
        $catSkus = $skusByCategory[$fmt->category] ?? collect();
    @endphp
    <div class="card border {{ $cardBg }} flex flex-col">
        {{-- Header --}}
        <div class="px-5 py-4 border-b border-inherit flex items-center justify-between">
            <div>
                <h3 class="font-semibold {{ $textColor }}">{{ $fmt->label }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">Prefijo: <span class="font-mono font-semibold">{{ $fmt->prefix }}</span> · {{ $fmt->pad }} dígitos</p>
            </div>
            <span class="{{ $badgeBg }} text-white text-xs font-bold rounded-full h-8 w-8 flex items-center justify-center" title="SKUs creados">{{ $catSkus->count() }}</span>
        </div>

        {{-- SKU List --}}
        <div class="flex-1 overflow-y-auto max-h-52">
            @if($catSkus->isEmpty())
                <div class="px-5 py-6 text-center text-gray-400 text-sm">Sin SKUs</div>
            @else
                <ul class="divide-y divide-gray-100">
                @foreach($catSkus as $sku)
                    <li class="px-5 py-2 flex items-center justify-between hover:bg-white/80 group">
                        <span class="font-mono text-sm font-medium text-gray-700">{{ $sku->code }}</span>
                        <button type="button" class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-red-500"
                                onclick="openDeleteModal({{ $sku->id }}, '{{ $sku->code }}')" title="Eliminar">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </li>
                @endforeach
                </ul>
            @endif
        </div>

        {{-- Crear --}}
        <div class="px-5 py-3 border-t border-inherit bg-white/40">
            <form action="{{ route('sku.store') }}" method="POST">
                @csrf
                <input type="hidden" name="category" value="{{ $fmt->category }}">
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div>
                        <label class="form-label text-xs">Prefijo</label>
                        <input name="prefix" value="{{ $fmt->prefix }}" class="form-input font-mono text-xs">
                    </div>
                    <div>
                        <label class="form-label text-xs">Dígitos</label>
                        <select name="pad" class="form-select text-xs">
                            @for($i = 1; $i <= 6; $i++)
                            <option value="{{ $i }}" @selected($fmt->pad === $i)>{{ $i }} → {{ str_pad('1', $i, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <button class="btn-primary btn-sm w-full">Crear SKU</button>
            </form>
        </div>
    </div>
    @endforeach
</div>

{{-- Modal eliminar --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Eliminar SKU</h3>
                <p class="text-sm text-gray-500">¿Eliminar <span id="deleteSkuCode" class="font-mono font-semibold"></span>?</p>
            </div>
        </div>
        <form id="deleteForm" method="POST" class="flex gap-2">
            @csrf @method('DELETE')
            <button type="button" onclick="closeDeleteModal()" class="btn-secondary flex-1">Cancelar</button>
            <button type="submit" class="btn-danger flex-1">Eliminar</button>
        </form>
    </div>
</div>

<script>
function openDeleteModal(id, code) {
    document.getElementById('deleteSkuCode').textContent = code;
    document.getElementById('deleteForm').action = '/sku/' + id;
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

@endsection
