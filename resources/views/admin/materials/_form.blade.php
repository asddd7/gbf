<div class="admin-form-grid">
    <div class="admin-form-group">
        <label for="name">Nama Material *</label>
        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $material->name ?? '') }}"
            placeholder="Contoh: Rusted Weapons"
            required
        >
        @error('name') <small class="field-error">{{ $message }}</small> @enderror
    </div>

    <div class="admin-form-group">
        <label for="slug">Slug</label>
        <input
            type="text"
            id="slug"
            name="slug"
            value="{{ old('slug', $material->slug ?? '') }}"
            placeholder="Kosongkan untuk dibuat otomatis"
        >
        @error('slug') <small class="field-error">{{ $message }}</small> @enderror
    </div>

    <div class="admin-form-group">
        <label for="category">Kategori</label>
        <input
            type="text"
            id="category"
            name="category"
            value="{{ old('category', $material->category ?? '') }}"
            placeholder="Contoh: Weapon"
        >
        @error('category') <small class="field-error">{{ $message }}</small> @enderror
    </div>

    <div class="admin-form-group">
        <label for="unit">Unit *</label>
        <input
            type="text"
            id="unit"
            name="unit"
            value="{{ old('unit', $material->unit ?? 'item') }}"
            placeholder="item"
            required
        >
        @error('unit') <small class="field-error">{{ $message }}</small> @enderror
    </div>

    <div class="admin-form-group admin-form-full">
        <label for="image">Gambar Material</label>

        <div class="character-image-upload">
            <div class="character-image-preview">
                <img
                    id="materialImagePreview"
                    src="{{ $material->image_path ?? '' }}"
                    alt="Preview Material"
                    @if (empty($material->image_path)) hidden @endif
                >

                <div
                    id="materialImagePlaceholder"
                    @if (!empty($material->image_path)) hidden @endif
                >
                    <span>+</span>
                    <small>Preview 1:1</small>
                </div>
            </div>

            <div class="character-image-controls">
                <input
                    type="file"
                    name="image"
                    id="materialImage"
                    accept="image/jpeg,image/png,image/webp"
                >

                <small>
                    JPG, PNG, atau WEBP. Maksimal 5 MB.
                    Gambar akan dipotong menjadi 600 × 600 px.
                </small>

                @error('image')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </div>

    <p class="admin-form-full material-stage-empty">
        Atur relasi karakter, tahap, jumlah, dan catatan material melalui menu Progression.
    </p>
</div>

<div class="admin-form-footer">
    <a href="{{ route('admin.materials.index') }}" class="admin-button admin-button-secondary">
        Batal
    </a>
    <button type="submit" class="admin-button admin-button-primary">
        {{ isset($material) ? 'Simpan Perubahan' : 'Tambah Material' }}
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('materialImage');
    const preview = document.getElementById('materialImagePreview');
    const placeholder = document.getElementById('materialImagePlaceholder');

    if (!input || !preview || !placeholder) return;

    let previewUrl = null;

    input.addEventListener('change', function () {
        const file = this.files[0];

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        if (!file) return;

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            this.value = '';
            alert('Format gambar harus JPG, PNG, atau WEBP.');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            this.value = '';
            alert('Ukuran gambar maksimal 5 MB.');
            return;
        }

        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        preview.hidden = false;
        placeholder.hidden = true;
    });
});
</script>
