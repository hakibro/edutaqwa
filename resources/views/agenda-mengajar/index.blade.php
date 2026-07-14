<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Agenda Mengajar (Selfie)') }}</h2>
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
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Jadwal Hari Ini — Ambil Selfie</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($jadwalHariIni as $j)
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <div class="font-semibold text-gray-900">{{ $j->mapel->nama }}</div>
                                    <div class="text-sm text-gray-500">{{ $j->kelas->nama }} — Jam
                                        ke-{{ $j->jam_ke }}</div>
                                    <a href="{{ route('agenda-mengajar.create', ['jadwal_id' => $j->id]) }}"
                                        class="mt-3 inline-block rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                        📸 Ambil Selfie
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Riwayat Agenda --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Riwayat Agenda</h3>
                        <form method="GET" class="flex items-center gap-2">
                            <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                                class="rounded-md border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                        </form>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse ($agendas as $a)
                            <div class="rounded-lg border border-gray-200 overflow-hidden">
                                <a href="{{ route('agenda-mengajar.show', $a) }}" class="block">
                                    <img src="{{ Storage::url($a->foto_path) }}" alt="Selfie"
                                        class="w-full h-48 object-cover">
                                </a>
                                <div class="p-3">
                                    <div class="font-semibold text-sm text-gray-900">
                                        {{ $a->jadwal->mapel->nama ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ $a->kelas->nama }} •
                                        {{ $a->tanggal->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-400">Pertemuan ke-{{ $a->pertemuan_ke }}</div>
                                    <div class="mt-2 flex items-center justify-between">
                                        <span
                                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                            {{ $a->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ $a->is_verified ? 'Terverifikasi' : 'Pending' }}
                                        </span>
                                        @if (!$a->is_verified)
                                            <form action="{{ route('agenda-mengajar.destroy', $a) }}" method="POST"
                                                onsubmit="return confirm('Hapus agenda ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-xs text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-8 text-sm text-gray-400">Belum ada agenda mengajar.
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-4">
                        {{ $agendas->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
