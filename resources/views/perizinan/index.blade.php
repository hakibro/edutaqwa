<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Perizinan Siswa') }}</h2>
            <a href="{{ route('perizinan.create') }}"
                class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                + Input Perizinan
            </a>
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

            {{-- Filter --}}
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <form method="GET" class="flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Kelas</label>
                            <select name="kelas_id" class="rounded-md border-gray-300 text-sm">
                                <option value="">Semua Kelas</option>
                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id }}"
                                        {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
                            <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                                class="rounded-md border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Jenis</label>
                            <select name="jenis" class="rounded-md border-gray-300 text-sm">
                                <option value="">Semua</option>
                                <option value="sakit" {{ request('jenis') == 'sakit' ? 'selected' : '' }}>Sakit
                                </option>
                                <option value="izin" {{ request('jenis') == 'izin' ? 'selected' : '' }}>Izin</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Filter
                            </button>
                            <a href="{{ route('perizinan.index') }}"
                                class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Card Perizinan --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    @if (!$perizinans->isEmpty())
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Perizinan</h3>
                            <a href="{{ route('perizinan.create') }}"
                                class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Tambah Perizinan
                            </a>
                        </div>
                    @endif

                    @if ($perizinans->isEmpty())
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                                stroke-width="1" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <p class="text-sm text-gray-500 mb-4">Belum ada data perizinan.</p>
                            <a href="{{ route('perizinan.create') }}"
                                class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Input Perizinan Baru
                            </a>
                        </div>
                    @else
                        {{-- Card Grid — responsive: 1 col mobile, 2 col tablet, 3 col desktop --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($perizinans as $p)
                                <div
                                    class="relative rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-lg hover:border-gray-300 transition-all duration-200">
                                    {{-- Top row: tanggal + jenis badge + status --}}
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium text-gray-500">
                                                {{ $p->tanggal->format('d/m/Y') }}
                                            </span>
                                            <span
                                                class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                                                {{ $p->jenis === 'sakit' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                                {{ ucfirst($p->jenis) }}
                                            </span>
                                        </div>
                                        @if ($p->is_applied)
                                            <span
                                                class="inline-flex items-center gap-1 text-xs text-green-600 bg-green-50 rounded-full px-2 py-0.5"
                                                title="Telah diterapkan ke jurnal">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 bg-gray-50 rounded-full px-2 py-0.5"
                                                title="Menunggu jurnal">pending</span>
                                        @endif
                                    </div>

                                    {{-- Siswa avatar + info --}}
                                    <div class="flex items-center gap-3 mb-3">
                                        <div
                                            class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                            {{ strtoupper(mb_substr($p->siswa->nama, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-semibold text-gray-900 text-sm truncate">
                                                {{ $p->siswa->nama }}</div>
                                            <div class="text-xs text-gray-500 truncate">{{ $p->siswa->nis ?? '-' }}
                                                &middot; {{ $p->kelas->nama }}</div>
                                        </div>
                                    </div>

                                    {{-- Keterangan --}}
                                    @if ($p->keterangan)
                                        <div class="mb-3 bg-gray-50 rounded-lg px-3 py-2">
                                            <p class="text-xs text-gray-600 line-clamp-2 italic">"{{ $p->keterangan }}"
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Validator info --}}
                                    @if ($p->validator)
                                        <div class="text-xs text-gray-400 mb-3">
                                            Diinput oleh: <span
                                                class="font-medium text-gray-500">{{ $p->validator->name ?? '-' }}</span>
                                        </div>
                                    @endif

                                    {{-- Actions --}}
                                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                        @if ($p->lampiran)
                                            <a href="{{ Storage::url($p->lampiran) }}" target="_blank"
                                                class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 rounded-lg px-2.5 py-1.5 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                                </svg>
                                                Lampiran
                                            </a>
                                        @else
                                            <span></span>
                                        @endif
                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('perizinan.edit', $p) }}"
                                                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                                Edit
                                            </a>
                                            <form action="{{ route('perizinan.destroy', $p) }}" method="POST"
                                                onsubmit="return confirm('Hapus perizinan ini? Status di jurnal akan dikembalikan ke Alpha.')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $perizinans->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
