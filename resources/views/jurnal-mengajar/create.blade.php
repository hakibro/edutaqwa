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

                {{-- STEP 1: Selfie --}}
                <div class="step-content" data-step="1">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600">
                                    <strong>Mapel:</strong> {{ $jadwal->mapel->nama }}<br>
                                    <strong>Kelas:</strong> {{ $jadwal->kelas->nama }}<br>
                                    <strong>Jam ke-</strong>{{ $jadwal->jam_ke }} • {{ $jadwal->hari }}
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Foto Selfie di Depan
                                    Kelas</label>
                                <p class="text-xs text-gray-500 mb-2">Kamera depan akan aktif. Izinkan akses kamera dan
                                    lokasi.</p>
                                <div id="camera-preview" class="mb-3">
                                    <video id="video" class="w-full rounded-lg border" autoplay playsinline></video>
                                    <canvas id="canvas" class="hidden"></canvas>
                                    <div class="mt-2 flex gap-2">
                                        <button type="button" id="capture-btn"
                                            class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                                            📸 Ambil Foto
                                        </button>
                                        <button type="button" id="retake-btn"
                                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300 hidden">
                                            🔄 Ulang
                                        </button>
                                    </div>
                                </div>
                                <div id="captured-preview" class="hidden mb-3">
                                    <img id="captured-img" class="w-full rounded-lg border" />
                                </div>
                                <div id="camera-error"
                                    class="hidden mb-3 p-3 bg-red-50 rounded-lg text-sm text-red-700">
                                    Kamera tidak tersedia. Pastikan: (1) Perangkat memiliki kamera, (2) Izin kamera
                                    diizinkan, (3) Buka lewat HTTPS jika dari jarak jauh.
                                </div>
                                <input type="hidden" name="foto_base64" id="foto_base64">
                                @error('foto')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4 hidden">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Latitude</label>
                                    <input type="text" name="latitude" id="latitude"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Longitude</label>
                                    <input type="text" name="longitude" id="longitude"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" readonly>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="button" onclick="nextStep()"
                                    class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    Lanjut ke Presensi &rarr;
                                </button>
                            </div>
                        </div>
                    </div>
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

                            <div class="rounded-lg bg-gray-50 p-4 mb-6">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Ringkasan</h4>
                                <ul class="text-sm text-gray-600 space-y-1" id="ringkasan">
                                    <li>✓ Selfie akan diambil</li>
                                    <li>✓ Presensi <span id="ringkasan-siswa">{{ $siswas->count() }}</span> siswa</li>
                                    <li>⏺ Pertemuan ke-{{ $pertemuanKe }}</li>
                                </ul>
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
                                                    value="{{ $nj->id }}" checked
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

    @push('scripts')
        <script>
            // Step navigation
            let currentStep = 1;
            const totalSteps = 3;

            function showStep(step) {
                document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
                document.querySelector(`.step-content[data-step="${step}"]`).classList.remove('hidden');

                document.querySelectorAll('.step-indicator').forEach(el => {
                    const s = parseInt(el.dataset.step);
                    el.classList.remove('active', 'bg-indigo-600', 'text-white', 'bg-gray-200', 'text-gray-500');
                    if (s === step) {
                        el.classList.add('active', 'bg-indigo-600', 'text-white');
                    } else if (s < step) {
                        el.classList.add('bg-green-500', 'text-white');
                    } else {
                        el.classList.add('bg-gray-200', 'text-gray-500');
                    }
                });
                currentStep = step;
            }

            function nextStep() {
                if (currentStep < totalSteps) showStep(currentStep + 1);
            }

            function prevStep() {
                if (currentStep > 1) showStep(currentStep - 1);
            }

            // Toggle next jadwal list
            function toggleNextJadwal(checkbox) {
                document.getElementById('next-jadwal-list').classList.toggle('hidden', !checkbox.checked);
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
            const preview = document.getElementById('camera-preview');
            const captureBtn = document.getElementById('capture-btn');
            const retakeBtn = document.getElementById('retake-btn');
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
                        preview.classList.remove('hidden');
                        cameraError.classList.add('hidden');
                        return;
                    } catch (e) {
                        console.warn('Camera constraint failed:', JSON.stringify(c.video), e.name, e.message);
                        continue;
                    }
                }
                preview.classList.add('hidden');
                cameraError.classList.remove('hidden');
            }

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                startCamera();
            } else {
                preview.classList.add('hidden');
                cameraError.classList.remove('hidden');
            }

            if (captureBtn) {
                captureBtn.addEventListener('click', function() {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    fotoBase64.value = canvas.toDataURL('image/jpeg', 0.8);

                    // Show captured preview
                    capturedImg.src = fotoBase64.value;
                    capturedPreview.classList.remove('hidden');
                    preview.classList.add('hidden');
                    retakeBtn.classList.remove('hidden');
                });
            }

            if (retakeBtn) {
                retakeBtn.addEventListener('click', function() {
                    fotoBase64.value = '';
                    capturedPreview.classList.add('hidden');
                    retakeBtn.classList.add('hidden');
                    startCamera();
                });
            }

            // GPS auto-fill — required
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    document.getElementById('latitude').value = pos.coords.latitude.toFixed(6);
                    document.getElementById('longitude').value = pos.coords.longitude.toFixed(6);
                }, function(err) {
                    console.warn('Geolocation gagal:', err.message);
                    // Still allow form submit — user might be on desktop without GPS
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            }
        </script>
    @endpush
</x-app-layout>
