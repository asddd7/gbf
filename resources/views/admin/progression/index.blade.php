@extends('layouts.app')

@section('title', 'Progression Character - Admin GBF')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">DATA MANAGEMENT</span>
            <h1>Character Progression</h1>
            <p>Daftar karakter yang sudah memiliki pengaturan tahap.</p>
        </div>

        <a href="{{ route('admin.progression.create') }}" class="admin-button admin-button-primary">
            + Tambah Progression
        </a>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success">{{ session('success') }}</div>
    @endif

    <div class="admin-panel">
        <div class="admin-panel-heading">
            <div>
                <h2>Daftar Character</h2>
                <p>{{ $characters->count() }} character sudah ditambahkan</p>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nama Character</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($characters as $character)
                        <tr>
                            <td><strong>{{ $character->name }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td class="admin-empty">
                                Belum ada character yang ditambahkan ke progression.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
