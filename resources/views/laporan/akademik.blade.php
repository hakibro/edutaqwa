<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Laporan Akademik — Rekap Nilai') }}
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
                            <label class="block text-sm font-medium text-gray-700">Mapel</label>
                            <select name="mapel_id"
                                class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Pilih Mapel —</option>
                                @foreach ($mapelList as $m)
                                    <option value="{{ $m->id }}" @selected($mapelId == $m->id)>{{ $m->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Tampilkan</button>
                        @if ($kelasId && $mapelId)
                            <a href="{{ route('laporan.export-akademik', ['kelas_id' => $kelasId, 'mapel_id' => $mapelId]) }}"
                                class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Export
                                Excel</a>
                        @endif
                    </form>

                    @if ($nilais->isNotEmpty())
                        @php
                            $rataNilai = $nilais->flatMap(fn($n) => $n->pluck('nilai'))->avg();
                        @endphp
                        <div class="mb-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">Rata-rata Kelas</p>
                                <p class="text-xl font-bold text-gray-900">{{ number_format($rataNilai, 2) }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">Total Siswa</p>
                                <p class="text-xl font-bold text-gray-900">{{ $nilais->count() }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">Nilai Tertinggi</p>
                                <p class="text-xl font-bold text-green-600">
                                    {{ number_format($nilais->flatMap(fn($n) => $n->pluck('nilai'))->max(), 0) }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">Nilai Terendah</p>
                                <p class="text-xl font-bold text-red-600">
                                    {{ number_format($nilais->flatMap(fn($n) => $n->pluck('nilai'))->min(), 0) }}</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">No</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Nama Siswa</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Jenis Nilai</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($nilais as $siswaId => $nilaiSiswa)
                                        @php $siswa = $nilaiSiswa->first()->siswa; @endphp
                                        @foreach ($nilaiSiswa as $nilai)
                                            <tr>
                                                <td class="px-3 py-2 text-gray-500">{{ $loop->parent->iteration }}</td>
                                                <td class="px-3 py-2 font-medium text-gray-900">
                                                    {{ $siswa->nama ?? '-' }}</td>
                                                <td class="px-3 py-2 text-gray-600">
                                                    {{ $nilai->jenisNilai->nama ?? '-' }}</td>
                                                <td class="px-3 py-2 font-bold">{{ number_format($nilai->nilai, 0) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif ($kelasId && $mapelId)
                        <p class="text-gray-500">Belum ada nilai final untuk kelas & mapel ini.</p>
                    @else
                        <p class="text-gray-500">Pilih kelas & mapel, lalu klik Tampilkan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
