<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Laporan Presensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="mb-6 flex flex-wrap items-end gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kelas</label>
                            <select name="kelas_id"
                                class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Pilih Kelas —</option>
                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id }}" @selected($kelasId == $k->id)>{{ $k->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Bulan</label>
                            <input type="month" name="bulan" value="{{ $bulan }}"
                                class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <button type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Tampilkan</button>
                        @if ($kelasId)
                            <a href="{{ route('laporan.export-presensi', ['kelas_id' => $kelasId, 'bulan' => $bulan]) }}"
                                class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Export
                                Excel</a>
                        @endif
                    </form>

                    @if ($kelasId && $statistik)
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Rekap Kehadiran Bulan
                            {{ \Carbon\Carbon::parse($bulan . '-01')->format('F Y') }}</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Nama Siswa</th>
                                        <th class="px-3 py-2 text-center font-semibold text-gray-700">Hadir</th>
                                        <th class="px-3 py-2 text-center font-semibold text-gray-700">Sakit</th>
                                        <th class="px-3 py-2 text-center font-semibold text-gray-700">Izin</th>
                                        <th class="px-3 py-2 text-center font-semibold text-gray-700">Alpha</th>
                                        <th class="px-3 py-2 text-center font-semibold text-gray-700">Terlambat</th>
                                        <th class="px-3 py-2 text-center font-semibold text-gray-700">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @php
                                        $siswaIds = $statistik->keys();
                                        $siswaList = \App\Models\Siswa::whereIn('id', $siswaIds)->get()->keyBy('id');
                                    @endphp
                                    @foreach ($statistik as $siswaId => $statuses)
                                        @php
                                            $siswa = $siswaList->get($siswaId);
                                            $hadir = $statuses->where('status', 'hadir')->sum('jumlah');
                                            $sakit = $statuses->where('status', 'sakit')->sum('jumlah');
                                            $izin = $statuses->where('status', 'izin')->sum('jumlah');
                                            $alpha = $statuses->where('status', 'alpha')->sum('jumlah');
                                            $terlambat = $statuses->where('status', 'terlambat')->sum('jumlah');
                                            $total = $hadir + $sakit + $izin + $alpha + $terlambat;
                                        @endphp
                                        <tr>
                                            <td class="px-3 py-2 font-medium text-gray-900">{{ $siswa->nama ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2 text-center text-green-700 font-semibold">
                                                {{ $hadir }}</td>
                                            <td class="px-3 py-2 text-center text-blue-700">{{ $sakit }}</td>
                                            <td class="px-3 py-2 text-center text-yellow-700">{{ $izin }}</td>
                                            <td class="px-3 py-2 text-center text-red-700 font-semibold">
                                                {{ $alpha }}</td>
                                            <td class="px-3 py-2 text-center text-orange-700">{{ $terlambat }}</td>
                                            <td class="px-3 py-2 text-center font-bold">{{ $total }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif ($kelasId)
                        <p class="text-gray-500">Belum ada presensi bulan ini.</p>
                    @else
                        <p class="text-gray-500">Pilih kelas & bulan, lalu klik Tampilkan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
