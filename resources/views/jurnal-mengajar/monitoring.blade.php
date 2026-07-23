<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Monitoring Jurnal Mengajar') }}</h2>
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

            {{-- Filter --}}
            <div class="mb-4 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <form method="GET" class="flex items-end gap-4 flex-wrap">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Tanggal</label>
                            <input type="date" name="tanggal" value="{{ request('tanggal', now()->toDateString()) }}"
                                class="mt-1 rounded-md border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Tingkat</label>
                            <select name="tingkat" id="filter-tingkat"
                                class="mt-1 rounded-md border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                                <option value="">Semua</option>
                                @foreach ($tingkats as $t)
                                    <option value="{{ $t }}"
                                        {{ request('tingkat') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Kelas</label>
                            <select name="kelas_id" id="filter-kelas"
                                class="mt-1 rounded-md border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                                <option value="">Semua</option>
                                @foreach ($kelases as $k)
                                    <option value="{{ $k->id }}"
                                        {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Guru</label>
                            <select name="guru_id" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm"
                                onchange="this.form.submit()">
                                <option value="">Semua Guru</option>
                                @foreach ($gurus as $g)
                                    <option value="{{ $g->id }}"
                                        {{ request('guru_id') == $g->id ? 'selected' : '' }}>{{ $g->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Status</label>
                            <select name="verified" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm"
                                onchange="this.form.submit()">
                                <option value="">Semua</option>
                                <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>Belum
                                    Verifikasi</option>
                                <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>
                                    Terverifikasi</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($jurnals->isEmpty())
                        <p class="text-center py-8 text-sm text-gray-400">Tidak ada jurnal.</p>
                    @else
                        {{-- Bulk Actions --}}
                        <div class="mb-4 flex items-center gap-2 flex-wrap">
                            <label class="flex items-center gap-1 text-sm text-gray-600 cursor-pointer select-none">
                                <input type="checkbox" id="bulk-select-all"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                <span>Pilih Semua</span>
                            </label>
                            <button type="button" onclick="submitBulk('{{ route('jurnal-mengajar.bulk-verify') }}')"
                                class="rounded-md bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-green-500 disabled:opacity-50"
                                id="btn-bulk-verify" disabled>
                                Verifikasi Terpilih
                            </button>
                            <button type="button" onclick="submitBulk('{{ route('jurnal-mengajar.bulk-unverify') }}')"
                                class="rounded-md bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-500 disabled:opacity-50"
                                id="btn-bulk-unverify" disabled>
                                Batal Verifikasi Terpilih
                            </button>
                            <span id="bulk-count" class="text-xs text-gray-500 hidden"></span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="w-10 px-3 py-2"></th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Kelas</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Guru
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Mapel</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jam
                                            Ke</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                            Status</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($jurnals as $key => $group)
                                        @php
                                            $first = $group->first();
                                            $jamKeList = $group->pluck('jadwal.jam_ke')->filter()->sort()->values();
                                            $jamKeLabel =
                                                $jamKeList->count() > 1
                                                    ? $jamKeList->first() . '-' . $jamKeList->last()
                                                    : $jamKeList->first() ?? '—';
                                            $allVerified = $group->every(fn($j) => $j->is_verified);
                                            $anyVerified = $group->contains(fn($j) => $j->is_verified);
                                            $groupIds = $group->pluck('id')->toJson();
                                        @endphp
                                        {{-- Group header row --}}
                                        <tr class="hover:bg-gray-50 cursor-pointer accordion-toggle"
                                            data-group="{{ $key }}">
                                            <td class="px-3 py-2" onclick="event.stopPropagation()">
                                                <input type="checkbox" value="{{ $groupIds }}"
                                                    data-group="{{ $key }}"
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm bulk-group-checkbox">
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $first->kelas->nama }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $first->guru->nama ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-600">
                                                {{ $first->jadwal->mapel->nama ?? '—' }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-500">{{ $jamKeLabel }}</td>
                                            <td class="px-3 py-2 text-center">
                                                <span
                                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                                    {{ $allVerified ? 'bg-green-100 text-green-700' : ($anyVerified ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') }}">
                                                    {{ $allVerified ? 'Terverifikasi' : ($anyVerified ? 'Sebagian' : 'Pending') }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                                @if (!$allVerified)
                                                    <button type="button"
                                                        onclick="verifyGroup('{{ route('jurnal-mengajar.bulk-verify') }}', {{ $groupIds }})"
                                                        class="rounded-md bg-green-600 px-2 py-1 text-xs font-semibold text-white shadow-sm hover:bg-green-500">
                                                        Verifikasi
                                                    </button>
                                                @else
                                                    <button type="button"
                                                        onclick="verifyGroup('{{ route('jurnal-mengajar.bulk-unverify') }}', {{ $groupIds }})"
                                                        class="rounded-md bg-amber-600 px-2 py-1 text-xs font-semibold text-white shadow-sm hover:bg-amber-500">
                                                        Batal
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        {{-- Detail rows (hidden, expand on click) --}}
                                        @foreach ($group as $j)
                                            <tr class="bg-gray-50 accordion-detail hidden"
                                                data-group="{{ $key }}">
                                                <td class="px-3 py-1"></td>
                                                <td class="px-3 py-1">
                                                    <input type="checkbox" value="{{ $j->id }}"
                                                        class="rounded border-gray-300 text-indigo-600 shadow-sm bulk-checkbox">
                                                </td>
                                                <td class="px-3 py-1"></td>
                                                <td class="px-3 py-1 text-xs text-gray-500">
                                                    Pertemuan ke-{{ $j->pertemuan_ke }}
                                                </td>
                                                <td class="px-3 py-1 text-xs text-gray-500">
                                                    Jam ke-{{ $j->jadwal->jam_ke ?? '—' }}
                                                </td>
                                                <td class="px-3 py-1 text-center">
                                                    <span
                                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                                        {{ $j->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                        {{ $j->is_verified ? '✓' : 'Pending' }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-1 text-right">
                                                    <a href="{{ route('jurnal-mengajar.show', $j) }}"
                                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                                        Lihat
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Guru Belum Mengisi Jurnal --}}
                    @if ($jadwalBelum->isNotEmpty())
                        <div class="mt-6">
                            <h3 class="text-sm font-semibold text-red-700 mb-2">⚠️ Guru Belum Mengisi Jurnal Tanggal
                                {{ \Carbon\Carbon::parse($tanggal ?? now())->translatedFormat('d F Y') }}</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-red-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-red-700 uppercase">
                                                Kelas</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-red-700 uppercase">
                                                Guru</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-red-700 uppercase">
                                                Mapel</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-red-700 uppercase">
                                                Jam Ke</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach ($jadwalBelum as $key => $group)
                                            @php
                                                $first = $group->first();
                                                $jamKeList = $group->pluck('jam_ke')->filter()->sort()->values();
                                                $jamKeLabel =
                                                    $jamKeList->count() > 1
                                                        ? $jamKeList->first() . '-' . $jamKeList->last()
                                                        : $jamKeList->first() ?? '—';
                                            @endphp
                                            <tr>
                                                <td class="px-3 py-2 text-sm text-gray-900">{{ $first->kelas->nama }}
                                                </td>
                                                <td class="px-3 py-2 text-sm text-gray-900">
                                                    {{ $first->guru->nama ?? '—' }}</td>
                                                <td class="px-3 py-2 text-sm text-gray-600">
                                                    {{ $first->mapel->nama ?? '—' }}</td>
                                                <td class="px-3 py-2 text-sm text-red-600 font-medium">
                                                    {{ $jamKeLabel }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selectAll = document.getElementById('bulk-select-all');
                const checkboxes = document.querySelectorAll('.bulk-checkbox');
                const btnVerify = document.getElementById('btn-bulk-verify');
                const btnUnverify = document.getElementById('btn-bulk-unverify');
                const countEl = document.getElementById('bulk-count');

                function getSelectedIds() {
                    return Array.from(document.querySelectorAll('.bulk-checkbox:checked')).map(cb => cb.value);
                }

                function updateUI() {
                    const count = getSelectedIds().length;
                    btnVerify.disabled = count === 0;
                    btnUnverify.disabled = count === 0;
                    if (count > 0) {
                        countEl.textContent = count + ' dipilih';
                        countEl.classList.remove('hidden');
                    } else {
                        countEl.classList.add('hidden');
                    }
                }

                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        document.querySelectorAll('.bulk-group-checkbox').forEach(gcb => gcb.checked = this
                            .checked);
                        document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = this.checked);
                        updateUI();
                    });
                }

                // Group checkbox → sync detail checkboxes
                document.querySelectorAll('.bulk-group-checkbox').forEach(gcb => {
                    gcb.addEventListener('change', function() {
                        const group = this.dataset.group;
                        document.querySelectorAll('.bulk-checkbox').forEach(cb => {
                            const row = cb.closest('tr');
                            if (row && row.dataset.group === group) {
                                cb.checked = this.checked;
                            }
                        });
                        updateUI();
                    });
                });

                checkboxes.forEach(cb => {
                    cb.addEventListener('change', function() {
                        // Sync group checkbox state
                        const row = this.closest('tr');
                        if (row) {
                            const group = row.dataset.group;
                            const groupCbs = document.querySelectorAll('.bulk-checkbox');
                            const inGroup = Array.from(groupCbs).filter(c => c.closest('tr').dataset
                                .group === group);
                            const allChecked = inGroup.every(c => c.checked);
                            const groupCheckbox = document.querySelector(
                                '.bulk-group-checkbox[data-group="' + group + '"]');
                            if (groupCheckbox) groupCheckbox.checked = allChecked;
                        }
                        updateUI();
                    });
                });

                // Tingkat change → hapus kelas_id sebelum submit
                const filterTingkat = document.getElementById('filter-tingkat');
                const filterKelas = document.getElementById('filter-kelas');
                if (filterTingkat && filterKelas) {
                    filterTingkat.addEventListener('change', function() {
                        filterKelas.value = '';
                    });
                }

                // Accordion toggle by group
                document.querySelectorAll('.accordion-toggle').forEach(row => {
                    row.addEventListener('click', function() {
                        const group = this.dataset.group;
                        document.querySelectorAll('.accordion-detail[data-group="' + group + '"]')
                            .forEach(d => {
                                d.classList.toggle('hidden');
                            });
                    });
                });
            });

            function verifyGroup(url, ids) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
                ids.forEach(id => {
                    form.innerHTML += '<input type="hidden" name="ids[]" value="' + id + '">';
                });
                document.body.appendChild(form);
                form.submit();
            }

            function submitBulk(url) {
                const ids = Array.from(document.querySelectorAll('.bulk-checkbox:checked')).map(cb => cb.value);
                if (ids.length === 0) return;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
                ids.forEach(id => {
                    form.innerHTML += '<input type="hidden" name="ids[]" value="' + id + '">';
                });
                document.body.appendChild(form);
                form.submit();
            }
        </script>
    @endpush
</x-app-layout>
