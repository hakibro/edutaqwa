<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Tambah Jadwal') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('jadwal.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Kelas</label>
                            <select name="kelas_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id }}"
                                        {{ old('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Mapel</label>
                            <select name="mapel_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Mapel --</option>
                                @foreach ($mapels as $m)
                                    <option value="{{ $m->id }}"
                                        {{ old('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                                @endforeach
                            </select>
                            @error('mapel_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Guru</label>
                            <select name="guru_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach ($gurus as $g)
                                    <option value="{{ $g->id }}"
                                        {{ old('guru_id') == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                                @endforeach
                            </select>
                            @error('guru_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                            <select name="tahun_ajaran_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach ($tahunAjarans as $ta)
                                    <option value="{{ $ta->id }}"
                                        {{ old('tahun_ajaran_id') == $ta->id ? 'selected' : ($ta->is_active ? 'selected' : '') }}>
                                        {{ $ta->nama }} {{ $ta->is_active ? '(Aktif)' : '' }}</option>
                                @endforeach
                            </select>
                            @error('tahun_ajaran_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Hari</label>
                            <select name="hari"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Hari --</option>
                                @foreach ($hariList as $h)
                                    <option value="{{ $h }}" {{ old('hari') == $h ? 'selected' : '' }}>
                                        {{ $h }}</option>
                                @endforeach
                            </select>
                            @error('hari')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Jam Ke</label>
                            <select name="jam_ke" id="jam_ke"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Jam --</option>
                            </select>
                            @error('jam_ke')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('jadwal.index') }}"
                                class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            const timetableLabels = @json($timetableLabels);
            const hariSelect = document.querySelector('select[name="hari"]');
            const jamKeSelect = document.querySelector('select[name="jam_ke"]');
            const oldJamKe = '{{ old('jam_ke') }}';

            function populateJamKe(hari) {
                jamKeSelect.innerHTML = '<option value="">-- Pilih Jam --</option>';
                const labels = timetableLabels[hari] || {};
                for (const [ke, label] of Object.entries(labels)) {
                    const opt = document.createElement('option');
                    opt.value = ke;
                    opt.textContent = label;
                    if (oldJamKe === ke) opt.selected = true;
                    jamKeSelect.appendChild(opt);
                }
            }

            if (hariSelect) {
                hariSelect.addEventListener('change', () => populateJamKe(hariSelect.value));
                if (hariSelect.value) populateJamKe(hariSelect.value);
            }
        </script>
    @endpush
</x-app-layout>
