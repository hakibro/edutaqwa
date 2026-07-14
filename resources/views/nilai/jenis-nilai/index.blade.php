<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Jenis Nilai') }}</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            {{-- Form Tambah --}}
            <div class="mb-6 bg-white p-4 shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Tambah Jenis Nilai</h3>
                <form method="POST" action="{{ route('nilai.jenis-nilai.store') }}"
                    class="flex flex-wrap gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama</label>
                        <input type="text" name="nama" value="{{ old('nama') }}"
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            placeholder="Harian / PTS / PAS / UKK" required>
                        @error('nama')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bobot (%)</label>
                        <input type="number" name="bobot" value="{{ old('bobot') }}" min="0" max="100"
                            step="0.01"
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm w-24"
                            placeholder="0-100" required>
                        @error('bobot')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <button type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Tambah</button>
                    </div>
                </form>
            </div>

            {{-- Daftar --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($jenisNilais->count())
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Nama</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Bobot</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($jenisNilais as $jn)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-medium">{{ $jn->nama }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $jn->bobot }}%</td>
                                        <td class="px-4 py-2 text-sm">
                                            <button
                                                onclick="editJenis({{ $jn->id }}, '{{ $jn->nama }}', {{ $jn->bobot }})"
                                                class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                            <form method="POST"
                                                action="{{ route('nilai.jenis-nilai.destroy', $jn->id) }}"
                                                class="inline"
                                                onsubmit="return confirm('Hapus jenis nilai {{ $jn->nama }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="ml-2 text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-center text-gray-500">Belum ada jenis nilai. Tambah jenis nilai terlebih dahulu.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Modal Edit --}}
            <div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50">
                <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                    <h3 class="mb-4 text-lg font-medium">Edit Jenis Nilai</h3>
                    <form method="POST" id="editForm">
                        @csrf @method('PUT')
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Nama</label>
                            <input type="text" name="nama" id="editNama"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Bobot (%)</label>
                            <input type="number" name="bobot" id="editBobot" min="0" max="100"
                                step="0.01"
                                class="mt-1 block w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="closeEdit()"
                                class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</button>
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editJenis(id, nama, bobot) {
                document.getElementById('editForm').action = '{{ url('nilai/jenis-nilai') }}/' + id;
                document.getElementById('editNama').value = nama;
                document.getElementById('editBobot').value = bobot;
                document.getElementById('editModal').classList.remove('hidden');
                document.getElementById('editModal').classList.add('flex');
            }

            function closeEdit() {
                document.getElementById('editModal').classList.add('hidden');
                document.getElementById('editModal').classList.remove('flex');
            }
        </script>
    @endpush
</x-app-layout>
