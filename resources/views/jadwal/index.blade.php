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
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif
            @if (session('import_errors'))
                <div class="mb-4 rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                    <p class="font-medium mb-1">Beberapa masalah:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach (session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Tab: Daftar / Grid View --}}
            <div class="mb-4 flex gap-2 flex-wrap">
                <a href="{{ route('jadwal.index') }}"
                    class="rounded-md px-4 py-2 text-sm font-semibold {{ !request('grid_kelas_id') ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Daftar</a>
                @foreach ($kelasList as $k)
                    <a href="{{ route('jadwal.index', ['grid_kelas_id' => $k->id]) }}"
                        class="rounded-md px-4 py-2 text-sm font-semibold {{ request('grid_kelas_id') == $k->id ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">{{ $k->nama }}</a>
                @endforeach
            </div>

            @if ($gridKelasId && $gridView)
                {{-- Grid Editor --}}
                @php
                    $gridKelas = $kelasList->firstWhere('id', $gridKelasId);
                    $yayasanId =
                        auth()->user()->yayasan_id ??
                        \App\Models\Lembaga::find(auth()->user()->lembaga_id)?->yayasan_id;
                    $tahunAktif = \App\Models\TahunAjaran::where('yayasan_id', $yayasanId)
                        ->where('is_active', true)
                        ->first();
                    $gridTahunAjaranId = $tahunAktif?->id;
                    $maxJamKe = 0;
                    if ($timetableLabels) {
                        foreach ($hariList as $h) {
                            $maxJamKe = max($maxJamKe, count($timetableLabels[$h] ?? []));
                        }
                    }
                    $gridData = [];
                    foreach ($hariList as $hari) {
                        foreach ($gridView[$hari] ?? collect() as $j) {
                            $gridData[] = [
                                'hari' => $j->hari,
                                'jam_ke' => $j->jam_ke,
                                'mapel_id' => $j->mapel_id,
                                'guru_id' => $j->guru_id,
                                'mapel_nama' => $j->mapel->nama,
                                'guru_nama' => $j->guru->nama,
                            ];
                        }
                    }
                @endphp

                <script id="grid-data" type="application/json">{{ json_encode($gridData) }}</script>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg" x-data="jadwalGrid({{ $gridKelasId }}, {{ $gridTahunAjaranId ?? 'null' }}, {{ json_encode($hariList) }}, {{ json_encode($timetableLabels) }})"
                    x-init="init()">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Jadwal Kelas {{ $gridKelas?->nama }}
                                @if ($tahunAktif)
                                    <span
                                        class="text-sm font-normal text-gray-500 ml-2">({{ $tahunAktif->nama }})</span>
                                @endif
                            </h3>
                            <div class="flex gap-2">
                                <button type="button" @click="showCopyModal = true"
                                    class="rounded-md bg-amber-100 px-3 py-1.5 text-sm font-semibold text-amber-700 hover:bg-amber-200">
                                    Salin dari Kelas Lain</button>
                                <button type="button" @click="saveAll()" :disabled="!hasChanges || saving"
                                    class="rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                                    x-text="saving ? 'Menyimpan...' : 'Simpan Semua'"></button>
                            </div>
                        </div>

                        <div x-show="saveMsg" x-text="saveMsg"
                            class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800" x-cloak></div>
                        <div x-show="saveErrors.length" class="mb-4 rounded-md bg-yellow-50 p-3 text-sm text-yellow-800"
                            x-cloak>
                            <p class="font-medium mb-1">Beberapa error:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <template x-for="err in saveErrors" :key="err">
                                    <li x-text="err"></li>
                                </template>
                            </ul>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 w-24">
                                            Jam</th>
                                        @foreach ($hariList as $hari)
                                            <th
                                                class="px-2 py-2 text-center text-xs font-medium uppercase tracking-wider text-gray-500 min-w-[120px]">
                                                {{ $hari }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @for ($jam = 1; $jam <= $maxJamKe; $jam++)
                                        <tr>
                                            <td
                                                class="px-3 py-1 text-sm font-medium text-gray-700 whitespace-nowrap bg-gray-50">
                                                @php
                                                    $labelFound = false;
                                                    foreach ($hariList as $h) {
                                                        if (isset($timetableLabels[$h][$jam])) {
                                                            echo e($timetableLabels[$h][$jam]);
                                                            $labelFound = true;
                                                            break;
                                                        }
                                                    }
                                                    if (!$labelFound) {
                                                        echo 'Jam ' . $jam;
                                                    }
                                                @endphp
                                            </td>
                                            @foreach ($hariList as $hari)
                                                @php $isKbm = isset($timetableLabels[$hari][$jam]); @endphp
                                                <td class="px-1 py-1 text-center align-middle border text-sm"
                                                    :class="{
                                                        'bg-yellow-50': isDirty('{{ $hari }}',
                                                            {{ $jam }}),
                                                        'bg-gray-50': !{{ $isKbm ? 'true' : 'false' }},
                                                        'cursor-pointer hover:bg-blue-50': {{ $isKbm ? 'true' : 'false' }}
                                                    }"
                                                    @if ($isKbm) @click="openCell('{{ $hari }}', {{ $jam }}, $event)" @endif>
                                                    @if ($isKbm)
                                                        <div
                                                            class="min-h-[2rem] flex flex-col justify-center leading-tight">
                                                            <span class="font-medium text-gray-900"
                                                                x-text="cellState['{{ $hari }}']?.[{{ $jam }}]?.mapel_nama || 'Kosong'"
                                                                :class="cellState['{{ $hari }}']?.[
                                                                        {{ $jam }}] ? '' :
                                                                    'italic text-gray-400 text-xs'"></span>
                                                            <span class="text-xs text-gray-500"
                                                                x-show="cellState['{{ $hari }}']?.[{{ $jam }}]"
                                                                x-text="cellState['{{ $hari }}']?.[{{ $jam }}]?.guru_nama"></span>
                                                        </div>
                                                    @else
                                                        <span class="text-xs text-gray-400">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Single global dropdown popup --}}
                    <div x-show="editingCell" @click.outside="closeCell()"
                        :style="'position:fixed;left:' + popupX + 'px;top:' + popupY + 'px;z-index:50'"
                        class="bg-white border rounded-md shadow-lg p-2 min-w-[220px]" x-cloak>
                        <select class="w-full rounded-md border-gray-300 text-sm" x-ref="slotSelect"
                            @change="setCell($event.target.value)">
                            <option value="">-- Kosongkan --</option>
                            <template x-for="slot in slotOptions" :key="slot.mapel_id + '-' + slot.guru_id">
                                <option :value="slot.mapel_id + '|' + slot.guru_id"
                                    :selected="currentEditCell && cellState[currentEditCell.hari]?.[currentEditCell.jam_ke]
                                        ?.mapel_id === slot.mapel_id && cellState[currentEditCell.hari]?.[
                                            currentEditCell.jam_ke
                                        ]?.guru_id === slot.guru_id"
                                    x-text="slot.label"></option>
                            </template>
                        </select>
                        <button @click="closeCell()"
                            class="mt-1 w-full text-xs text-gray-500 hover:text-gray-700 py-1">Tutup</button>
                    </div>

                    {{-- Copy Modal --}}
                    <div x-show="showCopyModal"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-cloak>
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md"
                            @click.outside="showCopyModal = false">
                            <h4 class="text-lg font-semibold mb-4">Salin Jadwal dari Kelas Lain</h4>
                            <p class="text-sm text-gray-500 mb-4">Semua jadwal kelas ini akan ditimpa. Jadwal yang
                                bentrok akan dilewati.</p>
                            <form method="POST" action="{{ route('jadwal.copy') }}">
                                @csrf
                                <input type="hidden" name="target_kelas_id" value="{{ $gridKelasId }}">
                                <input type="hidden" name="tahun_ajaran_id" value="{{ $gridTahunAjaranId }}">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Sumber Kelas</label>
                                    <select name="source_kelas_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach ($kelasList->where('id', '!=', $gridKelasId) as $k)
