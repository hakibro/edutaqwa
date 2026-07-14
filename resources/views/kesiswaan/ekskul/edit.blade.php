<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Ekstrakurikuler') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('kesiswaan.ekskul.update', $ekskul) }}">
                    @csrf @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nama Ekskul</label>
                        <input type="text" name="nama" value="{{ old('nama', $ekskul->nama) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Pembina</label>
                        <select name="pembina_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Pilih Pembina --</option>
                            @foreach ($gurus as $g)
                                <option value="{{ $g->id }}"
                                    {{ old('pembina_id', $ekskul->pembina_id) == $g->id ? 'selected' : '' }}>
                                    {{ $g->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('kesiswaan.ekskul.index') }}"
                            class="px-4 py-2 bg-gray-200 rounded-md text-sm">Batal</a>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
