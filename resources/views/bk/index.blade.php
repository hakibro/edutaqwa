<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">
                {{ __('Dashboard BK') }}
            </h2>
            <form method="GET" class="flex gap-2">
                <select name="kelas_id" onchange="this.form.submit()"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Semua Kelas --</option>
                    @foreach ($kelasList as $k)
                        <option value="{{ $k->id }}" @selected($kelasId == $k->id)>{{ $k->nama }}</option>
                    @endforeach
                </select>
                <select name="tahun_ajaran_id" onchange="this.form.submit()"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($tahunAjarans as $ta)
                        <option value="{{ $ta->id }}" @selected($tahunAjaranId == $ta->id)>{{ $ta->nama }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Stat Cards -->
            <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Total Pelanggaran</p>
                    <p class="text-2xl font-bold text-red-600">{{ $siswas->sum('total_pelanggaran') }}</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Siswa Bermasalah</p>
                    <p class="text-2xl font-bold text-amber-600">
                        {{ $siswas->where('total_pelanggaran', '>', 0)->count() }}</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Total Poin</p>
                    <p class="text-2xl font-bold text-red-600">{{ $siswas->sum('total_poin') }}</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Siswa Bersih</p>
                    <p class="text-2xl font-bold text-green-600">{{ $siswas->where('total_pelanggaran', 0)->count() }}
                    </p>
                </div>
            </div>

            <!-- Kategori Stats -->
            @if ($kategoriStats->isNotEmpty())
                <div class="mb-6 overflow-hidden rounded-lg bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-800">Pelanggaran per Kategori</h3>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach ($kategoriStats as $stat)
                            <div class="rounded-lg border border-gray-200 p-3 text-center">
                                <p class="text-sm text-gray-600">{{ $stat['nama'] }}</p>
                                <p class="text-xl font-bold text-red-600">{{ $stat['total'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Siswa Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Siswa Bimbingan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    No</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    NISN</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Nama</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Kelas</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Pelanggaran</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Total Poin</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($siswas as $i => $siswa)
                                <tr class="hover:bg-gray-50 {{ $siswa->total_pelanggaran > 0 ? 'bg-red-50' : '' }}">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $i + 1 }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-700">
                                        {{ $siswa->nisn ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $siswa->nama }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ $siswa->kelasAktif()->first()?->nama ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                                        @if ($siswa->total_pelanggaran > 0)
                                            <span
                                                class="rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">
                                                {{ $siswa->total_pelanggaran }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-center text-sm font-semibold {{ $siswa->total_poin > 0 ? 'text-red-700' : 'text-gray-500' }}">
                                        {{ $siswa->total_poin ?: '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                                        <a href="{{ route('pelanggaran.index', ['siswa_id' => $siswa->id]) }}"
                                            class="text-indigo-600 hover:text-indigo-900">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada
                                        data siswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
