@extends('layouts.app')

@section('title', 'Admin Dashboard - GBF Calculator')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold">
            Admin Dashboard
        </h1>

        <p class="mt-2">
            Selamat datang, {{ auth()->user()->name }}.
        </p>
    </div>
@endsection