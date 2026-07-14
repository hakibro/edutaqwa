<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Penilaian — Input Nilai') }}</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            {{-- Pilih Mapel, Kelas, Jenis Nilai --}}
            <div class="mb-6 bg-white p-4 shadow-sm sm:rounded-lg">
                <form method="GET" action="{{ route('nilai.create') }}" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mapel</label>
                        <select name="mapel_id" required
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Pilih --</option>
                            @foreach ($pengajaran->pluck('mapel')->unique('id') as $m)
                                <option value="{{ $m->id }}">{{ $m->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <select name="kelas_id" required
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Pilih --</option>
                            @foreach ($pengajaran->pluck('kelas')->filter()->unique('id') as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jenis Nilai</label>
                        <select name="jenis_nilai_id" required
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Pilih --</option>
                            @foreach ($jenisNilais as $jn)
                                <option value="{{ $jn->id }}">{{ $jn->nama }} (bobot: {{ $jn->bobot }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Input
                            Nilai</button>
                    </div>
                </form>
            </div>

            {{-- Filter riwayat nilai --}}
            <div class="mb-6 bg-white p-4 shadow-sm sm:rounded-lg">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mapel</label>
                        <select name="mapel_id"
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Semua</option>
                            @foreach ($mapels as $m)
                                <option value="{{ $m->id }}"
                                    {{ request('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <select name="kelas_id"
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Semua</option>
                            @foreach ($kelass as $k)
                                <option value="{{ $k->id }}"
                                    {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jenis Nilai</label>
                        <select name="jenis_nilai_id"
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Semua</option>
                            @foreach ($jenisNilais as $jn)
                                <option value="{{ $jn->id }}"
                                    {{ request('jenis_nilai_id') == $jn->id ? 'selected' : '' }}>{{ $jn->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit"
                            class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">Filter</button>
                        <a href="{{ route('nilai.index') }}"
                            class="ml-2 text-sm text-gray-600 hover:text-gray-900">Reset</a>
                    </div>
                </form>
            </div>

            {{-- Daftar Nilai --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($nilais->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Siswa</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Mapel</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Kelas</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Jenis</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            TP</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Nilai</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($nilais as $n)
                                        <tr>
                                            <td class="px-4 py-2 text-sm">{{ $n->siswa?->nama ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $n->mapel->nama }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $n->kelas->nama }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $n->jenisNilai->nama }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $n->tp?->kode ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm font-semibold">{{ $n->nilai }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                @if ($n->is_finalized)
                                                    <span
                                                        class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Final</span>
                                                @else
                                                    <span
                                                        class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800">Draft</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm">
                                                @if (!$n->is_finalized)
                                                    <a href="{{ route('nilai.edit', ['mapel_id' => $n->mapel_id, 'kelas_id' => $n->kelas_id, 'jenis_nilai_id' => $n->jenis_nilai_id]) }}"
                                                        class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                @else
                                                    <span class="text-gray-400">Terkunci</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $nilais->links() }}</div>
                    @else
                        <p class="text-center text-gray-500">Belum ada data nilai. Pilih mapel & kelas untuk mulai
                            input.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
