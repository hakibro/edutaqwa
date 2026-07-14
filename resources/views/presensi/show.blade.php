<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Detail Presensi') }}</h2>
            <a href="{{ route('presensi.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr;
                Kembali</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="block text-xs text-gray-500">Tanggal</span>
                            <span class="font-semibold">{{ $presensi->tanggal->format('d/m/Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500">Mapel</span>
                            <span class="font-semibold">{{ $presensi->jadwal->mapel->nama }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500">Kelas</span>
                            <span class="font-semibold">{{ $presensi->jadwal->kelas->nama }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500">Pertemuan ke-</span>
                            <span class="font-semibold">{{ $presensi->pertemuan_ke }}</span>
                        </div>
                    </div>
                    @if ($presensi->materi)
                        <div class="mt-3 text-sm">
                            <span class="block text-xs text-gray-500">Materi</span>
                            <span>{{ $presensi->materi }}</span>
                        </div>
                    @endif
                </div>

                <div class="p-6">
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
                                @foreach ($presensi->detailPresensis as $i => $d)
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

                <div class="flex justify-end gap-3 bg-gray-50 px-6 py-4">
                    <a href="{{ route('presensi.edit', $presensi->id) }}"
                        class="rounded-md bg-yellow-600 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-500">
                        Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
