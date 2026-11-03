
@php
    $isEdit = isset($character);
    $currentImage = $character->image_path ?? null;
@endphp

<div class="admin-form-grid">
    <div class="admin-form-group">
        <label for="name">Nama Character *</label>
        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $character->name ?? '') }}"
            placeholder="Contoh: Seofon"
            required
        >
        @error('name')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="admin-form-group">
        <label for="slug">Slug</label>
        <input
            type="text"
            id="slug"
            name="slug"
            value="{{ old('slug', $character->slug ?? '') }}"
            placeholder="Kosongkan untuk dibuat otomatis"
        >
        @error('slug')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="admin-form-group">
        <label for="character_type">Character Type *</label>
        <select id="character_type" name="character_type" required>
            <option value="">Pilih tipe</option>
            <option value="eternal" @selected(old('character_type', $character->character_type ?? '') === 'eternal')>
                Eternal
            </option>
            <option value="evoker" @selected(old('character_type', $character->character_type ?? '') === 'evoker')>
                Evoker
            </option>
        </select>
        @error('character_type')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="admin-form-group">
        <label for="element">Element</label>
        <select id="element" name="element">
            <option value="">Tidak ditentukan</option>
            @foreach (['fire', 'water', 'earth', 'wind', 'light', 'dark'] as $element)
                <option
                    value="{{ $element }}"
                    @selected(old('element', $character->element ?? '') === $element)
                >
                    {{ ucfirst($element) }}
                </option>
            @endforeach
        </select>
        @error('element')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="admin-form-group admin-form-full">
        <label for="image">Gambar Character</label>

        <div class="character-image-upload">
            <div class="character-image-preview">
                <img
                    id="imagePreview"
                    src="{{ $currentImage ?? '' }}"
                    alt="Preview Character"
                    @if (!$currentImage) hidden @endif
                >

                <div id="imagePlaceholder" @if ($currentImage) hidden @endif>
                    <span>+</span>
                    <small>Preview 1:1</small>
                </div>
            </div>

            <div class="character-image-controls">
                <input
                    type="file"
                    name="image"
                    id="image"
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

    <div class="admin-form-group admin-form-full">
        <label for="description">Deskripsi</label>
        <textarea
            id="description"
            name="description"
            rows="5"
            placeholder="Deskripsi karakter..."
        >{{ old('description', $character->description ?? '') }}</textarea>
        @error('description')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="admin-form-footer">
    <a
        href="{{ route('admin.characters.index') }}"
        class="admin-button admin-button-secondary"
    >
        Batal
    </a>

    <button type="submit" class="admin-button admin-button-primary">
        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Character' }}
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('image');
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePlaceholder');

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
