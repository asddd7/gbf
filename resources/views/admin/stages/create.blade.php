@extends('layouts.app')

@section('title', 'Tambah Tahap - Admin GBF')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">STAGE MANAGEMENT</span>
            <h1>Tambah Tahap</h1>
            <p>Tentukan character dan informasi tahap yang akan ditambahkan.</p>
        </div>
    </div>

    <div class="admin-panel admin-form-panel">
        <form method="POST" action="{{ route('admin.stages.store') }}">
            @csrf
            @include('admin.stages._form')
        </form>
    </div>
</div>
@endsection
