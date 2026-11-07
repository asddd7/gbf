@extends('layouts.app')

@section('title', 'Dashboard - GBF Character Tracker')

@section('content')
@php
    $eternals = $charactersByType->get('eternal', collect());
    $evokers = $charactersByType->get('evoker', collect());
@endphp

<div class="dashboard-page">
    <section class="dashboard-hero">
        <div class="dashboard-hero-content">
            <span class="dashboard-eyebrow">GRANBLUE FANTASY TRACKER</span>
            <h1>
                @auth
                    Selamat datang, {{ Auth::user()->name }}.
                @else
                    Rencanakan perjalananmu.
                @endauth
            </h1>
            <p>
                Pilih karakter Eternal atau Evoker untuk mulai melihat kebutuhan
                progression dan merencanakan materialmu.
            </p>
        </div>
        <div class="dashboard-hero-mark" aria-hidden="true">
            <span>GBF</span>
            <i></i>
            <i></i>
            <i></i>
        </div>
        <div class="dashboard-summary" aria-label="Jumlah karakter tersedia">
            <div>
                <strong>{{ $eternals->count() + $evokers->count() }}</strong>
                <span>Karakter</span>
            </div>
            <div>
                <strong>{{ $eternals->count() }}</strong>
                <span>Eternal</span>
            </div>
            <div>
                <strong>{{ $evokers->count() }}</strong>
                <span>Evoker</span>
            </div>
        </div>
    </section>

    <section class="dashboard-roster" aria-labelledby="roster-title">
        <div class="dashboard-section-heading">
            <div>
                <span class="dashboard-eyebrow">CHARACTER ROSTER</span>
                <h2 id="roster-title">Pilih perjalananmu</h2>
                <p>Pilih kategori untuk melihat karakter yang tersedia.</p>
            </div>
        </div>

        <div class="dashboard-tabs" role="tablist" aria-label="Kategori karakter">
            <button
                type="button"
                class="dashboard-tab is-active"
                id="eternal-tab"
                role="tab"
                aria-selected="true"
                aria-controls="eternal-panel"
                tabindex="0"
                data-character-tab="eternal"
            >
                <span class="dashboard-tab-icon" aria-hidden="true">E</span>
                <span class="dashboard-tab-copy">
                    <strong>Eternals</strong>
                    <small>{{ $eternals->count() }} karakter</small>
                </span>
                <span class="dashboard-tab-arrow" aria-hidden="true">→</span>
            </button>
            <button
                type="button"
                class="dashboard-tab"
                id="evoker-tab"
                role="tab"
                aria-selected="false"
                aria-controls="evoker-panel"
                tabindex="-1"
                data-character-tab="evoker"
            >
                <span class="dashboard-tab-icon dashboard-tab-icon-evoker" aria-hidden="true">A</span>
                <span class="dashboard-tab-copy">
                    <strong>Evokers</strong>
                    <small>{{ $evokers->count() }} karakter</small>
                </span>
                <span class="dashboard-tab-arrow" aria-hidden="true">→</span>
            </button>
        </div>

        <div
            class="dashboard-character-panel"
            id="eternal-panel"
            role="tabpanel"
            aria-labelledby="eternal-tab"
            data-character-panel="eternal"
        >
            @if ($eternals->isNotEmpty())
                <div class="dashboard-character-grid">
                    @foreach ($eternals as $character)
                        <article class="dashboard-character-card" data-type="eternal">
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
                                <span class="dashboard-character-type">ETERNAL</span>
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
                                    <p>Siapkan material dan ikuti setiap tahap progression.</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="dashboard-empty-state">
                    <span class="dashboard-empty-icon" aria-hidden="true">E</span>
                    <h3>Belum ada Eternal</h3>
                    <p>Karakter pada kategori ini akan muncul di sini setelah tersedia.</p>
                </div>
            @endif
        </div>

        <div
            class="dashboard-character-panel"
            id="evoker-panel"
            role="tabpanel"
            aria-labelledby="evoker-tab"
            data-character-panel="evoker"
            hidden
        >
            @if ($evokers->isNotEmpty())
                <div class="dashboard-character-grid">
                    @foreach ($evokers as $character)
                        <article class="dashboard-character-card" data-type="evoker">
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
                                <span class="dashboard-character-type">EVOKER</span>
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
                                    <p>Siapkan material dan ikuti setiap tahap progression.</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="dashboard-empty-state">
                    <span class="dashboard-empty-icon" aria-hidden="true">A</span>
                    <h3>Belum ada Evoker</h3>
                    <p>Karakter pada kategori ini akan muncul di sini setelah tersedia.</p>
                </div>
            @endif
        </div>
    </section>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = Array.from(document.querySelectorAll('[data-character-tab]'));
            const panels = Array.from(document.querySelectorAll('[data-character-panel]'));

            function activateTab(tab, moveFocus = false) {
                const selectedType = tab.dataset.characterTab;

                tabs.forEach(function (item) {
                    const isActive = item === tab;
                    item.classList.toggle('is-active', isActive);
                    item.setAttribute('aria-selected', String(isActive));
                    item.tabIndex = isActive ? 0 : -1;
                });

                panels.forEach(function (panel) {
                    panel.hidden = panel.dataset.characterPanel !== selectedType;
                });

                if (moveFocus) {
                    tab.focus();
                }
            }

            tabs.forEach(function (tab, index) {
                tab.addEventListener('click', function () {
                    activateTab(tab);
                });

                tab.addEventListener('keydown', function (event) {
                    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
                        return;
                    }

                    event.preventDefault();
                    const direction = event.key === 'ArrowRight' ? 1 : -1;
                    const nextIndex = (index + direction + tabs.length) % tabs.length;
                    activateTab(tabs[nextIndex], true);
                });
            });
        });
    </script>
@endpush
@endsection
