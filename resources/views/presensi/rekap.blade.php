<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Rekap Presensi') }}</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            {{-- Filter --}}
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="flex flex-wrap gap-3 items-end">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Bulan</label>
                            <input type="month" name="bulan" value="{{ $bulan }}"
                                class="rounded-md border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Kelas</label>
                            <select name="kelas_id" class="rounded-md border-gray-300 text-sm">
                                <option value="">Semua Kelas</option>
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Mapel</label>
                            <select name="mapel_id" class="rounded-md border-gray-300 text-sm">
                                <option value="">Semua Mapel</option>
                                @foreach ($mapels as $m)
                                    <option value="{{ $m->id }}" {{ $mapelId == $m->id ? 'selected' : '' }}>
                                        {{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            Filter
                        </button>
                        <a href="{{ route('presensi.rekap') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-300">
                            Reset
                        </a>
                    </form>
                </div>
            </div>

            {{-- Summary --}}
            <div class="mb-6 grid grid-cols-2 md:grid-cols-5 gap-3">
                <div class="rounded-lg bg-green-50 p-4 text-center">
                    <span class="block text-2xl font-bold text-green-700">{{ $summary['hadir'] }}</span>
                    <span class="text-xs text-green-600">Hadir</span>
                </div>
                <div class="rounded-lg bg-yellow-50 p-4 text-center">
                    <span class="block text-2xl font-bold text-yellow-700">{{ $summary['sakit'] }}</span>
                    <span class="text-xs text-yellow-600">Sakit</span>
                </div>
                <div class="rounded-lg bg-orange-50 p-4 text-center">
                    <span class="block text-2xl font-bold text-orange-700">{{ $summary['izin'] }}</span>
                    <span class="text-xs text-orange-600">Izin</span>
                </div>
                <div class="rounded-lg bg-red-50 p-4 text-center">
                    <span class="block text-2xl font-bold text-red-700">{{ $summary['alpha'] }}</span>
                    <span class="text-xs text-red-600">Alpha</span>
                </div>
                <div class="rounded-lg bg-purple-50 p-4 text-center">
                    <span class="block text-2xl font-bold text-purple-700">{{ $summary['terlambat'] }}</span>
                    <span class="text-xs text-purple-600">Terlambat</span>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Presensi</h3>

                    @if ($presensis->isEmpty())
                        <p class="text-sm text-gray-500">Belum ada data presensi untuk filter ini.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="border-b bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tanggal</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Mapel</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Kelas</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Guru</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">P</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Materi</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Rekap</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($presensis as $p)
                                        @php
                                            $counts = [
                                                'hadir' => 0,
                                                'sakit' => 0,
                                                'izin' => 0,
                                                'alpha' => 0,
                                                'terlambat' => 0,
                                            ];
                                            foreach ($p->detailPresensis as $d) {
                                                $counts[$d->status] = ($counts[$d->status] ?? 0) + 1;
                                            }
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3">{{ $p->tanggal->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3 font-medium">{{ $p->jadwal->mapel->nama }}</td>
                                            <td class="px-4 py-3">{{ $p->jadwal->kelas->nama }}</td>
                                            <td class="px-4 py-3 text-gray-600">{{ $p->jadwal->guru->nama }}</td>
                                            <td class="px-4 py-3">P-{{ $p->pertemuan_ke }}</td>
                                            <td class="px-4 py-3 max-w-xs truncate">{{ $p->materi ?? '-' }}</td>
                                            <td class="px-4 py-3 text-xs">
                                                <span class="text-green-600">H:{{ $counts['hadir'] }}</span>
                                                <span class="text-yellow-600 ml-1">S:{{ $counts['sakit'] }}</span>
                                                <span class="text-orange-600 ml-1">I:{{ $counts['izin'] }}</span>
                                                <span class="text-red-600 ml-1">A:{{ $counts['alpha'] }}</span>
                                                @if ($counts['terlambat'] > 0)
                                                    <span
                                                        class="text-purple-600 ml-1">T:{{ $counts['terlambat'] }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <a href="{{ route('presensi.show', $p->id) }}"
                                                    class="text-indigo-600 hover:text-indigo-900 text-xs font-medium">Detail</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $presensis->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
