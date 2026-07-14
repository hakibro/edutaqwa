<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Approval Guru') }}</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Nama</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Lembaga</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Jenis PTK</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        TMT</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        NIY</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Satminkal</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Dibuat</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($gurus as $g)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $g->nama }}
                                            @if ($g->email)
                                                <br><span class="text-xs text-gray-500">{{ $g->email }}</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $g->lembaga->nama }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $g->jenisPtk?->nama ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $g->tmt?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-700">
                                            {{ $g->niy ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span
                                                class="rounded-full px-2 py-1 text-xs font-semibold {{ $g->status_satminkal ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                                {{ $g->status_satminkal ? 'Ya' : 'Tidak' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ $g->created_at->diffForHumans() }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <div class="flex justify-end gap-2" x-data="{ showReject: false }">
                                                <form action="{{ route('guru.approve', $g) }}" method="POST"
                                                    class="inline">
                                                    @csrf @method('PUT')
                                                    <button type="submit"
                                                        class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-xs"
                                                        onclick="return confirm('Setujui guru {{ $g->nama }}?')">Setujui</button>
                                                </form>
                                                <button @click="showReject = !showReject"
                                                    class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Tolak</button>
                                            </div>
                                            <div x-show="showReject" x-transition class="mt-2">
                                                <form action="{{ route('guru.reject', $g) }}" method="POST"
                                                    class="flex gap-2">
                                                    @csrf @method('PUT')
                                                    <input type="text" name="alasan" placeholder="Alasan (opsional)"
                                                        class="w-full text-xs rounded border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                                    <button type="submit"
                                                        class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Konfirmasi</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">Semua
                                            guru sudah disetujui. Tidak ada pending.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $gurus->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
