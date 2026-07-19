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
                $absensiHariIni = null;

                if ($guru) {
                    $jadwalHariIni = \App\Models\Jadwal::with(['kelas', 'mapel'])
                        ->where('guru_id', $guru->id)
                        ->where('hari', $todayName)
                        ->get();

                    $absensiHariIni = \App\Models\AbsensiPtk::where('guru_id', $guru->id)
                        ->whereDate('tanggal', $today)
                        ->first();
                }

                // Total CP yang dibuat
                $totalCp = $guru ? \App\Models\Cp::where('guru_id', $guru->id)->count() : 0;

                // Jurnal mengajar hari ini
                $jurnalHariIni = $guru
                    ? \App\Models\JurnalMengajar::where('guru_id', $guru->id)->whereDate('tanggal', $today)->count()
                    : 0;

                // Jurnal mengajar minggu ini
                $jurnalMingguIni = $guru
                    ? \App\Models\JurnalMengajar::where('guru_id', $guru->id)
                        ->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()])
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
                    <p class="text-sm text-gray-500">Jurnal Hari Ini</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $jurnalHariIni }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Jurnal Minggu Ini</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $jurnalMingguIni }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">CP Dibuat</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalCp }}</p>
                </div>
            </div>

            <!-- Absensi Status -->
            <div class="mb-6">
                @if ($guru && $guru->isStruktural())
                    {{-- Guru struktural: wajib absen --}}
                    @if ($absensiHariIni)
                        @if ($absensiHariIni->check_out)
                            <div class="rounded-lg bg-green-50 border border-green-200 p-4">
                                <p class="text-green-700 font-medium">Absensi hari ini sudah lengkap.</p>
                                <p class="text-sm text-green-600">Check-in:
                                    {{ $absensiHariIni->check_in?->format('H:i') }}
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
                        <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-red-700 font-medium">Anda wajib absen hari ini!</p>
                                    <p class="text-sm text-red-600">Guru struktural harus check-in & check-out setiap
                                        hari.</p>
                                </div>
                                <a href="{{ route('absensi-ptk.index') }}"
                                    class="inline-block rounded-md bg-red-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-700">Absen
                                    Sekarang</a>
                            </div>
                        </div>
                    @endif
                @elseif ($guru && !$guru->isStruktural())
                    {{-- Guru non-struktural: opsional --}}
                    @if ($absensiHariIni)
                        @if ($absensiHariIni->check_out)
                            <div class="rounded-lg bg-green-50 border border-green-200 p-4">
                                <p class="text-green-700 font-medium">Absensi hari ini sudah lengkap.</p>
                                <p class="text-sm text-green-600">Check-in:
                                    {{ $absensiHariIni->check_in?->format('H:i') }}
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
                        <div class="rounded-lg bg-blue-50 border border-blue-200 p-4">
                            <div class="flex items-center justify-between">
                                <p class="text-blue-700 font-medium">Absensi harian (opsional)</p>
                                <a href="{{ route('absensi-ptk.index') }}"
                                    class="inline-block rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Absen
                                    Sekarang</a>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Akses Cepat -->
            <div class="mb-6">
                <div class="flex flex-wrap gap-2">
                    <a href="/jurnal-mengajar"
                        class="inline-flex items-center gap-1.5 rounded-md bg-teal-50 px-3 py-1.5 text-sm font-medium text-teal-700 hover:bg-teal-100">
                        📝 Jurnal Mengajar
                    </a>
                    <a href="{{ route('perangkat-ajar.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-md bg-purple-50 px-3 py-1.5 text-sm font-medium text-purple-700 hover:bg-purple-100">
                        📖 Perangkat Ajar
                    </a>
                    <a href="/jadwal-saya"
                        class="inline-flex items-center gap-1.5 rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-100">
                        📅 Jadwal Saya
                    </a>
                    <button type="button" onclick="window.pengumumanInstance?.showLatest()"
                        class="inline-flex items-center gap-1.5 rounded-md bg-yellow-50 px-3 py-1.5 text-sm font-medium text-yellow-700 hover:bg-yellow-100">
                        📢 Pengumuman
                    </button>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-md bg-green-50 px-3 py-1.5 text-sm font-medium text-green-400 cursor-not-allowed opacity-60">
                        📊 Input Nilai
                    </span>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-md bg-orange-50 px-3 py-1.5 text-sm font-medium text-orange-400 cursor-not-allowed opacity-60">
                        ⚠️ Catat Pelanggaran
                    </span>
                </div>
            </div>

            <!-- Jadwal Hari Ini & Jurnal -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Jadwal Hari Ini -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Jadwal Hari Ini ({{ $todayName }})</h3>
                        @if ($jadwalHariIni->isNotEmpty())
                            <div class="space-y-2">
                                @foreach ($jadwalHariIni as $j)
                                    @php
                                        $sudahJurnal = \App\Models\JurnalMengajar::where('jadwal_id', $j->id)
                                            ->whereDate('tanggal', $today)
                                            ->exists();
                                    @endphp
                                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $j->mapel?->nama ?? 'Mapel' }}</p>
                                            <p class="text-sm text-gray-500">Kelas {{ $j->kelas?->nama ?? '-' }} · Jam
                                                ke-{{ $j->jam_ke }}</p>
                                        </div>
                                        <div>
                                            @if ($sudahJurnal)
                                                <span
                                                    class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">Sudah
                                                    Jurnal</span>
                                            @else
                                                <a href="{{ route('jurnal-mengajar.create', ['jadwal_id' => $j->id]) }}"
                                                    class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-200">Buat
                                                    Jurnal</a>
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

                <!-- Jurnal Mengajar Terbaru -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Jurnal Mengajar Terbaru</h3>
                        @php
                            $jurnalTerbaru = $guru
                                ? \App\Models\JurnalMengajar::where('guru_id', $guru->id)
                                    ->with(['jadwal.kelas', 'jadwal.mapel'])
                                    ->latest()
                                    ->take(5)
                                    ->get()
                                : collect();
                        @endphp
                        @forelse ($jurnalTerbaru as $j)
                            <div class="border-b border-gray-100 pb-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <p class="font-medium text-gray-900">{{ $j->jadwal?->mapel?->nama ?? '-' }} ·
                                        {{ $j->jadwal?->kelas?->nama ?? '-' }}</p>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $j->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $j->is_verified ? 'Verified' : 'Pending' }}</span>
                                </div>
                                <p class="text-gray-500">{{ \Carbon\Carbon::parse($j->tanggal)->format('d M Y') }} —
                                    Pertemuan ke-{{ $j->pertemuan_ke }}
                                    @if ($j->foto_path)
                                        <span class="text-green-600 ml-1">📷</span>
                                    @endif
                                </p>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">Belum ada jurnal tercatat.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

