<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">
                {{ __('Wali Kelas') }} — {{ $kelas->nama }}
            </h2>
            <form method="GET" class="flex gap-2">
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
                    <p class="text-sm text-gray-500">Jumlah Siswa</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ $siswas->count() }}</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Hadir Bulan Ini</p>
                    <p class="text-2xl font-bold text-green-600">{{ collect($presensiSummary)->sum('hadir') }}</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Tidak Hadir</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ collect($presensiSummary)->sum('sakit') + collect($presensiSummary)->sum('izin') + collect($presensiSummary)->sum('alpha') }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Alpha Bulan Ini</p>
                    <p class="text-2xl font-bold text-amber-600">{{ collect($presensiSummary)->sum('alpha') }}</p>
                </div>
            </div>

            <!-- Siswa Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Siswa — {{ $kelas->nama }}</h3>
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
                                    class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                    L/P</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Hadir</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Sakit</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Izin</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Alpha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($siswas as $i => $siswa)
                                <tr class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $i + 1 }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-700">
                                        {{ $siswa->nisn ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $siswa->nama }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-700">
                                        {{ $siswa->jenis_kelamin ?? '-' }}</td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-center text-sm text-green-700 font-semibold">
                                        {{ $presensiSummary[$siswa->id]['hadir'] ?? 0 }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-blue-700">
                                        {{ $presensiSummary[$siswa->id]['sakit'] ?? 0 }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-amber-700">
                                        {{ $presensiSummary[$siswa->id]['izin'] ?? 0 }}</td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-center text-sm text-red-700 font-semibold">
                                        {{ $presensiSummary[$siswa->id]['alpha'] ?? 0 }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada
                                        siswa di kelas ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('presensi.index', ['kelas_id' => $kelas->id]) }}"
                    class="flex items-center gap-3 rounded-lg bg-white p-4 shadow-sm hover:bg-indigo-50 transition">
                    <span class="text-2xl">📋</span>
                    <div>
                        <p class="font-semibold text-gray-800">Presensi Siswa</p>
                        <p class="text-xs text-gray-500">Lihat & kelola presensi kelas</p>
                    </div>
                </a>
                <a href="{{ route('kesiswaan.pelanggaran.create', ['kelas_id' => $kelas->id]) }}"
                    class="flex items-center gap-3 rounded-lg bg-white p-4 shadow-sm hover:bg-indigo-50 transition">
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <p class="font-semibold text-gray-800">Catat Pelanggaran</p>
                        <p class="text-xs text-gray-500">Laporkan pelanggaran siswa</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
