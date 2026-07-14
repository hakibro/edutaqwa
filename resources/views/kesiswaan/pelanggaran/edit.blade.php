<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Pelanggaran') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('kesiswaan.pelanggaran.update', $pelanggaran) }}">
                    @csrf @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Siswa</label>
                        <select name="siswa_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($siswas as $s)
                                <option value="{{ $s->id }}"
                                    {{ old('siswa_id', $pelanggaran->siswa_id) == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama }} ({{ $s->nis }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select name="kategori_pelanggaran_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($kategoris as $k)
                                <option value="{{ $k->id }}"
                                    {{ old('kategori_pelanggaran_id', $pelanggaran->kategori_pelanggaran_id) == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama }} ({{ $k->poin }} poin)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Guru Pelapor</label>
                        <select name="guru_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($gurus as $g)
                                <option value="{{ $g->id }}"
                                    {{ old('guru_id', $pelanggaran->guru_id) == $g->id ? 'selected' : '' }}>
                                    {{ $g->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                        <input type="date" name="tanggal"
                            value="{{ old('tanggal', $pelanggaran->tanggal->format('Y-m-d')) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('deskripsi', $pelanggaran->deskripsi) }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Tindakan</label>
                        <textarea name="tindakan" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('tindakan', $pelanggaran->tindakan) }}</textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('kesiswaan.pelanggaran.index') }}"
                            class="px-4 py-2 bg-gray-200 rounded-md text-sm">Batal</a>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
