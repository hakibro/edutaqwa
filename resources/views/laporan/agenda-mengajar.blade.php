<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Laporan Agenda Mengajar') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <form method="GET" class="flex items-end gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Guru</label>
                                <select name="guru_id"
                                    class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Semua Guru</option>
                                    @foreach ($guruList as $g)
                                        <option value="{{ $g->id }}" @selected($guruId == $g->id)>
                                            {{ $g->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Filter</button>
                        </form>
                        <a href="{{ route('laporan.export-agenda-mengajar', ['guru_id' => $guruId]) }}"
                            class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Export
                            Excel</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">No</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Guru</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Mapel</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Kelas</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Tanggal</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Deskripsi</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($agendas as $a)
                                    <tr>
                                        <td class="px-3 py-2 text-gray-500">{{ $loop->iteration }}</td>
                                        <td class="px-3 py-2 font-medium text-gray-900">{{ $a->guru->nama ?? '-' }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $a->jadwal->mapel->nama ?? '-' }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $a->jadwal->kelas->nama ?? '-' }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $a->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="px-3 py-2 text-gray-600 max-w-xs truncate">{{ $a->deskripsi ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <span
                                                class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $a->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                {{ $a->is_verified ? 'Verified' : 'Pending' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-4 text-center text-gray-500">Belum ada agenda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $agendas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
