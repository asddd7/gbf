@extends('layouts.app')

@section('title', 'Edit Material - Admin GBF')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">MATERIAL MANAGEMENT</span>
            <h1>Edit Material</h1>
            <p>Perbarui {{ $material->name }} dan relasinya dengan tahap karakter.</p>
        </div>
    </div>

    <div class="admin-panel admin-form-panel">
        <form method="POST" action="{{ route('admin.materials.update', $material) }}">
            @csrf
            @method('PUT')
            @include('admin.materials._form')
        </form>
    </div>
</div>
@endsection
