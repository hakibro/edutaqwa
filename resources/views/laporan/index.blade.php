<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Laporan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('laporan.akademik') }}"
                    class="rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="rounded-full bg-indigo-100 p-2">
                            <x-heroicon-o-chart-bar class="h-6 w-6 text-indigo-600" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Laporan Akademik</h3>
                    </div>
                    <p class="text-sm text-gray-500">Rekap nilai per kelas & mapel, analisis ketuntasan.</p>
                </a>

                <a href="{{ route('laporan.kesiswaan') }}"
                    class="rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="rounded-full bg-red-100 p-2">
                            <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Laporan Kesiswaan</h3>
                    </div>
                    <p class="text-sm text-gray-500">Rekap siswa per kelas, pelanggaran & mutasi.</p>
                </a>

                <a href="{{ route('laporan.presensi') }}"
                    class="rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="rounded-full bg-green-100 p-2">
                            <x-heroicon-o-check-circle class="h-6 w-6 text-green-600" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Laporan Presensi</h3>
                    </div>
                    <p class="text-sm text-gray-500">Rekap kehadiran siswa per kelas & bulan.</p>
                </a>

                <a href="{{ route('laporan.absensi-ptk') }}"
                    class="rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="rounded-full bg-blue-100 p-2">
                            <x-heroicon-o-clock class="h-6 w-6 text-blue-600" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Laporan Absensi PTK</h3>
                    </div>
                    <p class="text-sm text-gray-500">Rekap absensi guru per bulan.</p>
                </a>

                <a href="{{ route('laporan.agenda-mengajar') }}"
                    class="rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="rounded-full bg-purple-100 p-2">
                            <x-heroicon-o-photo class="h-6 w-6 text-purple-600" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Laporan Agenda Mengajar</h3>
                    </div>
                    <p class="text-sm text-gray-500">Rekap agenda selfie guru per mapel.</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
