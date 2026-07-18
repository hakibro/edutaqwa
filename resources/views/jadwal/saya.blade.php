<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Jadwal Saya') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @php
                $maxJam = 0;
                foreach ($hariList as $hari) {
                    $last = $gridView[$hari]?->last()?->jam_ke ?? 0;
                    if ($last > $maxJam) {
                        $maxJam = $last;
                    }
                }
                $todayName = now()->locale('id')->dayName;
            @endphp

            {{-- Info guru --}}
            <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-500">
                    {{ $guru->nama }} · {{ $guru->nuptk ?? '—' }}
                </p>
            </div>

            {{-- Legend waktu --}}
            @if (!empty($timetableLabels))
                <div class="mb-4 flex flex-wrap gap-2">
                    @foreach ($timetableLabels[$hariList[0]] ?? [] as $jam => $label)
                        <span class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600">
                            {{ $label }}
                        </span>
                    @endforeach
                </div>
            @endif

            {{-- Tabel jadwal (desktop scroll, mobile card) --}}
            {{-- Desktop: tabel --}}
            <div class="hidden sm:block overflow-x-auto rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Jam ke-</th>
                            @foreach ($hariList as $hari)
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 {{ $hari === $todayName ? 'bg-indigo-50 text-indigo-700' : '' }}">
                                    {{ $hari }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php $rowNum = 0; @endphp
                        @for ($jam = 1; $jam <= $maxJam; $jam++)
                            @php $rowNum++; @endphp
                            <tr class="{{ $rowNum % 2 === 0 ? 'bg-gray-50' : '' }}">
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-700">
                                    {{ $jam }}
                                    @if (!empty($timetableLabels[$hariList[0]][$jam] ?? null))
                                        <br><span
                                            class="text-xs text-gray-400">{{ Str::before($timetableLabels[$hariList[0]][$jam], '(') }}</span>
                                    @endif
                                </td>
                                @foreach ($hariList as $hari)
                                    @php
                                        $item = $gridView[$hari]?->firstWhere('jam_ke', $jam);
                                    @endphp
                                    <td
                                        class="px-4 py-3 text-center {{ $hari === $todayName ? 'bg-indigo-50/50' : '' }}">
                                        @if ($item)
                                            <div class="rounded-md bg-indigo-50 p-2">
                                                <p class="font-medium text-indigo-700">{{ $item->mapel->nama }}</p>
                                                <p class="text-xs text-gray-500">{{ $item->kelas->nama }}</p>
                                            </div>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- Mobile: card per hari --}}
            <div class="block sm:hidden space-y-4">
                @foreach ($hariList as $hari)
                    @php
                        $items = $gridView[$hari] ?? collect();
                        $isToday = $hari === $todayName;
                    @endphp
                    <div class="rounded-lg bg-white shadow-sm {{ $isToday ? 'ring-2 ring-indigo-400' : '' }}">
                        <div
                            class="flex items-center gap-2 border-b border-gray-100 px-4 py-3 {{ $isToday ? 'bg-indigo-50' : 'bg-gray-50' }}">
                            <span
                                class="text-sm font-semibold {{ $isToday ? 'text-indigo-700' : 'text-gray-700' }}">{{ $hari }}</span>
                            @if ($isToday)
                                <span
                                    class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-600">Hari
                                    ini</span>
                            @endif
                            @if ($items->isNotEmpty())
                                <span class="ml-auto text-xs text-gray-400">{{ $items->count() }} jam</span>
                            @endif
                        </div>
                        @if ($items->isNotEmpty())
                            <div class="divide-y divide-gray-100">
                                @foreach ($items as $item)
                                    <div class="flex items-center gap-3 px-4 py-3">
                                        <div
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">
                                            {{ $item->jam_ke }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $item->mapel->nama }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ $item->kelas->nama }}</p>
                                        </div>
                                        @if (!empty($timetableLabels[$hari][$item->jam_ke] ?? null))
                                            <span
                                                class="shrink-0 text-xs text-gray-400">{{ Str::before($timetableLabels[$hari][$item->jam_ke], '(') }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="px-4 py-4 text-center text-sm text-gray-400">Tidak ada jadwal</p>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Ringkasan --}}
            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $totalJam = 0;
                    foreach ($hariList as $hari) {
                        $totalJam += $gridView[$hari]?->count() ?? 0;
                    }
                    $kelasList = collect();
                    foreach ($hariList as $hari) {
                        foreach ($gridView[$hari] ?? [] as $j) {
                            $kelasList->push($j->kelas->nama);
                        }
                    }
                    $kelasUnik = $kelasList->unique()->values();
                @endphp
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Total Jam/Minggu</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalJam }}</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Hari Mengajar</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ collect($hariList)->filter(fn($h) => $gridView[$h]?->isNotEmpty())->count() }} hari
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Kelas</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $kelasUnik->count() }}</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Mapel</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ collect($hariList)->flatMap(fn($h) => $gridView[$h] ?? [])->pluck('mapel.nama')->unique()->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
