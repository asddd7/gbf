@extends('layouts.app')

@section('title', 'Materials - Admin GBF')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">DATA MANAGEMENT</span>
            <h1>Materials</h1>
            <p>Kelola katalog material. Relasi karakter dan tahap diatur melalui Progression.</p>
        </div>

        <a href="{{ route('admin.materials.create') }}" class="admin-button admin-button-primary">
            + Tambah Material
        </a>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="admin-alert admin-alert-danger">
            Ada data yang perlu diperiksa kembali.
        </div>
    @endif

    <div class="admin-panel">
        <div class="admin-panel-heading">
            <div>
                <h2>Daftar Materials</h2>
                <p>{{ $materials->total() }} material terdaftar</p>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-table material-table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Kategori</th>
                        <th>Unit</th>
                        <th>Relasi Character</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materials as $material)
                        @php
                            $characterNames = $material->stageMaterials
                                ->map(fn ($stageMaterial) => $stageMaterial->assignment?->character?->name)
                                ->filter()
                                ->unique()
                                ->values();
                        @endphp
                        <tr>
                            <td>
                                <div class="material-cell">
                                    @if ($material->image_path)
                                        <img
                                            src="{{ $material->image_path }}"
                                            alt="{{ $material->name }}"
                                            class="material-thumbnail"
                                        >
                                    @else
                                        <div class="material-placeholder" aria-hidden="true">
                                            {{ strtoupper(substr($material->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $material->name }}</strong>
                                        <small>{{ $material->slug }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $material->category ?: '-' }}</td>
                            <td>{{ $material->unit }}</td>
                            <td>
                                @if ($characterNames->isNotEmpty())
                                    <div class="material-character-list">
                                        @foreach ($characterNames->take(3) as $characterName)
                                            <span class="admin-tag tag-material">
                                                {{ $characterName }}
                                            </span>
                                        @endforeach
                                        @if ($characterNames->count() > 3)
                                            <span class="material-more">
                                                +{{ $characterNames->count() - 3 }} lainnya
                                            </span>
                                        @endif
                                    </div>
                                    <small class="material-stage-count">
                                        {{ $material->stage_materials_count }} relasi tahap
                                    </small>
                                @else
                                    <span class="material-unassigned">Belum dihubungkan</span>
                                @endif
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <a
                                        href="{{ route('admin.materials.edit', $material) }}"
                                        class="admin-button admin-button-secondary"
                                    >
                                        Edit
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.materials.destroy', $material) }}"
                                        onsubmit="return confirm('Hapus material ini? Relasi material pada progression karakter juga akan dihapus.')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-button admin-button-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-empty">
                                Belum ada material. Silakan tambahkan material pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            {{ $materials->links() }}
        </div>
    </div>
</div>
@endsection
