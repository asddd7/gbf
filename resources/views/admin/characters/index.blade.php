
@extends('layouts.app')

@section('title', 'Characters - Admin GBF')

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">DATA MANAGEMENT</span>
            <h1>Characters</h1>
            <p>Kelola data Eternal dan Evoker.</p>
        </div>

        <a href="{{ route('admin.characters.create') }}" class="admin-button admin-button-primary">
            + Tambah Character
        </a>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="admin-alert admin-alert-danger">
            Ada data yang perlu diperiksa kembali.
        </div>
    @endif

    <div class="admin-panel">
        <div class="admin-panel-heading">
            <div>
                <h2>Daftar Characters</h2>
                <p>{{ $characters->total() }} karakter terdaftar</p>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Character</th>
                        <th>Type</th>
                        <th>Element</th>
                        <th>Stages</th>
                        <th>Urutan</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($characters as $character)
                        <tr>
                            <td>
                                <div class="character-cell">
                                    @if ($character->image_path)
                                        <img
                                            src="{{ $character->image_path }}"
                                            alt="{{ $character->name }}"
                                            class="character-thumbnail"
                                        >
                                    @else
                                        <div class="character-placeholder">
                                            {{ strtoupper(substr($character->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div>
                                        <strong>{{ $character->name }}</strong>
                                        <small>{{ $character->slug }}</small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="admin-tag {{ $character->character_type === 'eternal' ? 'tag-eternal' : 'tag-evoker' }}">
                                    {{ ucfirst($character->character_type) }}
                                </span>
                            </td>

                            <td>{{ $character->element ?: '-' }}</td>
                            <td>{{ $character->stages_count }}</td>
                            <td>{{ $character->sort_order }}</td>

                            <td>
                                <div class="admin-actions">
                                    <a
                                        href="{{ route('admin.characters.edit', $character) }}"
                                        class="admin-button admin-button-secondary"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.characters.destroy', $character) }}"
                                        onsubmit="return confirm('Yakin ingin menghapus karakter ini beserta seluruh tahapannya?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="admin-button admin-button-danger"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-empty">
                                Belum ada karakter. Silakan tambahkan karakter pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            {{ $characters->links() }}
        </div>
    </div>
</div>
@endsection
