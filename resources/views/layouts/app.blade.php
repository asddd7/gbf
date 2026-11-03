
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
    @php($isAdminArea = request()->routeIs('admin.*'))

    <div class="app-layout {{ $isAdminArea ? 'admin-layout' : '' }}">
        @if ($isAdminArea)
            <aside class="admin-sidebar" aria-label="Navigasi admin">
                <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand">
                    <span class="admin-brand-mark" aria-hidden="true">G</span>
                    <span>
                        <strong>GBF Calculator</strong>
                        <small>Admin Panel</small>
                    </span>
                </a>

                <span class="admin-sidebar-label">MENU</span>

                <nav class="admin-sidebar-navigation" aria-label="Menu admin">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        @class([
                            'admin-nav-link',
                            'is-active' => request()->routeIs('admin.dashboard'),
                        ])
                        @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M3.75 10.5 12 3.75l8.25 6.75v9a.75.75 0 0 1-.75.75h-5.25v-6h-4.5v6H4.5a.75.75 0 0 1-.75-.75v-9Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a
                        href="{{ route('admin.characters.index') }}"
                        @class([
                            'admin-nav-link',
                            'is-active' => request()->routeIs('admin.characters.*'),
                        ])
                        @if (request()->routeIs('admin.characters.*')) aria-current="page" @endif
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 3.75 19.5 7.5v9L12 20.25 4.5 16.5v-9L12 3.75Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            <path d="m4.875 7.688 7.125 3.562 7.125-3.562M12 11.25v9" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        </svg>
                        <span>Characters</span>
                    </a>

                    <a
                        href="{{ route('admin.stages.index') }}"
                        @class([
                            'admin-nav-link',
                            'is-active' => request()->routeIs('admin.stages.*'),
                        ])
                        @if (request()->routeIs('admin.stages.*')) aria-current="page" @endif
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5.25 5.25h13.5v4.5H5.25v-4.5ZM5.25 14.25h13.5v4.5H5.25v-4.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            <path d="M8.25 7.5h.008M8.25 16.5h.008" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                        <span>Tahap</span>
                    </a>

                    <a
                        href="{{ route('admin.progression.index') }}"
                        @class([
                            'admin-nav-link',
                            'is-active' => request()->routeIs('admin.progression.*'),
                        ])
                        @if (request()->routeIs('admin.progression.*')) aria-current="page" @endif
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5.25 5.25h13.5v13.5H5.25V5.25Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            <path d="M8.25 9h7.5M8.25 12h4.5M8.25 15h7.5M15.75 11.25l1.5 1.5 2.25-2.25" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Progression</span>
                    </a>

                    <a
                        href="{{ route('admin.materials.index') }}"
                        @class([
                            'admin-nav-link',
                            'is-active' => request()->routeIs('admin.materials.*'),
                        ])
                        @if (request()->routeIs('admin.materials.*')) aria-current="page" @endif
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5.25 4.5h13.5v15H5.25v-15Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            <path d="M8.25 8.25h7.5M8.25 12h7.5M8.25 15.75h4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                        <span>Materials</span>
                    </a>
                </nav>

                <div class="admin-sidebar-footer">
                    <a href="{{ route('admin.dashboard') }}" class="admin-back-link">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M14.25 5.25 7.5 12l6.75 6.75M8.25 12h12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Kembali ke aplikasi</span>
                    </a>
                </div>
            </aside>
        @endif

        <div class="app-content-shell {{ $isAdminArea ? 'admin-content-shell' : '' }}">
            <header class="app-header {{ $isAdminArea ? 'admin-topbar' : '' }}">
                @if ($isAdminArea)
                    <a href="{{ route('admin.dashboard') }}" class="admin-topbar-brand">
                        Admin Panel
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="brand-link">
                        GBF Calculator
                    </a>
                @endif

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
                            <a href="{{ route('register') }}" class="button button-primary">
                                Daftar
                            </a>
                        @endif
                    @endauth
                </div>
            </header>

            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')

</body>
</html>
