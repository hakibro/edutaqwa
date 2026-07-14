<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Input Nilai ') . $jenisNilai->nama . ' — ' . $mapel->nama . ' — ' . $kelas->nama }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($isHarian)
                        {{-- Nilai Harian: per TP --}}
                        @if ($tps->isEmpty())
                            <p class="mb-4 text-sm text-yellow-700 bg-yellow-50 p-3 rounded">Anda belum memiliki TP untuk
                                mapel ini. Silakan buat TP terlebih dahulu.</p>
                        @endif

                        @foreach ($tps as $tp)
                            <div class="mb-8 border rounded-lg p-4">
                                <h3 class="font-semibold text-gray-700 mb-2">{{ $tp->kode }} — {{ $tp->deskripsi }}
                                </h3>

                                <form method="POST" action="{{ route('nilai.store') }}">
                                    @csrf
                                    <input type="hidden" name="mapel_id" value="{{ $mapel->id }}">
                                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                                    <input type="hidden" name="jenis_nilai_id" value="{{ $jenisNilai->id }}">
                                    <input type="hidden" name="tp_id" value="{{ $tp->id }}">

                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700">Keterangan
                                            (opsional)</label>
                                        <input type="text" name="keterangan"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            placeholder="Misal: Ulangan Harian 1">
                                    </div>

                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th
                                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                    #</th>
                                                <th
                                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                    NIS</th>
                                                <th
                                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                    Nama</th>
                                                <th
                                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                    Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach ($siswas as $i => $s)
                                                @php
                                                    $key = $s->id . '_' . $tp->id;
                                                    $existing = $existingNilai->get($key);
                                                @endphp
                                                <tr>
                                                    <td class="px-4 py-2 text-sm">{{ $i + 1 }}</td>
                                                    <td class="px-4 py-2 text-sm">{{ $s->nis }}</td>
                                                    <td class="px-4 py-2 text-sm">{{ $s->nama }}</td>
                                                    <td class="px-4 py-2">
                                                        <input type="number" name="nilai[{{ $s->id }}]"
                                                            value="{{ $existing?->nilai ?? '' }}" min="0"
                                                            max="100" step="0.01"
                                                            class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                            placeholder="0-100"
                                                            {{ $existing?->is_finalized ? 'disabled' : '' }}>
                                                        @if ($existing?->is_finalized)
                                                            <span class="ml-2 text-xs text-green-600">Final</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <div class="mt-4 flex justify-end gap-2">
                                        <a href="{{ route('nilai.index') }}"
                                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Kembali</a>
                                        <button type="submit"
                                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Simpan
                                            TP {{ $tp->kode }}</button>
                                    </div>
                                </form>
                            </div>
                        @endforeach

                        @if ($tps->isEmpty())
                            <div class="flex justify-end">
                                <a href="{{ route('nilai.index') }}"
                                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Kembali</a>
                            </div>
                        @endif
                    @else
                        {{-- PTS / PAS / UKK: 1 kolom per siswa --}}
                        <form method="POST" action="{{ route('nilai.store') }}">
                            @csrf
                            <input type="hidden" name="mapel_id" value="{{ $mapel->id }}">
                            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                            <input type="hidden" name="jenis_nilai_id" value="{{ $jenisNilai->id }}">

                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700">Keterangan (opsional)</label>
                                <input type="text" name="keterangan"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    placeholder="Misal: PTS Ganjil 2025/2026">
                            </div>

                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">NIS
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Nilai</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($siswas as $i => $s)
                                        @php
                                            $existing = $existingNilai->get($s->id . '_');
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-2 text-sm">{{ $i + 1 }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $s->nis }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $s->nama }}</td>
                                            <td class="px-4 py-2">
                                                <input type="number" name="nilai[{{ $s->id }}]"
                                                    value="{{ $existing?->nilai ?? '' }}" min="0" max="100"
                                                    step="0.01"
                                                    class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                    placeholder="0-100"
                                                    {{ $existing?->is_finalized ? 'disabled' : '' }}>
                                                @if ($existing?->is_finalized)
                                                    <span class="ml-2 text-xs text-green-600">Final</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="mt-4 flex justify-end gap-2">
                                <a href="{{ route('nilai.index') }}"
                                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Kembali</a>
                                <button type="submit"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Simpan
                                    Semua</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
