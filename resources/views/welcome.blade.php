<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MyDaruttaqwa - KBM Multi-Lembaga</title>
    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="antialiased bg-gradient-to-br from-emerald-50 to-teal-100 dark:from-gray-900 dark:to-gray-800 min-h-screen">
    <div class="relative">
        <nav class="flex items-center justify-between px-6 py-4 mx-auto max-w-7xl">
            <div class="flex items-center gap-2">
                <span class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">MyDaruttaqwa</span>
            </div>
            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}"
                            class="px-4 py-2 text-sm font-medium text-emerald-700 border border-emerald-300 rounded-lg hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-gray-800 transition">Log
                            in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">Register</a>
                        @endif
                    @endauth
                @endif
            </div>
        </nav>
        <main class="flex flex-col items-center justify-center px-6 pt-16 pb-24 mx-auto max-w-7xl">
            <div class="text-center max-w-3xl">
                <h1
                    class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl md:text-6xl dark:text-white">
                    <span class="text-emerald-600 dark:text-emerald-400">MyDaruttaqwa</span>
                    <br>KBM Multi-Lembaga
                </h1>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                    Platform manajemen Kegiatan Belajar Mengajar untuk yayasan dan lembaga pendidikan.
                    Kelola guru, siswa, jadwal, presensi, penilaian, dan rapor dalam satu ekosistem terpadu.
                </p>
                <div class="mt-10 flex items-center justify-center gap-4">
                    @guest
                        <a href="{{ route('login') }}"
                            class="px-6 py-3 text-base font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 shadow-lg transition">Mulai
                            Sekarang</a>
                        <a href="{{ route('register') }}"
                            class="px-6 py-3 text-base font-semibold text-emerald-700 border border-emerald-300 rounded-xl hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-gray-800 transition">Daftar</a>
                    @else
                        <a href="{{ url('/dashboard') }}"
                            class="px-6 py-3 text-base font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 shadow-lg transition">Ke
                            Dashboard</a>
                    @endguest
                </div>
            </div>
            <div class="mt-24 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 w-full">
                <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                    <div
                        class="w-10 h-10 flex items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 text-xl mb-4">
                        📚</div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Akademik</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Mapel, CP/TP/ATP, jadwal, dan penugasan
                        guru.</p>
                </div>
                <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                    <div
                        class="w-10 h-10 flex items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 text-xl mb-4">
                        ✅</div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Presensi & Absensi</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Presensi siswa, absensi PTK, dan agenda
                        selfie.</p>
                </div>
                <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                    <div
                        class="w-10 h-10 flex items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 text-xl mb-4">
                        📊</div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Penilaian & Rapor</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Nilai harian, PTS, PAS, dan cetak rapor.
                    </p>
                </div>
            </div>
        </main>
        <footer class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ date('Y') }} MyDaruttaqwa. All rights reserved.
        </footer>
    </div>
</body>

</html>
