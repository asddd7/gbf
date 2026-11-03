
@php
    $isEdit = isset($character);
    $currentImage = old('image_path', $character->image_path ?? null);
    $stageRows = old(
        'stages',
        isset($character)
            ? $character->stages->map(fn ($stage) => [
                'id' => $stage->id,
                'stage_key' => $stage->stage_key,
                'name' => $stage->name,
                'description' => $stage->description,
                'sort_order' => $stage->sort_order,
            ])->all()
            : []
    );
    $stageRows = is_array($stageRows) ? $stageRows : [];
    $nextStageIndex = $stageRows === []
        ? 0
        : max(array_map('intval', array_keys($stageRows))) + 1;
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
        @error('description') <small class="field-error">{{ $message }}</small> @enderror
    </div>

    <section class="admin-form-group admin-form-full character-stage-editor" aria-labelledby="character-stages-heading">
        <div class="character-stage-editor-heading">
            <div>
                <h2 id="character-stages-heading">Tahap Character</h2>
                <p>Tahap ini digunakan untuk menghubungkan material beserta jumlah kebutuhannya.</p>
            </div>
            <button type="button" class="admin-button admin-button-secondary" id="addCharacterStage">
                + Tambah Tahap
            </button>
        </div>

        @if ($unassignedStages->isNotEmpty())
            <div class="unassigned-stage-picker">
                <h3>Hubungkan Tahap yang Belum Terhubung</h3>
                <p>Pilih tahap yang dibuat dari halaman Tahap untuk menghubungkannya ke character ini.</p>
                @foreach ($unassignedStages as $unassignedStage)
                    <label class="unassigned-stage-option">
                        <input
                            type="checkbox"
                            name="unassigned_stage_ids[]"
                            value="{{ $unassignedStage->id }}"
                            @checked(in_array(
                                (string) $unassignedStage->id,
                                array_map('strval', old('unassigned_stage_ids', [])),
                                true
                            ))
                        >
                        <span>
                            <strong>{{ $unassignedStage->name }}</strong>
                            <small>Urutan {{ $unassignedStage->sort_order }}</small>
                        </span>
                    </label>
                @endforeach
                @error('unassigned_stage_ids') <small class="field-error">{{ $message }}</small> @enderror
                @error('unassigned_stage_ids.*') <small class="field-error">{{ $message }}</small> @enderror
            </div>
        @endif

        @if ($errors->has('stages') || $errors->has('stages.*.id') || $errors->has('stages.*.stage_key') || $errors->has('stages.*.name') || $errors->has('stages.*.sort_order'))
            <div class="admin-alert admin-alert-danger">
                Periksa kembali nama, key, dan urutan tahap. Stage key harus unik untuk setiap character.
            </div>
        @endif

        <div class="character-stage-list" id="characterStageList">
            @foreach ($stageRows as $index => $stage)
                <div class="character-stage-row">
                    <input type="hidden" name="stages[{{ $index }}][id]" value="{{ $stage['id'] ?? '' }}">
                    <div class="admin-form-group">
                        <label>Stage Key *</label>
                        <input
                            type="text"
                            name="stages[{{ $index }}][stage_key]"
                            value="{{ $stage['stage_key'] ?? '' }}"
                            placeholder="contoh: uncap-1"
                            maxlength="80"
                            required
                        >
                    </div>
                    <div class="admin-form-group">
                        <label>Nama Tahap *</label>
                        <input
                            type="text"
                            name="stages[{{ $index }}][name]"
                            value="{{ $stage['name'] ?? '' }}"
                            placeholder="Contoh: Uncap 1"
                            required
                        >
                    </div>
                    <div class="admin-form-group">
                        <label>Urutan *</label>
                        <input
                            type="number"
                            name="stages[{{ $index }}][sort_order]"
                            min="0"
                            max="65535"
                            value="{{ $stage['sort_order'] ?? 0 }}"
                            required
                        >
                    </div>
                    <div class="admin-form-group character-stage-description">
                        <label>Deskripsi</label>
                        <input
                            type="text"
                            name="stages[{{ $index }}][description]"
                            value="{{ $stage['description'] ?? '' }}"
                            placeholder="Opsional"
                        >
                    </div>
                    <button
                        type="button"
                        class="admin-button admin-button-danger character-stage-remove"
                        aria-label="Hapus tahap"
                    >
                        Hapus
                    </button>
                </div>
            @endforeach
        </div>

        <template id="characterStageTemplate">
            <div class="character-stage-row">
                <input type="hidden" name="stages[__INDEX__][id]" value="">
                <div class="admin-form-group">
                    <label>Stage Key *</label>
                    <input
                        type="text"
                        name="stages[__INDEX__][stage_key]"
                        placeholder="contoh: uncap-1"
                        maxlength="80"
                        required
                    >
                </div>
                <div class="admin-form-group">
                    <label>Nama Tahap *</label>
                    <input
                        type="text"
                        name="stages[__INDEX__][name]"
                        placeholder="Contoh: Uncap 1"
                        required
                    >
                </div>
                <div class="admin-form-group">
                    <label>Urutan *</label>
                    <input
                        type="number"
                        name="stages[__INDEX__][sort_order]"
                        min="0"
                        max="65535"
                        value="0"
                        required
                    >
                </div>
                <div class="admin-form-group character-stage-description">
                    <label>Deskripsi</label>
                    <input
                        type="text"
                        name="stages[__INDEX__][description]"
                        placeholder="Opsional"
                    >
                </div>
                <button
                    type="button"
                    class="admin-button admin-button-danger character-stage-remove"
                    aria-label="Hapus tahap"
                >
                    Hapus
                </button>
            </div>
        </template>
    </section>
</div>

<div class="admin-form-footer">
    <a href="{{ route('admin.characters.index') }}" class="admin-button admin-button-secondary">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('characterStageList');
    const template = document.getElementById('characterStageTemplate');
    const addButton = document.getElementById('addCharacterStage');
    let nextIndex = {{ $nextStageIndex }};

    addButton.addEventListener('click', function () {
        const row = template.content.cloneNode(true);

        row.querySelectorAll('[name]').forEach(function (field) {
            field.name = field.name.replaceAll('__INDEX__', nextIndex);
        });

        list.appendChild(row);
        nextIndex += 1;
    });

    list.addEventListener('click', function (event) {
        if (event.target.closest('.character-stage-remove')) {
            event.target.closest('.character-stage-row').remove();
        }
    });
});
</script>
