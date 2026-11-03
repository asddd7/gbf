@extends('layouts.app')

@section('title', 'Atur Progression - Admin GBF')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">DATA MANAGEMENT</span>
            <h1>Character Progression</h1>
            <p>Atur beberapa tahap dan kebutuhan material karakter dari satu halaman.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="admin-alert admin-alert-danger">
            Data belum tersimpan. Periksa nama tahap, pilihan material, dan quantity.
        </div>
    @endif

    <section class="admin-panel progression-character-panel">
        <div class="admin-panel-heading">
            <div>
                <h2>1. Pilih Character</h2>
                <p>Pilih character untuk melihat dan mengatur tahapnya.</p>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.progression.index') }}" class="progression-character-form">
            <div class="admin-form-group">
                <label for="character_id">Character</label>
                <select id="character_id" name="character_id" required>
                    <option value="">Pilih character</option>
                    @foreach ($characters as $option)
                        <option
                            value="{{ $option->id }}"
                            @selected((string) ($character?->id ?? '') === (string) $option->id)
                        >
                            {{ $option->name }} ({{ ucfirst($option->character_type) }})
                        </option>
                    @endforeach
                </select>
                @error('character_id') <small class="field-error">{{ $message }}</small> @enderror
            </div>
            <button type="submit" class="admin-button admin-button-primary">
                Pilih Character
            </button>
        </form>
    </section>

    @if (!$character)
        <div class="progression-empty">
            @if ($characters->isEmpty())
                Belum ada character. Tambahkan character terlebih dahulu.
            @else
                Pilih character di atas untuk mulai mengatur tahap dan materialnya.
            @endif
        </div>
    @else
        @php
            $savedStages = $character->stages->map(fn ($stage) => [
                'id' => $stage->id,
                'name' => $stage->name,
                'sort_order' => $stage->sort_order,
                'materials' => $stage->materials->map(fn ($row) => [
                    'material_id' => $row->material_id,
                    'quantity' => $row->quantity,
                ])->values()->all(),
            ])->values()->all();
            $stageRows = old('stages', $savedStages);
            $stageRows = is_array($stageRows) ? $stageRows : [];
            $nextStageIndex = $stageRows === []
                ? 0
                : max(array_map('intval', array_keys($stageRows))) + 1;
        @endphp

        @if ($materials->isEmpty())
            <div class="admin-alert admin-alert-danger progression-material-warning">
                Belum ada material untuk dipilih.
                <a href="{{ route('admin.materials.create') }}">Tambahkan material</a>
                terlebih dahulu.
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.progression.update') }}"
            class="progression-editor"
            id="progressionForm"
        >
            @csrf
            @method('PUT')
            <input type="hidden" name="character_id" value="{{ $character->id }}">

            <div class="progression-editor-heading">
                <div>
                    <span class="admin-eyebrow">2. ATUR TAHAP & MATERIAL</span>
                    <h2>{{ $character->name }}</h2>
                    <p>Tambahkan beberapa tahap, lalu masukkan material dan quantity untuk setiap tahap.</p>
                </div>
                <button
                    type="button"
                    class="admin-button admin-button-secondary"
                    id="addProgressionStage"
                >
                    + Tambah Tahap
                </button>
            </div>

            <div class="progression-stage-list" id="progressionStageList">
                @foreach ($stageRows as $stageIndex => $stage)
                    @php
                        $stageMaterials = $stage['materials'] ?? [];
                        $stageMaterials = is_array($stageMaterials) ? $stageMaterials : [];
                    @endphp
                    <section class="progression-stage-card">
                        <input
                            type="hidden"
                            name="stages[{{ $stageIndex }}][id]"
                            value="{{ $stage['id'] ?? '' }}"
                        >

                        <div class="progression-stage-heading">
                            <span class="progression-stage-number">{{ $loop->iteration }}</span>
                            <div class="progression-stage-fields">
                                <div class="admin-form-group">
                                    <label>Nama Tahap *</label>
                                    <input
                                        type="text"
                                        name="stages[{{ $stageIndex }}][name]"
                                        value="{{ $stage['name'] ?? '' }}"
                                        placeholder="Contoh: Transcendance 1"
                                        required
                                    >
                                    @error("stages.{$stageIndex}.name")
                                        <small class="field-error">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="admin-form-group progression-order-field">
                                    <label>Urutan *</label>
                                    <input
                                        type="number"
                                        name="stages[{{ $stageIndex }}][sort_order]"
                                        min="0"
                                        max="65535"
                                        value="{{ $stage['sort_order'] ?? $loop->iteration }}"
                                        required
                                    >
                                    @error("stages.{$stageIndex}.sort_order")
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
                                    <h3>Material & Quantity</h3>
                                    <p>Setiap material hanya dapat ditambahkan satu kali dalam satu tahap.</p>
                                </div>
                                <button
                                    type="button"
                                    class="admin-button admin-button-secondary progression-add-material"
                                    @disabled($materials->isEmpty())
                                >
                                    + Tambah Material
                                </button>
                            </div>

                            <div class="progression-material-list">
                                @foreach ($stageMaterials as $materialIndex => $stageMaterial)
                                    <div class="progression-material-row">
                                        <div class="admin-form-group">
                                            <label>Material *</label>
                                            <select
                                                name="stages[{{ $stageIndex }}][materials][{{ $materialIndex }}][material_id]"
                                                required
                                            >
                                                <option value="">Pilih material</option>
                                                @foreach ($materials as $material)
                                                    <option
                                                        value="{{ $material->id }}"
                                                        @selected((string) ($stageMaterial['material_id'] ?? '') === (string) $material->id)
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
                                                value="{{ $stageMaterial['quantity'] ?? 1 }}"
                                                required
                                            >
                                            @error("stages.{$stageIndex}.materials.{$materialIndex}.quantity")
                                                <small class="field-error">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <button
                                            type="button"
                                            class="admin-button admin-button-danger progression-remove-material"
                                            aria-label="Hapus material"
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
                Belum ada tahap. Klik “Tambah Tahap” untuk mulai menyusun progression.
            </div>

            <div class="admin-form-footer progression-footer">
                <button type="submit" class="admin-button admin-button-primary">
                    Simpan Progression
                </button>
            </div>
        </form>

        <template id="progressionStageTemplate">
            <section class="progression-stage-card">
                <input type="hidden" name="stages[__STAGE__][id]" value="">
                <div class="progression-stage-heading">
                    <span class="progression-stage-number"></span>
                    <div class="progression-stage-fields">
                        <div class="admin-form-group">
                            <label>Nama Tahap *</label>
                            <input
                                type="text"
                                name="stages[__STAGE__][name]"
                                placeholder="Contoh: Transcendance 1"
                                required
                            >
                        </div>
                        <div class="admin-form-group progression-order-field">
                            <label>Urutan *</label>
                            <input
                                type="number"
                                name="stages[__STAGE__][sort_order]"
                                min="0"
                                max="65535"
                                value="0"
                                required
                            >
                        </div>
                    </div>
                    <button type="button" class="admin-button admin-button-danger progression-remove-stage">
                        Hapus Tahap
                    </button>
                </div>
                <div class="progression-materials">
                    <div class="progression-materials-heading">
                        <div>
                            <h3>Material & Quantity</h3>
                            <p>Tambahkan material yang dibutuhkan pada tahap ini.</p>
                        </div>
                        <button
                            type="button"
                            class="admin-button admin-button-secondary progression-add-material"
                            @disabled($materials->isEmpty())
                        >
                            + Tambah Material
                        </button>
                    </div>
                    <div class="progression-material-list"></div>
                </div>
            </section>
        </template>

        <template id="progressionMaterialTemplate">
            <div class="progression-material-row">
                <div class="admin-form-group">
                    <label>Material *</label>
                    <select name="stages[__STAGE__][materials][__MATERIAL__][material_id]" required>
                        <option value="">Pilih material</option>
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
            const form = document.getElementById('progressionForm');
            const stageList = document.getElementById('progressionStageList');
            const stageTemplate = document.getElementById('progressionStageTemplate');
            const materialTemplate = document.getElementById('progressionMaterialTemplate');
            const emptyMessage = document.querySelector('.progression-empty-stages');
            let nextStageIndex = {{ $nextStageIndex }};

            function updateStageNumbers() {
                stageList.querySelectorAll('.progression-stage-card').forEach(function (stage, index) {
                    stage.querySelector('.progression-stage-number').textContent = index + 1;
                });
                emptyMessage.hidden = stageList.querySelectorAll('.progression-stage-card').length > 0;
            }

            function addMaterial(stageCard) {
                const stageId = stageCard.querySelector('input[name$="[id]"]').name.match(/stages\[(\d+)\]/)[1];
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
                updateStageNumbers();
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
        });
        </script>
    @endif
</div>
@endsection
