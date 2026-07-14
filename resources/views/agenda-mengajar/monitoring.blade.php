<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Monitoring Agenda Mengajar') }}</h2>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse ($agendas as $a)
                            <div class="rounded-lg border border-gray-200 overflow-hidden">
                                <a href="{{ route('agenda-mengajar.show', $a) }}" class="block">
                                    <img src="{{ Storage::url($a->foto_path) }}" alt="Selfie"
                                        class="w-full h-48 object-cover">
                                </a>
                                <div class="p-3">
                                    <div class="font-semibold text-sm text-gray-900">{{ $a->guru->nama ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ $a->jadwal->mapel->nama ?? '—' }} •
                                        {{ $a->kelas->nama }}</div>
                                    <div class="text-xs text-gray-400">{{ $a->tanggal->format('d/m/Y') }} • Pertemuan
                                        ke-{{ $a->pertemuan_ke }}</div>
                                    <div class="mt-2 flex items-center justify-between">
                                        <span
                                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                            {{ $a->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ $a->is_verified ? 'Terverifikasi' : 'Pending' }}
                                        </span>
                                        @if (!$a->is_verified)
                                            <form action="{{ route('agenda-mengajar.verify', $a) }}" method="POST"
                                                onsubmit="return confirm('Verifikasi agenda ini?')">
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
                        @empty
                            <div class="col-span-full text-center py-8 text-sm text-gray-400">Tidak ada agenda.</div>
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
