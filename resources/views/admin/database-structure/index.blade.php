@extends('layouts.app')

@section('title', 'Struktur Database - Admin GBF')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">DATABASE REFERENCE</span>
            <h1>Struktur DB</h1>
            <p>Pilih modul untuk melihat relasi database dan file halaman yang terkait, lalu salin keterangannya.</p>
        </div>
    </div>

    <section class="admin-panel database-structure-panel">
        <div class="admin-panel-heading">
            <div>
                <h2>Pilih Modul</h2>
                <p>Konten disusun berdasarkan relasi dan implementasi yang ada di aplikasi.</p>
            </div>
        </div>

        <form
            method="GET"
            action="{{ route('admin.database-structure.index') }}"
            class="database-structure-selector"
        >
            <div class="admin-form-group">
                <label for="module">Modul</label>
                <select id="module" name="module" required>
                    @foreach ($modules as $key => $module)
                        <option value="{{ $key }}" @selected($selectedKey === $key)>
                            {{ $module['title'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="admin-button admin-button-primary">
                Tampilkan
            </button>
        </form>

        <div class="database-structure-content">
            <div class="database-structure-description">
                <span class="admin-eyebrow">MODUL TERPILIH</span>
                <h2>{{ $selectedModule['title'] }}</h2>
                <p>{{ $selectedModule['description'] }}</p>
            </div>

            <div class="database-structure-grid">
                <section class="database-structure-section">
                    <h3>Relasi</h3>
                    <ul>
                        @foreach ($selectedModule['relations'] as $relation)
                            <li>{{ $relation }}</li>
                        @endforeach
                    </ul>
                </section>

                <section class="database-structure-section">
                    <h3>Tabel Database</h3>
                    <ul class="database-structure-code-list">
                        @foreach ($selectedModule['tables'] as $table)
                            <li><code>{{ $table }}</code></li>
                        @endforeach
                    </ul>
                </section>
            </div>

            <section class="database-structure-section database-structure-files">
                <h3>File yang Terhubung</h3>
                <ul class="database-structure-code-list">
                    @foreach ($selectedModule['files'] as $file)
                        <li><code>{{ $file }}</code></li>
                    @endforeach
                </ul>
            </section>

            <div class="database-structure-copy">
                <div class="database-structure-copy-heading">
                    <div>
                        <h3>Teks untuk disalin</h3>
                        <p>Salinan berisi deskripsi modul, relasi, tabel, dan file terkait.</p>
                    </div>
                    <button
                        type="button"
                        class="admin-button admin-button-secondary"
                        id="copyDatabaseStructure"
                    >
                        Salin Keterangan
                    </button>
                </div>
                <textarea id="databaseStructureText" readonly rows="16" aria-label="Keterangan struktur database">{{ $copyText }}</textarea>
                <p class="database-structure-copy-status" id="copyDatabaseStructureStatus" role="status" aria-live="polite"></p>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const copyButton = document.getElementById('copyDatabaseStructure');
    const copyText = document.getElementById('databaseStructureText');
    const status = document.getElementById('copyDatabaseStructureStatus');

    copyButton.addEventListener('click', async function () {
        try {
            await navigator.clipboard.writeText(copyText.value);
            status.textContent = 'Keterangan berhasil disalin.';
        } catch {
            copyText.focus();
            copyText.select();
            status.textContent = 'Clipboard tidak tersedia. Teks sudah dipilih; salin secara manual.';
        }
    });
});
</script>
@endsection
