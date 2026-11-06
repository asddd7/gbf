<div class="admin-form-grid">
    <div class="admin-form-group">
        <label for="name">Nama Tahap *</label>
        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $stage->name ?? '') }}"
            placeholder="Contoh: Uncap 1"
            required
        >
        @error('name')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="admin-form-footer">
    <a href="{{ route('admin.stages.index') }}" class="admin-button admin-button-secondary">
        Batal
    </a>

    <button type="submit" class="admin-button admin-button-primary">
        {{ isset($stage) ? 'Simpan Perubahan' : 'Tambah Tahap' }}
    </button>
</div>