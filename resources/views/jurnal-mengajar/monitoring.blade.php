<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Monitoring Jurnal Mengajar') }}</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            {{-- Filter --}}
            <div class="mb-4 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <form method="GET" class="flex items-end gap-4 flex-wrap">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Guru</label>
                            <select name="guru_id" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Semua Guru</option>
                                @foreach ($gurus as $g)
                                    <option value="{{ $g->id }}"
                                        {{ request('guru_id') == $g->id ? 'selected' : '' }}>{{ $g->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Tanggal</label>
                            <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                                class="mt-1 rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Status</label>
                            <select name="verified" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Semua</option>
                                <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>Belum
                                    Verifikasi</option>
                                <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Terverifikasi
                                </option>
                            </select>
                        </div>
                        <div>
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($jurnals->isEmpty())
                        <p class="text-center py-8 text-sm text-gray-400">Tidak ada jurnal.</p>
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
                                                class="w-full h-48 object-cover">
                                        </a>
                                    @endif
                                    <div class="p-3">
                                        <div class="font-semibold text-sm text-gray-900">{{ $j->guru->nama ?? '—' }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $j->jadwal->mapel->nama ?? '—' }} •
                                            {{ $j->kelas->nama }}</div>
                                        <div class="text-xs text-gray-400">{{ $j->tanggal->format('d/m/Y') }} •
                                            Pertemuan ke-{{ $j->pertemuan_ke }}</div>
                                        <div class="mt-1 text-xs">
                                            <span class="text-green-600">H:{{ $counts['hadir'] }}</span>
                                            <span class="text-yellow-600 ml-1">S:{{ $counts['sakit'] }}</span>
                                            <span class="text-orange-600 ml-1">I:{{ $counts['izin'] }}</span>
                                            <span class="text-red-600 ml-1">A:{{ $counts['alpha'] }}</span>
                                            @if ($counts['terlambat'] > 0)
                                                <span class="text-purple-600 ml-1">T:{{ $counts['terlambat'] }}</span>
                                            @endif
                                        </div>
                                        @if ($j->materi)
                                            <div class="text-xs text-gray-500 truncate mt-1">{{ $j->materi }}</div>
                                        @endif
                                        <div class="mt-2 flex items-center justify-between">
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                                {{ $j->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                {{ $j->is_verified ? 'Terverifikasi' : 'Pending' }}
                                            </span>
                                            @if (!$j->is_verified)
                                                <form action="{{ route('jurnal-mengajar.verify', $j) }}" method="POST"
                                                    onsubmit="return confirm('Verifikasi jurnal ini?')">
                                                    @csrf
                                                    <button type="submit"
                                                        class="rounded-md bg-green-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-green-500">
                                                        Verifikasi
                                                    </button>
                                                </form>
                                            @endif
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
