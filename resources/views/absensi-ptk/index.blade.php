<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Absensi PTK (Kehadiran Harian)') }}</h2>
            <span class="text-sm text-gray-500">{{ $guru->nama }}</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            {{-- Status Hari Ini --}}
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Hari Ini</h3>
                    @if (!$jamKerjaHariIni)
                        <p class="text-sm text-gray-500">Tidak ada jam kerja untuk hari ini.</p>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <span class="block text-xs text-gray-500">Jam Masuk</span>
                                <span class="text-lg font-semibold">{{ $jamKerjaHariIni->jam_masuk }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Jam Pulang</span>
                                <span class="text-lg font-semibold">{{ $jamKerjaHariIni->jam_pulang }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Toleransi</span>
                                <span class="text-lg font-semibold">{{ $jamKerjaHariIni->toleransi_keterlambatan }}
                                    menit</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Status Hari Ini</span>
                                @if ($absensiHariIni)
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                        {{ $absensiHariIni->status === 'tepat_waktu' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $absensiHariIni->status === 'terlambat' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $absensiHariIni->status === 'pulang_awal' ? 'bg-orange-100 text-orange-700' : '' }}
                                        {{ $absensiHariIni->status === 'tidak_absen' ? 'bg-red-100 text-red-700' : '' }}">
                                        {{ str_replace('_', ' ', ucfirst($absensiHariIni->status)) }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">Belum absen</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex gap-3">
                            @if ($canCheckIn)
                                <form action="{{ route('absensi-ptk.check-in') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="rounded-md bg-green-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-green-500">
                                        ✅ Check-in
                                    </button>
                                </form>
                            @endif
                            @if ($canCheckOut)
                                <form action="{{ route('absensi-ptk.check-out') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="rounded-md bg-orange-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-orange-500">
                                        ⏏️ Check-out
                                    </button>
                                </form>
                            @endif
                            @if ($absensiHariIni && $absensiHariIni->check_in && !$canCheckIn && !$canCheckOut)
                                <p class="text-sm text-gray-500 py-3">✔ Check-in & Check-out selesai hari ini.</p>
                            @endif
                        </div>
                        @if ($absensiHariIni && $absensiHariIni->check_in)
                            <p class="mt-2 text-xs text-gray-400">Check-in:
                                {{ $absensiHariIni->check_in->format('H:i') }}
                                @if ($absensiHariIni->keterlambatan_menit > 0)
                                    (Terlambat {{ $absensiHariIni->keterlambatan_menit }} menit)
                                @endif
                            </p>
                        @endif
                        @if ($absensiHariIni && $absensiHariIni->check_out)
                            <p class="text-xs text-gray-400">Check-out: {{ $absensiHariIni->check_out->format('H:i') }}
                            </p>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Riwayat --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Riwayat Absensi</h3>
                        <form method="GET" class="flex items-center gap-2">
                            <input type="month" name="bulan" value="{{ $bulan }}"
                                class="rounded-md border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                        </form>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Tanggal</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Check-in</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Check-out</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Keterlambatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($absensis as $a)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                        {{ $a->tanggal->format('d/m/Y') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                        {{ $a->check_in?->format('H:i') ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                        {{ $a->check_out?->format('H:i') ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                            {{ $a->status === 'tepat_waktu' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $a->status === 'terlambat' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                            {{ $a->status === 'pulang_awal' ? 'bg-orange-100 text-orange-700' : '' }}
                                            {{ $a->status === 'tidak_absen' ? 'bg-red-100 text-red-700' : '' }}
                                            {{ $a->status === 'libur' ? 'bg-gray-100 text-gray-700' : '' }}">
                                            {{ str_replace('_', ' ', ucfirst($a->status)) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                        {{ $a->keterlambatan_menit > 0 ? $a->keterlambatan_menit . ' menit' : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">Belum ada
                                        data absensi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
