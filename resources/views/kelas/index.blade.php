<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Data Kelas') }}</h2>
            @if (!auth()->user()->lembaga?->sisda_mode)
                <a href="{{ route('kelas.create') }}"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    + Tambah Kelas
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            {{-- Filter Bar --}}
            <div class="mb-4 bg-white p-4 shadow-sm sm:rounded-lg">
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <x-input-label for="filterTingkat" value="Tingkat" />
                        <select id="filterTingkat"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Semua Tingkat</option>
                            @foreach ($tingkats as $t)
                                <option value="{{ $t }}" @selected(request('tingkat') == $t)>{{ $t }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="filterJurusan" value="Jurusan" />
                        <select id="filterJurusan"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Semua Jurusan</option>
                            @foreach ($jurusans as $j)
                                <option value="{{ $j->id }}" @selected(request('jurusan_id') == $j->id)>{{ $j->nama }}
                                </option>
                            @endforeach
                        </select>
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
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Nama</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Tingkat</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Jurusan</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Lembaga</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Siswa</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white" id="kelasTableBody">
                                @include('kelas._table')
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4" id="kelasPagination">{{ $kelas->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function fetchKelas() {
                const params = new URLSearchParams();
                const tingkat = document.getElementById('filterTingkat')?.value;
                const jurusanId = document.getElementById('filterJurusan')?.value;
                const perPage = document.getElementById('perPage')?.value;

                if (tingkat) params.set('tingkat', tingkat);
                if (jurusanId) params.set('jurusan_id', jurusanId);
                if (perPage) params.set('per_page', perPage);

                fetch(`{{ route('kelas.index') }}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(r => r.json()).then(d => {
                    document.getElementById('kelasTableBody').innerHTML = d.html;
                    document.getElementById('kelasPagination').innerHTML = d.pagination;
                }).catch(() => {});
            }

            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('filterTingkat')?.addEventListener('change', fetchKelas);
                document.getElementById('filterJurusan')?.addEventListener('change', fetchKelas);
                document.getElementById('perPage')?.addEventListener('change', fetchKelas);

                // Pagination click via AJAX
                document.getElementById('kelasPagination')?.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    if (!link) return;
                    e.preventDefault();

                    fetch(link.href, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(r => r.json()).then(d => {
                        document.getElementById('kelasTableBody').innerHTML = d.html;
                        document.getElementById('kelasPagination').innerHTML = d.pagination;
                    }).catch(() => {});
                });
            });
        </script>
    @endpush
</x-app-layout>
