@php
    $user = auth()->user();
    $guru = \App\Models\Guru::find($user->guru_id);
    $lembagaId = $user->lembaga_id;
    $today = now()->toDateString();
    $todayName = now()->locale('id')->dayName;
    $todayFormatted = now()->locale('id')->translatedFormat('l, d F Y');

    // Jadwal hari ini
    $jadwalHariIni = collect();
    $absensiHariIni = null;

    if ($guru) {
        $jadwalHariIni = \App\Models\Jadwal::with(['kelas', 'mapel'])
            ->where('guru_id', $guru->id)
            ->where('hari', $todayName)
            ->orderBy('jam_ke')
            ->get();

        $absensiHariIni = \App\Models\AbsensiPtk::where('guru_id', $guru->id)->whereDate('tanggal', $today)->first();
    }

    // Total CP
    $totalCp = $guru ? \App\Models\Cp::where('guru_id', $guru->id)->count() : 0;

    // Jurnal hari ini & minggu ini
    $jurnalHariIni = $guru
        ? \App\Models\JurnalMengajar::where('guru_id', $guru->id)->whereDate('tanggal', $today)->count()
        : 0;
    $jurnalMingguIni = $guru
        ? \App\Models\JurnalMengajar::where('guru_id', $guru->id)
            ->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()])
            ->count()
        : 0;

    // JP stats
    $totalJpTarget = $guru ? (\App\Models\Jadwal::where('guru_id', $guru->id)->count() * 2 ?: 18) : 18;
    $jpTerlaksana = $jurnalMingguIni;
    $pctTerlaksana = $totalJpTarget > 0 ? round(($jpTerlaksana / $totalJpTarget) * 100) : 0;

    // Group jadwal by kelas — 1 card per kelas
    $jadwalGrouped = $jadwalHariIni->groupBy('kelas_id');
    $jurnalCountsToday = collect();
    $draftCountsToday = collect();
    if ($jadwalHariIni->isNotEmpty()) {
        $allJurnalsToday = \App\Models\JurnalMengajar::whereIn('jadwal_id', $jadwalHariIni->pluck('id'))
            ->whereDate('tanggal', $today)
            ->get();
        $jurnalCountsToday = $allJurnalsToday->where('is_draft', false)->groupBy('jadwal_id')->map->count();
        $draftCountsToday = $allJurnalsToday->where('is_draft', true)->groupBy('jadwal_id')->map->count();
    }

    // KBM items for jam labels & status
    $kbmItems = $guru ? \App\Models\AkademikSetting::getKbmItems($lembagaId, $todayName) : [];

    // Pre-map status & jam label per jadwal
    $jadwalMap = [];
    foreach ($jadwalHariIni as $j) {
        $slot = $kbmItems[$j->jam_ke] ?? null;
        $jamLabel = $slot ? $slot['jam_mulai'] . ' - ' . $slot['jam_selesai'] : 'Jam ke-' . $j->jam_ke;
        $jamKeLabel = $slot ? $slot['label'] : 'Jam ke-' . $j->jam_ke;
        $jadwalMap[$j->id] = [
            'status' => $j->statusSesi(),
            'label' => $j->labelStatusSesi(),
            'jamLabel' => $jamLabel,
            'jamKeLabel' => $jamKeLabel,
        ];
    }

    // Wali kelas
    $isWaliKelas = $guru && $guru->isWaliKelas();
    $kelasWali = $isWaliKelas ? $guru->kelasWali() : null;

    // Greeting
    $hour = now()->hour;
    $greeting = match (true) {
        $hour < 10 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };
@endphp

