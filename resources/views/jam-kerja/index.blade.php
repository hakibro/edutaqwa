<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Konfigurasi Jam Kerja Lembaga') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Hari</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Jam Masuk</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Jam Pulang</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Toleransi (menit)</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($hariList as $hari)
                                @php $jk = $jamKerja->get($hari); @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ $hari }}</td>
                                    @if ($jk)
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                            {{ $jk->jam_masuk }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                            {{ $jk->jam_pulang }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                            {{ $jk->toleransi_keterlambatan }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $jk->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $jk->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-right space-x-2">
                                            <a href="{{ route('jam-kerja.edit', $jk) }}"
                                                class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form action="{{ route('jam-kerja.destroy', $jk) }}" method="POST"
                                                class="inline"
                                                onsubmit="return confirm('Hapus jam kerja {{ $hari }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    @else
                                        <td colspan="4" class="px-4 py-3 text-sm text-gray-400 italic">Belum
                                            dikonfigurasi</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-right">
                                            <button
                                                onclick="document.getElementById('form-{{ $loop->index }}').classList.toggle('hidden')"
                                                class="text-indigo-600 hover:text-indigo-900">+ Tambah</button>
                                        </td>
                                    @endif
                                </tr>
                                @if (!$jk)
                                    <tr id="form-{{ $loop->index }}" class="hidden bg-gray-50">
                                        <td colspan="6" class="px-4 py-4">
                                            <form action="{{ route('jam-kerja.store') }}" method="POST"
                                                class="flex items-end gap-4 flex-wrap">
                                                @csrf
                                                <input type="hidden" name="hari" value="{{ $hari }}">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-500">Jam
                                                        Masuk</label>
                                                    <input type="time" name="jam_masuk" value="07:00" required
                                                        class="mt-1 block rounded-md border-gray-300 shadow-sm text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-500">Jam
                                                        Pulang</label>
                                                    <input type="time" name="jam_pulang" value="15:00" required
                                                        class="mt-1 block rounded-md border-gray-300 shadow-sm text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-500">Toleransi
                                                        (menit)</label>
                                                    <input type="number" name="toleransi_keterlambatan" value="15"
                                                        min="0" max="120"
                                                        class="mt-1 block w-24 rounded-md border-gray-300 shadow-sm text-sm">
                                                </div>
                                                <div>
                                                    <button type="submit"
                                                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                                        Simpan
                                                    </button>
                                                    <button type="button"
                                                        onclick="this.closest('tr').classList.add('hidden')"
                                                        class="ml-2 rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                                                        Batal
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
