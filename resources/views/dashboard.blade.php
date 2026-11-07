@extends('layouts.app')

@section('title', 'Dashboard - GBF Character Tracker')

@section('content')
<div class="dashboard-page">
    <header class="dashboard-heading">
        <span class="dashboard-eyebrow">GBF CHARACTER TRACKER</span>
        <h1>Pilih kategori karakter</h1>
        <p>Pilih Eternal atau Evoker untuk melihat daftar karakter.</p>
    </header>

    <div class="dashboard-category-grid">
        <a href="{{ route('characters.eternals') }}" class="dashboard-category-card dashboard-category-eternal">
            <span class="dashboard-category-mark" aria-hidden="true">E</span>
            <span class="dashboard-category-copy">
                <span class="dashboard-category-label">THE ETERNALS</span>
                <strong>Eternal</strong>
                <span>Lihat daftar karakter Eternal.</span>
            </span>
            <span class="dashboard-category-footer">
                <span>{{ $characterCounts->get('eternal', 0) }} karakter</span>
                <span class="dashboard-category-arrow" aria-hidden="true">→</span>
            </span>
        </a>

        <a href="{{ route('characters.evokers') }}" class="dashboard-category-card dashboard-category-evoker">
            <span class="dashboard-category-mark" aria-hidden="true">A</span>
            <span class="dashboard-category-copy">
                <span class="dashboard-category-label">THE ARCARUM</span>
                <strong>Evoker</strong>
                <span>Lihat daftar karakter Evoker.</span>
            </span>
            <span class="dashboard-category-footer">
                <span>{{ $characterCounts->get('evoker', 0) }} karakter</span>
                <span class="dashboard-category-arrow" aria-hidden="true">→</span>
            </span>
        </a>
    </div>
</div>
@endsection
