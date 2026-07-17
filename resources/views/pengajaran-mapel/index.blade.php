<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Penugasan Guru ke Mapel') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('pengajaran-mapel.template') }}"
                    class="rounded-md bg-gray-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-400">
                    Download Template
                </a>
                <button onclick="document.getElementById('importForm').classList.toggle('hidden')"
                    class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                    Import XLSX
                </button>
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
                    <p class="font-medium mb-1">Detail baris yang dilewati:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach (session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Import Form --}}
            <div id="importForm" class="hidden mb-6 p-4 bg-gray-50 border rounded-lg">
                <form action="{{ route('pengajaran-mapel.import') }}" method="POST" enctype="multipart/form-data"
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

            {{-- Filter --}}
            <div class="mb-6 bg-white p-4 shadow-sm sm:rounded-lg">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                        <select name="tahun_ajaran_id"
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Semua</option>
                            @foreach ($tahunAjarans as $ta)
                                <option value="{{ $ta->id }}"
                                    {{ request('tahun_ajaran_id') == $ta->id ? 'selected' : '' }}>{{ $ta->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cari Guru/Mapel</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Ketik nama guru atau mapel..."
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <button type="submit"
                            class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">Filter</button>
                        <a href="{{ route('pengajaran-mapel.index') }}"
                            class="ml-2 text-sm text-gray-600 hover:text-gray-900">Reset</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{-- Inline Add Form (Google Form-style) --}}
                    <div class="mb-6 rounded-lg border border-dashed border-indigo-300 bg-indigo-50 p-4">
                        <form action="{{ route('pengajaran-mapel.store') }}" method="POST"
                            class="flex flex-wrap items-end gap-3">
                            @csrf
                            <input type="hidden" name="tahun_ajaran_id"
                                value="{{ request('tahun_ajaran_id', $tahunAjarans->firstWhere('is_active', true)->id ?? '') }}">
                            <div class="flex-1 min-w-[180px]">
                                <label class="block text-xs font-medium text-indigo-700 mb-1">Mapel</label>
                                <select name="mapel_id"
                                    class="block w-full rounded-md border-indigo-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('mapel_id') border-red-300 @enderror"
                                    required>
                                    <option value="">-- Pilih Mapel --</option>
                                    @foreach ($mapels as $m)
                                        <option value="{{ $m->id }}"
                                            {{ old('mapel_id') == $m->id ? 'selected' : '' }}>
                                            {{ $m->nama }} ({{ $m->kode ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('mapel_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex-1 min-w-[180px]">
                                <label class="block text-xs font-medium text-indigo-700 mb-1">Guru Pengampu</label>
                                <select name="guru_id"
                                    class="block w-full rounded-md border-indigo-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    required>
                                    <option value="">-- Pilih Guru --</option>
                                    @foreach ($gurus as $g)
                                        <option value="{{ $g->id }}"
                                            {{ old('guru_id') == $g->id ? 'selected' : '' }}>
                                            {{ $g->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <button type="submit"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Tambah</button>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Mapel</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Guru</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Tahun Ajaran</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($pengajarans as $p)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $p->mapel->nama }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $p->guru->nama }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $p->tahunAjaran->nama }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <a href="{{ route('pengajaran-mapel.edit', $p) }}"
                                                class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form action="{{ route('pengajaran-mapel.destroy', $p) }}" method="POST"
                                                class="inline" onsubmit="return confirm('Hapus penugasan ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="ml-2 text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada
                                            penugasan guru.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $pengajarans->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
