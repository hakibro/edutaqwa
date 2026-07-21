<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Detail Jurnal Mengajar') }}</h2>
            <div class="flex items-center gap-3">
                @if (!$jurnal->is_verified)
                    <a href="{{ route('jurnal-mengajar.edit', $jurnal) }}"
                        class="rounded-md bg-yellow-500 px-4 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-yellow-400">
                        ✏️ Edit
                    </a>
                @endif
                <a href="{{ route('jurnal-mengajar.index') }}"
                    class="text-sm text-indigo-600 hover:text-indigo-900">&larr;
                    Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{-- Foto Selfie --}}
                    @if ($jurnal->foto_path)
                        <div class="mb-4">
                            <img src="{{ Storage::url($jurnal->foto_path) }}" alt="Selfie"
                                class="w-full max-w-md mx-auto rounded-lg shadow">
                        </div>
                    @endif

                    {{-- Info --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
                        <div>
                            <span class="block text-xs text-gray-500">Tanggal</span>
                            <span class="font-semibold">{{ $jurnal->tanggal->format('d/m/Y') }}</span>
                        </div>
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

                    @if ($jurnal->latitude)
                        <div class="text-sm text-gray-500 mb-4">
                            Lokasi: {{ $jurnal->latitude }}, {{ $jurnal->longitude }}
                        </div>
                    @endif

                    @if ($jurnal->materi)
                        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                            <span class="block text-xs text-gray-500 mb-1">Materi</span>
                            <span class="text-sm text-gray-900">{{ $jurnal->materi }}</span>
                        </div>
                    @endif

                    @if ($jurnal->atp)
                        <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <span class="block text-xs text-gray-500 mb-2 font-semibold">ATP — Alur Tujuan
                                Pembelajaran</span>
                            <div class="text-sm space-y-1.5">
                                <p class="text-gray-700">
                                    <span class="font-medium text-blue-700">CP:</span>
                                    @if ($jurnal->atp->tp->cp->kode)
                                        <span class="text-blue-600">[{{ $jurnal->atp->tp->cp->kode }}]</span>
                                    @endif
                                    {{ $jurnal->atp->tp->cp->deskripsi ?? '-' }}
                                </p>
                                <p class="text-gray-700">
                                    <span class="font-medium text-indigo-700">TP:</span>
                                    @if ($jurnal->atp->tp->kode)
                                        <span class="text-indigo-600">[{{ $jurnal->atp->tp->kode }}]</span>
                                    @endif
                                    {{ $jurnal->atp->tp->deskripsi ?? '-' }}
                                </p>
                                <p class="text-gray-700">
                                    <span class="font-medium text-gray-600">ATP:</span>
                                    Minggu {{ $jurnal->atp->minggu_ke }} — {{ $jurnal->atp->materi }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($jurnal->is_verified)
                        <div class="mb-4 text-xs text-gray-500">
                            Diverifikasi {{ $jurnal->verified_at->format('d/m/Y H:i') }} oleh
                            {{ $jurnal->verifikator->name ?? '—' }}
                        </div>
                    @endif
                </div>

                {{-- Daftar Siswa --}}
                <div class="p-6 border-t border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-3">Kehadiran Siswa</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="border-b bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">No</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Nama</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">NIS</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($jurnal->detailSiswas as $i => $d)
                                    <tr>
                                        <td class="px-4 py-2 text-gray-400">{{ $i + 1 }}</td>
                                        <td class="px-4 py-2 font-medium">{{ $d->siswa->nama }}</td>
                                        <td class="px-4 py-2 text-gray-500">{{ $d->siswa->nis }}</td>
                                        <td class="px-4 py-2">
                                            <span
                                                class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                                                {{ $d->status === 'hadir' ? 'bg-green-100 text-green-700' : '' }}
                                                {{ $d->status === 'sakit' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                {{ $d->status === 'izin' ? 'bg-orange-100 text-orange-700' : '' }}
                                                {{ $d->status === 'alpha' ? 'bg-red-100 text-red-700' : '' }}
                                                {{ $d->status === 'terlambat' ? 'bg-purple-100 text-purple-700' : '' }}">
                                                {{ ucfirst($d->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-gray-500">{{ $d->keterangan ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
