<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Input Nilai ') . $jenisNilai->nama . ' — ' . $mapel->nama . ' — ' . $kelas->nama }}
            </h2>
            <a href="{{ route('nilai.index') }}"
                class="text-sm text-emerald-600 hover:text-emerald-800 font-medium">&larr; Kembali</a>
        </div>
    </x-slot>

    @php
        $isHarian = strtolower($jenisNilai->nama) === 'harian';
    @endphp

    <div class="py-8 pb-24">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($isHarian)
                {{-- Nilai Harian: satu form, dropdown TP opsional --}}
                @if ($tps->isEmpty())
                    <div class="bg-white shadow-sm sm:rounded-xl border border-slate-200/60 p-8 text-center">
                        <svg class="mx-auto w-12 h-12 text-slate-300" fill="none" stroke="currentColor"
                            stroke-width="1" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                        <p class="mt-3 font-medium text-slate-700">Belum ada TP untuk mapel ini</p>
                        <p class="text-sm text-slate-500 mt-1">Silakan buat TP & CP dulu di menu Perangkat Ajar.</p>
                        <a href="{{ route('perangkat-ajar.index') }}"
                            class="mt-4 inline-block text-sm font-medium text-emerald-600 hover:text-emerald-800">Ke
                            Perangkat Ajar &rarr;</a>
                    </div>
                @else
                    <div x-data="nilaiHarian()">
                        <form method="POST" action="{{ route('nilai.store') }}">
                            @csrf
                            <input type="hidden" name="mapel_id" value="{{ $mapel->id }}">
                            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                            <input type="hidden" name="jenis_nilai_id" value="{{ $jenisNilai->id }}">
                            <input type="hidden" name="tp_id" :value="selectedTp">

                            {{-- Sticky top bar --}}
                            <div
                                class="sticky top-0 z-30 bg-white/95 backdrop-blur-sm shadow-sm border border-slate-200/60 rounded-xl p-4 mb-6">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                                    {{-- Dropdown TP (opsional) --}}
                                    <div class="sm:col-span-5">
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih TP <span
                                                class="text-slate-400 font-normal">(opsional)</span></label>
                                        <select x-model="selectedTp"
                                            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                            <option value="">-- Semua TP (tanpa TP) --</option>
                                            @foreach ($tps as $tp)
                                                <option value="{{ $tp->id }}">{{ $tp->kode }} —
                                                    {{ Str::limit($tp->deskripsi, 55) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Keterangan --}}
                                    <div class="sm:col-span-4">
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">
                                            Keterangan <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="keterangan" required
                                            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                                            placeholder="Misal: Ulangan Harian 1">
                                    </div>

                                    {{-- Tombol --}}
                                    <div class="sm:col-span-3 flex gap-2">
                                        <button type="submit"
                                            class="flex-1 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition active:scale-95">
                                            Simpan
                                        </button>
                                        <button type="button" @click="accordionOpen = !accordionOpen"
                                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition"
                                            :class="{ 'bg-slate-100': accordionOpen }" title="Info CP / TP">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Accordion Info CP/TP --}}
                                <div x-show="accordionOpen" x-collapse.duration.200ms
                                    class="mt-3 border-t border-slate-100 pt-3">
                                    <template x-for="tp in tpData" :key="tp.id">
                                        <div x-show="tp.id == selectedTp" class="text-sm text-slate-600 space-y-1">
                                            <div class="flex flex-wrap gap-2 mb-1">
                                                <span
                                                    class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700"
                                                    x-text="tp.kode"></span>
                                                <span
                                                    class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700"
                                                    x-text="tp.cp_kode" x-show="tp.cp_kode"></span>
                                                <span
                                                    class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs text-slate-600"
                                                    x-text="'Fase ' + tp.fase" x-show="tp.fase"></span>
                                            </div>
                                            <p class="font-medium" x-text="tp.deskripsi"></p>
                                            <p x-show="tp.cp_deskripsi" class="text-xs text-slate-500">
                                                <span class="font-medium">CP:</span> <span
                                                    x-text="tp.cp_deskripsi"></span>
                                            </p>
                                        </div>
                                    </template>
                                    <div x-show="!selectedTp" class="text-sm text-slate-400 italic">Tidak ada TP
                                        dipilih.</div>
                                </div>
                            </div>

                            {{-- Toolbar: Set All / Clear Nilai --}}
                            <div class="flex items-center gap-2 mb-3 px-1">
                                <span class="text-xs font-semibold text-slate-600">Set All:</span>
                                <input type="number" x-model="bulkNilai" min="0" max="100" step="0.01"
                                    class="w-20 text-center rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                                    placeholder="0-100">
                                <button type="button" @click="fillAllNilai()"
                                    class="rounded-lg bg-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-300 transition">Terapkan
                                    ke Semua</button>
                                <button type="button" @click="clearAllNilai()"
                                    class="rounded-lg bg-red-100 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-200 transition">Kosongkan
                                    Semua</button>
                            </div>

                            {{-- Tabel siswa --}}
                            <div class="bg-white shadow-sm sm:rounded-xl border border-slate-200/60 overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 w-10">
                                                    #</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                    NIS</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                    Nama</th>
                                                <th
                                                    class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 w-28">
                                                    Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100" x-ref="nilaiTable">
                                            @foreach ($siswas as $i => $s)
                                                <tr class="hover:bg-slate-50/50 transition">
                                                    <td class="px-4 py-2.5 text-sm text-slate-500">{{ $i + 1 }}
                                                    </td>
                                                    <td class="px-4 py-2.5 text-sm text-slate-600">{{ $s->nis }}
                                                    </td>
                                                    <td class="px-4 py-2.5 text-sm font-medium text-slate-800">
                                                        {{ $s->nama }}</td>
                                                    <td class="px-4 py-2.5 text-center">
                                                        <input type="number" name="nilai[{{ $s->id }}]"
                                                            value="" min="0" max="100"
                                                            step="0.01"
                                                            class="w-24 text-center rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm nilai-input"
                                                            placeholder="0-100">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            @else
                {{-- PTS / PAS / UKK: 1 kolom per siswa --}}
                <div class="bg-white shadow-sm sm:rounded-xl border border-slate-200/60">
                    <div class="bg-linear-to-r from-emerald-50 to-teal-50/50 px-5 py-4 border-b border-slate-200/60">
                        <h3 class="font-semibold text-slate-700">{{ $jenisNilai->nama }} — {{ $mapel->nama }} —
                            {{ $kelas->nama }}</h3>
                    </div>
                    <div class="p-5">
                        <form method="POST" action="{{ route('nilai.store') }}">
                            @csrf
                            <input type="hidden" name="mapel_id" value="{{ $mapel->id }}">
                            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                            <input type="hidden" name="jenis_nilai_id" value="{{ $jenisNilai->id }}">

                            <div class="mb-4 flex flex-wrap gap-4 items-end">
                                <div class="flex-1 min-w-50">
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                                        Keterangan <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="keterangan" required
                                        class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                                        placeholder="Misal: PTS Ganjil 2025/2026">
                                </div>
                                <button type="submit"
                                    class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition active:scale-95">
                                    Simpan Semua
                                </button>
                            </div>

                            <div class="overflow-x-auto rounded-lg border border-slate-200">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 w-10">
                                                #</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                NIS</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                Nama</th>
                                            <th
                                                class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 w-28">
                                                Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($siswas as $i => $s)
                                            @php
                                                $existing = $existingNilai->get($s->id . '_');
                                            @endphp
                                            <tr class="hover:bg-slate-50/50 transition">
                                                <td class="px-4 py-2.5 text-sm text-slate-500">{{ $i + 1 }}
                                                </td>
                                                <td class="px-4 py-2.5 text-sm text-slate-600">{{ $s->nis }}
                                                </td>
                                                <td class="px-4 py-2.5 text-sm font-medium text-slate-800">
                                                    {{ $s->nama }}</td>
                                                <td class="px-4 py-2.5 text-center">
                                                    <input type="number" name="nilai[{{ $s->id }}]"
                                                        value="{{ $existing?->nilai ?? '' }}" min="0"
                                                        max="100" step="0.01"
                                                        class="w-24 text-center rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                                                        placeholder="0-100"
                                                        {{ $existing?->is_finalized ? 'disabled' : '' }}>
                                                    @if ($existing?->is_finalized)
                                                        <span
                                                            class="ml-1.5 inline-flex items-center gap-0.5 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Final</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($isHarian && $tps->isNotEmpty())
        <script>
            function nilaiHarian() {
                return {
                    selectedTp: '',
                    accordionOpen: false,
                    bulkNilai: '',
                    get tpData() {
                        return @json($tpData);
                    },
                    fillAllNilai() {
                        const inputs = this.$refs.nilaiTable.querySelectorAll('.nilai-input');
                        inputs.forEach(el => el.value = this.bulkNilai);
                    },
                    clearAllNilai() {
                        const inputs = this.$refs.nilaiTable.querySelectorAll('.nilai-input');
                        inputs.forEach(el => el.value = '');
                        this.bulkNilai = '';
                    }
                }
            }
        </script>
    @endif
</x-app-layout>
