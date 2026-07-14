<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Edit Jenis PTK') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('jenis-ptk.update', $jenisPtk) }}" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')

                    <div>
                        <x-input-label for="nama" value="Nama Jenis PTK" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                            :value="old('nama', $jenisPtk->nama)" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="keterangan" value="Keterangan (opsional)" />
                        <x-text-input id="keterangan" name="keterangan" type="text" class="mt-1 block w-full"
                            :value="old('keterangan', $jenisPtk->keterangan)" />
                        <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked(old('is_active', $jenisPtk->is_active))>
                        <x-input-label for="is_active" value="Aktif" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('jenis-ptk.index') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
