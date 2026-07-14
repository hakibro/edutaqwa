<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tambah Kalender Akademik') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('kalender-akademik.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf

                    @if (auth()->user()->isSuperAdmin())
                        <div>
                            <x-input-label for="yayasan_id" value="Yayasan" />
                            <select id="yayasan_id" name="yayasan_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Yayasan --</option>
                                @foreach ($yayasans as $y)
                                    <option value="{{ $y->id }}" @selected(old('yayasan_id', $yayasanId) == $y->id)>{{ $y->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('yayasan_id')" class="mt-2" />
                        </div>
                    @endif

                    <div>
                        <x-input-label for="tanggal" value="Tanggal" />
                        <x-text-input id="tanggal" name="tanggal" type="date" class="mt-1 block w-full"
                            :value="old('tanggal')" required />
                        <x-input-error :messages="$errors->get('tanggal')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="label" value="Label" />
                        <x-text-input id="label" name="label" type="text" class="mt-1 block w-full"
                            :value="old('label')" placeholder="Libur Nasional, PTS, dll" required />
                        <x-input-error :messages="$errors->get('label')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="jenis" value="Jenis" />
                        <select id="jenis" name="jenis"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                            <option value="efektif" @selected(old('jenis') == 'efektif')>Hari Efektif</option>
                            <option value="libur" @selected(old('jenis') == 'libur')>Libur</option>
                            <option value="ujian" @selected(old('jenis') == 'ujian')>Ujian</option>
                            <option value="lainnya" @selected(old('jenis') == 'lainnya')>Lainnya</option>
                        </select>
                        <x-input-error :messages="$errors->get('jenis')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="keterangan" value="Keterangan" />
                        <textarea id="keterangan" name="keterangan"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="2">{{ old('keterangan') }}</textarea>
                        <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('kalender-akademik.index', ['yayasan_id' => $yayasanId]) }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
