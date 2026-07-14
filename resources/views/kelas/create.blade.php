<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Tambah Kelas') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('kelas.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf

                    @if (!auth()->user()->lembaga_id)
                        <div>
                            <x-input-label for="lembaga_id" value="Lembaga" />
                            <select id="lembaga_id" name="lembaga_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach ($lembagas as $l)
                                    <option value="{{ $l->id }}" @selected(old('lembaga_id') == $l->id)>{{ $l->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <x-input-label for="nama" value="Nama Kelas" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                            :value="old('nama')" required placeholder="contoh: X IPA 1, XI TKJ 2, VII-A" />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tingkat" value="Tingkat" />
                            <select id="tingkat" name="tingkat"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Tingkat --</option>
                                @foreach (['X', 'XI', 'XII', 'XIII', 'VII', 'VIII', 'IX', '1', '2', '3', '4', '5', '6'] as $t)
                                    <option value="{{ $t }}" @selected(old('tingkat') == $t)>{{ $t }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="jurusan_id" value="Jurusan" />
                            <select id="jurusan_id" name="jurusan_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Tanpa Jurusan --</option>
                                @foreach ($jurusans as $j)
                                    <option value="{{ $j->id }}" @selected(old('jurusan_id') == $j->id)>{{ $j->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('kelas.index') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
