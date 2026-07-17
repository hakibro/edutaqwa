<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Data Siswa') }}</h2>
            <div class="flex gap-2 flex-wrap">
                @if (!auth()->user()->lembaga?->sisda_mode)
                    <a href="{{ route('siswa.create') }}"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        + Tambah Siswa
                    </a>
                @endif
                <a href="{{ route('sync-siswa.index') }}"
                    class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                    Sync dari Sisda
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{-- Filter --}}
                    <form method="GET" action="{{ route('siswa.index') }}"
                        class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        <div>
                            <input type="text" name="search" placeholder="Cari nama / NIS / NISN..."
                                value="{{ request('search') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <select name="tingkat"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Semua Tingkat</option>
                                @foreach ($tingkats as $t)
                                    <option value="{{ $t }}" @selected(request('tingkat') == $t)>
                                        Kelas {{ $t }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="jurusan_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Semua Jurusan</option>
                                @foreach ($jurusans as $j)
                                    <option value="{{ $j->id }}" @selected(request('jurusan_id') == $j->id)>
                                        {{ $j->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="kelas_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Semua Kelas</option>
                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>
                                        {{ $k->nama }} {{ $k->jurusan ? '(' . $k->jurusan->nama . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-500">Filter</button>
                            @if (request()->anyFilled(['search', 'tingkat', 'jurusan_id', 'kelas_id']))
                                <a href="{{ route('siswa.index') }}"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300">Reset</a>
                            @endif
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        ID Person</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        NIS</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Nama</th>

                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Lembaga</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        JK</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($siswas as $s)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-500">
                                            {{ $s->idperson ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-700">
                                            {{ $s->nis ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $s->nama }}</td>

                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $s->lembaga->nama }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $s->jenis_kelamin ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span
                                                class="rounded-full px-2 py-1 text-xs font-semibold {{ $s->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $s->status }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <a href="{{ route('siswa.edit', $s) }}"
                                                class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            @if (auth()->user()->role === 'super_admin')
                                                <form action="{{ route('siswa.destroy', $s) }}" method="POST"
                                                    class="inline" onsubmit="return confirm('Hapus siswa ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="ml-2 text-red-600 hover:text-red-900">Hapus</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada
                                            data siswa.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $siswas->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Auto-submit on dropdown change + live search on input --}}
    <script>
        const filterForm = document.querySelector('form[action="{{ route('siswa.index') }}"]');
        if (!filterForm) throw new Error('Filter form not found');
        let searchTimer;

        document.querySelector('input[name="search"]').addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                const params = new URLSearchParams(new FormData(filterForm));
                window.location.href = filterForm.action + '?' + params.toString();
            }, 400);
        });

        filterForm.querySelectorAll('select[name="tingkat"], select[name="jurusan_id"], select[name="kelas_id"]')
            .forEach(el => el.addEventListener('change', function() {
                filterForm.submit();
            }));
    </script>
</x-app-layout>
