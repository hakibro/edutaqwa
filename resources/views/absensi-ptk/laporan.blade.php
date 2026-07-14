<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Laporan Absensi PTK') }}</h2>
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
                            <label class="block text-xs font-medium text-gray-500">Bulan</label>
                            <input type="month" name="bulan" value="{{ $bulan }}"
                                class="mt-1 rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Guru</label>
                            <select name="guru_id" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Semua Guru</option>
                                @foreach ($gurus as $g)
                                    <option value="{{ $g->id }}" {{ $guruId == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama }}</option>
                                @endforeach
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

            {{-- Summary --}}
            @if (empty($guruId) && count($summary) > 0)
                <div class="mb-4 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Rekap Bulan {{ $bulan }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Guru</th>
                                        <th class="px-3 py-2 text-center font-medium text-green-600">Tepat Waktu</th>
                                        <th class="px-3 py-2 text-center font-medium text-yellow-600">Terlambat</th>
                                        <th class="px-3 py-2 text-center font-medium text-orange-600">Pulang Awal</th>
                                        <th class="px-3 py-2 text-center font-medium text-red-600">Tidak Absen</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($gurus as $g)
                                        @php $s = $summary[$g->id] ?? []; @endphp
                                        <tr>
                                            <td class="px-3 py-2 text-gray-900">{{ $g->nama }}</td>
                                            <td class="px-3 py-2 text-center text-green-700">
                                                {{ $s['tepat_waktu'] ?? 0 }}</td>
                                            <td class="px-3 py-2 text-center text-yellow-700">{{ $s['terlambat'] ?? 0 }}
                                            </td>
                                            <td class="px-3 py-2 text-center text-orange-700">
                                                {{ $s['pulang_awal'] ?? 0 }}</td>
                                            <td class="px-3 py-2 text-center text-red-700">{{ $s['tidak_absen'] ?? 0 }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Detail --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Guru</th>
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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($absensis as $a)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                        {{ $a->guru->nama ?? '-' }}</td>
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
                                            {{ $a->status === 'tidak_absen' ? 'bg-red-100 text-red-700' : '' }}">
                                            {{ str_replace('_', ' ', ucfirst($a->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">Tidak ada
                                        data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $absensis->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
