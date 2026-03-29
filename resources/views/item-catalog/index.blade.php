@extends('layouts.app')
@section('title','Catálogo de Artículos')
@section('page-title','Catálogo de Artículos')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('almacen.index', ['tab' => 'inventario']) }}" class="btn-secondary btn-sm">← Almacén</a>
        <a href="{{ route('item-catalog.create') }}" class="btn-primary">+ Nuevo artículo</a>
    </div>
    <div class="flex gap-2">
        <form method="GET" action="{{ route('item-catalog.index') }}" class="flex gap-2 flex-1 max-w-xl">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o tipo…" class="form-input flex-1">
            <select name="type" class="form-select w-36">
                <option value="">Todos</option>
                <option value="TONER" @selected(request('type')=='TONER')>Tóner</option>
                <option value="REFACCION" @selected(request('type')=='REFACCION')>Refacción</option>
            </select>
            <button type="submit" class="btn-secondary btn-sm">Filtrar</button>
            @if(request()->anyFilled(['search','type']))<a href="{{ route('item-catalog.index') }}" class="btn-secondary btn-sm">Limpiar</a>@endif
        </form>
    </div>
    <div class="card">
        <div class="card-header"><span class="text-sm text-gray-500">{{ $query->total() }} artículo(s)</span></div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr><th>Nombre</th><th>Tipo</th><th>Marca</th><th>Color</th><th>En inventario</th><th>Activo</th><th class="text-right">Acciones</th></tr>
                    </thead>
                    <tbody>
                        @forelse($query as $item)
                        <tr>
                            <td class="font-medium text-gray-900">{{ $item->item_name }}</td>
                            <td>
                                @if($item->item_type === 'TONER')
                                <span class="badge-blue">Tóner</span>
                                @else
                                <span class="badge-yellow">Refacción</span>
                                @endif
                            </td>
                            <td class="text-gray-600">{{ $item->brand?->name ?? '—' }}</td>
                            <td>
                                @php $colors = ['K'=>'Negro','C'=>'Cyan','M'=>'Magenta','Y'=>'Amarillo']; @endphp
                                {{ $item->color ? $colors[$item->color].' ('.$item->color.')' : '—' }}
                            </td>
                            <td class="text-gray-500">{{ $item->inventory_items_count }}</td>
                            <td>
                                @if($item->is_active)<span class="badge-green">Sí</span>@else<span class="badge-gray">No</span>@endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('item-catalog.edit', $item) }}" class="btn-secondary btn-sm">Editar</a>
                                    <form action="{{ route('item-catalog.destroy', $item) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar «{{ addslashes($item->item_name) }}»?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-gray-400 py-10">No hay artículos en el catálogo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($query->hasPages())<div class="flex justify-end">{{ $query->links() }}</div>@endif
</div>
@endsection
