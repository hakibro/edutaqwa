<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Jurnal Mengajar') }}</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            {{-- Jadwal Hari Ini --}}
            @if ($jadwalHariIni->isNotEmpty())
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Jadwal Hari Ini</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($jadwalHariIni as $j)
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <div class="font-semibold text-gray-900">{{ $j->mapel->nama }}</div>
                                    <div class="text-sm text-gray-500">{{ $j->kelas->nama }} — Jam
                                        ke-{{ $j->jam_ke }}</div>
                                    <a href="{{ route('jurnal-mengajar.create', ['jadwal_id' => $j->id]) }}"
                                        class="mt-3 inline-block rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                        📋 Isi Jurnal
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Riwayat Jurnal --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Riwayat Jurnal</h3>
                        <form method="GET" class="flex items-center gap-2">
                            <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                                class="rounded-md border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                        </form>
                    </div>

                    @if ($jurnals->isEmpty())
                        <p class="text-center py-8 text-sm text-gray-400">Belum ada jurnal mengajar.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($jurnals as $j)
                                @php
                                    $counts = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'terlambat' => 0];
                                    foreach ($j->detailSiswas as $d) {
                                        $counts[$d->status] = ($counts[$d->status] ?? 0) + 1;
                                    }
                                @endphp
                                <div class="rounded-lg border border-gray-200 overflow-hidden">
                                    @if ($j->foto_path)
                                        <a href="{{ route('jurnal-mengajar.show', $j) }}" class="block">
                                            <img src="{{ Storage::url($j->foto_path) }}" alt="Selfie"
                                                class="w-full h-40 object-cover">
                                        </a>
                                    @endif
                                    <div class="p-3">
                                        <div class="font-semibold text-sm text-gray-900">
                                            {{ $j->jadwal->mapel->nama ?? '—' }}</div>
                                        <div class="text-xs text-gray-500">{{ $j->kelas->nama }} •
                                            {{ $j->tanggal->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-400">Pertemuan ke-{{ $j->pertemuan_ke }}</div>
                                        @if ($j->materi)
                                            <div class="text-xs text-gray-500 truncate mt-1">{{ $j->materi }}</div>
                                        @endif
                                        <div class="mt-2 text-xs">
                                            <span class="text-green-600">H:{{ $counts['hadir'] }}</span>
                                            <span class="text-yellow-600 ml-1">S:{{ $counts['sakit'] }}</span>
                                            <span class="text-orange-600 ml-1">I:{{ $counts['izin'] }}</span>
                                            <span class="text-red-600 ml-1">A:{{ $counts['alpha'] }}</span>
                                            @if ($counts['terlambat'] > 0)
                                                <span class="text-purple-600 ml-1">T:{{ $counts['terlambat'] }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-2 flex items-center justify-between">
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                                {{ $j->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                {{ $j->is_verified ? 'Terverifikasi' : 'Pending' }}
                                            </span>
                                            <div class="flex gap-2">
                                                <a href="{{ route('jurnal-mengajar.show', $j) }}"
                                                    class="text-xs text-indigo-600 hover:text-indigo-900">Detail</a>
                                                @if (!$j->is_verified)
                                                    <a href="{{ route('jurnal-mengajar.edit', $j) }}"
                                                        class="text-xs text-yellow-600 hover:text-yellow-900">Edit</a>
                                                    <form action="{{ route('jurnal-mengajar.destroy', $j) }}"
                                                        method="POST" onsubmit="return confirm('Hapus jurnal ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit"
                                                            class="text-xs text-red-600 hover:text-red-900">Hapus</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">{{ $jurnals->withQueryString()->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
