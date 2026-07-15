@forelse ($gurus as $g)
    <tr>
        <td class="whitespace-nowrap px-6 py-4 text-sm">
            <input type="checkbox" name="ids[]" value="{{ $g->id }}" form="bulk-form"
                class="row-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-700">
            {{ $g->kode_guru_lembaga ?? '-' }}
            @if ($g->kode_guru_satminkal)
                <br><span class="text-xs text-indigo-600">{{ $g->kode_guru_satminkal }}</span>
            @endif
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-700">
            {{ $g->niy ?? '-' }}
        </td>
        <td class="px-6 py-4 text-sm text-gray-900">{{ $g->nama }}</td>
        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
            {{ $g->lembaga->nama }}</td>
        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
            <select
                class="inline-update-jenis-ptk block w-full min-w-[130px] rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                data-guru-id="{{ $g->id }}">
                <option value="">-- Pilih --</option>
                @foreach ($jenisPtks->where('lembaga_id', $g->lembaga_id) as $j)
                    <option value="{{ $j->id }}" @selected($g->jenis_ptk_id == $j->id)>{{ $j->nama }}</option>
                @endforeach
            </select>
        </td>
        <td class="px-6 py-4 text-sm text-gray-700">
            <div class="tugas-tambahan-inline" data-guru-id="{{ $g->id }}">
                @php $ttList = $g->tugasTambahans; @endphp
                @if ($ttList->isNotEmpty())
                    @foreach ($ttList as $tt)
                        <div class="tt-row flex flex-wrap items-center gap-1 mb-1">
                            <select
                                class="tt-jenis rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                style="min-width:120px">
                                <option value="">-- Pilih --</option>
                                <option value="Guru Mapel" @selected($tt->jenis == 'Guru Mapel')>Guru Mapel</option>
                                <option value="BK" @selected($tt->jenis == 'BK')>BK</option>
                                <option value="Wali Kelas" @selected($tt->jenis == 'Wali Kelas')>Wali Kelas</option>
                                <option value="Pembina Ekskul" @selected($tt->jenis == 'Pembina Ekskul')>Pembina Ekskul</option>
                                <option value="Koordinator" @selected($tt->jenis == 'Koordinator')>Koordinator</option>
                            </select>
                            <input type="text" value="{{ $tt->keterangan }}"
                                class="tt-keterangan w-24 rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Ket">
                            <select
                                class="tt-ta rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- TA --</option>
                                @foreach ($tahunAjarans as $ta)
                                    <option value="{{ $ta->id }}" @selected($tt->tahun_ajaran_id == $ta->id)>
                                        {{ $ta->nama }}</option>
                                @endforeach
                            </select>
                            <button type="button"
                                class="tt-remove text-red-400 hover:text-red-600 text-xs leading-none">&times;</button>
                        </div>
                    @endforeach
                @else
                    <span class="tt-empty text-xs text-gray-400">-</span>
                @endif
                <button type="button" class="tt-add text-xs text-indigo-600 hover:text-indigo-800 mt-1">+
                    Tambah</button>
            </div>
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-sm">
            <span
                class="rounded-full px-2 py-1 text-xs font-semibold {{ $g->status_satminkal ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                {{ $g->status_satminkal ? 'Satminkal' : 'Non-Satminkal' }}
            </span>
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-sm">
            <span
                class="rounded-full px-2 py-1 text-xs font-semibold {{ $g->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $g->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
            <a href="{{ route('guru.edit', $g) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
            <form action="{{ route('guru.destroy', $g) }}" method="POST" class="inline"
                onsubmit="return confirm('Hapus guru ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="ml-2 text-red-600 hover:text-red-900">Hapus</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada data guru.</td>
    </tr>
@endforelse
