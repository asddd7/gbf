@extends('layouts.app')

@section('title', $character->name.' Progression - Admin GBF')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">CHARACTER PROGRESSION</span>
            <h1>{{ $character->name }}</h1>
            <p>Daftar tahap, item, dan quantity yang dibutuhkan karakter.</p>
        </div>
        <div class="progression-detail-actions">
            <a href="{{ route('admin.progression.index') }}" class="admin-button admin-button-secondary">
                Kembali
            </a>
            <a
                href="{{ route('admin.progression.edit', $character) }}"
                class="admin-button admin-button-primary"
            >
                Edit Progression
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success">{{ session('success') }}</div>
    @endif

    <div class="progression-detail-list">
        @foreach ($character->stages as $stage)
            <section class="admin-panel progression-detail-stage">
                <div class="admin-panel-heading progression-detail-stage-heading">
                    <div class="progression-stage-number">{{ $loop->iteration }}</div>
                    <div>
                        <span class="admin-eyebrow">TAHAP {{ $stage->sort_order }}</span>
                        <h2>{{ $stage->name }}</h2>
                    </div>
                </div>

                <div class="admin-table-wrapper">
                    <table class="admin-table progression-detail-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stage->materials as $stageMaterial)
                                <tr>
                                    <td>
                                        <strong>{{ $stageMaterial->material->name }}</strong>
                                    </td>
                                    <td>{{ number_format($stageMaterial->quantity) }}</td>
                                    <td>{{ $stageMaterial->material->unit }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="admin-empty">
                                        Belum ada item untuk tahap ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection
