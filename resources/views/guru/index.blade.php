<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Data Guru') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('guru.template') }}"
                    class="rounded-md bg-gray-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-400">
                    Download Template CSV
                </a>
                <button onclick="document.getElementById('importForm').classList.toggle('hidden')"
                    class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                    Import CSV
                </button>
                <a href="{{ route('guru.create') }}"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    + Tambah Guru
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

            {{-- Import Form --}}
            <div id="importForm" class="hidden mb-6 p-4 bg-gray-50 border rounded-lg">
                <form action="{{ route('guru.import') }}" method="POST" enctype="multipart/form-data"
                    class="flex items-end gap-4">
                    @csrf
                    @if (!auth()->user()->lembaga_id)
                        <div>
                            <x-input-label for="lembaga_id" value="Lembaga" />
                            <select id="lembaga_id" name="lembaga_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih --</option>
                                @foreach (\App\Models\Lembaga::where('is_active', true)->get() as $l)
                                    <option value="{{ $l->id }}">{{ $l->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <x-input-label for="file" value="File XLSX" />
                        <input type="file" id="file" name="file" accept=".xlsx"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            required>
                    </div>
                    <div>
                        <x-primary-button>Import</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Kode</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        NIY</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Nama</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Lembaga</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Jenis PTK</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Tugas Tambahan</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($gurus as $g)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-700">
                                            {{ $g->kode_guru_lembaga ?? '-' }}
                                            @if ($g->kode_guru_satminkal)
                                                <br><span
                                                    class="text-xs text-indigo-600">{{ $g->kode_guru_satminkal }}</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-700">
                                            {{ $g->niy ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $g->nama }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $g->lembaga->nama }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            <select
                                                class="inline-update-jenis-ptk block w-full min-w-[130px] rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                data-guru-id="{{ $g->id }}">
                                                <option value="">-- Pilih --</option>
                                                @foreach ($jenisPtks->where('lembaga_id', $g->lembaga_id) as $j)
                                                    <option value="{{ $j->id }}" @selected($g->jenis_ptk_id == $j->id)>
                                                        {{ $j->nama }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <div class="tugas-tambahan-inline" data-guru-id="{{ $g->id }}">
                                                @php $ttList = $g->tugasTambahans; @endphp
                                                @if ($ttList->isNotEmpty())
                                                    @foreach ($ttList as $tt)
                                                        <div class="tt-row flex flex-wrap items-center gap-1 mb-1">
                                                            <select
                                                                class="tt-jenis rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                style="min-width:120px">
                                                                <option value="">-- Pilih --</option>
                                                                <option value="Guru Mapel" @selected($tt->jenis == 'Guru Mapel')>
                                                                    Guru Mapel</option>
                                                                <option value="BK" @selected($tt->jenis == 'BK')>BK
                                                                </option>
                                                                <option value="Wali Kelas" @selected($tt->jenis == 'Wali Kelas')>
                                                                    Wali Kelas</option>
                                                                <option value="Pembina Ekskul"
                                                                    @selected($tt->jenis == 'Pembina Ekskul')>Pembina Ekskul</option>
                                                                <option value="Koordinator"
                                                                    @selected($tt->jenis == 'Koordinator')>Koordinator</option>
                                                            </select>
                                                            <input type="text" value="{{ $tt->keterangan }}"
                                                                class="tt-keterangan w-24 rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                placeholder="Ket">
                                                            <select
                                                                class="tt-ta rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                                <option value="">-- TA --</option>
                                                                @foreach ($tahunAjarans as $ta)
                                                                    <option value="{{ $ta->id }}"
                                                                        @selected($tt->tahun_ajaran_id == $ta->id)>
                                                                        {{ $ta->nama }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button"
                                                                class="tt-remove text-red-400 hover:text-red-600 text-xs leading-none">&times;</button>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <span class="tt-empty text-xs text-gray-400">-</span>
                                                @endif
                                                <button type="button"
                                                    class="tt-add text-xs text-indigo-600 hover:text-indigo-800 mt-1">+
                                                    Tambah</button>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span
                                                class="rounded-full px-2 py-1 text-xs font-semibold {{ $g->status_satminkal ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                                {{ $g->status_satminkal ? 'Satminkal' : 'Non-Satminkal' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span
                                                class="rounded-full px-2 py-1 text-xs font-semibold {{ $g->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $g->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <a href="{{ route('guru.edit', $g) }}"
                                                class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form action="{{ route('guru.destroy', $g) }}" method="POST"
                                                class="inline" onsubmit="return confirm('Hapus guru ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="ml-2 text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada
                                            data guru.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $gurus->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    // Inline update: debounce helper
    function debounce(fn, ms) {
        let t;
        return (...a) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...a), ms)
        };
    }

    // Jenis PTK dropdown change
    document.querySelectorAll('.inline-update-jenis-ptk').forEach(el => {
        el.addEventListener('change', function() {
            const guruId = this.dataset.guruId;
            fetch(`/guru/${guruId}/inline-update`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    field: 'jenis_ptk_id',
                    value: this.value
                })
            }).then(r => r.json()).then(d => {
                if (!d.success) alert('Gagal update Jenis PTK');
            });
        });
    });

    // Tugas Tambahan: save state per guru
    function saveTugasTambahan(container) {
        const guruId = container.dataset.guruId;
        const rows = container.querySelectorAll('.tt-row');
        const tugasTambahan = [];
        rows.forEach(row => {
            const jenis = row.querySelector('.tt-jenis')?.value || '';
            const keterangan = row.querySelector('.tt-keterangan')?.value || '';
            const ta = row.querySelector('.tt-ta')?.value || '';
            if (jenis) {
                tugasTambahan.push({
                    jenis,
                    keterangan,
                    tahun_ajaran_id: ta
                });
            }
        });
        fetch(`/guru/${guruId}/inline-update`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                field: 'tugas_tambahan',
                tugas_tambahan: tugasTambahan
            })
        }).then(r => r.json()).then(d => {
            if (!d.success) alert('Gagal update Tugas Tambahan');
        });
    }

    const debouncedSaveTT = debounce(saveTugasTambahan, 500);

    // Delegated events for tugas tambahan
    document.querySelectorAll('.tugas-tambahan-inline').forEach(container => {
        // Add row
        container.querySelector('.tt-add')?.addEventListener('click', function() {
            const empty = container.querySelector('.tt-empty');
            if (empty) empty.remove();
            const div = document.createElement('div');
            div.className = 'tt-row flex flex-wrap items-center gap-1 mb-1';
            div.innerHTML = `
                <select class="tt-jenis rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" style="min-width:120px">
                    <option value="">-- Pilih --</option>
                    <option value="Guru Mapel">Guru Mapel</option>
                    <option value="BK">BK</option>
                    <option value="Wali Kelas">Wali Kelas</option>
                    <option value="Pembina Ekskul">Pembina Ekskul</option>
                    <option value="Koordinator">Koordinator</option>
                </select>
                <input type="text" value="" class="tt-keterangan w-24 rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ket">
                <select class="tt-ta rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- TA --</option>
                    @foreach ($tahunAjarans as $ta)
                        <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                    @endforeach
                </select>
                <button type="button" class="tt-remove text-red-400 hover:text-red-600 text-xs leading-none">&times;</button>
            `;
            container.insertBefore(div, this);
            attachTTEvents(div, container);
        });
    });

    function attachTTEvents(row, container) {
        row.querySelector('.tt-remove')?.addEventListener('click', function() {
            row.remove();
            if (!container.querySelector('.tt-row')) {
                const span = document.createElement('span');
                span.className = 'tt-empty text-xs text-gray-400';
                span.textContent = '-';
                container.insertBefore(span, container.querySelector('.tt-add'));
            }
            saveTugasTambahan(container);
        });
        ['change', 'input'].forEach(evt => {
            row.querySelectorAll('.tt-jenis, .tt-keterangan, .tt-ta').forEach(el => {
                el.addEventListener(evt, () => debouncedSaveTT(container));
            });
        });
    }

    // Attach events to existing rows
    document.querySelectorAll('.tugas-tambahan-inline').forEach(container => {
        container.querySelectorAll('.tt-row').forEach(row => attachTTEvents(row, container));
    });
</script>
