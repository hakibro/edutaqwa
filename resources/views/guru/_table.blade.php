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
        <td class="px-6 py-4 text-sm text-gray-700 max-w-[280px]">
            <div class="flex items-start gap-2">
                <div class="tt-info flex-1 min-w-0">
                    @php $ttList = $g->tugasTambahans; @endphp
                    @if ($ttList->isNotEmpty())
                        @foreach ($ttList as $tt)
                            <div class="text-xs leading-relaxed">
                                <span class="font-medium">{{ $tt->jenis }}</span>
                                @if ($tt->keterangan)
                                    <span class="text-gray-400"> — {{ $tt->keterangan }}</span>
                                @endif
                                @if ($tt->jenis === 'Wali Kelas' && $tt->kelas)
                                    <span class="text-indigo-500"> • {{ $tt->kelas->nama }}</span>
                                @endif
                                @if ($tt->tahunAjaran)
                                    <span class="text-gray-400"> • {{ $tt->tahunAjaran->nama }}</span>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <span class="text-xs text-gray-400">-</span>
                    @endif
                </div>
                <button type="button"
                    class="tt-edit-btn flex-shrink-0 text-gray-400 hover:text-indigo-600 transition-colors"
                    data-guru-id="{{ $g->id }}" data-lembaga-id="{{ $g->lembaga_id }}"
                    title="Edit Tugas Tambahan">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
            </div>
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-sm">
            <span
                class="rounded-full px-2 py-1 text-xs font-semibold {{ $g->status_satminkal ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                {{ $g->status_satminkal ? 'Satminkal' : 'Non-Satminkal' }}
            </span>
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-sm text-center">
            <span
                class="rounded-full px-2 py-1 text-xs font-semibold {{ $g->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $g->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-sm text-center">
            @php
                $warnings = [];
                $hasUser = $g->relationLoaded('user') && $g->user;
                if ($hasUser && !$g->tmt) {
                    $warnings[] = 'TMT belum diisi';
                }
                if (!$g->is_approved) {
                    $warnings[] = 'Akun belum disetujui';
                }
            @endphp
            @if ($warnings)
                @foreach ($warnings as $w)
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-800 mb-1">
                        ⚠ {{ $w }}
                    </span>
                @endforeach
            @else
                <span class="text-xs text-gray-400">-</span>
            @endif
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
            <a href="{{ route('guru.reset-password', $g) }}" class="text-amber-600 hover:text-amber-900">Reset PW</a>
            <a href="{{ route('guru.edit', $g) }}" class="ml-2 text-indigo-600 hover:text-indigo-900">Edit</a>
            <form action="{{ route('guru.destroy', $g) }}" method="POST" class="inline"
                onsubmit="return confirm('Hapus guru ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="ml-2 text-red-600 hover:text-red-900">Hapus</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada data guru.</td>
    </tr>
@endforelse
