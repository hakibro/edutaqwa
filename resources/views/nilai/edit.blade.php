<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Edit Nilai ') . $jenisNilai->nama . ' — ' . $mapel->nama . ' — ' . $kelas->nama }}
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
                    <div class="mb-4 flex justify-between items-center">
                        <p class="text-sm text-gray-600">Edit nilai untuk {{ $mapel->nama }} - {{ $kelas->nama }} -
                            {{ $jenisNilai->nama }}</p>
                    </div>

                    @if ($isHarian && $tps->isNotEmpty())
                        {{-- Edit Nilai Harian per TP --}}
                        @foreach ($tps as $tp)
                            @php
                                $tpExists = $existingNilai->where(fn($n, $k) => str_ends_with($k, '_' . $tp->id));
                            @endphp
                            <div class="mb-8 border rounded-lg p-4">
                                <h3 class="font-semibold text-gray-700 mb-2">{{ $tp->kode }} — {{ $tp->deskripsi }}
                                </h3>

                                <form method="POST" action="{{ route('nilai.update') }}">
                                    @csrf
                                    <input type="hidden" name="mapel_id" value="{{ $mapel->id }}">
                                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                                    <input type="hidden" name="jenis_nilai_id" value="{{ $jenisNilai->id }}">
                                    <input type="hidden" name="tp_id" value="{{ $tp->id }}">

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
                                                <th
                                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                    Status</th>
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
                                                            {{ $existing?->is_finalized ? 'disabled' : '' }}>
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        @if ($existing?->is_finalized)
                                                            <span
                                                                class="text-green-600 text-xs font-medium">Final</span>
                                                        @elseif ($existing)
                                                            <span
                                                                class="text-yellow-600 text-xs font-medium">Draft</span>
                                                        @else
                                                            <span class="text-gray-400 text-xs">Kosong</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <div class="mt-4 flex justify-end gap-2">
                                        <button type="submit"
                                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Simpan
                                            {{ $tp->kode }}</button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    @else
                        {{-- Edit PTS / PAS / UKK --}}
                        <form method="POST" action="{{ route('nilai.update') }}">
                            @csrf
                            <input type="hidden" name="mapel_id" value="{{ $mapel->id }}">
                            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                            <input type="hidden" name="jenis_nilai_id" value="{{ $jenisNilai->id }}">

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
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Status</th>
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
                                                    {{ $existing?->is_finalized ? 'disabled' : '' }}>
                                            </td>
                                            <td class="px-4 py-2 text-sm">
                                                @if ($existing?->is_finalized)
                                                    <span class="text-green-600 text-xs font-medium">Final</span>
                                                @elseif ($existing)
                                                    <span class="text-yellow-600 text-xs font-medium">Draft</span>
                                                @else
                                                    <span class="text-gray-400 text-xs">Kosong</span>
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
                                    Perubahan</button>
                            </div>
                        </form>
                    @endif

                    {{-- Tombol Finalisasi --}}
                    <div class="mt-6 border-t pt-4">
                        <form method="POST" action="{{ route('nilai.finalize') }}"
                            onsubmit="return confirm('Yakin finalisasi? Semua nilai {{ $jenisNilai->nama }} untuk kelas ini akan terkunci dan tidak bisa diedit.')">
                            @csrf
                            <input type="hidden" name="mapel_id" value="{{ $mapel->id }}">
                            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                            <input type="hidden" name="jenis_nilai_id" value="{{ $jenisNilai->id }}">
                            <button type="submit"
                                class="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500">
                                Finalisasi (Kunci) Nilai {{ $jenisNilai->nama }}
                            </button>
                        </form>
                        <p class="mt-1 text-xs text-gray-500">Setelah difinalisasi, nilai tidak bisa diedit lagi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
