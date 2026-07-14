<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Sync Siswa — Sisda API
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <p class="mb-4 text-gray-600">
                        Sinkronisasi data siswa dari Sisda API
                        (<code>https://api.daruttaqwa.or.id/sisda/v1/siswa</code>).
                        Data akan diambil berdasarkan <strong>Unit Formal</strong> yang sudah diset pada masing-masing
                        lembaga.
                    </p>

                    @if ($lembagas->isEmpty())
                        <div class="p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
                            Belum ada lembaga dengan <strong>Unit Formal</strong> terkonfigurasi.
                            Silakan set <code>unit_formal</code> di menu Lembaga terlebih dahulu.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Lembaga</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit
                                            Formal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($lembagas as $lembaga)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $lembaga->nama }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">{{ $lembaga->unit_formal }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <form method="POST" action="{{ route('sync-siswa.sync') }}"
                                                    onsubmit="return confirm('Sync data siswa dari Sisda?')">
                                                    @csrf
                                                    <input type="hidden" name="lembaga_id" value="{{ $lembaga->id }}">
                                                    <button type="submit"
                                                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
                                                        Sync Sekarang
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Kenaikan Kelas --}}
                        @if ($tahunAjarans->isNotEmpty())
                            <hr class="my-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Sync Kenaikan Kelas</h3>
                            <p class="mb-4 text-sm text-gray-600">
                                Jalankan saat tahun ajaran baru aktif. Sistem akan menutup riwayat kelas lama dan
                                membuat riwayat kelas baru berdasarkan data terbaru dari Sisda.
                            </p>
                            <form method="POST" action="{{ route('sync-siswa.kenaikan-kelas') }}"
                                onsubmit="return confirm('Proses kenaikan kelas untuk tahun ajaran baru?')"
                                class="flex items-end gap-4">
                                @csrf
                                <div>
                                    <x-input-label for="lembaga_id" value="Lembaga" />
                                    <select id="lembaga_id" name="lembaga_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                        <option value="">-- Pilih --</option>
                                        @foreach ($lembagas as $l)
                                            <option value="{{ $l->id }}">{{ $l->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="tahun_ajaran_id" value="Tahun Ajaran Baru" />
                                    <select id="tahun_ajaran_id" name="tahun_ajaran_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                        <option value="">-- Pilih --</option>
                                        @foreach ($tahunAjarans as $ta)
                                            <option value="{{ $ta->id }}">{{ $ta->nama }}
                                                {{ $ta->semester }} {{ $ta->is_active ? '(Aktif)' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <button type="submit"
                                        class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-sm">
                                        Proses Kenaikan Kelas
                                    </button>
                                </div>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