<x-app-layout>
    <div x-data="dashboardGuru()" class="relative overflow-x-clip">
        <!-- decorative blur shapes -->
        <div
            class="absolute -top-10 -right-10 w-[400px] h-[400px] bg-violet-400/15 rounded-full blur-3xl pointer-events-none -z-10">
        </div>
        <div
            class="absolute bottom-20 -left-10 w-[300px] h-[300px] bg-emerald-400/10 rounded-full blur-3xl pointer-events-none -z-10">
        </div>

        <!-- ========== GREETING HEADER ========== -->
        <div class="px-4 pt-8 pb-2 max-w-lg mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">{{ $greeting }},</p>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $user->name }}
                    </h1>
                </div>
                <div
                    class="w-11 h-11 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-sm border-2 border-white shadow-sm">
                    {{ strtoupper(substr($user->name, 0, 1)) }}{{ ($space = strpos($user->name, ' ')) !== false ? strtoupper(substr($user->name, $space + 1, 1)) : '' }}
                </div>
            </div>
        </div>

        <!-- ========== MAIN CONTENT ========== -->
        <main class="px-4 pt-2 space-y-4 max-w-lg mx-auto content-safe-bottom relative">

            <!-- JUDUL: Jadwal Hari Ini -->
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">Jadwal Hari Ini</h2>
                <span class="text-sm font-medium text-slate-500">{{ $todayFormatted }}</span>
            </div>

            @forelse ($jadwalGrouped as $kelasId => $jadwalKelas)
                @php
                    $kelas = $jadwalKelas->first()->kelas;
                    $totalJamKelas = $jadwalKelas->count();
                    $terisiKelas = $jadwalKelas->filter(fn($j) => ($jurnalCountsToday[$j->id] ?? 0) >= 1)->count();
                    $sesiStatuses = $jadwalKelas->map(fn($j) => $jadwalMap[$j->id]['status']);
                    $hasOngoing = $sesiStatuses->contains('sedang_berlangsung');
                    $allDone = $sesiStatuses->every(fn($s) => $s === 'selesai');
                    $cardStatus = $allDone ? 'selesai' : ($hasOngoing ? 'sedang_berlangsung' : 'belum_mulai');
                    $bgClass = match ($cardStatus) {
                        'sedang_berlangsung'
                            => 'bg-gradient-to-br from-emerald-50 to-white border-emerald-300/60 shadow-emerald-200/30',
                        'selesai' => 'bg-white/60 border-slate-200/50 shadow-slate-100',
                        default => 'bg-white/70 border-slate-200/50 shadow-slate-100',
                    };
                    $badgeClass = match ($cardStatus) {
                        'sedang_berlangsung' => 'bg-emerald-500 text-white shadow-sm shadow-emerald-200',
                        'selesai' => 'bg-slate-300/60 text-slate-500',
                        default => 'bg-slate-200 text-slate-600',
                    };
                    $jpBadgeClass = match ($cardStatus) {
                        'sedang_berlangsung' => 'bg-emerald-100 text-emerald-700',
                        'selesai' => 'text-slate-400 bg-slate-200/80',
                        default => 'text-slate-500 bg-slate-200/80',
                    };
                    $isExpanded = $cardStatus === 'sedang_berlangsung';
                @endphp
                <div class="rounded-2xl p-4 border shadow-sm relative overflow-hidden {{ $bgClass }}"
                    x-data="{ expanded: {{ $isExpanded ? 'true' : 'false' }} }">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-sm font-semibold px-2 py-0.5 rounded-full
                                {{ $jpBadgeClass }}">
                                {{ $totalJamKelas }} JP
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
                                {{ $cardStatus === 'selesai' ? 'Selesai' : ($hasOngoing ? 'Sedang Berlangsung' : 'Belum Mulai') }}
                            </span>
                        </div>
                        <button
                            class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 active:scale-95 transition"
                            @click="expanded = !expanded" :aria-label="expanded ? 'Minimize' : 'Maximize'">
                            <svg x-show="expanded" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                            </svg>
                            <svg x-show="!expanded" class="w-5 h-5" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </div>
                    <!-- selalu visible: kelas + 1 mapel -->
                    <div class="mt-1">
                        <h3 class="text-sm font-semibold text-slate-900">Kelas {{ $kelas?->nama ?? '-' }}</h3>
                        <p class="text-sm font-normal text-slate-500">
                            {{ $jadwalKelas->first()->mapel?->nama ?? 'Mapel' }}
                            · {{ $terisiKelas }}/{{ $totalJamKelas }} jurnal
                        </p>
                    </div>
                    <!-- expandable: daftar jam -->
                    <div x-collapse.duration.300 x-show="expanded">
                        <div class="mt-3 space-y-2">
                            @foreach ($jadwalKelas as $j)
                                @php
                                    $info = $jadwalMap[$j->id];
                                    $jurnalCount = $jurnalCountsToday[$j->id] ?? 0;
                                    $draftCount = $draftCountsToday[$j->id] ?? 0;
                                    $totalJam = 1;
                                    $isJurnalComplete = $jurnalCount >= $totalJam;
                                    $hasDraftOnly = !$isJurnalComplete && $draftCount > 0;
                                @endphp
                                <div
                                    class="flex items-center justify-between rounded-xl bg-white/80 p-3 border {{ $isJurnalComplete ? 'border-slate-100/60' : 'border-emerald-100/60' }}">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-sm font-semibold {{ $isJurnalComplete ? 'text-slate-500' : 'text-emerald-700' }}">{{ $info['jamKeLabel'] }}</span>
                                            <span
                                                class="text-xs font-semibold px-1.5 py-0.5 rounded-full
                                                {{ $info['status'] === 'sedang_berlangsung' ? 'bg-emerald-100 text-emerald-700' : ($info['status'] === 'belum_mulai' ? 'bg-slate-100 text-slate-500' : 'bg-slate-200/60 text-slate-400') }}">
                                                {{ $info['label'] }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-slate-500 mt-0.5">
                                            {{ $info['jamLabel'] }}
                                        </p>
                                    </div>
                                    @if ($hasDraftOnly)
                                        <a href="{{ route('jurnal-mengajar.create', ['jadwal_id' => $j->id]) }}"
                                            class="shrink-0 flex items-center justify-center gap-1 text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm active:scale-[0.98] transition bg-amber-500 text-white shadow-amber-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                                            </svg>
                                            Lanjutkan
                                        </a>
                                    @elseif (!$isJurnalComplete)
                                        <a href="{{ route('jurnal-mengajar.create', ['jadwal_id' => $j->id]) }}"
                                            class="shrink-0 flex items-center justify-center gap-1 text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm active:scale-[0.98] transition bg-emerald-600 text-white shadow-emerald-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                            Isi
                                        </a>
                                    @else
                                        <a href="{{ route('jurnal-mengajar.index', ['jadwal_id' => $j->id]) }}"
                                            class="shrink-0 flex items-center justify-center gap-1 bg-white text-emerald-700 text-sm font-semibold px-4 py-2.5 rounded-xl border border-emerald-200 shadow-sm active:scale-[0.98] transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            Lihat
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-slate-50/80 p-6 border border-slate-200/60 shadow-sm text-center">
                    <p class="text-sm font-medium text-slate-500">Tidak ada jadwal mengajar hari ini.</p>
                </div>
            @endforelse

            <!-- SEPARATOR -->
            <div class="flex items-center gap-3 pt-2">
                <div class="h-px flex-1 bg-slate-200"></div>
                <span class="text-sm font-medium text-slate-400">Ringkasan</span>
                <div class="h-px flex-1 bg-slate-200"></div>
            </div>

            <!-- KETERCAPAIAN MENGAJAR -->
            <div class="rounded-3xl bg-white p-6 shadow-sm shadow-slate-100 border border-slate-100/80">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800">Minggu
                        Ini</span>
                </div>
                <h3 class="text-base font-semibold text-slate-900 mb-4">Ketercapaian Mengajar</h3>

                <div class="flex items-center gap-5 mb-5">
                    <div class="relative w-20 h-20 shrink-0">
                        <svg class="w-20 h-20 -rotate-90" viewBox="0 0 72 72">
                            <circle cx="36" cy="36" r="30" fill="none" stroke="#e2e8f0"
                                stroke-width="6" />
                            <circle cx="36" cy="36" r="30" fill="none" stroke="#10b981"
                                stroke-width="6" stroke-dasharray="188.5"
                                stroke-dashoffset="{{ 188.5 - (188.5 * $pctTerlaksana) / 100 }}"
                                stroke-linecap="round" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-lg font-extrabold text-emerald-700">{{ $pctTerlaksana }}%</span>
                        </div>
                    </div>
                    <div class="flex-1 grid grid-cols-2 gap-x-4 gap-y-2">
                        <div>
                            <p class="text-lg font-bold text-slate-900 leading-none">{{ $jpTerlaksana }}</p>
                            <p class="text-sm font-medium text-slate-500">JP Terlaksana</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-slate-900 leading-none">{{ $totalJpTarget }}</p>
                            <p class="text-sm font-medium text-slate-500">JP Target</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-emerald-600 leading-none">{{ $totalCp }}</p>
                            <p class="text-sm font-medium text-slate-500">CP Tercapai</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-amber-600 leading-none">
                                {{ max(0, $totalJpTarget - $jpTerlaksana) }}</p>
                            <p class="text-sm font-medium text-slate-500">JP Kurang</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach ($jadwalGrouped->take(5) as $kelasId => $jadwalKelas)
                        @php
                            $kelas = $jadwalKelas->first()->kelas;
                            $jpKelas = $jadwalKelas
                                ->filter(
                                    fn($j) => \App\Models\JurnalMengajar::where('jadwal_id', $j->id)
                                        ->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()])
                                        ->exists(),
                                )
                                ->count();
                            $jpTargetKelas = $jadwalKelas->count();
                            $pctKelas = $jpTargetKelas > 0 ? round(($jpKelas / $jpTargetKelas) * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-700">Kelas {{ $kelas?->nama ?? '-' }}</span>
                                <span class="font-semibold text-slate-600">{{ $jpKelas }}/{{ $jpTargetKelas }}
                                    JP</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden mt-1">
                                <div class="h-full rounded-full transition {{ $pctKelas >= 70 ? 'bg-emerald-500' : 'bg-amber-500' }}"
                                    style="width: {{ min(100, $pctKelas) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                    @if ($jadwalGrouped->isEmpty())
                        <p class="text-sm text-slate-500 text-center py-2">Belum ada data jadwal.</p>
                    @endif
                </div>
            </div>

            @if ($isWaliKelas && $kelasWali)
                <!-- SEPARATOR -->
                <div class="flex items-center gap-3 pt-2">
                    <div class="h-px flex-1 bg-slate-200"></div>
                    <span class="text-sm font-medium text-slate-400">Wali Kelas</span>
                    <div class="h-px flex-1 bg-slate-200"></div>
                </div>

                <div class="rounded-3xl bg-white p-6 shadow-sm shadow-slate-100 border border-slate-100/80">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-2xl bg-teal-50 flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </div>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-teal-100 px-3 py-1 text-sm font-semibold text-teal-800">Wali
                            Kelas</span>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 mb-1">Perkembangan Kelas {{ $kelasWali->nama }}
                    </h3>
                    <p class="text-sm font-normal text-slate-500 leading-relaxed mb-4">Kelas {{ $kelasWali->nama }}
                    </p>
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm text-slate-500">Pantau perkembangan siswa di menu Wali Kelas.</p>
                        </div>
                        <a href="{{ route('guru.wali-kelas') }}"
                            class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 active:scale-95 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endif

            <div class="h-4"></div>
        </main>
    </div>

    <script>
        function dashboardGuru() {
            return {};
        }
    </script>
</x-app-layout>
