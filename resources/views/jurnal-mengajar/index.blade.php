@php
    $user = auth()->user();
    $guru = \App\Models\Guru::find($user->guru_id);
    $lembagaId = $user->lembaga_id;
    $today = now()->toDateString();
    $todayName = now()->locale('id')->dayName;
    $todayFormatted = now()->locale('id')->translatedFormat('l, d F Y');

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

    // Jurnal counts per jadwal today
    $jurnalCountsToday = collect();
    $draftCountsToday = collect();
    if ($jadwalHariIni->isNotEmpty()) {
        $allJurnalsToday = \App\Models\JurnalMengajar::whereIn('jadwal_id', $jadwalHariIni->pluck('id'))
            ->whereDate('tanggal', $today)
            ->get();
        $jurnalCountsToday = $allJurnalsToday->where('is_draft', false)->groupBy('jadwal_id')->map->count();
        $draftCountsToday = $allJurnalsToday->where('is_draft', true)->groupBy('jadwal_id')->map->count();
    }

    // Group jadwal by kelas
    $jadwalGrouped = $jadwalHariIni->groupBy('kelas_id');
@endphp

<x-app-layout>
    <div x-data="{}" class="relative overflow-x-clip">
        <!-- decorative blur shapes -->
        <div
            class="absolute -top-10 -right-10 w-[400px] h-[400px] bg-violet-400/15 rounded-full blur-3xl pointer-events-none -z-10">
        </div>
        <div
            class="absolute bottom-20 -left-10 w-[300px] h-[300px] bg-emerald-400/10 rounded-full blur-3xl pointer-events-none -z-10">
        </div>

        <!-- ========== HEADER ========== -->
        <div class="px-4 pt-8 pb-2 max-w-lg mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Jurnal Mengajar</p>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $user->name }}
                    </h1>
                </div>
                <div
                    class="w-11 h-11 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm border-2 border-white shadow-sm">
                    {{ strtoupper(substr($user->name, 0, 1)) }}{{ ($space = strpos($user->name, ' ')) !== false ? strtoupper(substr($user->name, $space + 1, 1)) : '' }}
                </div>
            </div>
        </div>

        <main class="px-4 pt-2 space-y-4 max-w-lg mx-auto content-safe-bottom relative">

            {{-- Flash messages --}}
            @if (session('success'))
                <div
                    class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm font-medium text-emerald-800">
                    {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-2xl bg-red-50 border border-red-200 p-4 text-sm font-medium text-red-800">
                    {{ session('error') }}</div>
            @endif

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
                            <span class="text-sm font-semibold px-2 py-0.5 rounded-full {{ $jpBadgeClass }}">
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
                    <!-- always visible: kelas + summary -->
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
                                    $isJurnalComplete = $jurnalCount >= 1;
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
                                        <p class="text-sm text-slate-500 mt-0.5">{{ $info['jamLabel'] }}</p>
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
                                        <a href="{{ route('jurnal-mengajar.show', $j) }}"
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
                <span class="text-sm font-medium text-slate-400">Riwayat Jurnal</span>
                <div class="h-px flex-1 bg-slate-200"></div>
            </div>

            {{-- Filter tanggal --}}
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                    class="rounded-xl border-slate-200 bg-white text-sm py-2 px-3 shadow-sm focus:border-indigo-400 focus:ring-indigo-400"
                    onchange="this.form.submit()">
                @if (request('tanggal'))
                    <a href="{{ route('jurnal-mengajar.index') }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-800">× Reset</a>
                @endif
            </form>

            {{-- Riwayat Jurnal Cards --}}
            @if ($jurnals->isEmpty())
                <div class="rounded-2xl bg-slate-50/80 p-6 border border-slate-200/60 shadow-sm text-center">
                    <p class="text-sm font-medium text-slate-500">Belum ada jurnal mengajar.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-3">
                    @foreach ($jurnals as $j)
                        @php
                            $counts = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'terlambat' => 0];
                            foreach ($j->detailSiswas as $d) {
                                $counts[$d->status] = ($counts[$d->status] ?? 0) + 1;
                            }
                        @endphp
                        <div class="rounded-2xl bg-white p-4 border border-slate-200/60 shadow-sm">
                            <div class="flex items-start gap-3">
                                @if ($j->foto_path)
                                    <a href="{{ route('jurnal-mengajar.show', $j) }}" class="shrink-0">
                                        <img src="{{ Storage::url($j->foto_path) }}" alt="Selfie"
                                            class="w-16 h-16 rounded-xl object-cover">
                                    </a>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-semibold text-slate-900 truncate">
                                            {{ $j->jadwal?->mapel?->nama ?? ($j->metadata['mapel'] ?? '—') }}
                                        </h4>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            @if ($j->is_draft)
                                                <span
                                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-slate-100 text-slate-500">
                                                    Draft
                                                </span>
                                            @endif
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                                {{ $j->is_verified ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                {{ $j->is_verified ? 'Terverifikasi' : 'Pending' }}
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $j->kelas?->nama ?? ($j->metadata['kelas'] ?? '—') }} ·
                                        {{ $j->tanggal?->format('d/m/Y') ?? '—' }} ·
                                        Jam ke-{{ $j->metadata['jam_ke'] ?? ($j->jadwal?->jam_ke ?? '—') }} ·
                                        Pertemuan ke-{{ $j->pertemuan_ke }}
                                    </p>
                                    @if ($j->materi)
                                        <p class="text-xs text-slate-400 truncate mt-1">{{ $j->materi }}</p>
                                    @endif
                                    <div class="mt-2 flex items-center gap-3 text-xs font-medium">
                                        <span class="text-emerald-600">H:{{ $counts['hadir'] }}</span>
                                        <span class="text-amber-600">S:{{ $counts['sakit'] }}</span>
                                        <span class="text-orange-600">I:{{ $counts['izin'] }}</span>
                                        <span class="text-red-600">A:{{ $counts['alpha'] }}</span>
                                        @if ($counts['terlambat'] > 0)
                                            <span class="text-purple-600">T:{{ $counts['terlambat'] }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-3 flex items-center gap-2">
                                        <a href="{{ route('jurnal-mengajar.show', $j) }}"
                                            class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                                            Detail
                                        </a>
                                        @if (!$j->is_verified)
                                            <a href="{{ route('jurnal-mengajar.edit', $j) }}"
                                                class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                                                Edit
                                            </a>
                                            <form action="{{ route('jurnal-mengajar.destroy', $j) }}" method="POST"
                                                onsubmit="return confirm('Hapus jurnal ini?')" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $jurnals->withQueryString()->links() }}</div>
            @endif

        </main>
    </div>
</x-app-layout>
