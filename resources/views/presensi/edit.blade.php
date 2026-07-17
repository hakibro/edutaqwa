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

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr class="border-b">
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 w-8">#</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Nama</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-green-700 w-10">H</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-yellow-700 w-10">S</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-orange-700 w-10">I</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-red-700 w-10">A</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-purple-700 w-10">T</th>
                                    <th class="px-2 py-2 text-xs font-medium text-gray-500 w-28">Ket</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($presensi->detailPresensis as $i => $d)
                                    @php $curStatus = old("siswa.{$i}.status", $d->status); @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-2 py-2 text-gray-400">{{ $i + 1 }}</td>
                                        <td class="px-2 py-2 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $d->siswa->nama }}
                                            <span class="ml-1 text-xs text-gray-400">{{ $d->siswa->nis }}</span>
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <input type="radio" name="siswa[{{ $i }}][status]"
                                                value="hadir"
                                                class="h-4 w-4 text-green-600 focus:ring-green-500 cursor-pointer"
                                                {{ $curStatus === 'hadir' ? 'checked' : '' }}
                                                onchange="toggleKet({{ $i }}, this)">
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <input type="radio" name="siswa[{{ $i }}][status]"
                                                value="sakit"
                                                class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 cursor-pointer"
                                                {{ $curStatus === 'sakit' ? 'checked' : '' }}
                                                onchange="toggleKet({{ $i }}, this)">
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <input type="radio" name="siswa[{{ $i }}][status]"
                                                value="izin"
                                                class="h-4 w-4 text-orange-600 focus:ring-orange-500 cursor-pointer"
                                                {{ $curStatus === 'izin' ? 'checked' : '' }}
                                                onchange="toggleKet({{ $i }}, this)">
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <input type="radio" name="siswa[{{ $i }}][status]"
                                                value="alpha"
                                                class="h-4 w-4 text-red-600 focus:ring-red-500 cursor-pointer"
                                                {{ $curStatus === 'alpha' ? 'checked' : '' }}
                                                onchange="toggleKet({{ $i }}, this)">
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <input type="radio" name="siswa[{{ $i }}][status]"
                                                value="terlambat"
                                                class="h-4 w-4 text-purple-600 focus:ring-purple-500 cursor-pointer"
                                                {{ $curStatus === 'terlambat' ? 'checked' : '' }}
                                                onchange="toggleKet({{ $i }}, this)">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="hidden" name="siswa[{{ $i }}][id]"
                                                value="{{ $d->id }}">
                                            <input type="text" name="siswa[{{ $i }}][keterangan]"
                                                placeholder="Ket." id="ket-{{ $i }}"
                                                value="{{ old("siswa.{$i}.keterangan", $d->keterangan) }}"
                                                class="w-full rounded-md border-gray-300 text-sm py-1 {{ $curStatus === 'hadir' ? 'hidden' : '' }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
            document.querySelectorAll('input[type="radio"][name$="[status]"]').forEach(r => {
                if (r.value === status) r.checked = true;
                r.dispatchEvent(new Event('change'));
            });
        }

        function toggleKet(idx, radio) {
            const ket = document.getElementById('ket-' + idx);
            if (ket) {
                ket.classList.toggle('hidden', radio.value === 'hadir');
                if (radio.value === 'hadir') ket.value = '';
            }
        }
    </script>
</x-app-layout>
