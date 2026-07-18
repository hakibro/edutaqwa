<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Mobile sidebar overlay -->
    <div x-data="{ sidebarOpen: false }" @keydown.window.escape="sidebarOpen = false">
        <button @click="sidebarOpen = true"
            class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-md text-gray-500 bg-white shadow hover:text-gray-700 focus:outline-none">
            <x-heroicon-o-bars-3 class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </x-heroicon-o-bars-3>
        </button>

        <div x-show="sidebarOpen" x-cloak class="lg:hidden fixed inset-0 z-40 flex">
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" @click="sidebarOpen = false"
                class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>

            <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                class="relative flex-1 flex flex-col max-w-xs w-full bg-white" @click.away="sidebarOpen = false">
                @include('layouts.sidebar-content')
            </div>
        </div>
    </div>
    @stack('scripts')

    <!-- Notifikasi Badge Polling -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function fetchNotifCount() {
                fetch('{{ route('notifikasi.count') }}')
                    .then(r => r.json())
                    .then(data => {
                        const badge = document.getElementById('notif-badge');
                        if (badge) {
                            if (data.count > 0) {
                                badge.classList.remove('hidden');
                                badge.textContent = data.count > 99 ? '99+' : data.count;
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                    })
                    .catch(() => {});
            }
            fetchNotifCount();
            setInterval(fetchNotifCount, 30000); // every 30s
        });
    </script>
</body>

</html>
