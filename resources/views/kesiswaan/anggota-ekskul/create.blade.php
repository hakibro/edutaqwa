<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Anggota: :nama', ['nama' => $ekskul->nama]) }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('kesiswaan.anggota-ekskul.store', $ekskul) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Siswa</label>
                        <select name="siswa_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach ($siswas as $s)
                                <option value="{{ $s->id }}" {{ old('siswa_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama }} ({{ $s->nis }})</option>
                            @endforeach
                        </select>
                        @error('siswa_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                        <select name="tahun_ajaran_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach ($tahunAjarans as $ta)
                                <option value="{{ $ta->id }}"
                                    {{ old('tahun_ajaran_id') == $ta->id ? 'selected' : '' }}>
                                    {{ $ta->label ?? $ta->tahun_mulai . '/' . $ta->tahun_selesai }}</option>
                            @endforeach
                        </select>
                        @error('tahun_ajaran_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('kesiswaan.anggota-ekskul.index', $ekskul) }}"
                            class="px-4 py-2 bg-gray-200 rounded-md text-sm">Batal</a>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
