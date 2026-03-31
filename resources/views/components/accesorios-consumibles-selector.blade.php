{{--
  Selector dinámico de accesorios y consumibles.
  Variables que se esperan del contexto:
    $itemSelectId  — id del <select> de equipo (default: "item_id_select")
    $selectedAccesorios — colección ya seleccionada (para edit), default: collect()
    $selectedConsumibles — colección ya seleccionada (para edit), default: collect()
--}}
@php
    $itemSelectId        = $itemSelectId       ?? 'item_id_select';
    $selectedAccesorios  = $selectedAccesorios ?? collect();
    $selectedConsumibles = $selectedConsumibles ?? collect();
    $selAccIds  = $selectedAccesorios->pluck('id')->toArray();
    $selConIds  = $selectedConsumibles->pluck('id')->toArray();
@endphp

<div id="accesorios-consumibles-panel" class="{{ (count($selAccIds) || count($selConIds)) ? '' : 'hidden' }} mt-4">
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-gray-700">Accesorios y consumibles incluidos</h3>
            <span id="acp-producto-nombre" class="text-xs text-gray-400"></span>
        </div>
        <div class="card-body space-y-5">

            {{-- Accesorios --}}
            <div id="acp-accesorios-wrap">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Accesorios</p>
                <div id="acp-accesorios-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    {{-- Pre-render selected (for edit) --}}
                    @foreach($selectedAccesorios as $acc)
                    <label class="flex items-center gap-2 text-sm cursor-pointer border border-gray-200 rounded px-3 py-2 hover:bg-gray-50">
                        <input type="checkbox" name="accesorios[]" value="{{ $acc->id }}" checked
                               class="w-4 h-4 rounded text-blue-600">
                        <span>
                            <span class="font-medium">{{ $acc->nombre }}</span>
                            @if($acc->codigo)<span class="text-gray-400 text-xs ml-1">{{ $acc->codigo }}</span>@endif
                        </span>
                    </label>
                    @endforeach
                </div>
                <p id="acp-no-accesorios" class="text-xs text-gray-400 {{ count($selAccIds) ? 'hidden' : '' }}">Sin accesorios registrados para este producto.</p>
            </div>

            {{-- Consumibles / Tóner --}}
            <div id="acp-consumibles-wrap">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Consumibles / Tóner</p>
                <div id="acp-consumibles-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($selectedConsumibles as $con)
                    <label class="flex items-center gap-2 text-sm cursor-pointer border border-gray-200 rounded px-3 py-2 hover:bg-gray-50">
                        <input type="checkbox" name="consumibles[]" value="{{ $con->id }}" checked
                               class="w-4 h-4 rounded text-blue-600">
                        <span>
                            <span class="font-medium">{{ $con->nombre }}</span>
                            <span class="text-gray-400 text-xs ml-1">
                                {{ $con->tipo }}{{ $con->color ? ' · '.$con->color : '' }}
                            </span>
                        </span>
                    </label>
                    @endforeach
                </div>
                <p id="acp-no-consumibles" class="text-xs text-gray-400 {{ count($selConIds) ? 'hidden' : '' }}">Sin consumibles registrados para este producto.</p>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const itemSelect = document.getElementById('{{ $itemSelectId }}');
    if (!itemSelect) return;

    // IDs preseleccionados (para edit)
    const preAccesorios  = @json($selAccIds);
    const preConsumibles = @json($selConIds);

    function renderCheckboxes(containerId, noMsgId, items, nameAttr, preSelected, labelFn, badgeFn) {
        const list  = document.getElementById(containerId);
        const noMsg = document.getElementById(noMsgId);
        list.innerHTML = '';

        if (!items || items.length === 0) {
            noMsg.classList.remove('hidden');
            return;
        }
        noMsg.classList.add('hidden');

        items.forEach(item => {
            const checked = preSelected.length ? preSelected.includes(item.id) : item.es_incluido ?? item.es_oficial ?? false;
            const label = document.createElement('label');
            label.className = 'flex items-center gap-2 text-sm cursor-pointer border border-gray-200 rounded px-3 py-2 hover:bg-gray-50';
            label.innerHTML = `
                <input type="checkbox" name="${nameAttr}" value="${item.id}" ${checked ? 'checked' : ''}
                       class="w-4 h-4 rounded text-blue-600">
                <span>
                    <span class="font-medium">${labelFn(item)}</span>
                    <span class="text-gray-400 text-xs ml-1">${badgeFn(item)}</span>
                </span>`;
            list.appendChild(label);
        });
    }

    function loadProductoDetalle(itemId) {
        const panel = document.getElementById('accesorios-consumibles-panel');
        if (!itemId) { panel.classList.add('hidden'); return; }

        fetch(`/equipment/${itemId}/producto-detalle`)
            .then(r => r.json())
            .then(data => {
                if (!data.producto) { panel.classList.add('hidden'); return; }

                document.getElementById('acp-producto-nombre').textContent = data.producto.nombre;
                panel.classList.remove('hidden');

                renderCheckboxes(
                    'acp-accesorios-list', 'acp-no-accesorios',
                    data.accesorios, 'accesorios[]', [],
                    a => a.nombre,
                    a => a.codigo ?? ''
                );

                renderCheckboxes(
                    'acp-consumibles-list', 'acp-no-consumibles',
                    data.consumibles, 'consumibles[]', [],
                    c => c.nombre,
                    c => [c.tipo, c.color].filter(Boolean).join(' · ')
                );
            });
    }

    itemSelect.addEventListener('change', function () {
        loadProductoDetalle(this.value);
    });

    // Si hay un equipo pre-seleccionado pero sin datos pre-renderizados, no recargar
    // (el edit ya tiene los datos del servidor, solo cargamos si cambia)
}());
</script>
@endpush
