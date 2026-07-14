<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard Orang Tua') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @php
                $user = auth()->user();
                $siswa = \App\Models\Siswa::find($user->siswa_id);
                $kelasSiswa = null;
                $jadwalHariIni = collect();
                $nilaiTerbaru = collect();

                if ($siswa) {
                    $kelasSiswa = \App\Models\RiwayatKelasSiswa::with('kelas')
                        ->where('siswa_id', $siswa->id)
                        ->whereNull('tanggal_keluar')
                        ->first();

                    if ($kelasSiswa) {
                        $hariIni = now()->locale('id')->dayName;
                        $jadwalHariIni = \App\Models\Jadwal::with(['mapel', 'guru'])
                            ->where('kelas_id', $kelasSiswa->kelas_id)
                            ->where('tahun_ajaran_id', $kelasSiswa->tahun_ajaran_id)
                            ->where('hari', $hariIni)
                            ->get();

                        $nilaiTerbaru = \App\Models\Nilai::where('siswa_id', $siswa->id)
                            ->where('is_finalized', true)
                            ->with(['mapel', 'jenisNilai'])
                            ->latest()
                            ->take(5)
                            ->get();
                    }

                    $totalPelanggaran = \App\Models\Pelanggaran::where('siswa_id', $siswa->id)->count();
                    $totalPoinPelanggaran = \App\Models\Pelanggaran::where('siswa_id', $siswa->id)
                        ->join(
                            'kategori_pelanggarans',
                            'pelanggarans.kategori_pelanggaran_id',
                            '=',
                            'kategori_pelanggarans.id',
                        )
                        ->sum('kategori_pelanggarans.poin');

                    // Presensi 7 hari terakhir
                    $presensiMingguan = \App\Models\DetailPresensi::where('siswa_id', $siswa->id)
                        ->whereHas('presensi', fn($q) => $q->whereDate('tanggal', '>=', now()->subDays(7)))
                        ->with(['presensi.jadwal.mapel'])
                        ->latest()
                        ->take(10)
                        ->get();
                }
            @endphp

            @if ($siswa && $kelasSiswa)
                <!-- Stats -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <p class="text-sm text-gray-500">Nama Siswa</p>
                        <p class="text-xl font-bold text-gray-900 truncate">{{ $siswa->nama }}</p>
                        <p class="text-xs text-gray-500 mt-1">NIS: {{ $siswa->nis ?? '-' }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <p class="text-sm text-gray-500">Kelas</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $kelasSiswa->kelas?->nama ?? '-' }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <p class="text-sm text-gray-500">Nilai Tersedia</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $nilaiTerbaru->count() }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <p class="text-sm text-gray-500">Poin Pelanggaran</p>
                        <p
                            class="text-3xl font-bold {{ $totalPoinPelanggaran > 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $totalPoinPelanggaran }}</p>
                    </div>
                </div>

                <!-- Jadwal & Presensi -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Jadwal Hari Ini -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-800">Jadwal Hari Ini
                                ({{ now()->locale('id')->dayName }})</h3>
                            @if ($jadwalHariIni->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach ($jadwalHariIni as $j)
                                        <div class="border-b border-gray-100 pb-2 text-sm">
                                            <p class="font-medium text-gray-900">Jam ke-{{ $j->jam_ke }}:
                                                {{ $j->mapel?->nama ?? '-' }}</p>
                                            <p class="text-gray-500">Guru: {{ $j->guru?->nama ?? '-' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-sm">Tidak ada jadwal hari ini.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Nilai Terbaru -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-800">Nilai Terbaru</h3>
                            @if ($nilaiTerbaru->isNotEmpty())
                                <div class="space-y-2 text-sm">
                                    @foreach ($nilaiTerbaru as $n)
                                        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $n->mapel?->nama ?? '-' }}</p>
                                                <p class="text-gray-500">{{ $n->jenisNilai?->nama ?? '-' }}</p>
                                            </div>
                                            <span
                                                class="font-bold text-gray-900">{{ number_format($n->nilai, 0) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-sm">Belum ada nilai.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Presensi 7 Hari Terakhir -->
                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Kehadiran 7 Hari Terakhir</h3>
                        @if ($presensiMingguan->isNotEmpty())
                            <div class="space-y-2 text-sm">
                                @foreach ($presensiMingguan as $d)
                                    @php
                                        $badgeColor = match ($d->status) {
                                            'hadir' => 'bg-green-100 text-green-700',
                                            'sakit' => 'bg-blue-100 text-blue-700',
                                            'izin' => 'bg-yellow-100 text-yellow-700',
                                            'alpha' => 'bg-red-100 text-red-700',
                                            'terlambat' => 'bg-orange-100 text-orange-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                        <div>
                                            <p class="font-medium text-gray-900">
                                                {{ $d->presensi->jadwal?->mapel?->nama ?? '-' }}</p>
                                            <p class="text-gray-500">{{ $d->presensi->tanggal->format('d M Y') }}</p>
                                        </div>
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $badgeColor }}">{{ ucfirst($d->status) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">Belum ada presensi minggu ini.</p>
                        @endif
                    </div>
                </div>

                @if ($totalPelanggaran > 0)
                    <div class="mt-6 rounded-lg bg-red-50 border border-red-200 p-4">
                        <p class="text-red-700 font-medium">Perhatian: {{ $siswa->nama }} memiliki
                            {{ $totalPelanggaran }} catatan pelanggaran (total {{ $totalPoinPelanggaran }} poin).</p>
                    </div>
                @endif
            @else
                <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-6">
                    <p class="text-yellow-700">Data siswa tidak ditemukan. Hubungi admin lembaga.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
