<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Laporan Absensi PTK') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <form method="GET" class="flex items-end gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Bulan</label>
                                <input type="month" name="bulan" value="{{ $bulan }}"
                                    class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Tampilkan</button>
                        </form>
                        <a href="{{ route('laporan.export-absensi-ptk', ['bulan' => $bulan]) }}"
                            class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Export
                            Excel</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">No</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Nama Guru</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Tanggal</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Check-in</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Check-out</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Status</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Keterlambatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($absensis as $a)
                                    @php
                                        $statusColor = match ($a->status) {
                                            'tepat_waktu' => 'bg-green-100 text-green-700',
                                            'terlambat' => 'bg-yellow-100 text-yellow-700',
                                            'pulang_awal' => 'bg-orange-100 text-orange-700',
                                            'tidak_absen' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 text-gray-500">{{ $loop->iteration }}</td>
                                        <td class="px-3 py-2 font-medium text-gray-900">{{ $a->guru->nama ?? '-' }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $a->tanggal->format('d M Y') }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $a->check_in?->format('H:i') ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-gray-600">{{ $a->check_out?->format('H:i') ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <span
                                                class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusColor }}">
                                                {{ str_replace('_', ' ', $a->status ?? '-') }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-gray-600">{{ $a->keterlambatan_menit }} menit</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-4 text-center text-gray-500">Tidak ada absensi
                                            bulan ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $absensis->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
