<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Laporan Kesiswaan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Rekap Siswa per Kelas -->
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Siswa per Kelas</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($siswaPerKelas as $kelas)
                            <div class="rounded-lg bg-gray-50 p-4">
                                <p class="text-sm text-gray-500">{{ $kelas->nama }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $kelas->riwayat_kelas_siswas_count }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Pelanggaran -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Catatan Pelanggaran</h3>
                        <div class="flex gap-3">
                            <form method="GET" class="flex items-end gap-3">
                                <div>
                                    <select name="kelas_id"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="">Semua Kelas</option>
                                        @foreach ($kelasList as $k)
                                            <option value="{{ $k->id }}" @selected($kelasId == $k->id)>
                                                {{ $k->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit"
                                    class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">Filter</button>
                            </form>
                            <a href="{{ route('laporan.export-kesiswaan', ['kelas_id' => $kelasId]) }}"
                                class="rounded-md bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700">Export
                                Excel</a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">No</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Siswa</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Kategori</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Poin</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Deskripsi</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Tanggal</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($pelanggarans as $p)
                                    <tr>
                                        <td class="px-3 py-2 text-gray-500">{{ $loop->iteration }}</td>
                                        <td class="px-3 py-2 font-medium text-gray-900">{{ $p->siswa->nama ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-gray-600">{{ $p->kategoriPelanggaran->nama ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <span
                                                class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">{{ $p->kategoriPelanggaran->poin ?? 0 }}</span>
                                        </td>
                                        <td class="px-3 py-2 text-gray-600">{{ Str::limit($p->deskripsi, 50) }}</td>
                                        <td class="px-3 py-2 text-gray-500">{{ $p->tanggal?->format('d M Y') }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $p->tindakan ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-4 text-center text-gray-500">Tidak ada
                                            pelanggaran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $pelanggarans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
