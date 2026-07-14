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
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Laporan Akademik</h3>
                    </div>
                    <p class="text-sm text-gray-500">Rekap nilai per kelas & mapel, analisis ketuntasan.</p>
                </a>

                <a href="{{ route('laporan.kesiswaan') }}"
                    class="rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="rounded-full bg-red-100 p-2">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Laporan Kesiswaan</h3>
                    </div>
                    <p class="text-sm text-gray-500">Rekap siswa per kelas, pelanggaran & mutasi.</p>
                </a>

                <a href="{{ route('laporan.presensi') }}"
                    class="rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="rounded-full bg-green-100 p-2">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Laporan Presensi</h3>
                    </div>
                    <p class="text-sm text-gray-500">Rekap kehadiran siswa per kelas & bulan.</p>
                </a>

                <a href="{{ route('laporan.absensi-ptk') }}"
                    class="rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="rounded-full bg-blue-100 p-2">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Laporan Absensi PTK</h3>
                    </div>
                    <p class="text-sm text-gray-500">Rekap absensi guru per bulan.</p>
                </a>

                <a href="{{ route('laporan.agenda-mengajar') }}"
                    class="rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="rounded-full bg-purple-100 p-2">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Laporan Agenda Mengajar</h3>
                    </div>
                    <p class="text-sm text-gray-500">Rekap agenda selfie guru per mapel.</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
