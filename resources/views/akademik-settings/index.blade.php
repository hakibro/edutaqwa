<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Pengaturan Akademik') }}</h2>
            <a href="{{ route('akademik-settings.timetable') }}"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Atur Timetable
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <form action="{{ route('akademik-settings.update') }}" method="POST">
                @csrf @method('PUT')

                {{-- Jam Mulai & Durasi --}}
                <div class="mb-6 bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Jam Mulai & Durasi') }}</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-gray-500">Tentukan jam mulai dan durasi per jenis slot. Susunan per hari
                            diatur di halaman Timetable (drag & drop).</p>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jam Mulai KBM</label>
                            <input type="time" name="jam_mulai"
                                value="{{ old('jam_mulai', $settings->get('jam_mulai')?->nilai ?? '07:00') }}"
                                class="mt-1 block w-36 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                            @error('jam_mulai')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Durasi Jam KBM (menit)</label>
                                <input type="number" name="durasi_jam_kbm"
                                    value="{{ old('durasi_jam_kbm', $settings->get('durasi_jam_kbm')?->nilai ?? '45') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    min="15" max="120" required>
                                @error('durasi_jam_kbm')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Durasi Istirahat (menit)</label>
                                <input type="number" name="durasi_istirahat"
                                    value="{{ old('durasi_istirahat', $settings->get('durasi_istirahat')?->nilai ?? '30') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    min="5" max="120" required>
                                @error('durasi_istirahat')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kegiatan --}}
                <div class="mb-6 bg-white shadow-sm sm:rounded-lg" x-data="{
                    kegiatan: {{ Js::from(old('kegiatan_nama') ? collect(old('kegiatan_nama'))->map(fn($n, $i) => ['nama' => $n, 'durasi_menit' => (int) (old('kegiatan_durasi')[$i] ?? 30)]) : $kegiatanList) }}
                }">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Kegiatan') }}</h3>
                        <button type="button" @click="kegiatan.push({nama:'', durasi_menit:30})"
                            class="rounded-md bg-indigo-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500">
                            + Tambah Kegiatan
                        </button>
                    </div>
                    <div class="p-6 space-y-3">
                        <p class="text-sm text-gray-500">Daftar kegiatan dengan durasi masing-masing. Contoh: Ishoma 30
                            mnt, Upacara 45 mnt, Piket 60 mnt.</p>
                        <template x-for="(item, index) in kegiatan" :key="index">
                            <div class="flex items-start gap-3 rounded-md border border-gray-200 p-3">
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-500 mb-1">Nama Kegiatan</label>
                                    <input type="text" :name="'kegiatan_nama[' + index + ']'" x-model="item.nama"
                                        placeholder="Nama kegiatan"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Durasi (mnt)</label>
                                    <input type="number" :name="'kegiatan_durasi[' + index + ']'"
                                        x-model="item.durasi_menit"
                                        class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        min="5" max="120">
                                </div>
                                <button type="button" @click="kegiatan.splice(index, 1)"
                                    class="mt-5 text-red-500 hover:text-red-700 text-lg leading-none">&times;</button>
                            </div>
                        </template>
                        <template x-if="kegiatan.length === 0">
                            <p class="text-sm text-gray-400 italic">Belum ada kegiatan. Tambah minimal 1.</p>
                        </template>
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Hari Efektif') }}</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-500 mb-3">Centang hari yang efektif KBM. Hari tidak dicentang =
                            libur.</p>
                        <div class="flex flex-wrap gap-4">
                            @foreach ($hariList as $h)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="hari_efektif[]" value="{{ $h }}"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        {{ in_array($h, old('hari_efektif', $hariEfektif)) ? 'checked' : '' }}>
                                    {{ $h }}
                                </label>
                            @endforeach
                        </div>
                        @error('hari_efektif')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
