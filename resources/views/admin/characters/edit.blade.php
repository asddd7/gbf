
@extends('layouts.app')

@section('title', 'Edit Character - Admin GBF')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-heading">
        <div>
            <span class="admin-eyebrow">CHARACTER MANAGEMENT</span>
            <h1>Edit Character</h1>
            <p>Perbarui informasi {{ $character->name }}.</p>
        </div>
    </div>

    <div class="admin-panel admin-form-panel">
        <form
            method="POST"
            action="{{ route('admin.characters.update', $character) }}"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PUT')

            @include('admin.characters._form')
        </form>
    </div>
</div>
@endsection
