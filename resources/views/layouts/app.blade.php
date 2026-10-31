
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'GBF Calculator')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body>

    <div class="app-layout">

        <!-- SIDEBAR -->
        <aside class="sidebar">

            <div>
                <!-- LOGO -->
                <div class="sidebar-brand">
                    <a href="{{ route('dashboard') }}" class="brand-link">
                        GBF Calculator
                    </a>
                </div>

                <!-- NAVIGATION -->
                <nav class="sidebar-navigation">

                    <a href="{{ route('dashboard') }}"
                       class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        Dashboard
                    </a>

                    <a href="#" class="nav-link">
                        Eternals Tracker
                    </a>

                    <a href="#" class="nav-link">
                        Evokers Tracker
                    </a>

                    <a href="#" class="nav-link">
                        Materials Inventory
                    </a>

                </nav>
            </div>

            <!-- SIDEBAR FOOTER -->
            <div class="sidebar-footer">
                GBF Calculator v1.0.0
            </div>

        </aside>

        <!-- MAIN WRAPPER -->
        <div class="main-wrapper">

            <!-- HEADER -->
            <header class="app-header">

                <div>
                    <h2 class="header-title">
                        @yield('page_title', 'Overview')
                    </h2>
                </div>

                <!-- HEADER RIGHT -->
                <div class="header-actions">

                    @auth

                        <span class="user-name">
                            {{ Auth::user()->name ?? Auth::user()->email }}
                        </span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button type="submit" class="button button-danger">
                                Logout
                            </button>
                        </form>

                    @else

                        <a href="{{ route('login') }}" class="auth-link">
                            Masuk
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="button button-primary">
                                Daftar
                            </a>
                        @endif

                    @endauth

                </div>

            </header>

            <!-- MAIN CONTENT -->
            <main class="main-content">
                @yield('content')
            </main>

        </div>

    </div>

    @stack('scripts')

</body>
</html>
