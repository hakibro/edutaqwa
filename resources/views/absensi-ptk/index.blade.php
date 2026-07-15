<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Absensi PTK (Kehadiran Harian)') }}</h2>
            <span class="text-sm text-gray-500">{{ $guru->nama }}</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            {{-- Info Lokasi Absen --}}
            @if ($lembaga->lokasi_absen || $lembaga->latitude_absen)
                <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 p-3 text-sm text-blue-700">
                    <strong>Lokasi absen:</strong> {{ $lembaga->lokasi_absen ?? 'Titik koordinat' }}
                    @if ($lembaga->latitude_absen)
                        · Radius {{ $lembaga->radius_absen_meter }}m
                    @endif
                    @if ($lembaga->wajib_selfie)
                        · <span class="font-semibold">Wajib upload selfie</span>
                    @endif
                </div>
            @endif

            {{-- Status Hari Ini --}}
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Hari Ini</h3>
                    @if (!$jamKerjaHariIni)
                        <p class="text-sm text-gray-500">Tidak ada jam kerja untuk hari ini.</p>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <span class="block text-xs text-gray-500">Jam Masuk</span>
                                <span class="text-lg font-semibold">{{ $jamKerjaHariIni->jam_masuk }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Jam Pulang</span>
                                <span class="text-lg font-semibold">{{ $jamKerjaHariIni->jam_pulang }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Toleransi</span>
                                <span class="text-lg font-semibold">{{ $jamKerjaHariIni->toleransi_keterlambatan }}
                                    menit</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-500">Status Hari Ini</span>
                                @if ($absensiHariIni)
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                        {{ $absensiHariIni->status === 'tepat_waktu' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $absensiHariIni->status === 'terlambat' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $absensiHariIni->status === 'pulang_awal' ? 'bg-orange-100 text-orange-700' : '' }}
                                        {{ $absensiHariIni->status === 'tidak_absen' ? 'bg-red-100 text-red-700' : '' }}">
                                        {{ str_replace('_', ' ', ucfirst($absensiHariIni->status)) }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">Belum absen</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex gap-3">
                            @if ($canCheckIn)
                                <form action="{{ route('absensi-ptk.check-in') }}" method="POST"
                                    enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="latitude" id="lat-in" value="">
                                    <input type="hidden" name="longitude" id="lng-in" value="">
                                    <input type="hidden" name="lokasi" id="lokasi-in" value="">

                                    @if ($lembaga->wajib_selfie)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Foto Selfie <span
                                                    class="text-red-500">*</span></label>
                                            <div class="mt-1 flex items-center gap-3">
                                                <button type="button" onclick="bukaKamera('checkin')"
                                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                                    📷 Buka Kamera
                                                </button>
                                                <span class="text-sm text-gray-400" id="status-cam-checkin">Belum
                                                    ambil foto</span>
                                            </div>
                                            <video id="video-checkin" autoplay playsinline
                                                class="mt-2 hidden w-full max-w-xs rounded-lg border"></video>
                                            <canvas id="canvas-checkin" class="hidden"></canvas>
                                            <input type="hidden" name="foto" id="foto-base64-checkin"
                                                value="">
                                            <input type="file" name="foto_file" accept="image/*" capture="user"
                                                onchange="fileToBase64(this, 'checkin')"
                                                class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                            <p class="mt-1 text-xs text-gray-400">Atau pilih dari galeri</p>
                                        </div>
                                    @endif

                                    <button type="submit" onclick="ambilLokasi(this.form)"
                                        class="rounded-md bg-green-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-green-500">
                                        ✅ Check-in
                                    </button>
                                </form>
                            @endif
                            @if ($canCheckOut)
                                <form action="{{ route('absensi-ptk.check-out') }}" method="POST"
                                    enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="latitude" id="lat-out" value="">
                                    <input type="hidden" name="longitude" id="lng-out" value="">
                                    <input type="hidden" name="lokasi" id="lokasi-out" value="">

                                    @if ($lembaga->wajib_selfie)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Foto Selfie <span
                                                    class="text-red-500">*</span></label>
                                            <div class="mt-1 flex items-center gap-3">
                                                <button type="button" onclick="bukaKamera('checkout')"
                                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                                    📷 Buka Kamera
                                                </button>
                                                <span class="text-sm text-gray-400" id="status-cam-checkout">Belum
                                                    ambil foto</span>
                                            </div>
                                            <video id="video-checkout" autoplay playsinline
                                                class="mt-2 hidden w-full max-w-xs rounded-lg border"></video>
                                            <canvas id="canvas-checkout" class="hidden"></canvas>
                                            <input type="hidden" name="foto" id="foto-base64-checkout"
                                                value="">
                                            <input type="file" name="foto_file" accept="image/*" capture="user"
                                                onchange="fileToBase64(this, 'checkout')"
                                                class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                            <p class="mt-1 text-xs text-gray-400">Atau pilih dari galeri</p>
                                        </div>
                                    @endif

                                    <button type="submit" onclick="ambilLokasi(this.form)"
                                        class="rounded-md bg-orange-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-orange-500">
                                        ⏏️ Check-out
                                    </button>
                                </form>
                            @endif
                            @if ($absensiHariIni && $absensiHariIni->check_in && !$canCheckIn && !$canCheckOut)
                                <p class="text-sm text-gray-500 py-3">✔ Check-in & Check-out selesai hari ini.</p>
                            @endif
                        </div>
                        @if ($absensiHariIni && $absensiHariIni->check_in)
                            <p class="mt-2 text-xs text-gray-400">Check-in:
                                {{ $absensiHariIni->check_in->format('H:i') }}
                                @if ($absensiHariIni->keterlambatan_menit > 0)
                                    (Terlambat {{ $absensiHariIni->keterlambatan_menit }} menit)
                                @endif
                                @if ($absensiHariIni->lokasi_check_in)
                                    · {{ $absensiHariIni->lokasi_check_in }}
                                @endif
                                @if ($absensiHariIni->foto_check_in)
                                    · <a href="{{ asset('storage/' . $absensiHariIni->foto_check_in) }}"
                                        target="_blank" class="text-indigo-600 underline">Lihat foto</a>
                                @endif
                            </p>
                        @endif
                        @if ($absensiHariIni && $absensiHariIni->check_out)
                            <p class="text-xs text-gray-400">Check-out:
                                {{ $absensiHariIni->check_out->format('H:i') }}
                                @if ($absensiHariIni->lokasi_check_out)
                                    · {{ $absensiHariIni->lokasi_check_out }}
                                @endif
                                @if ($absensiHariIni->foto_check_out)
                                    · <a href="{{ asset('storage/' . $absensiHariIni->foto_check_out) }}"
                                        target="_blank" class="text-indigo-600 underline">Lihat foto</a>
                                @endif
                            </p>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Riwayat --}}
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Riwayat Absensi</h3>
                        <form method="GET" class="flex items-center gap-2">
                            <input type="month" name="bulan" value="{{ $bulan }}"
                                class="rounded-md border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                        </form>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Tanggal</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Check-in</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Check-out</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Keterlambatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($absensis as $a)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                        {{ $a->tanggal->format('d/m/Y') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                        {{ $a->check_in?->format('H:i') ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                        {{ $a->check_out?->format('H:i') ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                            {{ $a->status === 'tepat_waktu' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $a->status === 'terlambat' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                            {{ $a->status === 'pulang_awal' ? 'bg-orange-100 text-orange-700' : '' }}
                                            {{ $a->status === 'tidak_absen' ? 'bg-red-100 text-red-700' : '' }}
                                            {{ $a->status === 'libur' ? 'bg-gray-100 text-gray-700' : '' }}">
                                            {{ str_replace('_', ' ', ucfirst($a->status)) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                        {{ $a->keterlambatan_menit > 0 ? $a->keterlambatan_menit . ' menit' : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">Belum ada
                                        data absensi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let mediaStreams = {};

        function bukaKamera(tipe) {
            const video = document.getElementById('video-' + tipe);
            const status = document.getElementById('status-cam-' + tipe);
            const btn = document.querySelector(`button[onclick="bukaKamera('${tipe}')"]`);

            if (mediaStreams[tipe]) {
                // Stop kamera
                mediaStreams[tipe].getTracks().forEach(t => t.stop());
                delete mediaStreams[tipe];
                video.classList.add('hidden');
                video.srcObject = null;
                btn.textContent = '📷 Buka Kamera';
                status.textContent = 'Belum ambil foto';
                return;
            }

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            width: {
                                ideal: 640
                            },
                            height: {
                                ideal: 480
                            }
                        },
                        audio: false
                    })
                    .then(function(stream) {
                        mediaStreams[tipe] = stream;
                        video.srcObject = stream;
                        video.classList.remove('hidden');
                        btn.textContent = '❌ Tutup Kamera';
                        status.textContent = 'Arahkan kamera ke wajah';
                        video.onclick = function() {
                            ambilFoto(tipe);
                        };
                    })
                    .catch(function(err) {
                        alert('Gagal akses kamera: ' + err.message +
                            '\nGunakan opsi pilih file sebagai cadangan.');
                    });
            } else {
                alert('Browser tidak mendukung akses kamera. Gunakan opsi pilih file.');
            }
        }

        function ambilFoto(tipe) {
            const video = document.getElementById('video-' + tipe);
            const canvas = document.getElementById('canvas-' + tipe);
            const status = document.getElementById('status-cam-' + tipe);
            const hiddenInput = document.getElementById('foto-base64-' + tipe);
            const btn = document.querySelector(`button[onclick="bukaKamera('${tipe}')"]`);

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);

            const base64 = canvas.toDataURL('image/jpeg', 0.8);
            hiddenInput.value = base64;

            // Stop kamera
            if (mediaStreams[tipe]) {
                mediaStreams[tipe].getTracks().forEach(t => t.stop());
                delete mediaStreams[tipe];
                video.classList.add('hidden');
                video.srcObject = null;
            }
            btn.textContent = '📷 Buka Kamera';
            status.textContent = '✅ Foto sudah diambil';
        }

        function fileToBase64(input, tipe) {
            const file = input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('foto-base64-' + tipe).value = e.target.result;
                document.getElementById('status-cam-' + tipe).textContent = '✅ Foto dari galeri';
            };
            reader.readAsDataURL(file);
        }

        function ambilLokasi(form) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        form.querySelector('[id^="lat-"]').value = pos.coords.latitude;
                        form.querySelector('[id^="lng-"]').value = pos.coords.longitude;
                        form.querySelector('[id^="lokasi-"]').value =
                            pos.coords.latitude.toFixed(6) + ',' + pos.coords.longitude.toFixed(6);
                        // Set base64 from camera hidden input if exists
                        const tipe = form.querySelector('[id^="foto-base64-"]')?.id?.split('-').pop();
                        if (tipe && !document.getElementById('foto-base64-' + tipe)?.value) {
                            // If no camera capture, check file input
                            const fileInput = form.querySelector('input[name="foto_file"]');
                            if (fileInput && fileInput.files.length > 0) {
                                fileToBase64(fileInput, tipe);
                            }
                        }
                        form.submit();
                    },
                    function(err) {
                        alert('Gagal mendapatkan lokasi: ' + err.message +
                            '\nPastikan GPS aktif dan izin lokasi diberikan.');
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            } else {
                alert('Browser tidak mendukung geolokasi.');
            }
        }
    </script>
</x-app-layout>
