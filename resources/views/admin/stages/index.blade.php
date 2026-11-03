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
            <p>Kelola nama tahap dan urutan tampilnya.</p>
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
                        <th>Nama Tahap</th>
                        <th>Urutan</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stages as $stage)
                        <tr>
                            <td>
                                <strong>{{ $stage->name }}</strong>
                            </td>
                            <td>{{ $stage->sort_order }}</td>
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
                                        onsubmit="return confirm('Hapus tahap ini? Relasi material yang terhubung ke tahap ini juga akan dihapus.')"
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
                            <td colspan="3" class="admin-empty">
                                Belum ada tahap. Silakan tambahkan tahap pertama.
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
