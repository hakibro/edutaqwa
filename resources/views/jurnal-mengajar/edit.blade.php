<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Edit Jurnal Mengajar') }}</h2>
            <div class="flex items-center gap-2 text-sm">
                <span class="text-gray-500">{{ $jurnal->jadwal->mapel->nama ?? '—' }} —
                    {{ $jurnal->kelas->nama }}</span>
                <span class="text-gray-400">|</span>
                <span class="text-gray-500">Pertemuan ke-{{ $jurnal->pertemuan_ke }}</span>
                <span class="text-gray-400">|</span>
                <span class="text-gray-500">{{ $jurnal->tanggal->format('d/m/Y') }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form action="{{ route('jurnal-mengajar.update', $jurnal) }}" method="POST" id="edit-jurnal-form">
                @csrf
                @method('PUT')

                {{-- Info jurnal --}}
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-3">Info Jurnal</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="block text-xs text-gray-500">Mapel</span>
                                <span class="font-semibold">{{ $jurnal->jadwal->mapel->nama ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Kelas</span>
                                <span class="font-semibold">{{ $jurnal->kelas->nama }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Pertemuan ke-</span>
                                <span class="font-semibold">{{ $jurnal->pertemuan_ke }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Tanggal</span>
                                <span class="font-semibold">{{ $jurnal->tanggal->format('d/m/Y') }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Jam Mulai</span>
                                <span class="font-semibold">{{ $jurnal->jam_mulai ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Status</span>
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $jurnal->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $jurnal->is_verified ? 'Terverifikasi' : 'Pending' }}
                                </span>
                            </div>
                        </div>
                        @if ($jurnal->foto_path)
                            <div class="mt-3">
                                <img src="{{ Storage::url($jurnal->foto_path) }}" alt="Selfie"
                                    class="w-48 rounded-lg border shadow-sm">
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Materi --}}
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-3">Materi Pertemuan</h3>
                        <textarea name="materi" rows="3" placeholder="Materi yang diajarkan..."
                            class="w-full rounded-md border-gray-300 text-sm">{{ old('materi', $jurnal->materi) }}</textarea>
                    </div>
                </div>

                {{-- Presensi Siswa --}}
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-base font-semibold text-gray-900">Kehadiran Siswa</h3>
                            <div class="flex gap-2">
                                <button type="button" onclick="setAll('hadir')"
                                    class="rounded bg-green-100 px-3 py-1 text-xs font-medium text-green-700 hover:bg-green-200">Semua
                                    Hadir</button>
                                <button type="button" onclick="setAll('alpha')"
                                    class="rounded bg-red-100 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-200">Semua
                                    Alpha</button>
                            </div>
                        </div>

                        @if ($siswas->isEmpty())
                            <p class="text-sm text-gray-500">Tidak ada siswa di kelas ini.</p>
                        @else
                            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="sticky top-0 bg-gray-50">
                                        <tr class="border-b">
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 w-8">#</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Nama</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-green-700 w-10">H
                                            </th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-yellow-700 w-10">S
                                            </th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-orange-700 w-10">I
                                            </th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-red-700 w-10">A
                                            </th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-purple-700 w-10">T
                                            </th>
                                            <th class="px-2 py-2 text-xs font-medium text-gray-500 w-28">Ket</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($siswas as $i => $s)
                                            @php
                                                $presensi = $presensiMap->get($s->id);
                                                $status = $presensi->status ?? 'hadir';
                                            @endphp
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-2 py-2 text-gray-400">{{ $i + 1 }}</td>
                                                <td class="px-2 py-2 font-medium text-gray-900 whitespace-nowrap">
                                                    {{ $s->nama }}
                                                    <span class="ml-1 text-xs text-gray-400">{{ $s->nis }}</span>
                                                </td>
                                                <td class="px-2 py-2 text-center">
                                                    <input type="radio" name="siswa[{{ $i }}][status]"
                                                        value="hadir" {{ $status === 'hadir' ? 'checked' : '' }}
                                                        class="h-4 w-4 text-green-600 focus:ring-green-500 cursor-pointer"
                                                        onchange="toggleKet({{ $i }}, this)">
                                                </td>
                                                <td class="px-2 py-2 text-center">
                                                    <input type="radio" name="siswa[{{ $i }}][status]"
                                                        value="sakit" {{ $status === 'sakit' ? 'checked' : '' }}
                                                        class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 cursor-pointer"
                                                        onchange="toggleKet({{ $i }}, this)">
                                                </td>
                                                <td class="px-2 py-2 text-center">
                                                    <input type="radio" name="siswa[{{ $i }}][status]"
                                                        value="izin" {{ $status === 'izin' ? 'checked' : '' }}
                                                        class="h-4 w-4 text-orange-600 focus:ring-orange-500 cursor-pointer"
                                                        onchange="toggleKet({{ $i }}, this)">
                                                </td>
                                                <td class="px-2 py-2 text-center">
                                                    <input type="radio" name="siswa[{{ $i }}][status]"
                                                        value="alpha" {{ $status === 'alpha' ? 'checked' : '' }}
                                                        class="h-4 w-4 text-red-600 focus:ring-red-500 cursor-pointer"
                                                        onchange="toggleKet({{ $i }}, this)">
                                                </td>
                                                <td class="px-2 py-2 text-center">
                                                    <input type="radio" name="siswa[{{ $i }}][status]"
                                                        value="terlambat"
                                                        {{ $status === 'terlambat' ? 'checked' : '' }}
                                                        class="h-4 w-4 text-purple-600 focus:ring-purple-500 cursor-pointer"
                                                        onchange="toggleKet({{ $i }}, this)">
                                                </td>
                                                <td class="px-2 py-2">
                                                    <input type="hidden" name="siswa[{{ $i }}][id]"
                                                        value="{{ $s->id }}">
                                                    <input type="text"
                                                        name="siswa[{{ $i }}][keterangan]"
                                                        placeholder="Ket." id="ket-{{ $i }}"
                                                        value="{{ old('siswa.' . $i . '.keterangan', $presensi->keterangan ?? '') }}"
                                                        class="w-full rounded-md border-gray-300 text-sm py-1 {{ $status === 'hadir' ? 'hidden' : '' }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="flex justify-between">
                    <a href="{{ route('jurnal-mengajar.show', $jurnal) }}"
                        class="rounded-md bg-gray-200 px-6 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                        &larr; Batal
                    </a>
                    <button type="submit"
                        class="rounded-md bg-indigo-600 px-8 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Set all students status
            function setAll(status) {
                document.querySelectorAll('input[type="radio"][name$="[status]"]').forEach(r => {
                    if (r.value === status) r.checked = true;
                    r.dispatchEvent(new Event('change'));
                });
            }

            // Toggle keterangan input — visible only when NOT hadir
            function toggleKet(idx, radio) {
                const ket = document.getElementById('ket-' + idx);
                if (ket) {
                    ket.classList.toggle('hidden', radio.value === 'hadir');
                }
            }

            // Init on load — hide ket for hadir students
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('input[type="radio"][name$="[status]"]:checked').forEach(r => {
                    r.dispatchEvent(new Event('change'));
                });
            });
        </script>
    @endpush
</x-app-layout>
