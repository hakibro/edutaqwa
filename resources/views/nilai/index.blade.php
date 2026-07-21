<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Penilaian — Input Nilai') }}</h2>
            @if ($tahunAjaranAktif)
                <span class="text-sm text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full font-medium">
                    {{ $tahunAjaranAktif->nama }}
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            {{-- Card: Pilih Mapel, Kelas, Jenis Nilai untuk Input Baru --}}
            <div class="mb-6 bg-white p-5 shadow-sm sm:rounded-xl border border-slate-200/60">
                <h3 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Input Nilai Baru
                </h3>
                <form method="GET" action="{{ route('nilai.create') }}" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mapel</label>
                        <select name="mapel_id" required
                            class="mt-1 rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm w-44">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach ($mapels as $m)
                                <option value="{{ $m->id }}">{{ $m->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <select name="kelas_id" required
                            class="mt-1 rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm w-36">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($kelass as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jenis Nilai</label>
                        <select name="jenis_nilai_id" required
                            class="mt-1 rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm w-40">
                            <option value="">-- Pilih --</option>
                            @foreach ($jenisNilais as $jn)
                                <option value="{{ $jn->id }}">{{ $jn->nama }} ({{ $jn->bobot }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit"
                            class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition active:scale-95">
                            ➜ Input
                        </button>
                    </div>
                </form>
            </div>

            {{-- Riwayat Nilai --}}
            <div class="bg-white shadow-sm sm:rounded-xl border border-slate-200/60 overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-slate-700">Riwayat Nilai</h3>

                        {{-- Filter inline --}}
                        <form method="GET" class="flex flex-wrap gap-2 items-end">
                            <div>
                                <select name="mapel_id"
                                    class="rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm w-36">
                                    <option value="">Semua Mapel</option>
                                    @foreach ($mapels as $m)
                                        <option value="{{ $m->id }}"
                                            {{ request('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <select name="kelas_id"
                                    class="rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm w-32">
                                    <option value="">Semua Kelas</option>
                                    @foreach ($kelass as $k)
                                        <option value="{{ $k->id }}"
                                            {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <select name="jenis_nilai_id"
                                    class="rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm w-36">
                                    <option value="">Semua Jenis</option>
                                    @foreach ($jenisNilais as $jn)
                                        <option value="{{ $jn->id }}"
                                            {{ request('jenis_nilai_id') == $jn->id ? 'selected' : '' }}>
                                            {{ $jn->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit"
                                class="rounded-lg bg-slate-700 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-600 transition">Filter</button>
                            <a href="{{ route('nilai.index') }}"
                                class="text-sm text-slate-500 hover:text-slate-700 px-2">Reset</a>
                        </form>
                    </div>
                </div>

                <div class="p-5">
                    @if ($nilais->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead>
                                    <tr>
                                        <th
                                            class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                            Siswa</th>
                                        <th
                                            class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                            Mapel</th>
                                        <th
                                            class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                            Kelas</th>
                                        <th
                                            class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                            Jenis</th>
                                        <th
                                            class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                            TP</th>
                                        <th
                                            class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                            Nilai</th>
                                        <th
                                            class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                            Status</th>
                                        <th
                                            class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($nilais as $n)
                                        <tr class="hover:bg-slate-50/50 transition">
                                            <td class="px-3 py-2.5 text-sm font-medium text-slate-800">
                                                {{ $n->siswa?->nama ?? '-' }}</td>
                                            <td class="px-3 py-2.5 text-sm text-slate-600">{{ $n->mapel->nama }}</td>
                                            <td class="px-3 py-2.5 text-sm text-slate-600">{{ $n->kelas->nama }}</td>
                                            <td class="px-3 py-2.5 text-sm text-slate-600">{{ $n->jenisNilai->nama }}
                                            </td>
                                            <td class="px-3 py-2.5 text-sm text-slate-500">{{ $n->tp?->kode ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2.5">
                                                <span
                                                    class="text-sm font-bold {{ $n->nilai >= 75 ? 'text-emerald-600' : 'text-red-500' }}">
                                                    {{ $n->nilai }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2.5">
                                                @if ($n->is_finalized)
                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                        Final
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full bg-yellow-50 px-2.5 py-0.5 text-xs font-semibold text-yellow-700">
                                                        Draft
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2.5">
                                                @if (!$n->is_finalized)
                                                    <a href="{{ route('nilai.edit', ['mapel_id' => $n->mapel_id, 'kelas_id' => $n->kelas_id, 'jenis_nilai_id' => $n->jenis_nilai_id]) }}"
                                                        class="text-sm font-medium text-emerald-600 hover:text-emerald-800 transition">Edit</a>
                                                @else
                                                    <span class="text-sm text-slate-400">Terkunci</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $nilais->links() }}</div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto w-12 h-12 text-slate-300" fill="none" stroke="currentColor"
                                stroke-width="1" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <p class="mt-3 text-sm text-slate-500">Belum ada data nilai.</p>
                            <p class="text-xs text-slate-400">Pilih mapel & kelas di atas untuk mulai input nilai.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
