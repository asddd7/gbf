
@extends('layouts.app')

@section('title', 'Admin Dashboard - GBF Calculator')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">GBF CALCULATOR</span>
            <h1>Admin Dashboard</h1>
            <p>Selamat datang, {{ auth()->user()->name }}.</p>
        </div>
    </div>

    <div class="admin-panel">
        <div class="admin-panel-heading">
            <h2>Data Management</h2>
            <p>Kelola data yang digunakan oleh GBF Calculator.</p>
        </div>

        <div class="admin-panel-body">
            <a
                href="{{ route('admin.characters.index') }}"
                class="admin-button admin-button-primary"
            >
                Kelola Characters →
            </a>
            <a
                href="{{ route('admin.materials.index') }}"
                class="admin-button admin-button-secondary"
            >
                Kelola Materials →
            </a>
        </div>
    </div>
</div>
@endsection
