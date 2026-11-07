@extends('layouts.app')

@section('title', $category['name'].' - GBF Character Tracker')

@section('content')
<div class="dashboard-page character-list-page">
    <header class="dashboard-heading">
        <a href="{{ route('dashboard') }}" class="dashboard-back-link">
            <span aria-hidden="true">←</span> Kembali ke kategori
        </a>
        <span class="dashboard-eyebrow">{{ $type === 'eternal' ? 'THE ETERNALS' : 'THE ARCARUM' }}</span>
        <h1>{{ $category['name'] }}</h1>
        <p>{{ $category['description'] }}</p>
    </header>

    @if ($characters->isNotEmpty())
        <div class="dashboard-character-grid">
            @foreach ($characters as $character)
                <article class="dashboard-character-card" data-type="{{ $type }}">
                    <div class="dashboard-character-art">
                        @if ($character->image_path)
                            <img
                                src="{{ $character->image_path }}"
                                alt="{{ $character->name }}"
                                loading="lazy"
                            >
                        @else
                            <span class="dashboard-character-initial" aria-hidden="true">
                                {{ mb_substr($character->name, 0, 1) }}
                            </span>
                        @endif
                        <span class="dashboard-character-type">{{ strtoupper($type) }}</span>
                    </div>
                    <div class="dashboard-character-info">
                        <div class="dashboard-character-title">
                            <h3>{{ $character->name }}</h3>
                            @if ($character->element)
                                <span class="dashboard-element">{{ ucfirst($character->element) }}</span>
                            @endif
                        </div>
                        @if ($character->description)
                            <p>{{ $character->description }}</p>
                        @else
                            <p>Informasi karakter akan segera tersedia.</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="dashboard-empty-state">
            <h2>Belum ada karakter {{ $type === 'eternal' ? 'Eternal' : 'Evoker' }}</h2>
            <p>Karakter yang ditambahkan akan muncul di halaman ini.</p>
        </div>
    @endif
</div>
@endsection
