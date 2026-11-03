@php
    $stageAssignments = isset($material)
        ? $material->stageMaterials->keyBy('character_stage_id')
        : collect();
    $oldStageIds = old('stage_ids', $stageAssignments->keys()->all());
    $selectedStageIds = is_array($oldStageIds)
        ? array_map('intval', $oldStageIds)
        : [];
@endphp

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

    <section class="admin-form-group admin-form-full material-stage-section" aria-labelledby="stage-material-heading">
        <div class="material-stage-heading">
            <div>
                <h2 id="stage-material-heading">Relasi Tahap Character</h2>
                <p>Pilih tahap yang membutuhkan material ini, lalu isi jumlah dan catatan.</p>
            </div>
            @error('stage_ids') <small class="field-error">{{ $message }}</small> @enderror
            @error('stage_ids.*') <small class="field-error">{{ $message }}</small> @enderror
            @error('quantities.*') <small class="field-error">{{ $message }}</small> @enderror
            @error('notes.*') <small class="field-error">{{ $message }}</small> @enderror
        </div>

        @if ($stages->isEmpty())
            <div class="material-stage-empty">
                Belum ada tahap karakter. Buat karakter dan tahapnya terlebih dahulu agar material dapat dihubungkan.
            </div>
        @else
            <div class="material-stage-groups">
                @foreach ($stages->groupBy(fn ($stage) => $stage->character?->name ?? 'Character') as $characterName => $characterStages)
                    <fieldset class="material-stage-group">
                        <legend>{{ $characterName }}</legend>
                        @foreach ($characterStages as $stage)
                            @php
                                $assignment = $stageAssignments->get($stage->id);
                                $isSelected = in_array($stage->id, $selectedStageIds, true);
                            @endphp
                            <div class="material-stage-row">
                                <label class="material-stage-select">
                                    <input
                                        type="checkbox"
                                        name="stage_ids[]"
                                        value="{{ $stage->id }}"
                                        @checked($isSelected)
                                    >
                                    <span>
                                        <strong>{{ $stage->name }}</strong>
                                        <small>{{ $stage->stage_key }}</small>
                                    </span>
                                </label>

                                <div class="material-stage-fields" data-stage-fields>
                                    <label>
                                        <span>Jumlah</span>
                                        <input
                                            type="number"
                                            name="quantities[{{ $stage->id }}]"
                                            min="1"
                                            max="4294967295"
                                            value="{{ old('quantities.'.$stage->id, $assignment?->quantity ?? 1) }}"
                                            @disabled(!$isSelected)
                                        >
                                    </label>
                                    <label>
                                        <span>Catatan</span>
                                        <input
                                            type="text"
                                            name="notes[{{ $stage->id }}]"
                                            value="{{ old('notes.'.$stage->id, $assignment?->notes ?? '') }}"
                                            placeholder="Opsional"
                                            @disabled(!$isSelected)
                                        >
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </fieldset>
                @endforeach
            </div>
        @endif
    </section>
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
document.querySelectorAll('.material-stage-row').forEach(function (row) {
    const checkbox = row.querySelector('input[type="checkbox"]');
    const fields = row.querySelectorAll('[data-stage-fields] input');

    checkbox.addEventListener('change', function () {
        fields.forEach(function (field) {
            field.disabled = !checkbox.checked;
        });
    });
});

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
