
@extends('layouts.app')

@section('title', 'Daftar - GBF Calculator')

@push('styles')
    @vite('resources/css/auth.css')
@endpush

@section('content')

<div class="auth-page">

    <div class="auth-heading">
        <h1 class="auth-title">Buat Akun</h1>
        <p class="auth-description">
            Daftar untuk mulai menggunakan GBF Calculator.
        </p>
    </div>

    @if ($errors->any())
        <div class="auth-errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="name" class="auth-label">Nama</label>

            <input
                id="name"
                class="auth-input"
                type="text"
                name="name"
                value="{{ old('name') }}"
                placeholder="Masukkan nama"
                required
                autofocus
                autocomplete="name"
            >
        </div>

        <div class="auth-field">
            <label for="email" class="auth-label">Email</label>

            <input
                id="email"
                class="auth-input"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Masukkan email"
                required
                autocomplete="username"
            >
        </div>

        <div class="auth-field">
            <label for="password" class="auth-label">Password</label>

            <input
                id="password"
                class="auth-input"
                type="password"
                name="password"
                placeholder="Buat password"
                required
                autocomplete="new-password"
            >
        </div>

        <div class="auth-field">
            <label for="password_confirmation" class="auth-label">
                Konfirmasi Password
            </label>

            <input
                id="password_confirmation"
                class="auth-input"
                type="password"
                name="password_confirmation"
                placeholder="Ulangi password"
                required
                autocomplete="new-password"
            >
        </div>

        <button type="submit" class="auth-submit">
            Daftar
        </button>
    </form>

    <div class="auth-footer">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="auth-link">
            Masuk
        </a>
    </div>

</div>

@endsection
