@extends('layouts.app')

@section('title', 'Tambah Material - Admin GBF')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">MATERIAL MANAGEMENT</span>
            <h1>Tambah Material</h1>
            <p>Tambahkan material dan hubungkan dengan tahap karakter.</p>
        </div>
    </div>

    <div class="admin-panel admin-form-panel">
        <form method="POST" action="{{ route('admin.materials.store') }}"
        enctype="multipart/form-data">
            @csrf
            @include('admin.materials._form')
        </form>
    </div>
</div>
@endsection
