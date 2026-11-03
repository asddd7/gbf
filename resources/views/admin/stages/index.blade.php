@extends('layouts.app')

@section('title', 'Tahap Character - Admin GBF')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">DATA MANAGEMENT</span>
            <h1>Tahap Character</h1>
            <p>Kelola tahap perkembangan karakter dan material yang dibutuhkan.</p>
        </div>

        <a href="{{ route('admin.stages.create') }}" class="admin-button admin-button-primary">
            + Tambah Tahap
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
                <h2>Daftar Tahap</h2>
                <p>{{ $stages->total() }} tahap terdaftar</p>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-table stage-table">
                <thead>
                    <tr>
                        <th>Character</th>
                        <th>Tahap</th>
                        <th>Stage Key</th>
                        <th>Urutan</th>
                        <th>Material</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stages as $stage)
                        <tr>
                            <td>
                                <span class="stage-character-name">
                                    {{ $stage->character?->name ?? 'Character tidak ditemukan' }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $stage->name }}</strong>
                                @if ($stage->description)
                                    <small class="stage-description">
                                        {{ \Illuminate\Support\Str::limit($stage->description, 90) }}
                                    </small>
                                @endif
                            </td>
                            <td><code class="stage-key">{{ $stage->stage_key }}</code></td>
                            <td>{{ $stage->sort_order }}</td>
                            <td>
                                <span class="admin-tag tag-material">
                                    {{ $stage->materials_count }} material
                                </span>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <a
                                        href="{{ route('admin.stages.edit', $stage) }}"
                                        class="admin-button admin-button-secondary"
                                    >
                                        Edit
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.stages.destroy', $stage) }}"
                                        onsubmit="return confirm('Hapus tahap ini? Semua kebutuhan material yang terhubung ke tahap ini juga akan dihapus.')"
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
                            <td colspan="6" class="admin-empty">
                                Belum ada tahap karakter. Tambahkan character terlebih dahulu, lalu buat tahapnya.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            {{ $stages->links() }}
        </div>
    </div>
</div>
@endsection
