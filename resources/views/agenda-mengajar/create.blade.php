<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Ambil Selfie Agenda') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-lg sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">
                            <strong>Mapel:</strong> {{ $jadwal->mapel->nama }}<br>
                            <strong>Kelas:</strong> {{ $jadwal->kelas->nama }}<br>
                            <strong>Jam ke-</strong>{{ $jadwal->jam_ke }} • {{ $jadwal->hari }}
                        </p>
                    </div>

                    <form action="{{ route('agenda-mengajar.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Selfie di Depan
                                Kelas</label>
                            <div id="camera-preview" class="mb-3 hidden">
                                <video id="video" class="w-full rounded-lg border" autoplay playsinline></video>
                                <canvas id="canvas" class="hidden"></canvas>
                                <button type="button" id="capture-btn"
                                    class="mt-2 rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                                    📸 Ambil Foto
                                </button>
                            </div>
                            <div id="fallback-upload">
                                <input type="file" name="foto" accept="image/jpeg,image/png,image/jpg"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0 file:text-sm file:font-semibold
                                    file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                            <input type="hidden" name="foto_base64" id="foto_base64">
                            @error('foto')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Latitude (opsional)</label>
                                <input type="text" name="latitude" id="latitude"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                                    placeholder="-6.xxxx">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Longitude (opsional)</label>
                                <input type="text" name="longitude" id="longitude"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                                    placeholder="106.xxxx">
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Simpan Selfie
                            </button>
                            <a href="{{ route('agenda-mengajar.index') }}"
                                class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Auto-fill GPS jika diizinkan
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    document.getElementById('latitude').value = pos.coords.latitude.toFixed(6);
                    document.getElementById('longitude').value = pos.coords.longitude.toFixed(6);
                }, function() {});
            }

            // Camera capture
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const preview = document.getElementById('camera-preview');
            const fallback = document.getElementById('fallback-upload');
            const captureBtn = document.getElementById('capture-btn');
            const fotoBase64 = document.getElementById('foto_base64');
            const fileInput = document.querySelector('input[name="foto"]');

            // Try camera
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user'
                        }
                    })
                    .then(function(stream) {
                        preview.classList.remove('hidden');
                        fallback.classList.add('hidden');
                        video.srcObject = stream;
                    })
                    .catch(function() {
                        // Fallback to file upload
                    });
            }

            captureBtn.addEventListener('click', function() {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                fotoBase64.value = canvas.toDataURL('image/jpeg', 0.8);
                // Stop camera
                video.srcObject.getTracks().forEach(t => t.stop());
                preview.classList.add('hidden');
                fallback.classList.remove('hidden');
                // Disable file input, use base64
                fileInput.disabled = true;
                document.querySelector('input[name="foto"]').disabled = true;
            });
        </script>
    @endpush
</x-app-layout>
