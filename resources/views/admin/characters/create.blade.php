
@extends('layouts.app')

@section('title', 'Tambah Character - Admin GBF')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">CHARACTER MANAGEMENT</span>
            <h1>Tambah Character</h1>
            <p>Masukkan informasi Eternal atau Evoker baru.</p>
        </div>
    </div>

    <div class="admin-panel admin-form-panel">
        <form method="POST" action="{{ route('admin.characters.store') }}">
            @csrf
            @include('admin.characters._form')
        </form>
    </div>
</div>
@endsection
