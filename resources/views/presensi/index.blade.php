<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Presensi Siswa') }}</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            {{-- Jadwal Hari Ini --}}
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Jadwal Mengajar Hari Ini</h3>
                    @if ($jadwalHariIni->isEmpty())
                        <p class="text-sm text-gray-500">Tidak ada jadwal mengajar hari ini.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($jadwalHariIni as $j)
                                <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4">
                                    <div>
                                        <span class="font-semibold text-gray-900">{{ $j->mapel->nama }}</span>
                                        <span class="ml-2 text-sm text-gray-500">{{ $j->kelas->nama }} | Jam
                                            ke-{{ $j->jam_ke }}</span>
                                    </div>
                                    <a href="{{ route('presensi.create', ['jadwal_id' => $j->id]) }}"
                                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                        Isi Presensi
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Riwayat Presensi --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Presensi</h3>

                    <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tanggal</label>
                            <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                                class="rounded-md border-gray-300 text-sm">
                        </div>
                        <button type="submit"
                            class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-500">
                            Filter
                        </button>
                        <a href="{{ route('presensi.index') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-300">
                            Reset
                        </a>
                    </form>

                    @if ($presensis->isEmpty())
                        <p class="text-sm text-gray-500">Belum ada data presensi.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="border-b bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tanggal</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Mapel</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Kelas</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Pertemuan</th>
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
                                            $total = array_sum($counts);
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3">{{ $p->tanggal->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3 font-medium">{{ $p->jadwal->mapel->nama }}</td>
                                            <td class="px-4 py-3">{{ $p->jadwal->kelas->nama }}</td>
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
                                                <a href="{{ route('presensi.edit', $p->id) }}"
                                                    class="ml-3 text-yellow-600 hover:text-yellow-900 text-xs font-medium">Edit</a>
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
