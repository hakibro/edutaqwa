<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard Guru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @php
                $user = auth()->user();
                $guru = \App\Models\Guru::find($user->guru_id);
                $lembagaId = $user->lembaga_id;
                $today = now()->toDateString();
                $todayName = now()->locale('id')->dayName;

                // Jadwal hari ini
                $jadwalHariIni = collect();
                $presensiHariIni = collect();
                $absensiHariIni = null;

                if ($guru) {
                    $jadwalHariIni = \App\Models\Jadwal::with(['kelas', 'mapel'])
                        ->where('guru_id', $guru->id)
                        ->where('hari', $todayName)
                        ->get();

                    $presensiHariIni = \App\Models\Presensi::whereIn('jadwal_id', $jadwalHariIni->pluck('id'))
                        ->whereDate('tanggal', $today)
                        ->get();

                    $absensiHariIni = \App\Models\AbsensiPtk::where('guru_id', $guru->id)
                        ->whereDate('tanggal', $today)
                        ->first();
                }

                // Total jadwal
                $totalJadwal = $guru ? \App\Models\Jadwal::where('guru_id', $guru->id)->count() : 0;

                // Total CP yang dibuat
                $totalCp = $guru ? \App\Models\Cp::where('guru_id', $guru->id)->count() : 0;

                // Agenda selfie minggu ini
                $agendaMingguIni = $guru
                    ? \App\Models\AgendaMengajar::where('guru_id', $guru->id)
                        ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                        ->count()
                    : 0;
            @endphp

            <!-- Stats Grid -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Jadwal Hari Ini</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $jadwalHariIni->count() }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Presensi Hari Ini</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $presensiHariIni->count() }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Total Jadwal</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalJadwal }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">CP Dibuat</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalCp }}</p>
                </div>
            </div>

            <!-- Absensi Status -->
            <div class="mb-6">
                @if ($absensiHariIni)
                    @if ($absensiHariIni->check_out)
                        <div class="rounded-lg bg-green-50 border border-green-200 p-4">
                            <p class="text-green-700 font-medium">Absensi hari ini sudah lengkap.</p>
                            <p class="text-sm text-green-600">Check-in: {{ $absensiHariIni->check_in?->format('H:i') }}
                                — Check-out: {{ $absensiHariIni->check_out?->format('H:i') }} — Status:
                                {{ $absensiHariIni->status }}</p>
                        </div>
                    @else
                        <div class="rounded-lg bg-blue-50 border border-blue-200 p-4">
                            <p class="text-blue-700 font-medium">Sudah check-in, jangan lupa check-out!</p>
                            <div class="mt-2">
                                <a href="{{ route('absensi-ptk.index') }}"
                                    class="text-blue-600 underline text-sm font-medium">Check-out sekarang →</a>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-yellow-700 font-medium">Belum absen hari ini.</p>
                            <a href="{{ route('absensi-ptk.index') }}"
                                class="inline-block rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Absen
                                Sekarang</a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Jadwal Hari Ini & Presensi -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Jadwal Hari Ini -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Jadwal Hari Ini ({{ $todayName }})</h3>
                        @if ($jadwalHariIni->isNotEmpty())
                            <div class="space-y-2">
                                @foreach ($jadwalHariIni as $j)
                                    @php
                                        $sudahPresensi = $presensiHariIni->where('jadwal_id', $j->id)->first();
                                    @endphp
                                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $j->mapel?->nama ?? 'Mapel' }}</p>
                                            <p class="text-sm text-gray-500">Kelas {{ $j->kelas?->nama ?? '-' }} · Jam
                                                ke-{{ $j->jam_ke }}</p>
                                        </div>
                                        <div>
                                            @if ($sudahPresensi)
                                                <span
                                                    class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">Sudah
                                                    Presensi</span>
                                            @else
                                                <a href="{{ route('presensi.create', ['jadwal_id' => $j->id]) }}"
                                                    class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-200">Input
                                                    Presensi</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">Tidak ada jadwal mengajar hari ini.</p>
                        @endif
                    </div>
                </div>

                <!-- Aktivitas Mengajar Terbaru -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Aktivitas Mengajar Terbaru</h3>
                        @php
                            $presensiTerbaru = $guru
                                ? \App\Models\Presensi::whereHas('jadwal', fn($q) => $q->where('guru_id', $guru->id))
                                    ->with(['jadwal.kelas', 'jadwal.mapel'])
                                    ->latest()
                                    ->take(5)
                                    ->get()
                                : collect();
                        @endphp
                        @forelse ($presensiTerbaru as $p)
                            <div class="border-b border-gray-100 pb-2 text-sm">
                                <p class="font-medium text-gray-900">{{ $p->jadwal?->mapel?->nama ?? '-' }} ·
                                    {{ $p->jadwal?->kelas?->nama ?? '-' }}</p>
                                <p class="text-gray-500">{{ $p->tanggal->format('d M Y') }} — Pertemuan
                                    ke-{{ $p->pertemuan_ke }}</p>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">Belum ada presensi tercatat.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Agenda Selfie Minggu Ini -->
            @if ($guru)
                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Agenda Selfie Minggu Ini</h3>
                            <a href="{{ route('agenda-mengajar.create') }}"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-800">+ Tambah Agenda</a>
                        </div>
                        @php
                            $agendaList = \App\Models\AgendaMengajar::where('guru_id', $guru->id)
                                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                                ->latest()
                                ->take(5)
                                ->get();
                        @endphp
                        @forelse ($agendaList as $a)
                            <div class="flex items-center gap-3 border-b border-gray-100 pb-2">
                                <span class="text-xs text-gray-500 w-24">{{ $a->created_at->format('d M H:i') }}</span>
                                <span
                                    class="text-sm text-gray-900 flex-1">{{ Str::limit($a->deskripsi ?? 'Agenda mengajar', 60) }}</span>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $a->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $a->is_verified ? 'Verified' : 'Pending' }}</span>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">Belum ada agenda minggu ini.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            <!-- Akses Cepat -->
            <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Akses Cepat</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <a href="{{ route('absensi-ptk.index') }}"
                            class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100 text-sm font-medium text-center">Absen
                            Harian</a>
                        <a href="{{ route('presensi.index') }}"
                            class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100 text-sm font-medium text-center">Presensi
                            Siswa</a>
                        <a href="{{ route('agenda-mengajar.index') }}"
                            class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100 text-sm font-medium text-center">Agenda
                            Selfie</a>
                        <a href="{{ route('nilai.index') }}"
                            class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100 text-sm font-medium text-center">Input
                            Nilai</a>
                        <a href="{{ route('cp.index') }}"
                            class="block rounded-md bg-green-50 p-3 text-green-700 hover:bg-green-100 text-sm font-medium text-center">CP/TP/ATP</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
