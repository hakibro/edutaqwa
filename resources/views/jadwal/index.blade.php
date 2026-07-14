<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Jadwal Pelajaran') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('jadwal.import.form') }}"
                    class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                    Import Excel
                </a>
                <a href="{{ route('jadwal.create') }}"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    + Tambah Jadwal
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            {{-- Tab: Daftar / Grid View --}}
            <div class="mb-4 flex gap-2">
                <a href="{{ route('jadwal.index') }}"
                    class="rounded-md px-4 py-2 text-sm font-semibold {{ !request('grid_kelas_id') ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Daftar</a>
                @foreach ($kelasList as $k)
                    <a href="{{ route('jadwal.index', ['grid_kelas_id' => $k->id]) }}"
                        class="rounded-md px-4 py-2 text-sm font-semibold {{ request('grid_kelas_id') == $k->id ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">{{ $k->nama }}</a>
                @endforeach
            </div>

            @if ($gridKelasId && $gridView)
                {{-- Grid View Per Kelas --}}
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Jadwal Kelas
                            {{ $kelasList->firstWhere('id', $gridKelasId)?->nama }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Hari</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Jam</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Mapel</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Guru</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($hariList as $hari)
                                        @php $jadwalHari = $gridView[$hari] ?? collect(); @endphp
                                        @if ($jadwalHari->isEmpty())
                                            <tr>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                    {{ $hari }}</td>
                                                <td colspan="3" class="px-4 py-3 text-sm text-gray-400 italic">Tidak
                                                    ada jadwal</td>
                                            </tr>
                                        @else
                                            @foreach ($jadwalHari as $j)
                                                <tr>
                                                    @if ($loop->first)
                                                        <td class="px-4 py-3 text-sm font-medium text-gray-900"
                                                            rowspan="{{ $jadwalHari->count() }}">{{ $hari }}
                                                        </td>
                                                    @endif
                                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                                        {{ $timetableLabels[$hari][$j->jam_ke] ?? 'Jam ' . $j->jam_ke }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                                        {{ $j->mapel->nama }}</td>
                                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                                        {{ $j->guru->nama }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Pilih
                                                kelas untuk melihat jadwal grid.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                {{-- Filter --}}
                <div class="mb-6 bg-white p-4 shadow-sm sm:rounded-lg">
                    <form method="GET" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kelas</label>
                            <select name="kelas_id"
                                class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Semua</option>
                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id }}"
                                        {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Hari</label>
                            <select name="hari"
                                class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Semua</option>
                                @foreach ($hariList as $h)
                                    <option value="{{ $h }}" {{ request('hari') == $h ? 'selected' : '' }}>
                                        {{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit"
                                class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">Filter</button>
                            <a href="{{ route('jadwal.index') }}"
                                class="ml-2 text-sm text-gray-600 hover:text-gray-900">Reset</a>
                        </div>
                    </form>
                </div>

                {{-- Daftar --}}
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Hari</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Jam</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Mapel</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Guru</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Kelas</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($jadwals as $j)
                                        <tr>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                {{ $j->hari }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                {{ $timetableLabels[$j->hari][$j->jam_ke] ?? 'Jam ' . $j->jam_ke }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                {{ $j->mapel->nama }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                {{ $j->guru->nama }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                {{ $j->kelas->nama }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                                <a href="{{ route('jadwal.edit', $j) }}"
                                                    class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                <form action="{{ route('jadwal.destroy', $j) }}" method="POST"
                                                    class="inline" onsubmit="return confirm('Hapus jadwal ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="ml-2 text-red-600 hover:text-red-900">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Belum
                                                ada data jadwal.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $jadwals->links() }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
