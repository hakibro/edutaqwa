<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Edit Siswa') }}: {{ $siswa->nama }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('siswa.update', $siswa) }}" method="POST" class="p-6 space-y-4"
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
                                    <option value="{{ $l->id }}" @selected(old('lembaga_id', $siswa->lembaga_id) == $l->id)>{{ $l->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="nis" value="NIS" />
                            <x-text-input id="nis" name="nis" type="text" class="mt-1 block w-full"
                                :value="old('nis', $siswa->nis)" required />
                        </div>
                        <div>
                            <x-input-label for="nisn" value="NISN" />
                            <x-text-input id="nisn" name="nisn" type="text" class="mt-1 block w-full"
                                :value="old('nisn', $siswa->nisn)" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="nama" value="Nama Lengkap" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                            :value="old('nama', $siswa->nama)" required />
                    </div>

                    <div>
                        <x-input-label for="foto" value="Foto (opsional)" />
                        @if ($siswa->foto)
                            <div class="mt-1 mb-2">
                                <img src="{{ asset('storage/' . $siswa->foto) }}" alt="Foto {{ $siswa->nama }}"
                                    class="w-24 h-24 object-cover rounded border">
                            </div>
                        @endif
                        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/jpg"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        <p class="mt-1 text-xs text-gray-500">Maks 2MB. Format: JPG/PNG. Kosongkan jika tidak ingin
                            mengganti.</p>
                        <x-input-error :messages="$errors->get('foto')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tempat_lahir" value="Tempat Lahir" />
                            <x-text-input id="tempat_lahir" name="tempat_lahir" type="text" class="mt-1 block w-full"
                                :value="old('tempat_lahir', $siswa->tempat_lahir)" />
                        </div>
                        <div>
                            <x-input-label for="tanggal_lahir" value="Tanggal Lahir" />
                            <x-text-input id="tanggal_lahir" name="tanggal_lahir" type="date"
                                class="mt-1 block w-full" :value="old('tanggal_lahir', $siswa->tanggal_lahir?->format('Y-m-d'))" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="jenis_kelamin" value="Jenis Kelamin" />
                            <select id="jenis_kelamin" name="jenis_kelamin"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Pilih --</option>
                                <option value="L" @selected(old('jenis_kelamin', $siswa->jenis_kelamin) == 'L')>Laki-laki</option>
                                <option value="P" @selected(old('jenis_kelamin', $siswa->jenis_kelamin) == 'P')>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="agama" value="Agama" />
                            <select id="agama" name="agama"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Pilih --</option>
                                @foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $a)
                                    <option value="{{ $a }}" @selected(old('agama', $siswa->agama) == $a)>{{ $a }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="alamat" value="Alamat" />
                        <textarea id="alamat" name="alamat"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="2">{{ old('alamat', $siswa->alamat) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="telp" value="Telepon" />
                            <x-text-input id="telp" name="telp" type="text" class="mt-1 block w-full"
                                :value="old('telp', $siswa->telp)" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                :value="old('email', $siswa->email)" />
                        </div>
                    </div>

                    <hr class="my-2">
                    <h3 class="text-sm font-semibold text-gray-700">Data Orang Tua</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="nama_ayah" value="Nama Ayah" />
                            <x-text-input id="nama_ayah" name="nama_ayah" type="text" class="mt-1 block w-full"
                                :value="old('nama_ayah', $siswa->nama_ayah)" />
                        </div>
                        <div>
                            <x-input-label for="nama_ibu" value="Nama Ibu" />
                            <x-text-input id="nama_ibu" name="nama_ibu" type="text" class="mt-1 block w-full"
                                :value="old('nama_ibu', $siswa->nama_ibu)" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="pekerjaan_ayah" value="Pekerjaan Ayah" />
                            <x-text-input id="pekerjaan_ayah" name="pekerjaan_ayah" type="text"
                                class="mt-1 block w-full" :value="old('pekerjaan_ayah', $siswa->pekerjaan_ayah)" />
                        </div>
                        <div>
                            <x-input-label for="pekerjaan_ibu" value="Pekerjaan Ibu" />
                            <x-text-input id="pekerjaan_ibu" name="pekerjaan_ibu" type="text"
                                class="mt-1 block w-full" :value="old('pekerjaan_ibu', $siswa->pekerjaan_ibu)" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="telp_orang_tua" value="Telepon Orang Tua" />
                        <x-text-input id="telp_orang_tua" name="telp_orang_tua" type="text"
                            class="mt-1 block w-full" :value="old('telp_orang_tua', $siswa->telp_orang_tua)" />
                    </div>

                    <hr class="my-2">
                    <h3 class="text-sm font-semibold text-gray-700">Status</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="status" value="Status Siswa" />
                            <select id="status" name="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="aktif" @selected(old('status', $siswa->status) == 'aktif')>Aktif</option>
                                <option value="pindah" @selected(old('status', $siswa->status) == 'pindah')>Pindah</option>
                                <option value="keluar" @selected(old('status', $siswa->status) == 'keluar')>Keluar</option>
                                <option value="dropout" @selected(old('status', $siswa->status) == 'dropout')>Drop Out</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                @checked(old('is_active', $siswa->is_active))>
                            <x-input-label for="is_active" value="Aktif" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('siswa.index') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
