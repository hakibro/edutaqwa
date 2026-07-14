<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Edit Guru') }}: {{ $guru->nama }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('guru.update', $guru) }}" method="POST" class="p-6 space-y-4"
                    enctype="multipart/form-data">
                    @csrf @method('PUT')

                    @if (!auth()->user()->lembaga_id)
                        <div>
                            <x-input-label for="lembaga_id" value="Lembaga" />
                            <select id="lembaga_id" name="lembaga_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach ($lembagas as $l)
                                    <option value="{{ $l->id }}" @selected(old('lembaga_id', $guru->lembaga_id) == $l->id)>{{ $l->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('lembaga_id')" class="mt-2" />
                        </div>
                    @endif

                    <div>
                        <x-input-label for="nama" value="Nama Lengkap" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                            :value="old('nama', $guru->nama)" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="nip" value="NIP" />
                            <x-text-input id="nip" name="nip" type="text" class="mt-1 block w-full"
                                :value="old('nip', $guru->nip)" />
                        </div>
                        <div>
                            <x-input-label for="nuptk" value="NUPTK" />
                            <x-text-input id="nuptk" name="nuptk" type="text" class="mt-1 block w-full"
                                :value="old('nuptk', $guru->nuptk)" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="jenis_ptk_id" value="Jenis PTK" />
                            <select id="jenis_ptk_id" name="jenis_ptk_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Pilih --</option>
                                @foreach ($jenisPtks as $j)
                                    <option value="{{ $j->id }}" @selected(old('jenis_ptk_id', $guru->jenis_ptk_id) == $j->id)>{{ $j->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Atur daftar Jenis PTK di menu <a
                                    href="{{ route('jenis-ptk.index') }}" class="text-indigo-600 underline">Jenis
                                    PTK</a></p>
                        </div>
                        <div>
                            <x-input-label for="status_satminkal" />
                            <label class="mt-2 flex items-center gap-2">
                                <input type="checkbox" id="status_satminkal" name="status_satminkal" value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    @checked(old('status_satminkal', $guru->status_satminkal))>
                                <span class="text-sm text-gray-700">Status Satminkal (PTK Tetap)</span>
                            </label>
                            @if ($guru->kode_guru_satminkal)
                                <p class="mt-1 text-xs text-indigo-600">Kode Satminkal: {{ $guru->kode_guru_satminkal }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <hr class="my-2">
                    <h3 class="text-sm font-semibold text-gray-700">Tugas Tambahan</h3>
                    <p class="text-xs text-gray-500">Guru Mapel, BK, Wali Kelas, dll.</p>
                    <div id="tugasTambahanContainer">
                        @foreach ($guru->tugasTambahans as $idx => $tt)
                            <div class="tugas-tambahan-item grid grid-cols-1 md:grid-cols-3 gap-2 mb-2">
                                <div>
                                    <select name="tugas_tambahan[{{ $idx }}][jenis]"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="">-- Pilih Tugas Tambahan --</option>
                                        <option value="Guru Mapel" @selected($tt->jenis == 'Guru Mapel')>Guru Mapel</option>
                                        <option value="BK" @selected($tt->jenis == 'BK')>BK</option>
                                        <option value="Wali Kelas" @selected($tt->jenis == 'Wali Kelas')>Wali Kelas</option>
                                        <option value="Pembina Ekskul" @selected($tt->jenis == 'Pembina Ekskul')>Pembina Ekskul
                                        </option>
                                        <option value="Koordinator" @selected($tt->jenis == 'Koordinator')>Koordinator</option>
                                    </select>
                                </div>
                                <div>
                                    <input type="text" name="tugas_tambahan[{{ $idx }}][keterangan]"
                                        value="{{ $tt->keterangan }}" placeholder="Keterangan (opsional)"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                                <div class="flex gap-1">
                                    <select name="tugas_tambahan[{{ $idx }}][tahun_ajaran_id]"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="">-- Tahun Ajaran --</option>
                                        @foreach ($tahunAjarans as $ta)
                                            <option value="{{ $ta->id }}" @selected($tt->tahun_ajaran_id == $ta->id)>
                                                {{ $ta->nama }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="this.parentElement.parentElement.remove()"
                                        class="text-red-500 hover:text-red-700 text-xs px-1">&times;</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addTugasTambahan()"
                        class="text-sm text-indigo-600 hover:text-indigo-800">+ Tambah baris</button>
                    <x-input-error :messages="$errors->get('tugas_tambahan')" class="mt-2" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tempat_lahir" value="Tempat Lahir" />
                            <x-text-input id="tempat_lahir" name="tempat_lahir" type="text" class="mt-1 block w-full"
                                :value="old('tempat_lahir', $guru->tempat_lahir)" />
                        </div>
                        <div>
                            <x-input-label for="tanggal_lahir" value="Tanggal Lahir" />
                            <x-text-input id="tanggal_lahir" name="tanggal_lahir" type="date"
                                class="mt-1 block w-full" :value="old('tanggal_lahir', $guru->tanggal_lahir?->format('Y-m-d'))" />
                        </div>
                        <div>
                            <x-input-label for="tmt" value="TMT (Tanggal Mulai Tugas)" />
                            <x-text-input id="tmt" name="tmt" type="date" class="mt-1 block w-full"
                                :value="old('tmt', $guru->tmt?->format('Y-m-d'))" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="alamat" value="Alamat" />
                        <textarea id="alamat" name="alamat"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="2">{{ old('alamat', $guru->alamat) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="telp" value="Telepon" />
                            <x-text-input id="telp" name="telp" type="text" class="mt-1 block w-full"
                                :value="old('telp', $guru->telp)" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                :value="old('email', $guru->email)" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="kode_guru_lembaga" value="Kode Guru Lembaga" />
                        <x-text-input id="kode_guru_lembaga" type="text" class="mt-1 block w-full bg-gray-100"
                            :value="$guru->kode_guru_lembaga" disabled />
                        <p class="mt-1 text-xs text-gray-500">Kode guru lembaga di-generate otomatis.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked(old('is_active', $guru->is_active))>
                        <x-input-label for="is_active" value="Aktif" />
                    </div>

                    <hr class="my-2">
                    <h3 class="text-sm font-semibold text-gray-700">Dokumen</h3>
                    @php $dokumenList = $guru->dokumen ? json_decode($guru->dokumen, true) : []; @endphp
                    @if (!empty($dokumenList))
                        <div class="space-y-2">
                            @foreach ($dokumenList as $idx => $path)
                                <div class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="keep_dokumen[]" value="{{ $idx }}" checked
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <a href="{{ asset('storage/' . $path) }}" target="_blank"
                                        class="text-indigo-600 hover:underline">Dokumen {{ $idx + 1 }}</a>
                                    <span class="text-xs text-gray-500">(centang = pertahankan)</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Belum ada dokumen.</p>
                    @endif
                    <div>
                        <x-input-label for="dokumen" value="Tambah Dokumen Baru" />
                        <input type="file" id="dokumen" name="dokumen[]" multiple accept=".pdf,.jpg,.jpeg,.png"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        <p class="mt-1 text-xs text-gray-500">Maks 5MB per file. Format: PDF/JPG/PNG.</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">

                        <div class="flex justify-end gap-3 pt-4">
                            <a href="{{ route('guru.index') }}"
                                class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                            <x-primary-button>Simpan</x-primary-button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    let tugasTambahanIndex = {{ count($guru->tugasTambahans) }};

    function addTugasTambahan() {
        const container = document.getElementById('tugasTambahanContainer');
        const template = `
        <div class="tugas-tambahan-item grid grid-cols-1 md:grid-cols-3 gap-2 mb-2">
            <div>
                <select name="tugas_tambahan[${tugasTambahanIndex}][jenis]"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">-- Pilih Tugas Tambahan --</option>
                    <option value="Guru Mapel">Guru Mapel</option>
                    <option value="BK">BK</option>
                    <option value="Wali Kelas">Wali Kelas</option>
                    <option value="Pembina Ekskul">Pembina Ekskul</option>
                    <option value="Koordinator">Koordinator</option>
                </select>
            </div>
            <div>
                <input type="text" name="tugas_tambahan[${tugasTambahanIndex}][keterangan]" placeholder="Keterangan (opsional)"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div class="flex gap-1">
                <select name="tugas_tambahan[${tugasTambahanIndex}][tahun_ajaran_id]"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">-- Tahun Ajaran --</option>
                    @foreach ($tahunAjarans as $ta)
                        <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                    @endforeach
                </select>
                <button type="button" onclick="this.parentElement.parentElement.remove()"
                    class="text-red-500 hover:text-red-700 text-xs px-1">&times;</button>
            </div>
        </div>`;
        container.insertAdjacentHTML('beforeend', template);
        tugasTambahanIndex++;
    }
</script>
