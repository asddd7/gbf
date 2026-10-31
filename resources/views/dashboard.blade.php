
@extends('layouts.app')

@section('title', 'Dashboard - Evoker & Eternals Calculator')
@section('page_title', 'Dashboard')

@section('content')

<div class="page-heading">
    <h1 class="page-title">
        Dashboard
    </h1>

    <p class="page-description">
        Pantau progres perjalananmu di Granblue Fantasy.
    </p>
</div>

<div class="dashboard-grid">

    <!-- WELCOME CARD -->
    <section class="dashboard-card">

        <h2>
            Selamat datang di GBF Calculator!
        </h2>

        <p>
            Mulai pantau progres material Grinding Granblue Fantasy
            Anda melalui Evoker Tracker dan Eternals Tracker.

            Kelola kebutuhan material, catat progres, dan rencanakan
            perjalanan Anda untuk mendapatkan karakter favorit.
        </p>

    </section>

</div>

@endsection
