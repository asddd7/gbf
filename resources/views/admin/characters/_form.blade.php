
@php
    $isEdit = isset($character);
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
        @error('name') <small class="field-error">{{ $message }}</small> @enderror
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
        @error('slug') <small class="field-error">{{ $message }}</small> @enderror
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
        @error('character_type') <small class="field-error">{{ $message }}</small> @enderror
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
        @error('element') <small class="field-error">{{ $message }}</small> @enderror
    </div>

    <div class="admin-form-group">
        <label for="sort_order">Urutan *</label>
        <input
            type="number"
            id="sort_order"
            name="sort_order"
            min="0"
            max="65535"
            value="{{ old('sort_order', $character->sort_order ?? 0) }}"
            required
        >
        @error('sort_order') <small class="field-error">{{ $message }}</small> @enderror
    </div>

    <div class="admin-form-group">
        <label for="image_path">Image Path / URL</label>
        <input
            type="text"
            id="image_path"
            name="image_path"
            value="{{ old('image_path', $character->image_path ?? '') }}"
            placeholder="https://example.com/image.png"
        >
        @error('image_path') <small class="field-error">{{ $message }}</small> @enderror
    </div>

    <div class="admin-form-group admin-form-full">
        <label for="description">Deskripsi</label>
        <textarea
            id="description"
            name="description"
            rows="5"
            placeholder="Deskripsi karakter..."
        >{{ old('description', $character->description ?? '') }}</textarea>
        @error('description') <small class="field-error">{{ $message }}</small> @enderror
    </div>
</div>

<div class="admin-form-footer">
    <a href="{{ route('admin.characters.index') }}" class="admin-button admin-button-secondary">
        Batal
    </a>

    <button type="submit" class="admin-button admin-button-primary">
        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Character' }}
    </button>
</div>
