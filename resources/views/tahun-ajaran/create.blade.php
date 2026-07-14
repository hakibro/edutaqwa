<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tambah Tahun Ajaran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('tahun-ajaran.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf

                    @if (auth()->user()->isSuperAdmin())
                        <div>
                            <x-input-label for="yayasan_id" value="Yayasan" />
                            <select id="yayasan_id" name="yayasan_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Yayasan --</option>
                                @foreach ($yayasans as $y)
                                    <option value="{{ $y->id }}" @selected(old('yayasan_id') == $y->id)>{{ $y->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('yayasan_id')" class="mt-2" />
                        </div>
                    @endif

                    <div>
                        <x-input-label for="nama" value="Tahun Ajaran" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                            :value="old('nama')" placeholder="2025/2026" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tanggal_mulai" value="Tanggal Mulai" />
                            <x-text-input id="tanggal_mulai" name="tanggal_mulai" type="date"
                                class="mt-1 block w-full" :value="old('tanggal_mulai')" required />
                            <x-input-error :messages="$errors->get('tanggal_mulai')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="tanggal_selesai" value="Tanggal Selesai" />
                            <x-text-input id="tanggal_selesai" name="tanggal_selesai" type="date"
                                class="mt-1 block w-full" :value="old('tanggal_selesai')" required />
                            <x-input-error :messages="$errors->get('tanggal_selesai')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <x-input-label for="is_active" value="Aktif (nonaktifkan tahun ajaran lain)" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('tahun-ajaran.index') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
