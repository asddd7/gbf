@extends('layouts.app')

@section('title', 'Edit Tahap - Admin GBF')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">STAGE MANAGEMENT</span>
            <h1>Edit Tahap</h1>
            <p>Perbarui tahap {{ $stage->name }} untuk {{ $stage->character?->name }}.</p>
        </div>
    </div>

    <div class="admin-panel admin-form-panel">
        <form method="POST" action="{{ route('admin.stages.update', $stage) }}">
            @csrf
            @method('PUT')
            @include('admin.stages._form')
        </form>
    </div>
</div>
@endsection
