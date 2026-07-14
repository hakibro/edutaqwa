<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Edit Presensi') }}</h2>
            <span class="text-sm text-gray-500">{{ $presensi->jadwal->mapel->nama }} —
                {{ $presensi->jadwal->kelas->nama }} | Pertemuan ke-{{ $presensi->pertemuan_ke }}</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form action="{{ route('presensi.update', $presensi->id) }}" method="POST"
                class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                @csrf
                @method('PUT')

                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-3">Materi Pertemuan</h3>
                    <input type="text" name="materi" value="{{ old('materi', $presensi->materi) }}"
                        placeholder="Materi yang diajarkan (opsional)"
                        class="w-full rounded-md border-gray-300 text-sm">
                </div>

                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-semibold text-gray-900">Daftar Siswa</h3>
                        <div class="flex gap-2">
                            <button type="button" onclick="setAll('hadir')"
                                class="rounded bg-green-100 px-3 py-1 text-xs font-medium text-green-700 hover:bg-green-200">Semua
                                Hadir</button>
                            <button type="button" onclick="setAll('alpha')"
                                class="rounded bg-red-100 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-200">Semua
                                Alpha</button>
                        </div>
                    </div>

                    <div class="space-y-1">
                        @foreach ($presensi->detailPresensis as $i => $d)
                            <div class="flex items-center gap-3 rounded-lg border border-gray-100 p-3 hover:bg-gray-50">
                                <span class="text-xs text-gray-400 w-8">{{ $i + 1 }}</span>
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-gray-900">{{ $d->siswa->nama }}</span>
                                    <span class="ml-2 text-xs text-gray-400">{{ $d->siswa->nis }}</span>
                                </div>
                                <input type="hidden" name="siswa[{{ $i }}][id]"
                                    value="{{ $d->id }}">
                                <select name="siswa[{{ $i }}][status]"
                                    class="rounded-md border-gray-300 text-sm py-1
                                    {{ $d->status === 'hadir' ? 'bg-green-50 border-green-300' : '' }}
                                    {{ $d->status === 'alpha' ? 'bg-red-50 border-red-300' : '' }}
                                    {{ $d->status === 'sakit' ? 'bg-yellow-50 border-yellow-300' : '' }}
                                    {{ $d->status === 'izin' ? 'bg-orange-50 border-orange-300' : '' }}
                                    {{ $d->status === 'terlambat' ? 'bg-purple-50 border-purple-300' : '' }}"
                                    onchange="this.className='rounded-md border-gray-300 text-sm py-1 ' + (this.value==='hadir'?'bg-green-50 border-green-300':this.value==='alpha'?'bg-red-50 border-red-300':this.value==='sakit'?'bg-yellow-50 border-yellow-300':this.value==='izin'?'bg-orange-50 border-orange-300':'bg-purple-50 border-purple-300')">
                                    <option value="hadir"
                                        {{ old("siswa.{$i}.status", $d->status) === 'hadir' ? 'selected' : '' }}>Hadir
                                    </option>
                                    <option value="sakit"
                                        {{ old("siswa.{$i}.status", $d->status) === 'sakit' ? 'selected' : '' }}>Sakit
                                    </option>
                                    <option value="izin"
                                        {{ old("siswa.{$i}.status", $d->status) === 'izin' ? 'selected' : '' }}>Izin
                                    </option>
                                    <option value="alpha"
                                        {{ old("siswa.{$i}.status", $d->status) === 'alpha' ? 'selected' : '' }}>Alpha
                                    </option>
                                    <option value="terlambat"
                                        {{ old("siswa.{$i}.status", $d->status) === 'terlambat' ? 'selected' : '' }}>
                                        Terlambat</option>
                                </select>
                                <input type="text" name="siswa[{{ $i }}][keterangan]" placeholder="Ket."
                                    value="{{ old("siswa.{$i}.keterangan", $d->keterangan) }}"
                                    class="rounded-md border-gray-300 text-sm py-1 w-32">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 bg-gray-50 px-6 py-4">
                    <a href="{{ route('presensi.show', $presensi->id) }}"
                        class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 border border-gray-300 hover:bg-gray-100">
                        Batal
                    </a>
                    <button type="submit"
                        class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function setAll(status) {
            document.querySelectorAll('select[name*="[status]"]').forEach(s => {
                s.value = status;
                s.className = 'rounded-md border-gray-300 text-sm py-1 ' +
                    (status === 'hadir' ? 'bg-green-50 border-green-300' :
                        status === 'alpha' ? 'bg-red-50 border-red-300' :
                        status === 'sakit' ? 'bg-yellow-50 border-yellow-300' :
                        status === 'izin' ? 'bg-orange-50 border-orange-300' :
                        'bg-purple-50 border-purple-300');
            });
        }
    </script>
</x-app-layout>
