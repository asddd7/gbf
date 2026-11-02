
@extends('layouts.app')

@section('title', 'Masuk - GBF Calculator')

@push('styles')
    @vite('resources/css/auth.css')
@endpush

@section('content')

<div class="auth-page">

    <div class="auth-heading">
        <h1 class="auth-title">Selamat Datang</h1>
        <p class="auth-description">
            Masuk ke akun GBF Calculator kamu.
        </p>
    </div>

    @if (session('status'))
        <div class="auth-errors">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="auth-errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

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
                autofocus
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
                placeholder="Masukkan password"
                required
                autocomplete="current-password"
            >
        </div>

        <label class="auth-checkbox">
            <input type="checkbox" name="remember" value="1">
            <span>Ingat saya</span>
        </label>

        <button type="submit" class="auth-submit">
            Masuk
        </button>
    </form>

    <div class="auth-footer">
        Belum punya akun?
        <a href="{{ route('register') }}" class="auth-link">
            Daftar sekarang
        </a>
    </div>

</div>

@endsection
