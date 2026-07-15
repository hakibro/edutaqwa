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
                <a href="{{ route('guru.export', request()->query()) }}"
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    Export Excel
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
                    {{-- Filter Bar --}}
                    <div class="mb-4 flex flex-wrap items-end gap-3">
                        <div class="flex-1 min-w-[200px]">
                            <x-input-label for="searchGuru" value="Cari" />
                            <input id="searchGuru" type="text" placeholder="Nama / NIP / NUPTK / NIY / Kode..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                value="{{ request('search') }}">
                        </div>
                        <div>
                            <x-input-label for="filterSatminkal" value="Status Satminkal" />
                            <select id="filterSatminkal"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">-- Semua --</option>
                                <option value="1" @selected(request('status_satminkal') === '1')>Satminkal</option>
                                <option value="0" @selected(request('status_satminkal') === '0')>Non-Satminkal</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="filterTmtFrom" value="TMT Dari" />
                            <input id="filterTmtFrom" type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                value="{{ request('tmt_from') }}">
                        </div>
                        <div>
                            <x-input-label for="filterTmtTo" value="TMT Sampai" />
                            <input id="filterTmtTo" type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                value="{{ request('tmt_to') }}">
                        </div>
                        <div>
                            <x-input-label for="perPage" value="Tampil" />
                            <select id="perPage"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="10" @selected(request('per_page', 10) == 10)>10</option>
                                <option value="25" @selected(request('per_page') == 25)>25</option>
                                <option value="50" @selected(request('per_page') == 50)>50</option>
                                <option value="100" @selected(request('per_page') == 100)>100</option>
                            </select>
                        </div>
                    </div>

                    <form id="bulk-form" method="POST" action="{{ route('guru.bulk-update') }}" class="mb-4">
                        @csrf
                        <div class="flex items-center gap-3" id="bulk-actions" style="display:none">
                            <span class="text-sm text-gray-600"><span id="selected-count">0</span> terpilih</span>
                            <button type="submit" name="action" value="activate"
                                class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-sm"
                                onclick="return confirm('Aktifkan semua guru terpilih?')">Aktifkan Semua</button>
                            <button type="submit" name="action" value="deactivate"
                                class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                                onclick="return confirm('Nonaktifkan semua guru terpilih?')">Nonaktifkan Semua</button>
                            <button type="button" id="bulk-delete-btn"
                                class="px-3 py-1.5 bg-red-700 text-white rounded hover:bg-red-800 text-sm"
                                onclick="bulkDelete()">Hapus Terpilih</button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        <input type="checkbox" id="check-all"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    </th>
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
                            <tbody id="guruTableBody" class="divide-y divide-gray-200 bg-white">
                                @include('guru._table')
                            </tbody>
                        </table>
                    </div>
                    <div id="guruPagination" class="mt-4">{{ $gurus->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function debounce(fn, ms) {
        let t;
        return (...a) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...a), ms)
        };
    }

    // ===== Inline update helpers =====
    function saveJenisPtk(el) {
        const guruId = el.dataset.guruId;
        fetch(`/guru/${guruId}/inline-update`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                field: 'jenis_ptk_id',
                value: el.value
            })
        }).then(r => r.json()).then(d => {
            if (!d.success) alert('Gagal update Jenis PTK');
        });
    }

    function saveTugasTambahan(container) {
        const guruId = container.dataset.guruId;
        const rows = container.querySelectorAll('.tt-row');
        const tugasTambahan = [];
        rows.forEach(row => {
            const jenis = row.querySelector('.tt-jenis')?.value || '';
            const keterangan = row.querySelector('.tt-keterangan')?.value || '';
            const ta = row.querySelector('.tt-ta')?.value || '';
            if (jenis) tugasTambahan.push({
                jenis,
                keterangan,
                tahun_ajaran_id: ta
            });
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

    function initTTEvents() {
        document.querySelectorAll('.tugas-tambahan-inline').forEach(container => {
            container.querySelectorAll('.tt-row').forEach(row => attachTTEvents(row, container));
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
        document.querySelectorAll('.inline-update-jenis-ptk').forEach(el => {
            el.addEventListener('change', function() {
                saveJenisPtk(this);
            });
        });
    }

    // ===== AJAX Search / Filter =====
    function fetchGuru() {
        const params = new URLSearchParams();
        const search = document.getElementById('searchGuru')?.value;
        const satminkal = document.getElementById('filterSatminkal')?.value;
        const tmtFrom = document.getElementById('filterTmtFrom')?.value;
        const tmtTo = document.getElementById('filterTmtTo')?.value;
        const perPage = document.getElementById('perPage')?.value;
        if (search) params.set('search', search);
        if (satminkal !== '') params.set('status_satminkal', satminkal);
        if (tmtFrom) params.set('tmt_from', tmtFrom);
        if (tmtTo) params.set('tmt_to', tmtTo);
        if (perPage) params.set('per_page', perPage);

        fetch(`{{ route('guru.index') }}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(r => r.json()).then(d => {
            document.getElementById('guruTableBody').innerHTML = d.html;
            document.getElementById('guruPagination').innerHTML = d.pagination;
            initTTEvents();
            initBulkCheckboxes();
        }).catch(() => {});
    }

    const debouncedFetch = debounce(fetchGuru, 400);

    function bulkDelete() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) {
            alert('Pilih guru terlebih dahulu.');
            return;
        }
        if (!confirm('Hapus ' + checked.length + ' guru terpilih?')) return;

        const form = document.getElementById('bulk-form');
        form.action = '{{ route('guru.bulk-delete') }}';
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        initTTEvents();

        document.getElementById('searchGuru')?.addEventListener('input', debouncedFetch);
        document.getElementById('filterSatminkal')?.addEventListener('change', fetchGuru);
        document.getElementById('filterTmtFrom')?.addEventListener('change', fetchGuru);
        document.getElementById('filterTmtTo')?.addEventListener('change', fetchGuru);
        document.getElementById('perPage')?.addEventListener('change', fetchGuru);

        // Pagination click via AJAX
        document.getElementById('guruPagination')?.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;
            e.preventDefault();

            fetch(link.href, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(r => r.json()).then(d => {
                document.getElementById('guruTableBody').innerHTML = d.html;
                document.getElementById('guruPagination').innerHTML = d.pagination;
                initTTEvents();
                initBulkCheckboxes();
            }).catch(() => {});
        });

        // ===== Bulk Action Checkboxes =====
        function initBulkCheckboxes() {
            const checkAll = document.getElementById('check-all');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');

            if (!checkAll) return;

            function updateBulkUI() {
                const checked = document.querySelectorAll('.row-checkbox:checked').length;
                selectedCount.textContent = checked;
                bulkActions.style.display = checked > 0 ? 'flex' : 'none';
            }

            checkAll.addEventListener('change', function() {
                rowCheckboxes.forEach(cb => cb.checked = checkAll.checked);
                updateBulkUI();
            });

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkUI);
            });
        }

        initBulkCheckboxes();
    });
</script>
