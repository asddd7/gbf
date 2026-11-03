@extends('layouts.app')

@section('title', 'Edit Progression - Admin GBF')

@push('styles')
    @vite('resources/css/admin.css')
@endpush

@section('content')
    @include('admin.progression._form')
@endsection
