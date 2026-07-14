<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Pelanggaran Siswa') }}</h2>
            <a href="{{ route('kesiswaan.pelanggaran.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                {{ __('Catat Pelanggaran') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    {{ session('success') }}</div>
            @endif

            <!-- Filter -->
            <div class="mb-4 bg-white p-4 rounded-lg shadow-sm">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari siswa..."
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <select name="kategori_id"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Semua kategori</option>
                        @foreach ($kategoris as $k)
                            <option value="{{ $k->id }}"
                                {{ request('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <button type="submit" class="px-4 py-2 bg-gray-200 rounded-md text-sm">Filter</button>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Poin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guru</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($pelanggarans as $p)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $p->tanggal->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    {{ $p->siswa->nama ?? '-' }}<br><span
                                        class="text-xs text-gray-500">{{ $p->siswa->nis ?? '' }}</span></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $p->kategoriPelanggaran->nama ?? '-' }}</td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ ($p->kategoriPelanggaran->poin ?? 0) >= 10 ? 'text-red-600' : 'text-yellow-600' }}">
                                    {{ $p->kategoriPelanggaran->poin ?? 0 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $p->guru->nama ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <a href="{{ route('kesiswaan.pelanggaran.edit', $p) }}"
                                        class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <form action="{{ route('kesiswaan.pelanggaran.destroy', $p) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Hapus pelanggaran ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @if ($p->deskripsi)
                                <tr class="bg-gray-50">
                                    <td colspan="6" class="px-6 py-2 text-xs text-gray-600">{{ $p->deskripsi }}</td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada
                                    pelanggaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $pelanggarans->links() }}</div>
        </div>
    </div>
</x-app-layout>
