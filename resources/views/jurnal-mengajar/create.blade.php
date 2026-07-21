<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Jurnal Mengajar') }}</h2>
            <span class="text-sm text-gray-500">{{ $jadwal->mapel->nama }} — {{ $jadwal->kelas->nama }} | Pertemuan
                ke-{{ $pertemuanKe }}</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            @if ($existingToday)
                <div class="mb-4 rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                    Jurnal untuk jadwal ini hari ini sudah ada. Silakan cek <a
                        href="{{ route('jurnal-mengajar.index') }}" class="text-indigo-600 underline">daftar jurnal</a>.
                </div>
            @endif

            {{-- Wizard Steps --}}
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm" id="step-indicators">
                        <span
                            class="step-indicator active rounded-full bg-indigo-600 px-3 py-1 text-white font-semibold"
                            data-step="1">1. Selfie</span>
                        <span class="text-gray-300">&rarr;</span>
                        <span class="step-indicator rounded-full bg-gray-200 px-3 py-1 text-gray-500" data-step="2">2.
                            Presensi Siswa</span>
                        <span class="text-gray-300">&rarr;</span>
                        <span class="step-indicator rounded-full bg-gray-200 px-3 py-1 text-gray-500" data-step="3">3.
                            Materi & Simpan</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('jurnal-mengajar.store') }}" method="POST" enctype="multipart/form-data"
                id="jurnal-form">
                @csrf
                <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
                <input type="hidden" name="draft_id" id="draft_id" value="{{ $draft->id ?? '' }}">

                {{-- STEP 1: Selfie — Fullscreen --}}
                <div class="step-content fixed inset-0 z-50 flex flex-col bg-black" data-step="1">
                    {{-- Top bar — close + step dots --}}
                    <div class="flex items-center justify-between px-4 pt-12 pb-2">
                        <button type="button" id="btn-back"
                            class="flex h-9 w-9 items-center justify-center rounded-full text-white/70 hover:text-white hover:bg-white/10 transition"
                            aria-label="Kembali">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <div class="flex items-center gap-1.5" id="step-dots">
                            <span class="step-dot h-2 w-2 rounded-full bg-white" data-step="1"></span>
                            <span class="step-dot h-2 w-2 rounded-full bg-white/30" data-step="2"></span>
                            <span class="step-dot h-2 w-2 rounded-full bg-white/30" data-step="3"></span>
                        </div>
                        <div class="w-9"></div>
                    </div>

                    {{-- Camera wrapper — fills remaining space --}}
                    <div class="relative flex-1 mx-4 mb-2" id="camera-wrapper">
                        {{-- Live camera --}}
                        <div id="camera-preview"
                            class="absolute inset-0 overflow-hidden rounded-2xl bg-black shadow-inner">
                            <video id="video" class="absolute inset-0 h-full w-full object-cover" autoplay
                                playsinline></video>
                            <canvas id="canvas" class="hidden"></canvas>

                            {{-- Capture button --}}
                            <div class="absolute bottom-6 left-0 right-0 flex justify-center z-10">
                                <button type="button" id="capture-btn"
                                    class="capture-pulse flex h-16 w-16 items-center justify-center rounded-full border-4 border-white bg-white/20 backdrop-blur-sm shadow-lg transition-transform hover:scale-105 active:scale-95"
                                    aria-label="Ambil Foto">
                                    <span class="h-11 w-11 rounded-full bg-white"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Captured preview --}}
                        <div id="captured-preview"
                            class="absolute inset-0 hidden overflow-hidden rounded-2xl bg-black shadow-inner">
                            <img id="captured-img" class="absolute inset-0 h-full w-full object-cover" />
                            {{-- Check mark --}}
                            <div
                                class="absolute top-3 right-3 z-10 flex h-7 w-7 items-center justify-center rounded-full bg-green-500 shadow">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="3"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>

                        {{-- Error state --}}
                        <div id="camera-error"
                            class="absolute inset-0 hidden flex items-center justify-center rounded-2xl bg-slate-900">
                            <div class="p-6 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-500 mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                                <p class="text-sm text-slate-300 font-medium">Kamera tidak tersedia</p>
                                <p class="text-xs text-slate-500 mt-1">Izinkan akses kamera & gunakan HTTPS</p>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom area: retake + next --}}
                    <div class="px-4 pb-10 space-y-3">
                        <button type="button" id="retake-btn"
                            class="hidden w-full rounded-xl border border-white/20 py-3 text-sm font-semibold text-white/70 hover:text-white hover:bg-white/10 transition"
                            aria-label="Ulang">
                            ↻ Ulang
                        </button>
                        <button type="button" id="lanjut-presensi"
                            class="w-full rounded-xl bg-indigo-600 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/25 transition active:scale-[0.98] disabled:opacity-30 disabled:cursor-not-allowed"
                            disabled>
                            Lanjut ke Presensi &rarr;
                        </button>
                    </div>

                    <input type="hidden" name="foto_base64" id="foto_base64">
                    @error('foto_base64')
                        <p class="mt-1 text-sm text-red-600 text-center">{{ $message }}</p>
                    @enderror

                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                </div>

                {{-- STEP 2: Presensi Siswa --}}
                <div class="step-content hidden" data-step="2">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-base font-semibold text-gray-900">Daftar Siswa</h3>
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
                                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="sticky top-0 bg-gray-50">
                                            <tr class="border-b">
                                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 w-8">#
                                                </th>
                                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Nama
                                                </th>
                                                <th
                                                    class="px-2 py-2 text-center text-xs font-medium text-green-700 w-10">
                                                    H</th>
                                                <th
                                                    class="px-2 py-2 text-center text-xs font-medium text-yellow-700 w-10">
                                                    S</th>
                                                <th
                                                    class="px-2 py-2 text-center text-xs font-medium text-orange-700 w-10">
                                                    I</th>
                                                <th
                                                    class="px-2 py-2 text-center text-xs font-medium text-red-700 w-10">
                                                    A</th>
                                                <th
                                                    class="px-2 py-2 text-center text-xs font-medium text-purple-700 w-10">
                                                    T</th>
                                                <th class="px-2 py-2 text-xs font-medium text-gray-500 w-28">Ket</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach ($siswas as $i => $s)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-2 py-2 text-gray-400">{{ $i + 1 }}</td>
                                                    <td class="px-2 py-2 font-medium text-gray-900 whitespace-nowrap">
                                                        {{ $s->nama }}
                                                        <span
                                                            class="ml-1 text-xs text-gray-400">{{ $s->nis }}</span>
                                                    </td>
                                                    <td class="px-2 py-2 text-center">
                                                        <input type="radio"
                                                            name="siswa[{{ $i }}][status]" value="hadir"
                                                            checked
                                                            class="h-4 w-4 text-green-600 focus:ring-green-500 cursor-pointer"
                                                            onchange="toggleKet({{ $i }}, this)">
                                                    </td>
                                                    <td class="px-2 py-2 text-center">
                                                        <input type="radio"
                                                            name="siswa[{{ $i }}][status]" value="sakit"
                                                            class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 cursor-pointer"
                                                            onchange="toggleKet({{ $i }}, this)">
                                                    </td>
                                                    <td class="px-2 py-2 text-center">
                                                        <input type="radio"
                                                            name="siswa[{{ $i }}][status]" value="izin"
                                                            class="h-4 w-4 text-orange-600 focus:ring-orange-500 cursor-pointer"
                                                            onchange="toggleKet({{ $i }}, this)">
                                                    </td>
                                                    <td class="px-2 py-2 text-center">
                                                        <input type="radio"
                                                            name="siswa[{{ $i }}][status]" value="alpha"
                                                            class="h-4 w-4 text-red-600 focus:ring-red-500 cursor-pointer"
                                                            onchange="toggleKet({{ $i }}, this)">
                                                    </td>
                                                    <td class="px-2 py-2 text-center">
                                                        <input type="radio"
                                                            name="siswa[{{ $i }}][status]"
                                                            value="terlambat"
                                                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 cursor-pointer"
                                                            onchange="toggleKet({{ $i }}, this)">
                                                    </td>
                                                    <td class="px-2 py-2">
                                                        <input type="hidden" name="siswa[{{ $i }}][id]"
                                                            value="{{ $s->id }}">
                                                        <input type="text"
                                                            name="siswa[{{ $i }}][keterangan]"
                                                            placeholder="Ket." id="ket-{{ $i }}"
                                                            class="w-full rounded-md border-gray-300 text-sm py-1 hidden">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <div class="flex justify-between mt-6">
                                <button type="button" onclick="prevStep()"
                                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                                    &larr; Kembali
                                </button>
                                <button type="button" onclick="nextStep()"
                                    class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    Lanjut ke Materi &rarr;
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STEP 3: Materi & Simpan --}}
                <div class="step-content hidden" data-step="3">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="mb-6">
                                <h3 class="text-base font-semibold text-gray-900 mb-3">Materi Pertemuan</h3>
                                <textarea name="materi" rows="3" placeholder="Materi yang diajarkan pada pertemuan ini..."
                                    class="w-full rounded-md border-gray-300 text-sm">{{ old('materi') }}</textarea>
                            </div>

                            {{-- ATP — opsional --}}
                            <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <div class="flex items-start gap-2 mb-2">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-3-3v6m-7 4h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700">ATP <span
                                                class="font-normal text-gray-400">(Opsional)</span></h4>
                                        <p class="text-xs text-gray-500">Terapkan Alur Tujuan Pembelajaran — lihat info
                                            CP &amp; TP terkait</p>
                                    </div>
                                </div>
                                <select name="atp_id" id="atp-select"
                                    class="w-full rounded-md border-gray-300 text-sm">
                                    <option value="">— Pilih ATP (opsional) —</option>
                                    @foreach ($atps as $atp)
                                        <option value="{{ $atp->id }}"
                                            data-cp-deskripsi="{{ $atp->tp->cp->deskripsi ?? '' }}"
                                            data-cp-kode="{{ $atp->tp->cp->kode ?? '' }}"
                                            data-tp-deskripsi="{{ $atp->tp->deskripsi ?? '' }}"
                                            data-tp-kode="{{ $atp->tp->kode ?? '' }}"
                                            {{ $draft && $draft->atp_id == $atp->id ? 'selected' : '' }}>
                                            Minggu {{ $atp->minggu_ke }} — {{ Str::limit($atp->materi, 60) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="atp-info" class="hidden mt-3 space-y-2">
                                    <div class="rounded bg-blue-50 p-3 text-sm">
                                        <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">CP
                                            &mdash; Capaian Pembelajaran</p>
                                        <p class="text-sm text-blue-900 mt-1" id="atp-info-cp"></p>
                                    </div>
                                    <div class="rounded bg-indigo-50 p-3 text-sm">
                                        <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wide">TP
                                            &mdash; Tujuan Pembelajaran</p>
                                        <p class="text-sm text-indigo-900 mt-1" id="atp-info-tp"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-lg bg-gray-50 p-4 mb-6">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Ringkasan</h4>
                                <ul class="text-sm text-gray-600 space-y-1" id="ringkasan">
                                    <li>✓ Selfie akan diambil</li>
                                    <li>✓ Presensi <span id="ringkasan-siswa">{{ $siswas->count() }}</span> siswa</li>
                                    <li>⏺ Pertemuan ke-{{ $pertemuanKe }}</li>
                                </ul>
                                @if ($draft)
                                    <div class="mt-2 text-xs text-indigo-600 bg-indigo-50 p-2 rounded">
                                        Draft tersimpan (step {{ $draft->draft_step }}).
                                        <button type="button" onclick="batalDraft()"
                                            class="text-red-600 underline ml-1">Batal</button>
                                    </div>
                                @endif
                            </div>

                            @if ($nextJadwals->isNotEmpty())
                                <div class="mb-6 rounded-lg border border-indigo-200 bg-indigo-50 p-4">
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input type="checkbox" id="terapkan-next" onchange="toggleNextJadwal(this)"
                                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <div>
                                            <span class="text-sm font-semibold text-indigo-900">Terapkan pada jam
                                                selanjutnya</span>
                                            <p class="text-xs text-indigo-700 mt-0.5">
                                                Guru juga mengajar kelas ini pada
                                                @foreach ($nextJadwals as $nj)
                                                    <span class="font-medium">Jam ke-{{ $nj->jam_ke }}</span>
                                                    @if (!$loop->last)
                                                        ,
                                                    @endif
                                                @endforeach
                                            </p>
                                        </div>
                                    </label>
                                    <div id="next-jadwal-list" class="hidden mt-3 space-y-1.5 pl-7">
                                        @foreach ($nextJadwals as $nj)
                                            <label
                                                class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                                <input type="checkbox" name="next_jadwal_ids[]"
                                                    value="{{ $nj->id }}" checked disabled
                                                    class="h-3.5 w-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                Jam ke-{{ $nj->jam_ke }} — {{ $nj->mapel->nama }}
                                                {{ $nj->kelas->nama }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="flex justify-between">
                                <button type="button" onclick="prevStep()"
                                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                                    &larr; Kembali
                                </button>
                                <button type="submit"
                                    class="rounded-md bg-indigo-600 px-8 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    Simpan Jurnal Mengajar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            .capture-pulse {
                animation: pulse-ring 2s ease-in-out infinite;
            }

            @keyframes pulse-ring {

                0%,
                100% {
                    box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.5);
                }

                50% {
                    box-shadow: 0 0 0 12px rgba(255, 255, 255, 0);
                }
            }

            /* Step 1 fullscreen hides header & sidebar */
            body.step-1-fullscreen header,
            body.step-1-fullscreen .sidebar-toggle {
                display: none !important;
            }

            body.step-1-fullscreen .lg\\:ml-64 {
                margin-left: 0 !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // Step navigation
            let currentStep = 1;
            const totalSteps = 3;
            let draftId = document.getElementById('draft_id')?.value || '';
            let saving = false;

            // Muat draft yang ada — set currentStep before DOMContentLoaded
            @if ($draft)
                currentStep = {{ min($draft->draft_step + 1, 3) }};
            @endif

            document.addEventListener('DOMContentLoaded', function() {
                showStep(currentStep);

                @if ($draft)
                    @if ($draft->foto_path)
                        // Tampilkan foto yg sudah disimpan
                        // Tampilkan foto yg sudah disimpan
                        document.getElementById('captured-img').src = '{{ asset('storage/' . $draft->foto_path) }}';
                        document.getElementById('captured-preview').classList.remove('hidden');
                        document.getElementById('camera-preview').classList.add('hidden');
                        document.getElementById('retake-btn').classList.remove('hidden');
                        document.getElementById('lanjut-presensi').disabled = false;
                        stopStream();
                        // foto_base64 kosong — server akan pakai foto_path dari draft
                    @endif
                    @if ($draft->latitude)
                        document.getElementById('latitude').value = '{{ $draft->latitude }}';
                        document.getElementById('longitude').value = '{{ $draft->longitude }}';
                    @endif
                    @if ($draft->materi)
                        document.querySelector('textarea[name="materi"]').value = '{{ $draft->materi }}';
                    @endif
                    @if ($draft->draft_step >= 2)
                        // Pre-fill presensi dari existingPresensi
                        const presensi = @json(
                            $existingPresensi->map(function ($p) {
                                    return ['siswa_id' => $p->siswa_id, 'status' => $p->status, 'keterangan' => $p->keterangan];
                                })->keyBy('siswa_id'));
                        document.querySelectorAll('input[type="radio"][name$="[status]"]').forEach(function(r) {
                            const match = r.name.match(/siswa\[(\d+)\]\[status\]/);
                            if (match) {
                                const idx = match[1];
                                const siswaRow = document.querySelector('input[name="siswa[' + idx + '][id]"]');
                                if (siswaRow) {
                                    const sId = siswaRow.value;
                                    if (presensi[sId]) {
                                        r.checked = r.value === presensi[sId].status;
                                        if (r.value !== 'hadir') r.dispatchEvent(new Event('change'));
                                    }
                                }
                            }
                        });
                        // Set keterangan
                        document.querySelectorAll('input[name$="[keterangan]"]').forEach(function(k) {
                            const match = k.name.match(/siswa\[(\d+)\]\[keterangan\]/);
                            if (match) {
                                const idx = match[1];
                                const siswaRow = document.querySelector('input[name="siswa[' + idx + '][id]"]');
                                if (siswaRow) {
                                    const sId = siswaRow.value;
                                    if (presensi[sId] && presensi[sId].keterangan) {
                                        k.value = presensi[sId].keterangan;
                                    }
                                }
                            }
                        });
                    @endif
                @endif
            });

            function showStep(step) {
                document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
                const el = document.querySelector(`.step-content[data-step="${step}"]`);
                if (el) el.classList.remove('hidden');

                // Step 1 fullscreen: lock body scroll, hide layout chrome
                document.body.classList.toggle('overflow-hidden', step === 1);
                document.body.classList.toggle('step-1-fullscreen', step === 1);
                const oldIndicators = document.getElementById('step-indicators');
                if (oldIndicators) {
                    oldIndicators.classList.toggle('hidden', step === 1);
                }

                // Update step dots (fullscreen top bar)
                document.querySelectorAll('.step-dot').forEach(d => {
                    const s = parseInt(d.dataset.step);
                    d.className = 'step-dot h-2 w-2 rounded-full ' + (s <= step ? 'bg-white' : 'bg-white/30');
                });

                currentStep = step;
            }

            function saveDraftAjax(step, callback) {
                if (saving) return;
                saving = true;

                const form = document.getElementById('jurnal-form');
                const formData = new FormData(form);
                formData.set('step', step);
                formData.set('draft_id', draftId);
                if (!formData.has('foto_base64') || !formData.get('foto_base64')) {
                    formData.delete('foto_base64');
                }
                // Jangan kirim next_jadwal_ids kalo checkbox utama gak dicentang
                if (!document.getElementById('terapkan-next')?.checked) {
                    const keys = [...formData.keys()].filter(k => k.startsWith('next_jadwal_ids'));
                    keys.forEach(k => formData.delete(k));
                }

                fetch('{{ route('jurnal-mengajar.save-draft') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                    .then(r => r.json())
                    .then(data => {
                        saving = false;
                        if (data.ok) {
                            draftId = data.draft_id;
                            document.getElementById('draft_id').value = draftId;
                            if (callback) callback();
                        } else {
                            console.warn('Save draft gagal:', data.message);
                            if (callback) callback();
                        }
                    })
                    .catch(e => {
                        saving = false;
                        console.warn('Save draft error:', e);
                        if (callback) callback();
                    });
            }

            function nextStep() {
                if (currentStep === 1) {
                    const foto = document.getElementById('foto_base64').value;
                    if (!foto && !draftId) {
                        alert('Ambil foto selfie dulu sebelum lanjut!');
                        return;
                    }
                }

                const next = currentStep + 1;
                if (next > totalSteps) return;

                // Simpan draft step skrg sblm lanjut
                saveDraftAjax(currentStep, function() {
                    showStep(next);
                });
            }

            function prevStep() {
                if (currentStep > 1) showStep(currentStep - 1);
            }

            // Toggle next jadwal list
            function toggleNextJadwal(checkbox) {
                document.getElementById('next-jadwal-list').classList.toggle('hidden', !checkbox.checked);
                // Disable/enable checkbox biar gak ke-submit kalo hidden
                document.querySelectorAll('#next-jadwal-list input[name="next_jadwal_ids[]"]').forEach(function(cb) {
                    cb.disabled = !checkbox.checked;
                });
            }

            // Set all students status
            function setAll(status) {
                document.querySelectorAll('input[type="radio"][name$="[status]"]').forEach(r => {
                    if (r.value === status) r.checked = true;
                    r.dispatchEvent(new Event('change'));
                });
            }

            // Toggle keterangan input — visible only when NOT hadir
            function toggleKet(idx, radio) {
                const ket = document.getElementById('ket-' + idx);
                if (ket) {
                    ket.classList.toggle('hidden', radio.value === 'hadir');
                    if (radio.value === 'hadir') ket.value = '';
                }
            }

            // Camera — front preferred, fallback to back, auto-location
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const cameraPreview = document.getElementById('camera-preview');
            const captureBtn = document.getElementById('capture-btn');
            const retakeBtn = document.getElementById('retake-btn');
            const lanjutBtn = document.getElementById('lanjut-presensi');
            const capturedPreview = document.getElementById('captured-preview');
            const capturedImg = document.getElementById('captured-img');
            const cameraError = document.getElementById('camera-error');
            const fotoBase64 = document.getElementById('foto_base64');
            let currentStream = null;

            async function startCamera() {
                const constraints = [{
                        video: {
                            facingMode: {
                                exact: 'user'
                            }
                        }
                    },
                    {
                        video: {
                            facingMode: 'user'
                        }
                    },
                    {
                        video: {
                            facingMode: {
                                exact: 'environment'
                            }
                        }
                    },
                    {
                        video: {
                            facingMode: 'environment'
                        }
                    },
                    {
                        video: true
                    },
                ];

                for (const c of constraints) {
                    try {
                        currentStream = await navigator.mediaDevices.getUserMedia(c);
                        video.srcObject = currentStream;
                        cameraPreview.classList.remove('hidden');
                        cameraError.classList.add('hidden');
                        return;
                    } catch (e) {
                        console.warn('Camera constraint failed:', JSON.stringify(c.video), e.name, e.message);
                        continue;
                    }
                }
                cameraPreview.classList.add('hidden');
                cameraError.classList.remove('hidden');
            }

            // Camera init — skip kalo draft udah punya foto
            @php $hasFoto = $draft && $draft->foto_path; @endphp
            @if (!$hasFoto)
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    startCamera();
                } else {
                    cameraPreview.classList.add('hidden');
                    cameraError.classList.remove('hidden');
                }
            @endif

            function stopStream() {
                if (currentStream) {
                    currentStream.getTracks().forEach(t => t.stop());
                    currentStream = null;
                }
            }

            function showCaptured(src) {
                capturedImg.src = src;
                capturedPreview.classList.remove('hidden');
                cameraPreview.classList.add('hidden');
                retakeBtn.classList.remove('hidden');
                lanjutBtn.disabled = false;
            }

            function showLive() {
                stopStream();
                fotoBase64.value = '';
                capturedPreview.classList.add('hidden');
                cameraPreview.classList.remove('hidden');
                retakeBtn.classList.add('hidden');
                lanjutBtn.disabled = true;
                startCamera();
            }

            if (captureBtn) {
                captureBtn.addEventListener('click', function() {
                    canvas.width = video.videoWidth || 720;
                    canvas.height = video.videoHeight || 1280;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    fotoBase64.value = canvas.toDataURL('image/jpeg', 0.8);
                    showCaptured(fotoBase64.value);

                    // Auto-save draft
                    setTimeout(function() {
                        if (fotoBase64.value) saveDraftAjax(1);
                    }, 100);
                });
            }

            if (retakeBtn) {
                retakeBtn.addEventListener('click', showLive);
            }

            if (lanjutBtn) {
                lanjutBtn.addEventListener('click', nextStep);
            }

            // Back button → go to previous page (cancel wizard)
            const backBtn = document.getElementById('btn-back');
            if (backBtn) {
                backBtn.addEventListener('click', function() {
                    if (draftId) batalDraft();
                    else window.history.back();
                });
            }

            // GPS auto-fill — required
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    document.getElementById('latitude').value = pos.coords.latitude.toFixed(6);
                    document.getElementById('longitude').value = pos.coords.longitude.toFixed(6);
                    // GPS aja jangan simpan draft — nanti simpan pas capture foto
                }, function(err) {
                    console.warn('Geolocation gagal:', err.message);
                    // Still allow form submit — user might be on desktop without GPS
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            }

            // ATP selector — show CP & TP info
            const atpSelect = document.getElementById('atp-select');
            const atpInfo = document.getElementById('atp-info');
            const atpInfoCp = document.getElementById('atp-info-cp');
            const atpInfoTp = document.getElementById('atp-info-tp');

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
                // Show info if already selected (draft restore)
                if (atpSelect.value) updateAtpInfo();
            }

            // Simpan draft sebelum submit final
            document.getElementById('jurnal-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const materi = document.querySelector('textarea[name="materi"]').value.trim();
                if (!materi) {
                    alert('Isi materi pertemuan dulu sebelum simpan!');
                    return;
                }

                saveDraftAjax(3, function() {
                    HTMLFormElement.prototype.submit.call(document.getElementById('jurnal-form'));
                });
            });

            // Batal draft & reload
            function batalDraft() {
                if (!draftId) {
                    window.location.reload();
                    return;
                }
                if (!confirm('Hapus draft dan mulai ulang?')) return;
                fetch('{{ route('jurnal-mengajar.destroy-draft', ['jurnal' => '__DRAFT_ID__']) }}'.replace('__DRAFT_ID__',
                    draftId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                }).then(function() {
                    window.location.reload();
                });
            }
        </script>
    @endpush
</x-app-layout>