<option value="{{ $k->id }}">{{ $k->nama }}</option>
@endforeach
                                    </select>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="showCopyModal = false"
                                        class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</button>
                                    <button type="submit"
                                        class="rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">Salin</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
@else
{{-- Filter --}}
                <div class="mb-6 bg-white p-4 shadow-sm sm:rounded-lg">
                    <form method="GET" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kelas</label>
                            <select name="kelas_id" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Semua</option>
                                @foreach ($kelasList as $k)
<option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Hari</label>
                            <select name="hari" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Semua</option>
                                @foreach ($hariList as $h)
<option value="{{ $h }}" {{ request('hari') == $h ? 'selected' : '' }}>{{ $h }}</option>
@endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">Filter</button>
                            <a href="{{ route('jadwal.index') }}" class="ml-2 text-sm text-gray-600 hover:text-gray-900">Reset</a>
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
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Hari</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jam</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Mapel</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Guru</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Kelas</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($jadwals as $j)
<tr>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $j->hari }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $timetableLabels[$j->hari][$j->jam_ke] ?? 'Jam ' . $j->jam_ke }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $j->mapel->nama }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $j->guru->nama }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $j->kelas->nama }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                                <a href="{{ route('jadwal.edit', $j) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                <form action="{{ route('jadwal.destroy', $j) }}" method="POST" class="inline" onsubmit="return confirm('Hapus jadwal ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="ml-2 text-red-600 hover:text-red-900">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada data jadwal.</td>
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

                                            @push('scripts')
                                                <script>
                                                    function jadwalGrid(kelasId, tahunAjaranId, hariList, timetableLabels) {
                                                        return {
                                                            kelasId,
                                                            tahunAjaranId,
                                                            hariList,
                                                            slotOptions: [],
                                                            cellState: {},
                                                            originalState: {},
                                                            editingCell: false,
                                                            currentEditCell: null,
                                                            popupX: 0,
                                                            popupY: 0,
                                                            hasChanges: false,
                                                            saving: false,
                                                            saveMsg: '',
                                                            saveErrors: [],
                                                            showCopyModal: false,

                                                            init() {
                                                                this.hariList.forEach(h => {
                                                                    this.cellState[h] = {};
                                                                    this.originalState[h] = {};
                                                                });
                                                                this.loadSlots();
                                                                this.loadInitialState();
                                                            },

                                                            loadInitialState() {
                                                                const el = document.getElementById('grid-data');
                                                                if (!el) return;
                                                                try {
                                                                    JSON.parse(el.textContent).forEach(item => {
                                                                        if (!this.cellState[item.hari]) this.cellState[item.hari] = {};
                                                                        if (!this.originalState[item.hari]) this.originalState[item.hari] = {};
                                                                        this.cellState[item.hari][item.jam_ke] = {
                                                                            mapel_id: item.mapel_id,
                                                                            guru_id: item.guru_id,
                                                                            mapel_nama: item.mapel_nama,
                                                                            guru_nama: item.guru_nama
                                                                        };
                                                                        this.originalState[item.hari][item.jam_ke] = JSON.parse(JSON.stringify(this.cellState[
                                                                            item.hari][item.jam_ke]));
                                                                    });
                                                                } catch (e) {
                                                                    console.error('Parse grid data error:', e);
                                                                }
                                                            },

                                                            async loadSlots() {
                                                                try {
                                                                    const res = await fetch(
                                                                        `{{ route('jadwal.slot-search') }}?kelas_id=${this.kelasId}&hari=Senin`);
                                                                    this.slotOptions = (await res.json()).slots || [];
                                                                } catch (e) {
                                                                    console.error('Load slots error:', e);
                                                                }
                                                            },

                                                            openCell(hari, jamKe, event) {
                                                                if (!this.cellState[hari]) this.cellState[hari] = {};
                                                                if (!this.originalState[hari]) this.originalState[hari] = {};
                                                                if (!this.cellState[hari][jamKe]) this.cellState[hari][jamKe] = null;
                                                                if (!this.originalState[hari][jamKe]) {
                                                                    this.originalState[hari][jamKe] = this.cellState[hari][jamKe] ? JSON.parse(JSON.stringify(this
                                                                        .cellState[hari][jamKe])) : null;
                                                                }
                                                                const rect = event.target.getBoundingClientRect();
                                                                this.popupX = rect.left;
                                                                this.popupY = rect.bottom + 4;
                                                                this.currentEditCell = {
                                                                    hari,
                                                                    jam_ke: jamKe
                                                                };
                                                                this.editingCell = true;
                                                                this.$nextTick(() => {
                                                                    const sel = this.$refs.slotSelect;
                                                                    if (sel) sel.focus();
                                                                });
                                                            },

                                                            closeCell() {
                                                                this.editingCell = false;
                                                                this.currentEditCell = null;
                                                            },

                                                            setCell(value) {
                                                                if (!this.currentEditCell) return;
                                                                const {
                                                                    hari,
                                                                    jam_ke
                                                                } = this.currentEditCell;
                                                                if (!value || value === '') {
                                                                    this.cellState[hari][jam_ke] = null;
                                                                } else {
                                                                    const [mapelId, guruId] = value.split('|');
                                                                    const slot = this.slotOptions.find(s => s.mapel_id == mapelId && s.guru_id == guruId);
                                                                    this.cellState[hari][jam_ke] = {
                                                                        mapel_id: parseInt(mapelId),
                                                                        guru_id: parseInt(guruId),
                                                                        mapel_nama: slot?.mapel_nama || '',
                                                                        guru_nama: slot?.guru_nama || ''
                                                                    };
                                                                }
                                                                this.updateDirty();
                                                                this.closeCell();
                                                            },

                                                            isDirty(hari, jamKe) {
                                                                const c = this.cellState[hari]?.[jamKe];
                                                                const o = this.originalState[hari]?.[jamKe];
                                                                if (!c && !o) return false;
                                                                if (!c || !o) return true;
                                                                return c.mapel_id !== o.mapel_id || c.guru_id !== o.guru_id;
                                                            },

                                                            updateDirty() {
                                                                this.hasChanges = false;
                                                                for (const h of this.hariList)
                                                                    for (const j in (this.cellState[h] || {}))
                                                                        if (this.isDirty(h, parseInt(j))) {
                                                                            this.hasChanges = true;
                                                                            return;
                                                                        }
                                                            },

                                                            async saveAll() {
                                                                if (!this.hasChanges || this.saving) return;
                                                                this.saving = true;
                                                                this.saveMsg = '';
                                                                this.saveErrors = [];
                                                                const entries = [];
                                                                for (const hari of this.hariList) {
                                                                    for (const jamKeStr in (this.cellState[hari] || {})) {
                                                                        const jamKe = parseInt(jamKeStr);
                                                                        if (!this.isDirty(hari, jamKe)) continue;
                                                                        const cell = this.cellState[hari][jamKe];
                                                                        entries.push({
                                                                            hari,
                                                                            jam_ke: jamKe,
                                                                            mapel_id: cell?.mapel_id ?? null,
                                                                            guru_id: cell?.guru_id ?? null
                                                                        });
                                                                    }
                                                                }
                                                                if (entries.length === 0) {
                                                                    this.saveMsg = 'Tidak ada perubahan.';
                                                                    this.saving = false;
                                                                    return;
                                                                }
                                                                try {
                                                                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                                                                    const res = await fetch('{{ route('jadwal.batch-store') }}', {
                                                                        method: 'POST',
                                                                        headers: {
                                                                            'Content-Type': 'application/json',
                                                                            'X-CSRF-TOKEN': csrf,
                                                                            'Accept': 'application/json'
                                                                        },
                                                                        body: JSON.stringify({
                                                                            kelas_id: this.kelasId,
                                                                            tahun_ajaran_id: this.tahunAjaranId,
                                                                            entries
                                                                        }),
                                                                    });
                                                                    const data = await res.json();
                                                                    if (data.success) {
                                                                        this.saveMsg = data.message || 'Jadwal tersimpan.';
                                                                        for (const hari of this.hariList) {
                                                                            if (!this.originalState[hari]) this.originalState[hari] = {};
                                                                            for (const jamKeStr in (this.cellState[hari] || {})) {
                                                                                const jamKe = parseInt(jamKeStr);
                                                                                this.originalState[hari][jamKe] = this.cellState[hari][jamKe] ? JSON.parse(JSON
                                                                                    .stringify(this.cellState[hari][jamKe])) : null;
                                                                            }
                                                                        }
                                                                        this.hasChanges = false;
                                                                        if (data.errors?.length) this.saveErrors = data.errors;
                                                                        setTimeout(() => window.location.reload(), 1500);
                                                                    } else {
                                                                        this.saveErrors = data.errors || ['Gagal menyimpan.'];
                                                                    }
                                                                } catch (e) {
                                                                    this.saveErrors = ['Network error: ' + (e.message || 'unknown')];
                                                                }
                                                                this.saving = false;
                                                            }
                                                        };
                                                    }
                                                </script>
                                            @endpush
                                            </x-app-layout>
                                            )
