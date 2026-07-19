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
        <!-- Desktop Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-64 pb-20 lg:pb-0">
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

    <!-- Bottom Navbar (Mobile only) -->
    @include('layouts.guru-bottom-nav')

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
            setInterval(fetchNotifCount, 30000);
        });
    </script>
</body>

</html>
