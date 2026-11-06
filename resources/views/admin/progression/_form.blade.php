@php
    $isEditing = $progressionCharacter !== null;
@endphp
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">DATA MANAGEMENT</span>
            <h1>{{ $isEditing ? 'Edit Character Progression' : 'Tambah Character Progression' }}</h1>
            <p>
                {{ $isEditing
                    ? 'Perbarui tahap dan item yang dibutuhkan karakter.'
                    : 'Pilih character, tahap yang sudah tersedia, lalu tambahkan item dan quantity.' }}
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="admin-alert admin-alert-danger">
            Data belum tersimpan. Periksa character, tahap, item, dan quantity.
        </div>
    @endif

    @if (!$isEditing && $characters->isEmpty())
        <div class="admin-alert admin-alert-danger">
            Belum ada character. Tambahkan character terlebih dahulu di menu Characters.
        </div>
    @elseif ($stages->isEmpty())
        <div class="admin-alert admin-alert-danger">
            Belum ada tahap. Tambahkan tahap terlebih dahulu di menu Tahap.
        </div>
    @elseif ($materials->isEmpty())
        <div class="admin-alert admin-alert-danger">
            Belum ada item. Tambahkan item terlebih dahulu di menu Materials.
        </div>
    @endif

    @php
        $defaultStageRows = $isEditing ? [] : [[
            'stage_id' => '',
            'materials' => [['material_id' => '', 'quantity' => 1]],
        ]];
        $stageRows = old('stages', $progressionRows ?: $defaultStageRows);
        $stageRows = is_array($stageRows) ? $stageRows : [];
        $nextStageIndex = $stageRows === []
            ? 0
            : max(array_map('intval', array_keys($stageRows))) + 1;
    @endphp

    <div class="admin-panel progression-create-panel">
        <form
            method="POST"
            action="{{ $isEditing
                ? route('admin.progression.update', $progressionCharacter)
                : route('admin.progression.store') }}"
            id="progressionCreateForm"
        >
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            @if ($isEditing)
                <input type="hidden" id="character_id" value="{{ $progressionCharacter->id }}">
                <div class="progression-create-character">
                    <span class="admin-eyebrow">CHARACTER</span>
                    <h2 class="progression-character-name">{{ $progressionCharacter->name }}</h2>
                </div>
            @else
                <div class="admin-form-group progression-create-character">
                    <label for="character_id">Character *</label>
                    <select id="character_id" name="character_id" required>
                        <option value="">Pilih character</option>
                        @foreach ($characters as $character)
                            <option value="{{ $character->id }}" @selected((string) old('character_id') === (string) $character->id)>
                                {{ $character->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('character_id') <small class="field-error">{{ $message }}</small> @enderror
                </div>
            @endif

            <div class="progression-editor-heading">
                <div>
                    <span class="admin-eyebrow">TAHAP & ITEM</span>
                    <h2>{{ $isEditing ? 'Tahap Character' : 'Atur Tahap Character' }}</h2>
                    <p>
                        {{ $isEditing
                            ? 'Ubah tahap dan item yang terhubung. Tahap yang dihapus dari daftar akan dilepas dari karakter.'
                            : 'Tambahkan satu atau beberapa tahap, kemudian masukkan item untuk setiap tahap.' }}
                    </p>
                </div>
                <button
                    type="button"
                    class="admin-button admin-button-secondary"
                    id="addProgressionStage"
                    @disabled($stages->isEmpty())
                >
                    + Tambah Tahap
                </button>
            </div>

            <div class="progression-stage-list" id="progressionStageList">
                @foreach ($stageRows as $stageIndex => $stageRow)
                    @php
                        $stageMaterials = $stageRow['materials'] ?? [];
                        $stageMaterials = is_array($stageMaterials) ? $stageMaterials : [];
                    @endphp
                    <section class="progression-stage-card">
                        <div class="progression-stage-heading">
                            <span class="progression-stage-number">{{ $loop->iteration }}</span>
                            <div class="progression-stage-fields">
                                <div class="admin-form-group">
                                    <label>Tahap *</label>
                                    <select name="stages[{{ $stageIndex }}][stage_id]" class="progression-stage-select" required>
                                        <option value="">Pilih tahap</option>
                                        @foreach ($stages as $stage)
                                            <option
                                                value="{{ $stage->id }}"
                                                @selected((string) ($stageRow['stage_id'] ?? '') === (string) $stage->id)
                                            >
                                                {{ $stage->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("stages.{$stageIndex}.stage_id")
                                        <small class="field-error">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <button
                                type="button"
                                class="admin-button admin-button-danger progression-remove-stage"
                            >
                                Hapus Tahap
                            </button>
                        </div>

                        <div class="progression-materials">
                            <div class="progression-materials-heading">
                                <div>
                                    <h3>Item & Quantity</h3>
                                    <p>Item yang sama hanya dapat ditambahkan satu kali dalam satu tahap.</p>
                                </div>
                                <button
                                    type="button"
                                    class="admin-button admin-button-secondary progression-add-material"
                                    @disabled($materials->isEmpty())
                                >
                                    + Tambah Item
                                </button>
                            </div>

                            <div class="progression-material-list">
                                @foreach ($stageMaterials as $materialIndex => $materialRow)
                                    <div class="progression-material-row">
                                        <div class="admin-form-group">
                                            <label>Item *</label>
                                            <select
                                                name="stages[{{ $stageIndex }}][materials][{{ $materialIndex }}][material_id]"
                                                required
                                            >
                                                <option value="">Pilih item</option>
                                                @foreach ($materials as $material)
                                                    <option
                                                        value="{{ $material->id }}"
                                                        @selected((string) ($materialRow['material_id'] ?? '') === (string) $material->id)
                                                    >
                                                        {{ $material->name }} ({{ $material->unit }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("stages.{$stageIndex}.materials.{$materialIndex}.material_id")
                                                <small class="field-error">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="admin-form-group progression-quantity-field">
                                            <label>Quantity *</label>
                                            <input
                                                type="number"
                                                name="stages[{{ $stageIndex }}][materials][{{ $materialIndex }}][quantity]"
                                                min="1"
                                                max="4294967295"
                                                value="{{ $materialRow['quantity'] ?? 1 }}"
                                                required
                                            >
                                            @error("stages.{$stageIndex}.materials.{$materialIndex}.quantity")
                                                <small class="field-error">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <button
                                            type="button"
                                            class="admin-button admin-button-danger progression-remove-material"
                                            aria-label="Hapus item"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="progression-empty-stages" @if ($stageRows !== []) hidden @endif>
                Belum ada tahap. Klik “Tambah Tahap” untuk mulai.
            </div>

            <div class="admin-form-footer progression-footer">
                <a
                    href="{{ $isEditing
                        ? route('admin.progression.show', $progressionCharacter)
                        : route('admin.progression.index') }}"
                    class="admin-button admin-button-secondary"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="admin-button admin-button-primary"
                    @disabled(!$isEditing && ($characters->isEmpty() || $stages->isEmpty() || $materials->isEmpty()))
                >
                    {{ $isEditing ? 'Simpan Perubahan' : 'Simpan Progression' }}
                </button>
            </div>
        </form>
    </div>

    <template id="progressionStageTemplate">
        <section class="progression-stage-card">
            <div class="progression-stage-heading">
                <span class="progression-stage-number"></span>

                <div class="progression-stage-fields">
                    <div class="admin-form-group">
                        <label>Tahap *</label>

                        <select
                            name="stages[__STAGE__][stage_id]"
                            class="progression-stage-select"
                            required
                        >
                            <option value="">Pilih tahap</option>

                            @foreach ($stages as $stage)
                                <option value="{{ $stage->id }}">
                                    {{ $stage->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button
                    type="button"
                    class="admin-button admin-button-danger progression-remove-stage"
                >
                    Hapus Tahap
                </button>
            </div>

            <div class="progression-materials">
                <div class="progression-materials-heading">
                    <div>
                        <h3>Item & Quantity</h3>
                        <p>Item yang sama hanya dapat ditambahkan satu kali dalam satu tahap.</p>
                    </div>

                    <button
                        type="button"
                        class="admin-button admin-button-secondary progression-add-material"
                    >
                        + Tambah Item
                    </button>
                </div>

                <div class="progression-material-list"></div>
            </div>
        </section>
    </template>

    <template id="progressionMaterialTemplate">
        <div class="progression-material-row">
            <div class="admin-form-group">
                <label>Item *</label>
                <select name="stages[__STAGE__][materials][__MATERIAL__][material_id]" required>
                    <option value="">Pilih item</option>
                    @foreach ($materials as $material)
                        <option value="{{ $material->id }}">
                            {{ $material->name }} ({{ $material->unit }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="admin-form-group progression-quantity-field">
                <label>Quantity *</label>
                <input
                    type="number"
                    name="stages[__STAGE__][materials][__MATERIAL__][quantity]"
                    min="1"
                    max="4294967295"
                    value="1"
                    required
                >
            </div>
            <button type="button" class="admin-button admin-button-danger progression-remove-material">
                Hapus
            </button>
        </div>
    </template>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('progressionCreateForm');
        const characterSelect = document.getElementById('character_id');
        const stageList = document.getElementById('progressionStageList');
        const stageTemplate = document.getElementById('progressionStageTemplate');
        const materialTemplate = document.getElementById('progressionMaterialTemplate');
        const emptyMessage = document.querySelector('.progression-empty-stages');
        let nextStageIndex = {{ $nextStageIndex }};

        function refreshStageOptions() {
            const selectedCharacterId = characterSelect.value;
            const stageSelects = Array.from(stageList.querySelectorAll('.progression-stage-select'));

            stageSelects.forEach(function (select) {
                const otherSelectedIds = stageSelects
                    .filter(function (otherSelect) { return otherSelect !== select; })
                    .map(function (otherSelect) { return otherSelect.value; })
                    .filter(Boolean);

                Array.from(select.options).forEach(function (option) {
                    if (!option.value) {
                        return;
                    }

                    const ownerId = option.dataset.characterId;
                    const usedElsewhere = otherSelectedIds.includes(option.value);
                    option.disabled = (ownerId && ownerId !== selectedCharacterId) || usedElsewhere;
                });

                if (select.selectedOptions[0]?.disabled) {
                    select.value = '';
                }
            });
        }

        function updateStageNumbers() {
            stageList.querySelectorAll('.progression-stage-card').forEach(function (stage, index) {
                stage.querySelector('.progression-stage-number').textContent = index + 1;
            });
            emptyMessage.hidden = stageList.querySelectorAll('.progression-stage-card').length > 0;
            refreshStageOptions();
        }

        function addMaterial(stageCard) {
            const stageId = stageCard.querySelector('.progression-stage-select').name.match(/stages\[(\d+)\]/)[1];
            const materialList = stageCard.querySelector('.progression-material-list');
            const materialIndex = materialList.children.length;
            const fragment = materialTemplate.content.cloneNode(true);

            fragment.querySelectorAll('[name]').forEach(function (field) {
                field.name = field.name
                    .replaceAll('__STAGE__', stageId)
                    .replaceAll('__MATERIAL__', materialIndex);
            });

            materialList.appendChild(fragment);
        }

        document.getElementById('addProgressionStage').addEventListener('click', function () {
            const fragment = stageTemplate.content.cloneNode(true);

            fragment.querySelectorAll('[name]').forEach(function (field) {
                field.name = field.name.replaceAll('__STAGE__', nextStageIndex);
            });

            stageList.appendChild(fragment);
            nextStageIndex += 1;
            addMaterial(stageList.lastElementChild);
            updateStageNumbers();
        });

        characterSelect.addEventListener('change', refreshStageOptions);
        form.addEventListener('change', function (event) {
            if (event.target.matches('.progression-stage-select')) {
                refreshStageOptions();
            }
        });
        form.addEventListener('click', function (event) {
            const addMaterialButton = event.target.closest('.progression-add-material');
            const removeMaterialButton = event.target.closest('.progression-remove-material');
            const removeStageButton = event.target.closest('.progression-remove-stage');

            if (addMaterialButton) {
                addMaterial(addMaterialButton.closest('.progression-stage-card'));
            } else if (removeMaterialButton) {
                removeMaterialButton.closest('.progression-material-row').remove();
            } else if (removeStageButton) {
                removeStageButton.closest('.progression-stage-card').remove();
                updateStageNumbers();
            }
        });

        refreshStageOptions();
    });
    </script>
</div>
