<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard Lembaga') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @php
                $lembagaId = auth()->user()->lembaga_id;
                $tahunAjaranAktif = \App\Models\TahunAjaran::where('yayasan_id', auth()->user()->yayasan_id)
                    ->where('is_active', true)
                    ->first();

                $totalGuru = \App\Models\Guru::where('lembaga_id', $lembagaId)->count();
                $totalSiswa = \App\Models\Siswa::where('lembaga_id', $lembagaId)->where('status', 'aktif')->count();
                $totalKelas = \App\Models\Kelas::where('lembaga_id', $lembagaId)->count();
                $guruPending = \App\Models\Guru::where('lembaga_id', $lembagaId)->where('is_approved', false)->count();

                // Statistik tambahan
                $totalMapel = \App\Models\Mapel::where('lembaga_id', $lembagaId)->count();
                $totalJadwal = \App\Models\Jadwal::where('lembaga_id', $lembagaId)
                    ->when($tahunAjaranAktif, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranAktif->id))
                    ->count();

                // Presensi hari ini
                $today = now()->toDateString();
                $presensiHariIni = \App\Models\Presensi::whereHas(
                    'jadwal',
                    fn($q) => $q->where('lembaga_id', $lembagaId),
                )
                    ->whereDate('tanggal', $today)
                    ->count();

                // Absensi PTK hari ini
                $absenHariIni = \App\Models\AbsensiPtk::where('lembaga_id', $lembagaId)
                    ->whereDate('tanggal', $today)
                    ->count();

                // Pelanggaran bulan ini
                $pelanggaranBulanIni = \App\Models\Pelanggaran::whereHas(
                    'siswa',
                    fn($q) => $q->where('lembaga_id', $lembagaId),
                )
                    ->whereMonth('tanggal', now()->month)
                    ->whereYear('tanggal', now()->year)
                    ->count();

                $role = auth()->user()->role;
            @endphp

            <!-- Stats Grid -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Total Guru</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalGuru }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Siswa Aktif</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalSiswa }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Total Kelas</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalKelas }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Mapel</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalMapel }}</p>
                </div>
            </div>

            <!-- Row 2 Stats -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Jadwal (TA Aktif)</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalJadwal }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Presensi Hari Ini</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $presensiHariIni }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Absen PTK Hari Ini</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $absenHariIni }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Pelanggaran (Bln Ini)</p>
                    <p class="text-3xl font-bold {{ $pelanggaranBulanIni > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $pelanggaranBulanIni }}</p>
                </div>
            </div>

            @if ($guruPending > 0 && in_array($role, ['admin_lembaga', 'kepala_lembaga']))
                <div class="mb-6 rounded-lg bg-yellow-50 border border-yellow-200 p-4">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-600" />
                        <p class="text-sm text-yellow-700">
                            <strong>{{ $guruPending }} guru</strong> menunggu approval.
                            <a href="{{ route('guru.approval') }}" class="underline font-medium">Review sekarang</a>
                        </p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Akses Cepat -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Akses Cepat</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ route('guru.index') }}"
                                class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100 text-sm font-medium">Kelola
                                Guru</a>
                            <a href="{{ route('siswa.index') }}"
                                class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100 text-sm font-medium">Kelola
                                Siswa</a>
                            <a href="{{ route('kelas.index') }}"
                                class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100 text-sm font-medium">Kelola
                                Kelas</a>
                            <a href="{{ route('jurusan.index') }}"
                                class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100 text-sm font-medium">Kelola
                                Jurusan</a>
                            <a href="{{ route('mapel.index') }}"
                                class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100 text-sm font-medium">Kelola
                                Mapel</a>
                            <a href="{{ route('jadwal.index') }}"
                                class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100 text-sm font-medium">Kelola
                                Jadwal</a>
                            @if ($role === 'kesiswaan')
                                <a href="{{ route('kesiswaan.pelanggaran.index') }}"
                                    class="block rounded-md bg-red-50 p-3 text-red-700 hover:bg-red-100 text-sm font-medium">Pelanggaran</a>
                                <a href="{{ route('kesiswaan.ekskul.index') }}"
                                    class="block rounded-md bg-green-50 p-3 text-green-700 hover:bg-green-100 text-sm font-medium">Ekskul</a>
                            @endif
                            @if ($role === 'kurikulum')
                                <a href="{{ route('pengajaran-mapel.index') }}"
                                    class="block rounded-md bg-green-50 p-3 text-green-700 hover:bg-green-100 text-sm font-medium">Penugasan
                                    Guru</a>
                                <a href="{{ route('akademik-settings.index') }}"
                                    class="block rounded-md bg-gray-50 p-3 text-gray-700 hover:bg-gray-100 text-sm font-medium">Akademik
                                    Settings</a>
                            @endif
                            @if ($role === 'kepala_lembaga')
                                <a href="{{ route('presensi.rekap') }}"
                                    class="block rounded-md bg-blue-50 p-3 text-blue-700 hover:bg-blue-100 text-sm font-medium">Rekap
                                    Presensi</a>
                                <a href="{{ route('absensi-ptk.laporan') }}"
                                    class="block rounded-md bg-blue-50 p-3 text-blue-700 hover:bg-blue-100 text-sm font-medium">Laporan
                                    Absensi</a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Aktivitas Terbaru -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Aktivitas Terbaru</h3>
                        <div class="space-y-2 text-sm">
                            @php
                                $logs = \App\Models\LogAktivita::where('lembaga_id', $lembagaId)
                                    ->with('user')
                                    ->latest()
                                    ->take(5)
                                    ->get();
                            @endphp
                            @forelse ($logs as $log)
                                <div class="border-b border-gray-100 pb-2">
                                    <span class="text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                                    <p class="text-gray-700">{{ $log->deskripsi }}</p>
                                </div>
                            @empty
                                <p class="text-gray-500">Belum ada aktivitas.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik Sederhana: Statistik per Kelas -->
            @if ($totalKelas > 0)
                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Siswa per Kelas</h3>
                        <div class="space-y-3">
                            @php
                                $kelasList = \App\Models\Kelas::where('lembaga_id', $lembagaId)
                                    ->withCount(['riwayatKelasSiswas' => fn($q) => $q->whereNull('tanggal_keluar')])
                                    ->get();
                            @endphp
                            @foreach ($kelasList as $kelas)
                                <div class="flex items-center gap-4">
                                    <span class="w-32 text-sm text-gray-700 truncate">{{ $kelas->nama }}</span>
                                    <div class="flex-1 bg-gray-200 rounded-full h-3">
                                        @php
                                            $max = max($kelasList->max('riwayat_kelas_siswas_count'), 1);
                                            $pct = ($kelas->riwayat_kelas_siswas_count / $max) * 100;
                                        @endphp
                                        <div class="bg-indigo-500 h-3 rounded-full"
                                            style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span
                                        class="text-sm text-gray-600 w-8 text-right">{{ $kelas->riwayat_kelas_siswas_count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
