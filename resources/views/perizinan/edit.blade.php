<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Edit Perizinan Siswa') }}</h2>
            <a href="{{ route('perizinan.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            <form action="{{ route('perizinan.update', $perizinan) }}" method="POST"
                class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                @csrf
                @method('PUT')

                <div class="p-6 space-y-5">
                    {{-- Pilih Kelas --}}
                    <div>
                        <label for="kelas_id" class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                        <select name="kelas_id" id="kelas_id" required
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                            onchange="loadSiswa(this.value)">
                            <option value="">Pilih Kelas</option>
                            @foreach ($kelasList as $k)
                                <option value="{{ $k->id }}"
                                    {{ old('kelas_id', $perizinan->kelas_id) == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pilih Siswa --}}
                    <div>
                        <label for="siswa_id" class="block text-sm font-medium text-gray-700 mb-1">Siswa</label>
                        <select name="siswa_id" id="siswa_id" required
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Pilih Siswa</option>
                            @foreach ($siswaList as $s)
                                <option value="{{ $s->id }}"
                                    {{ old('siswa_id', $perizinan->siswa_id) == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama }} {{ $s->nis ? '(' . $s->nis . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('siswa_id')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" required
                            value="{{ old('tanggal', $perizinan->tanggal->toDateString()) }}"
                            max="{{ now()->toDateString() }}"
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @error('tanggal')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jenis Perizinan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Perizinan</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="jenis" value="sakit"
                                    {{ old('jenis', $perizinan->jenis) === 'sakit' ? 'checked' : '' }}
                                    class="rounded-full border-gray-300 text-amber-600 focus:ring-amber-500">
                                <span class="text-sm font-medium text-gray-700">Sakit</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="jenis" value="izin"
                                    {{ old('jenis', $perizinan->jenis) === 'izin' ? 'checked' : '' }}
                                    class="rounded-full border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-gray-700">Izin</span>
                            </label>
                        </div>
                        @error('jenis')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Keterangan --}}
                    <div>
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="2" placeholder="Alasan sakit/izin (opsional)"
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('keterangan', $perizinan->keterangan) }}</textarea>
                        @error('keterangan')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Lampiran (Paste Gambar) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Lampiran Bukti
                        </label>
                        <p class="text-xs text-gray-400 mb-2">Paste gambar baru (Ctrl+V) untuk mengganti lampiran.
                            Kosongkan jika tidak ingin mengganti.</p>
                        <div id="lampiran-dropzone"
                            class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-emerald-400 transition min-h-[160px] flex flex-col items-center justify-center"
                            tabindex="0">
                            <div id="lampiran-placeholder" class="{{ $perizinan->lampiran ? 'hidden' : '' }}">
                                <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor"
                                    stroke-width="1" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                </svg>
                                <p class="text-sm text-gray-400">Ctrl+V untuk paste gambar baru</p>
                                <p class="text-xs text-gray-300 mt-1">atau klik area ini lalu paste</p>
                            </div>
                            <img id="lampiran-preview"
                                src="{{ $perizinan->lampiran ? Storage::url($perizinan->lampiran) : '' }}"
                                alt="Preview"
                                class="{{ $perizinan->lampiran ? '' : 'hidden' }} max-h-48 rounded-lg object-contain" />
                            <button type="button" id="lampiran-remove"
                                class="{{ $perizinan->lampiran ? '' : 'hidden' }} absolute top-2 right-2 w-7 h-7 rounded-full bg-red-500 text-white text-xs flex items-center justify-center hover:bg-red-600 shadow"
                                title="Hapus gambar">&times;</button>
                        </div>
                        <input type="hidden" name="lampiran" id="lampiran-input" value="">
                        @error('lampiran')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
                    <p class="text-xs text-gray-500">
                        Perizinan akan diterapkan ulang ke jurnal setelah update.
                    </p>
                    <button type="submit"
                        class="rounded-md bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                        Perbarui Perizinan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            async function loadSiswa(kelasId) {
                const select = document.getElementById('siswa_id');
                if (!kelasId) {
                    select.innerHTML = '<option value="">Pilih Kelas dulu</option>';
                    select.disabled = true;
                    return;
                }

                select.innerHTML = '<option value="">Memuat...</option>';
                select.disabled = true;

                try {
                    const res = await fetch(`{{ route('perizinan.get-siswa') }}?kelas_id=${kelasId}`);
                    if (!res.ok) throw new Error('Gagal memuat data');
                    const siswa = await res.json();

                    select.innerHTML = '<option value="">Pilih Siswa</option>';
                    siswa.forEach(s => {
                        select.innerHTML +=
                            `<option value="${s.id}">${s.nama} ${s.nis ? '('+s.nis+')' : ''}</option>`;
                    });
                    select.disabled = false;
                } catch (e) {
                    select.innerHTML = '<option value="">Gagal memuat</option>';
                    select.disabled = true;
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                // --- Paste image handler ---
                const dropzone = document.getElementById('lampiran-dropzone');
                const input = document.getElementById('lampiran-input');
                const preview = document.getElementById('lampiran-preview');
                const placeholder = document.getElementById('lampiran-placeholder');
                const removeBtn = document.getElementById('lampiran-remove');

                function setLampiran(base64) {
                    input.value = base64;
                    preview.src = base64;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                    removeBtn.classList.remove('hidden');
                }

                function clearLampiran() {
                    input.value = '';
                    preview.src = '';
                    preview.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                    removeBtn.classList.add('hidden');
                }

                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    clearLampiran();
                });

                dropzone.addEventListener('paste', (e) => {
                    const items = e.clipboardData?.items;
                    if (!items) return;
                    for (const item of items) {
                        if (item.type.startsWith('image/')) {
                            e.preventDefault();
                            const blob = item.getAsFile();
                            const reader = new FileReader();
                            reader.onload = (ev) => setLampiran(ev.target.result);
                            reader.readAsDataURL(blob);
                            return;
                        }
                    }
                });

                dropzone.addEventListener('click', () => dropzone.focus());
            });
        </script>
    @endpush
</x-app-layout>
