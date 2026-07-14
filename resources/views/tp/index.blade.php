<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('TP — Tujuan Pembelajaran') }}</h2>
            @if (auth()->user()->isGuru())
                <a href="{{ route('tp.create') }}"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    + Tambah TP
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <div class="mb-6 bg-white p-4 shadow-sm sm:rounded-lg">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Filter CP</label>
                        <select name="cp_id"
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Semua</option>
                            @foreach ($cps as $c)
                                <option value="{{ $c->id }}" {{ request('cp_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->kode ?? 'CP' }} — {{ $c->mapel->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit"
                            class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">Filter</button>
                        <a href="{{ route('tp.index') }}"
                            class="ml-2 text-sm text-gray-600 hover:text-gray-900">Reset</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Kode</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Deskripsi</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        CP</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        ATP</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($tps as $tp)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            {{ $tp->kode ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">
                                            {{ Str::limit($tp->deskripsi, 80) }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $tp->cp->kode ?? 'CP' }} ({{ $tp->cp->mapel->nama }})</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $tp->atps_count }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <a href="{{ route('tp.show', $tp) }}"
                                                class="text-gray-600 hover:text-gray-900">Lihat</a>
                                            <a href="{{ route('tp.edit', $tp) }}"
                                                class="ml-2 text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form action="{{ route('tp.destroy', $tp) }}" method="POST" class="inline"
                                                onsubmit="return confirm('Hapus TP ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="ml-2 text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada
                                            data TP.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $tps->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
