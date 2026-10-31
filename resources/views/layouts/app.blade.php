
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'GBF Calculator')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="min-h-screen flex bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-gray-100">

    <!-- SIDEBAR -->
    <aside class="fixed hidden md:flex flex-col justify-between w-64 h-full bg-white dark:bg-[#121212] border-r border-gray-200 dark:border-gray-800">

        <div>
            <!-- LOGO -->
            <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-gray-800">
                <a href="{{ route('dashboard') }}"
                   class="font-bold text-lg text-indigo-600 dark:text-indigo-400">
                    GBF Calculator
                </a>
            </div>

            <!-- NAVIGATION -->
            <nav class="p-4 space-y-2">

                <a href="{{ route('dashboard') }}"
                   class="flex items-center px-4 py-2 text-sm font-medium rounded-lg
                   {{ request()->routeIs('dashboard') 
                       ? 'bg-gray-100 text-indigo-600 dark:bg-gray-800 dark:text-indigo-400'
                       : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">
                    Dashboard
                </a>

                <a href="#"
                   class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition">
                    Eternals Tracker
                </a>

                <a href="#"
                   class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition">
                    Evokers Tracker
                </a>

                <a href="#"
                   class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition">
                    Materials Inventory
                </a>

            </nav>
        </div>

        <!-- SIDEBAR FOOTER -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-800 text-xs text-gray-400 text-center">
            GBF Calculator v1.0.0
        </div>

    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-1 flex flex-col min-h-screen md:pl-64">

        <!-- HEADER -->
        <header class="h-16 flex-shrink-0 sticky top-0 z-10 flex items-center justify-between px-6 bg-white dark:bg-[#121212] border-b border-gray-200 dark:border-gray-800">

            <!-- HEADER LEFT -->
            <div class="flex items-center">
                <h2 class="text-md font-semibold text-gray-700 dark:text-gray-300">
                    @yield('page_title', 'Overview')
                </h2>
            </div>

            <!-- HEADER RIGHT -->
            <div class="flex items-center space-x-4">

                @auth
                    <!-- JIKA SUDAH LOGIN -->
                    <span class="text-sm text-gray-600 dark:text-gray-300">
                        {{ Auth::user()->name ?? Auth::user()->email }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow transition">
                            Logout
                        </button>
                    </form>

                @else
                    <!-- JIKA BELUM LOGIN -->
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600 transition">
                        Masuk
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition">
                            Daftar
                        </a>
                    @endif

                @endauth

            </div>
        </header>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-6 lg:p-8">
            @yield('content')
        </main>

    </div>

    <!-- ADDITIONAL SCRIPTS -->
    @stack('scripts')

</body>
</html>
