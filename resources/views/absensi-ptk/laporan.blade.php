<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Laporan Absensi Harian PTK') }}</h2>
        </div>
    </x-slot>

    <div class="py-4 sm:py-12 pb-36 sm:pb-16">
        <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            {{-- Filter — auto-apply via JS --}}
            <div class="mb-4 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-3 sm:p-4">
                    <form id="filterForm" method="GET" class="space-y-3">
                        {{-- Range quick buttons --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-2">Periode</label>
                            <div class="flex flex-wrap gap-1.5">
                                @php
                                    $ranges = [
                                        'today' => 'Hari Ini',
                                        'yesterday' => 'Kemarin',
                                        'week' => 'Minggu Ini',
                                        'month' => 'Bulan Ini',
                                        'all' => 'Semua',
                                        'date' => 'Tanggal',
                                    ];
                                @endphp
                                @foreach ($ranges as $key => $label)
                                    <button type="button" data-range="{{ $key }}"
                                        class="range-btn rounded-md px-2.5 py-1.5 text-xs font-medium border transition
                                        {{ $range === $key ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' }}">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="range" id="rangeInput" value="{{ $range }}">
                        </div>

                        {{-- Date picker — shown only for 'date' range --}}
                        <div id="datePickerRow" class="{{ $range === 'date' ? '' : 'hidden' }} w-full sm:w-auto">
                            <label class="block text-xs font-medium text-gray-500">Pilih Tanggal</label>
                            <input type="date" name="tanggal" value="{{ $tanggal }}"
                                class="mt-1 w-full sm:w-auto rounded-md border-gray-300 shadow-sm text-sm"
                                onchange="document.getElementById('filterForm').submit()">
                        </div>

                        {{-- Guru PTK --}}
                        <div class="w-full sm:w-auto">
                            <label class="block text-xs font-medium text-gray-500">Guru PTK</label>
                            <select name="guru_id"
                                class="mt-1 w-full sm:w-auto rounded-md border-gray-300 shadow-sm text-sm"
                                onchange="document.getElementById('filterForm').submit()">
                                <option value="">Semua Guru PTK</option>
                                @foreach ($gurus as $g)
                                    <option value="{{ $g->id }}" {{ $guruId == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                document.querySelectorAll('.range-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.getElementById('rangeInput').value = this.dataset.range;
                        const dateRow = document.getElementById('datePickerRow');
                        if (this.dataset.range === 'date') {
                            dateRow.classList.remove('hidden');
                        } else {
                            dateRow.classList.add('hidden');
                        }
                        document.getElementById('filterForm').submit();
                    });
                });
            </script>

            {{-- Summary — always shown --}}
            @if (empty($guruId) && count($summary) > 0)
                <div class="mb-4 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-3 sm:p-4">
                        @php
                            $summaryLabel = match ($range) {
                                'today' => 'Rekap Hari Ini (' . \Carbon\Carbon::today()->format('d/m/Y') . ')',
                                'yesterday' => 'Rekap Kemarin (' . \Carbon\Carbon::yesterday()->format('d/m/Y') . ')',
                                'week' => 'Rekap Minggu Ini (' .
                                    \Carbon\Carbon::now()->startOfWeek()->format('d/m') .
                                    ' - ' .
                                    \Carbon\Carbon::now()->endOfWeek()->format('d/m/Y') .
                                    ')',
                                'month' => 'Rekap Bulan ' . \Carbon\Carbon::now()->format('F Y'),
                                'date' => 'Rekap Tanggal ' . \Carbon\Carbon::parse($tanggal)->format('d/m/Y'),
                                default => 'Rekap Semua',
                            };
                        @endphp
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ $summaryLabel }}</h3>
                        <div class="overflow-x-auto -mx-3 sm:mx-0">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 sm:px-3 py-2 text-left font-medium text-gray-500">Guru</th>
                                        <th class="px-2 sm:px-3 py-2 text-center font-medium text-green-600">Tepat</th>
                                        <th class="px-2 sm:px-3 py-2 text-center font-medium text-yellow-600">Telat</th>
                                        <th class="px-2 sm:px-3 py-2 text-center font-medium text-orange-600">P.Awal
                                        </th>
                                        <th class="px-2 sm:px-3 py-2 text-center font-medium text-red-600">T.Absen</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($gurus as $g)
                                        @php $s = $summary[$g->id] ?? []; @endphp
                                        <tr>
                                            <td class="px-2 sm:px-3 py-2 text-gray-900 max-w-30 sm:max-w-none truncate">
                                                {{ $g->nama }}</td>
                                            <td class="px-2 sm:px-3 py-2 text-center text-green-700">
                                                {{ $s['tepat_waktu'] ?? 0 }}</td>
                                            <td class="px-2 sm:px-3 py-2 text-center text-yellow-700">
                                                {{ $s['terlambat'] ?? 0 }}
                                            </td>
                                            <td class="px-2 sm:px-3 py-2 text-center text-orange-700">
                                                {{ $s['pulang_awal'] ?? 0 }}</td>
                                            <td class="px-2 sm:px-3 py-2 text-center text-red-700">
                                                {{ $s['tidak_absen'] ?? 0 }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Detail table — only for single-day ranges --}}
            @if ($isSingleDay)
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-3 sm:p-6">
                        {{-- Desktop table --}}
                        <div class="hidden sm:block">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Guru</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Tanggal</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Check-in</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Check-out</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($absensis as $a)
                                        <tr>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                                {{ $a->guru->nama ?? '-' }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                                {{ $a->tanggal->format('d/m/Y') }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                                {{ $a->check_in?->format('H:i') ?? '-' }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                                {{ $a->check_out?->format('H:i') ?? '-' }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                                <span
                                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                            {{ $a->status === 'tepat_waktu' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $a->status === 'terlambat' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                            {{ $a->status === 'pulang_awal' ? 'bg-orange-100 text-orange-700' : '' }}
                                            {{ $a->status === 'tidak_absen' ? 'bg-red-100 text-red-700' : '' }}">
                                                    {{ str_replace('_', ' ', ucfirst($a->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">Tidak
                                                ada
                                                data.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile card list --}}
                        <div class="sm:hidden space-y-3">
                            @forelse ($absensis as $a)
                                <div class="rounded-lg border border-gray-200 p-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <span
                                            class="font-medium text-sm text-gray-900">{{ $a->guru->nama ?? '-' }}</span>
                                        <span
                                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                    {{ $a->status === 'tepat_waktu' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $a->status === 'terlambat' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $a->status === 'pulang_awal' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $a->status === 'tidak_absen' ? 'bg-red-100 text-red-700' : '' }}">
                                            {{ str_replace('_', ' ', ucfirst($a->status)) }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-500">
                                        <div>
                                            <span class="text-gray-400">Tanggal</span>
                                            <p class="text-gray-700">{{ $a->tanggal->format('d/m/Y') }}</p>
                                        </div>
                                        <div>
                                            <span class="text-gray-400">Check-in</span>
                                            <p class="text-gray-700">{{ $a->check_in?->format('H:i') ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-gray-400">Check-out</span>
                                            <p class="text-gray-700">{{ $a->check_out?->format('H:i') ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-sm text-gray-400 py-6">Tidak ada data.</p>
                            @endforelse
                        </div>

                        <div class="mt-4">
                            {{ $absensis->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