{{-- Pengumuman Popup Modal --}}
<div x-data="pengumumanPopup()" x-show="show" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100">
    <div @click.away="dismiss()"
        class="relative mx-auto w-full max-w-2xl max-h-[80vh] overflow-y-auto rounded-xl bg-white p-6 shadow-2xl"
        x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900" x-text="data.judul"></h3>
            <button @click="dismiss()" class="text-gray-400 hover:text-gray-600">
                <x-heroicon-o-x-mark class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </x-heroicon-o-x-mark>
            </button>
        </div>
        <div class="pengumuman-content text-gray-700 leading-relaxed text-sm" x-html="data.konten"></div>
        <style>
            .pengumuman-content h2 {
                font-size: 1.35em;
                font-weight: 700;
                line-height: 1.3;
                margin: 0.6em 0;
            }

            .pengumuman-content h3 {
                font-size: 1.15em;
                font-weight: 700;
                line-height: 1.3;
                margin: 0.5em 0;
            }

            .pengumuman-content h4 {
                font-size: 1.05em;
                font-weight: 700;
                line-height: 1.3;
                margin: 0.5em 0;
            }

            .pengumuman-content p {
                line-height: 1.6;
                margin: 0.4em 0;
            }

            .pengumuman-content ul,
            .pengumuman-content ol {
                padding-left: 1.5em;
                margin: 0.4em 0;
            }

            .pengumuman-content ul {
                list-style: disc;
            }

            .pengumuman-content ol {
                list-style: decimal;
            }

            .pengumuman-content li {
                padding: 0.1em 0;
            }

            .pengumuman-content figure {
                margin: 0.8em 0;
                text-align: center;
            }

            .pengumuman-content figure img {
                max-width: 100%;
                height: auto;
                border-radius: 6px;
            }

            .pengumuman-content figcaption {
                font-size: 0.85em;
                color: #6b7280;
                margin-top: 0.3em;
            }

            .pengumuman-content blockquote {
                border-left: 4px solid #d1d5db;
                padding-left: 1em;
                font-style: italic;
                color: #4b5563;
                margin: 0.5em 0;
            }

            .pengumuman-content hr {
                margin: 1em 0;
                border: none;
                text-align: center;
            }

            .pengumuman-content hr:after {
                content: "***";
                font-size: 1.2em;
                letter-spacing: 0.2em;
                color: #9ca3af;
            }
        </style>
        <div class="mt-6 flex items-center justify-end gap-2">
            <span class="text-xs text-gray-400"
                x-text="data.published_at ? 'Dibuat ' + data.published_at : ''"></span>
            <button @click="dismiss()"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Tutup</button>
        </div>
    </div>
</div>

<script>
    function pengumumanPopup() {
        return {
            show: false,
            data: {},
            init() {
                window.pengumumanInstance = this;
                this.fetchLatest();
            },
            fetchLatest() {
                fetch('{{ route('pengumuman.popup') }}')
                    .then(r => r.json())
                    .then(res => {
                        if (res.has_pengumuman) {
                            this.data = res;
                            if (!sessionStorage.getItem('pengumuman_read_' + res.id)) {
                                this.show = true;
                            }
                        }
                    })
                    .catch(() => {});
            },
            showLatest() {
                fetch('{{ route('pengumuman.popup') }}')
                    .then(r => r.json())
                    .then(res => {
                        if (res.has_pengumuman) {
                            this.data = res;
                            this.show = true;
                        }
                    })
                    .catch(() => {});
            },
            dismiss() {
                if (this.data.id) {
                    sessionStorage.setItem('pengumuman_read_' + this.data.id, '1');
                    fetch('{{ route('pengumuman.mark-read', ['pengumuman' => '__ID__']) }}'.replace('__ID__', this.data
                        .id), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).catch(() => {});
                }
                this.show = false;
            }
        }
    }
</script>
