<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'EduTaqwa') }} — Dashboard Guru</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .content-safe-bottom {
            padding-bottom: calc(128px + env(safe-area-inset-bottom, 16px));
        }

        .nav-safe-bottom {
            padding-bottom: env(safe-area-inset-bottom, 8px);
            bottom: calc(env(safe-area-inset-bottom, 0px) + 12px);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen">
    {{ $slot }}

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
