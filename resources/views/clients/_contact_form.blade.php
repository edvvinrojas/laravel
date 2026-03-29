{{-- Partial: formulario inline para agregar un contacto nuevo --}}
<div class="border border-dashed border-gray-300 rounded-xl p-4 bg-gray-50">
    <button type="button" onclick="toggleNewContact()"
        class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1 mb-2">
        <span id="toggle-icon">＋</span> Agregar contacto
    </button>
    <div id="new-contact-fields" class="{{ old('new_contact_name') ? '' : 'hidden' }} grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
        <div class="sm:col-span-2">
            <label class="form-label">Nombre *</label>
            <input name="new_contact_name" value="{{ old('new_contact_name') }}" class="form-input" placeholder="Nombre completo">
        </div>
        <div>
            <label class="form-label">Teléfono</label>
            <input name="new_contact_phone" value="{{ old('new_contact_phone') }}" class="form-input" placeholder="55 1234 5678">
        </div>
        <div>
            <label class="form-label">Email</label>
            <input name="new_contact_email" type="email" value="{{ old('new_contact_email') }}" class="form-input" placeholder="correo@empresa.com">
        </div>
        <div class="sm:col-span-2">
            <label class="form-label">Cargo / Rol</label>
            <input name="new_contact_rol" value="{{ old('new_contact_rol') }}" class="form-input" placeholder="Gerente, Compras…">
        </div>
        <p class="sm:col-span-2 text-xs text-gray-400">Al guardar, se creará el contacto y quedará ligado a este cliente.</p>
    </div>
</div>
<script>
function toggleNewContact() {
    const f = document.getElementById('new-contact-fields');
    const i = document.getElementById('toggle-icon');
    const hidden = f.classList.toggle('hidden');
    i.textContent = hidden ? '＋' : '－';
}
</script>
