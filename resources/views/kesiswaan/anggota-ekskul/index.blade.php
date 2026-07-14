<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Anggota Ekskul: :nama', ['nama' => $ekskul->nama]) }}</h2>
            <a href="{{ route('kesiswaan.anggota-ekskul.create', $ekskul) }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                {{ __('Tambah Anggota') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">{{ session('error') }}
                </div>
            @endif

            <div class="mb-4">
                <form method="GET" class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari anggota..."
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm flex-1">
                    <button type="submit" class="px-4 py-2 bg-gray-200 rounded-md text-sm">Cari</button>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tahun Ajaran
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($anggota as $i => $a)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $anggota->firstItem() + $i }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $a->siswa->nis ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $a->siswa->nama ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $a->tahunAjaran->label ?? ($a->tahunAjaran->tahun_mulai ?? '-') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <form action="{{ route('kesiswaan.anggota-ekskul.destroy', [$ekskul, $a]) }}"
                                        method="POST" class="inline"
                                        onsubmit="return confirm('Keluarkan anggota ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:text-red-900">Keluarkan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada
                                    anggota.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $anggota->links() }}</div>
            <div class="mt-4">
                <a href="{{ route('kesiswaan.ekskul.index') }}"
                    class="text-sm text-gray-600 hover:text-gray-900">&larr; Kembali ke daftar ekskul</a>
            </div>
        </div>
    </div>
</x-app-layout>
