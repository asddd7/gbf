<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'GBF Calculator')</title>
    <!-- Pastikan Tailwind CSS sudah ter-load (Vite / CDN) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] min-h-screen flex">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white dark:bg-[#121212] border-r border-gray-200 dark:border-gray-800 fixed h-full hidden md:flex flex-col justify-between">
        <div>
            <!-- Logo / Brand -->
            <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-gray-800">
                <span class="font-bold text-lg text-indigo-600 dark:text-indigo-400">GBF Calculator</span>
            </div>
            <!-- Navigation Links -->
            <nav class="p-4 space-y-2">
                <a href="#" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <span>Dashboard</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition">
                    <span>Eternals Tracker</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition">
                    <span>Evokers Tracker</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition">
                    <span>Materials Inventory</span>
                </a>
            </nav>
        </div>
        <!-- Footer Sidebar (Opsional) -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-800 text-xs text-gray-400 text-center">
            v1.0.0
        </div>
    </aside>

    <!-- WRAPPER UNTUK HEADER & KONTEN UTAMA -->
    <div class="flex-1 flex flex-col md:pl-64">
        
        <!-- HEADER -->
        <header class="h-16 bg-white dark:bg-[#121212] border-b border-gray-200 dark:border-gray-800 flex items-center justify-between px-6 sticky top-0 z-10">
            <!-- Bagian Kiri Header (Bisa ditaruh pencarian atau toggle mobile menu) -->
            <div class="flex items-center">
                <h2 class="text-md font-semibold text-gray-700 dark:text-gray-300 hidden md:block">
                    @yield('page_title', 'Overview')
                </h2>
            </div>

            <!-- Bagian Kanan Header (Tombol Login) -->
            <div class="flex items-center space-x-4">
                @if(Route::has('login'))
                    @auth
                        <!-- Jika Sudah Login -->
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ Auth::user()->name }}</span>
                    @else
                        <!-- Jika Belum Login -->
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition">
                            Masuk / Login
                        </a>
                    @endauth
                @else
                    <!-- Fallback tombol jika route Laravel Auth belum disetup -->
                    <a href="/login" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition">
                        Masuk / Login
                    </a>
                @endif
            </div>
        </header>

        <!-- KONTEN UTAMA -->
        <main class="flex-1 p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
