<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Import Jadwal') }}</h2>
            <a href="{{ route('jadwal.template') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                &darr; Download Template
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            @if (session('import_errors'))
                <div class="mb-4 rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                    <p class="font-medium mb-1">Beberapa baris dilewati:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach (session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        Upload file Excel (.xlsx, .xls, .csv) dengan kolom: <strong>kelas, mapel, guru, hari,
                            jam_ke</strong>.
                        Nama kelas/mapel/guru harus cocok dengan data yang sudah ada. Hari gunakan: Senin, Selasa, Rabu,
                        Kamis, Jumat, Sabtu, Minggu. jam_ke diisi angka (1, 2, 3, ...).
                    </p>

                    <form action="{{ route('jadwal.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                            <select name="tahun_ajaran_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach ($tahunAjarans as $ta)
                                    <option value="{{ $ta->id }}"
                                        {{ old('tahun_ajaran_id') == $ta->id ? 'selected' : ($ta->is_active ? 'selected' : '') }}>
                                        {{ $ta->nama }} {{ $ta->is_active ? '(Aktif)' : '' }}</option>
                                @endforeach
                            </select>
                            @error('tahun_ajaran_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">File Excel</label>
                            <input type="file" name="file" accept=".xlsx,.xls,.csv"
                                class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                required>
                            @error('file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('jadwal.index') }}"
                                class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Import Jadwal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
