<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Edit Jurnal Mengajar') }}</h2>
            <a href="{{ route('jurnal-mengajar.show', $jurnal) }}"
                class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Kembali</a>
        </div>
    </x-slot>

    {{-- Header info bar (since guru layout doesn't render $header slot) --}}
    <div class="bg-white border-b border-gray-200">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8 py-3">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                <span class="font-semibold text-gray-800">{{ $jurnal->jadwal->mapel->nama ?? '—' }}</span>
                <span class="text-gray-300">|</span>
                <span class="text-gray-600">{{ $jurnal->kelas->nama }}</span>
                <span class="text-gray-300">|</span>
                <span class="text-gray-600">Pertemuan ke-{{ $jurnal->pertemuan_ke }}</span>
                <span class="text-gray-300">|</span>
                <span class="text-gray-600">{{ $jurnal->tanggal->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <div class="py-6 content-safe-bottom">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form action="{{ route('jurnal-mengajar.update', $jurnal) }}" method="POST" id="edit-jurnal-form">
                @csrf
                @method('PUT')

                {{-- Info jurnal --}}
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Info Jurnal</h3>
                        @if ($jurnal->foto_path)
                            <div class="mb-4">
                                <img src="{{ Storage::url($jurnal->foto_path) }}" alt="Selfie"
                                    class="w-full max-w-xs mx-auto rounded-lg border shadow-sm">
                            </div>
                        @endif
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="block text-xs text-gray-500">Tanggal</span>
                                <span class="font-semibold">{{ $jurnal->tanggal->format('d/m/Y') }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Mapel</span>
                                <span class="font-semibold">{{ $jurnal->jadwal->mapel->nama ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Kelas</span>
                                <span class="font-semibold">{{ $jurnal->kelas->nama }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Pertemuan ke-</span>
                                <span class="font-semibold">{{ $jurnal->pertemuan_ke }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Jam Mulai</span>
                                <span class="font-semibold">{{ $jurnal->jam_mulai ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Status</span>
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $jurnal->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $jurnal->is_verified ? 'Terverifikasi' : 'Pending' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Materi --}}
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-3">Materi Pertemuan</h3>
                        <textarea name="materi" rows="4" placeholder="Materi yang diajarkan..."
                            class="w-full rounded-md border-gray-300 text-sm">{{ old('materi', $jurnal->materi) }}</textarea>
                    </div>
                </div>

                {{-- ATP --}}
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-3">ATP <span
                                class="font-normal text-gray-400 text-sm">(Opsional)</span></h3>
                        <select name="atp_id" id="atp-edit-select" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">— Pilih ATP (opsional) —</option>
                            @foreach ($atps as $atp)
                                <option value="{{ $atp->id }}"
                                    data-cp-deskripsi="{{ $atp->tp->cp->deskripsi ?? '' }}"
                                    data-cp-kode="{{ $atp->tp->cp->kode ?? '' }}"
                                    data-tp-deskripsi="{{ $atp->tp->deskripsi ?? '' }}"
                                    data-tp-kode="{{ $atp->tp->kode ?? '' }}"
                                    {{ $jurnal->atp_id == $atp->id ? 'selected' : '' }}>
                                    Minggu {{ $atp->minggu_ke }} — {{ Str::limit($atp->materi, 60) }}
                                </option>
                            @endforeach
                        </select>
                        <div id="atp-edit-info" class="hidden mt-3 space-y-2">
                            <div class="rounded bg-blue-50 p-3 text-sm">
                                <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">CP &mdash;
                                    Capaian Pembelajaran</p>
                                <p class="text-sm text-blue-900 mt-1" id="atp-edit-info-cp"></p>
                            </div>
                            <div class="rounded bg-indigo-50 p-3 text-sm">
                                <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wide">TP &mdash;
                                    Tujuan Pembelajaran</p>
                                <p class="text-sm text-indigo-900 mt-1" id="atp-edit-info-tp"></p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Presensi Siswa --}}
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-base font-semibold text-gray-900">Kehadiran Siswa</h3>
                            <div class="flex gap-2">
                                <button type="button" onclick="setAll('hadir')"
                                    class="rounded bg-green-100 px-3 py-1 text-xs font-medium text-green-700 hover:bg-green-200">Semua
                                    Hadir</button>
                                <button type="button" onclick="setAll('alpha')"
                                    class="rounded bg-red-100 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-200">Semua
                                    Alpha</button>
                            </div>
                        </div>

                        @if ($siswas->isEmpty())
                            <p class="text-sm text-gray-500">Tidak ada siswa di kelas ini.</p>
                        @else
                            <div class="divide-y divide-gray-100 border rounded-lg">
                                @foreach ($siswas as $i => $s)
                                    @php
                                        $presensi = $presensiMap->get($s->id);
                                        $status = $presensi->status ?? 'hadir';
                                        $perizinan = $perizinanHariIni->get($s->id);
                                    @endphp
                                    <div class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50">
                                        <span class="text-xs text-gray-400 w-5 shrink-0">{{ $i + 1 }}</span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ \Illuminate\Support\Str::title($s->nama) }}
                                            </p>
                                            <p class="text-xs text-gray-400">{{ $s->nis }}</p>
                                        </div>
                                        <div class="shrink-0 text-center">
                                            @if ($perizinan)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold
                                                    {{ $perizinan->jenis === 'sakit' ? 'bg-yellow-100 text-yellow-700' : 'bg-orange-100 text-orange-700' }}">
                                                    {{ ucfirst($perizinan->jenis) }}
                                                </span>
                                                <span class="block text-[10px] text-gray-400 mt-0.5">perizinan</span>
                                                <input type="hidden" name="siswa[{{ $i }}][status]"
                                                    value="hadir">
                                                <input type="hidden" name="siswa[{{ $i }}][keterangan]"
                                                    value="{{ $perizinan->keterangan }}">
                                                <p class="text-[10px] text-gray-400 mt-0.5">
                                                    {{ $perizinan->keterangan }}</p>
                                            @else
                                                <input type="hidden" name="siswa[{{ $i }}][id]"
                                                    value="{{ $s->id }}">
                                                <label class="relative inline-flex items-center cursor-pointer gap-3">
                                                    <input type="checkbox" name="siswa[{{ $i }}][status]"
                                                        value="alpha" class="sr-only peer"
                                                        {{ $status === 'alpha' ? 'checked' : '' }}
                                                        onchange="toggleKet({{ $i }}, this)">
                                                    <span
                                                        class="text-xs font-semibold text-green-600 peer-checked:text-gray-400">Hadir</span>
                                                    <span
                                                        class="relative w-10 h-6 bg-green-500 rounded-full after:absolute after:top-0.5 after:start-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500 peer-checked:after:translate-x-4"></span>
                                                    <span
                                                        class="text-xs font-semibold text-gray-400 peer-checked:text-red-600">Alpha</span>
                                                </label>
                                                <input type="text" name="siswa[{{ $i }}][keterangan]"
                                                    placeholder="Keterangan…" id="ket-{{ $i }}"
                                                    value="{{ old('siswa.' . $i . '.keterangan', $presensi->keterangan ?? '') }}"
                                                    class="mt-1.5 w-28 rounded-md border-gray-300 text-xs py-1.5 {{ $status === 'alpha' ? '' : 'hidden' }}">
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Tombol --}}
                <div
                    class="sticky bottom-20 z-10 flex justify-between bg-slate-50/95 backdrop-blur-sm py-4 -mx-4 px-4 border-t border-gray-200">
                    <a href="{{ route('jurnal-mengajar.show', $jurnal) }}"
                        class="rounded-full bg-white border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm">
                        &larr; Batal
                    </a>
                    <button type="submit"
                        class="rounded-full bg-emerald-600 px-8 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Set all students status
            function setAll(status) {
                document.querySelectorAll('input[type="checkbox"][name$="[status]"]').forEach(cb => {
                    cb.checked = (status === 'alpha');
                    cb.dispatchEvent(new Event('change'));
                });
            }

            // Toggle keterangan input — visible when Alpha (checkbox checked)
            function toggleKet(idx, checkbox) {
                const ket = document.getElementById('ket-' + idx);
                if (ket) {
                    ket.classList.toggle('hidden', !checkbox.checked);
                    if (!checkbox.checked) ket.value = '';
                }
            }

            // ATP info toggle
            const atpSelect = document.getElementById('atp-edit-select');
            const atpInfo = document.getElementById('atp-edit-info');
            const atpInfoCp = document.getElementById('atp-edit-info-cp');
            const atpInfoTp = document.getElementById('atp-edit-info-tp');

            function updateAtpInfo() {
                const opt = atpSelect.options[atpSelect.selectedIndex];
                if (opt && opt.value) {
                    const cpKode = opt.dataset.cpKode;
                    const cpDeskripsi = opt.dataset.cpDeskripsi;
                    const tpKode = opt.dataset.tpKode;
                    const tpDeskripsi = opt.dataset.tpDeskripsi;
                    let cpText = '';
                    if (cpKode) cpText += '[' + cpKode + '] ';
                    cpText += cpDeskripsi || '-';
                    atpInfoCp.textContent = cpText;
                    let tpText = '';
                    if (tpKode) tpText += '[' + tpKode + '] ';
                    tpText += tpDeskripsi || '-';
                    atpInfoTp.textContent = tpText;
                    atpInfo.classList.remove('hidden');
                } else {
                    atpInfo.classList.add('hidden');
                }
            }

            if (atpSelect) {
                atpSelect.addEventListener('change', updateAtpInfo);
                if (atpSelect.value) updateAtpInfo();
            }

            // Init on load — show ket for alpha students
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('input[type="checkbox"][name$="[status]"]:checked').forEach(cb => {
                    cb.dispatchEvent(new Event('change'));
                });
            });
        </script>
    @endpush
</x-app-layout>
