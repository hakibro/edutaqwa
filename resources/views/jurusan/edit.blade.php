<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Edit Jurusan') }}: {{ $jurusan->nama }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('jurusan.update', $jurusan) }}" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')

                    @if (!auth()->user()->lembaga_id)
                        <div>
                            <x-input-label for="lembaga_id" value="Lembaga" />
                            <select id="lembaga_id" name="lembaga_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach ($lembagas as $l)
                                    <option value="{{ $l->id }}" @selected(old('lembaga_id', $jurusan->lembaga_id) == $l->id)>{{ $l->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <x-input-label for="nama" value="Nama Jurusan" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                            :value="old('nama', $jurusan->nama)" required />
                    </div>

                    <div>
                        <x-input-label for="kode" value="Kode (opsional)" />
                        <x-text-input id="kode" name="kode" type="text" class="mt-1 block w-full"
                            :value="old('kode', $jurusan->kode)" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('jurusan.index') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
