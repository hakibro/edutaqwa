<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Edit Kelompok Mapel') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('kelompok-mapel.update', $kelompokMapel) }}" method="POST">
                        @csrf @method('PUT')

                        @if (!auth()->user()->lembaga_id)
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Lembaga</label>
                                <select name="lembaga_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach ($lembagas as $l)
                                        <option value="{{ $l->id }}"
                                            {{ old('lembaga_id', $kelompokMapel->lembaga_id) == $l->id ? 'selected' : '' }}>
                                            {{ $l->nama }}</option>
                                    @endforeach
                                </select>
                                @error('lembaga_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Nama Kelompok Mapel</label>
                            <input type="text" name="nama" value="{{ old('nama', $kelompokMapel->nama) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                            @error('nama')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('kelompok-mapel.index') }}"
                                class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
