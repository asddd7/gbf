<div class="admin-form-grid">
    <div class="admin-form-group admin-form-full">
        <label for="character_id">Character *</label>
        <select id="character_id" name="character_id" required>
            <option value="">Pilih character</option>
            @foreach ($characters as $character)
                <option
                    value="{{ $character->id }}"
                    @selected((string) old('character_id', $stage->character_id ?? request('character_id', '')) === (string) $character->id)
                >
                    {{ $character->name }}
                </option>
            @endforeach
        </select>
        @error('character_id') <small class="field-error">{{ $message }}</small> @enderror
        @if ($characters->isEmpty())
            <small class="field-error">
                Belum ada character. Tambahkan character sebelum membuat tahap.
            </small>
        @endif
    </div>

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
        @error('name') <small class="field-error">{{ $message }}</small> @enderror
    </div>

    <div class="admin-form-group">
        <label for="stage_key">Stage Key *</label>
        <input
            type="text"
            id="stage_key"
            name="stage_key"
            value="{{ old('stage_key', $stage->stage_key ?? '') }}"
            placeholder="Contoh: uncap-1"
            maxlength="80"
            required
        >
        @error('stage_key') <small class="field-error">{{ $message }}</small> @enderror
        <small class="form-help">Harus unik untuk character yang dipilih.</small>
    </div>

    <div class="admin-form-group">
        <label for="sort_order">Urutan *</label>
        <input
            type="number"
            id="sort_order"
            name="sort_order"
            min="0"
            max="65535"
            value="{{ old('sort_order', $stage->sort_order ?? 0) }}"
            required
        >
        @error('sort_order') <small class="field-error">{{ $message }}</small> @enderror
    </div>

    <div class="admin-form-group admin-form-full">
        <label for="description">Deskripsi</label>
        <textarea
            id="description"
            name="description"
            rows="4"
            placeholder="Deskripsi tahap..."
        >{{ old('description', $stage->description ?? '') }}</textarea>
        @error('description') <small class="field-error">{{ $message }}</small> @enderror
    </div>
</div>

<div class="admin-form-footer">
    <a href="{{ route('admin.stages.index') }}" class="admin-button admin-button-secondary">
        Batal
    </a>
    <button
        type="submit"
        class="admin-button admin-button-primary"
        @disabled($characters->isEmpty())
    >
        {{ isset($stage) ? 'Simpan Perubahan' : 'Tambah Tahap' }}
    </button>
</div>
