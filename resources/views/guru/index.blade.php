<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Data Guru') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('guru.template') }}"
                    class="rounded-md bg-gray-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-400">
                    Download Template Excel
                </a>
                <a href="{{ route('guru.export', request()->query()) }}"
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    Export Excel
                </a>
                <button onclick="document.getElementById('importForm').classList.toggle('hidden')"
                    class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                    Import Excel
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

            @php
                $guruTmtKosong = $gurus->filter(fn($g) => $g->relationLoaded('user') && $g->user && !$g->tmt);
                $guruBelumApproved = $gurus->filter(fn($g) => !$g->is_approved);
            @endphp
            @if ($guruTmtKosong->isNotEmpty())
                <div class="mb-4 rounded-md bg-yellow-50 border border-yellow-300 p-4 text-sm text-yellow-800">
                    <p class="font-semibold">⚠ {{ $guruTmtKosong->count() }} guru belum mengisi TMT:</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($guruTmtKosong as $g)
                            <li>{{ $g->nama }} ({{ $g->kode_guru_lembaga }}) —
                                <a href="{{ route('guru.edit', $g) }}" class="text-indigo-600 underline">Edit TMT</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if ($guruBelumApproved->isNotEmpty())
                <div class="mb-4 rounded-md bg-yellow-50 border border-yellow-300 p-4 text-sm text-yellow-800">
                    <p class="font-semibold">⚠ {{ $guruBelumApproved->count() }} guru belum disetujui admin yayasan:
                    </p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($guruBelumApproved as $g)
                            <li>{{ $g->nama }} ({{ $g->kode_guru_lembaga }})
                                — @if (auth()->user()->isAdminYayasan())
                                    <a href="{{ route('guru.approval') }}" class="text-indigo-600 underline">Setujui di
                                        halaman Approval</a>
                                @else
                                    <span class="text-gray-500">menunggu persetujuan admin yayasan</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Import Form --}}
            <div id="importForm" class="hidden mb-6 p-4 bg-gray-50 border rounded-lg">
                <p class="text-sm text-gray-600 mb-3">
                    <strong>Import Baru:</strong> Gunakan <a href="{{ route('guru.template') }}"
                        class="text-indigo-600 underline">Template Excel</a> untuk menambah guru baru.<br>
                    <strong>Update Massal:</strong> <a href="{{ route('guru.export', request()->query()) }}"
                        class="text-indigo-600 underline">Export data</a> → edit di Excel → import kembali. Kolom
                    <em>ID</em> menentukan data mana yang diupdate.
                </p>
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
                                        Satminkal</th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Aktif</th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Peringatan</th>
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
    {{-- Tugas Tambahan Edit Popup --}}
    <div id="ttPopup" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40"
        onclick="if(event.target===this)closeTTPopup()">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6 max-h-[85vh] overflow-y-auto"
            onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Edit Tugas Tambahan</h3>
                <button onclick="closeTTPopup()"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>
            <div id="ttPopupRows"></div>
            <button type="button" id="ttPopupAdd" class="mt-3 text-sm text-indigo-600 hover:text-indigo-800">+
                Tambah</button>
            <div class="mt-6 flex justify-end gap-3">
                <button onclick="closeTTPopup()"
                    class="px-4 py-2 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Batal</button>
                <button onclick="saveTTPopup()"
                    class="px-4 py-2 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Simpan</button>
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

    // ===== Tugas Tambahan Popup =====
    let ttPopupGuruId = null;
    const kelasOptions = @json($kelasOptions ?? []);
    const activeTahunAjaranId = '{{ $activeTahunAjaran?->id }}';

    function openTTPopup(guruId, lembagaId) {
        ttPopupGuruId = guruId;
        fetch(`/guru/${guruId}/tugas-tambahan`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(r => r.json()).then(data => {
            const rows = document.getElementById('ttPopupRows');
            rows.innerHTML = '';
            if (data.length > 0) {
                data.forEach(tt => addTTRow(tt, lembagaId));
            }
            document.getElementById('ttPopup').classList.remove('hidden');
            document.getElementById('ttPopup').classList.add('flex');
        });
    }

    function addTTRow(data, lembagaId) {
        data = data || {};
        const rows = document.getElementById('ttPopupRows');
        const div = document.createElement('div');
        div.className = 'tt-popup-row border rounded-lg p-3 mb-2 bg-gray-50';
        const kelasOptionsHtml = kelasOptions
            .filter(k => k.lembaga_id == lembagaId)
            .map(k => `<option value="${k.id}" ${data.kelas_id==k.id?'selected':''}>${k.nama}</option>`).join('');
        div.innerHTML = `
            <div class="grid grid-cols-2 gap-2 mb-2">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-0.5">Jenis</label>
                    <select class="tt-popup-jenis w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih --</option>
                        <option value="Guru Mapel" ${data.jenis==='Guru Mapel'?'selected':''}>Guru Mapel</option>
                        <option value="BK" ${data.jenis==='BK'?'selected':''}>BK</option>
                        <option value="Wali Kelas" ${data.jenis==='Wali Kelas'?'selected':''}>Wali Kelas</option>
                        <option value="Pembina Ekskul" ${data.jenis==='Pembina Ekskul'?'selected':''}>Pembina Ekskul</option>
                        <option value="Koordinator" ${data.jenis==='Koordinator'?'selected':''}>Koordinator</option>
                        <option value="Validator Jurnal" ${data.jenis==='Validator Jurnal'?'selected':''}>Validator Jurnal</option>
                        <option value="Perizinan Siswa" ${data.jenis==='Perizinan Siswa'?'selected':''}>Perizinan Siswa</option>
                        <option value="Presensi PTK" ${data.jenis==='Presensi PTK'?'selected':''}>Presensi PTK</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-0.5">Keterangan</label>
                    <input type="text" value="${data.keterangan||''}" class="tt-popup-ket w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Keterangan">
                </div>
            </div>
            <div class="tt-popup-kelas-wrapper" style="${data.jenis==='Wali Kelas'?'':'display:none'}">
                <label class="block text-xs font-medium text-gray-600 mb-0.5">Kelas</label>
                <select class="tt-popup-kelas w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Kelas --</option>
                    ${kelasOptionsHtml}
                </select>
            </div>
            <button type="button" class="tt-popup-remove text-xs text-red-400 hover:text-red-600 mt-2">&times; Hapus</button>
        `;
        const jenisSelect = div.querySelector('.tt-popup-jenis');
        const kelasWrapper = div.querySelector('.tt-popup-kelas-wrapper');
        jenisSelect.addEventListener('change', function() {
            kelasWrapper.style.display = this.value === 'Wali Kelas' ? '' : 'none';
        });
        div.querySelector('.tt-popup-remove').addEventListener('click', () => div.remove());
        rows.appendChild(div);
    }

    function saveTTPopup() {
        const rows = document.querySelectorAll('#ttPopupRows .tt-popup-row');
        const tugasTambahan = [];
        rows.forEach(row => {
            const jenis = row.querySelector('.tt-popup-jenis')?.value || '';
            const keterangan = row.querySelector('.tt-popup-ket')?.value || '';
            const kelasId = row.querySelector('.tt-popup-kelas')?.value || '';
            if (jenis) tugasTambahan.push({
                jenis,
                keterangan,
                tahun_ajaran_id: activeTahunAjaranId,
                kelas_id: kelasId
            });
        });
        fetch(`/guru/${ttPopupGuruId}/inline-update`, {
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
            if (d.success) {
                closeTTPopup();
                fetchGuru(); // refresh table
            } else {
                alert('Gagal menyimpan tugas tambahan');
            }
        });
    }

    function closeTTPopup() {
        document.getElementById('ttPopup').classList.add('hidden');
        document.getElementById('ttPopup').classList.remove('flex');
        ttPopupGuruId = null;
    }

    // Attach edit button clicks
    function initTTEditButtons() {
        document.querySelectorAll('.tt-edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                openTTPopup(this.dataset.guruId, this.dataset.lembagaId);
            });
        });
    }

    document.getElementById('ttPopupAdd')?.addEventListener('click', function() {
        const lembagaId = document.querySelector('.tt-edit-btn')?.dataset?.lembagaId;
        addTTRow(null, lembagaId);
    });

    // ===== AJAX Search / Filter =====
    function fetchGuru() {
        const params = new URLSearchParams();
        const search = document.getElementById('searchGuru')?.value;
        const satminkal = document.getElementById('filterSatminkal')?.value;
        const tmtFrom = document.getElementById('filterTmtFrom')?.value;
        const tmtTo = document.getElementById('filterTmtTo')?.value;
        if (search) params.set('search', search);
        if (satminkal !== '') params.set('status_satminkal', satminkal);
        if (tmtFrom) params.set('tmt_from', tmtFrom);
        if (tmtTo) params.set('tmt_to', tmtTo);

        fetch(`{{ route('guru.index') }}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(r => r.json()).then(d => {
            document.getElementById('guruTableBody').innerHTML = d.html;
            document.getElementById('guruPagination').innerHTML = d.pagination;
            initTTEditButtons();
            initInlineJenisPtk();
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
        initTTEditButtons();
        initInlineJenisPtk();

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
                initTTEditButtons();
                initInlineJenisPtk();
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

    function initInlineJenisPtk() {
        document.querySelectorAll('.inline-update-jenis-ptk').forEach(el => {
            el.addEventListener('change', function() {
                saveJenisPtk(this);
            });
        });
    }
</script>
