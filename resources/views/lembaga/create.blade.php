<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tambah Lembaga') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('lembaga.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf

                    @if (auth()->user()->isSuperAdmin())
                        <div>
                            <x-input-label for="yayasan_id" value="Yayasan" />
                            <select id="yayasan_id" name="yayasan_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Yayasan --</option>
                                @foreach ($yayasans as $y)
                                    <option value="{{ $y->id }}" @selected(old('yayasan_id') == $y->id)>{{ $y->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('yayasan_id')" class="mt-2" />
                        </div>
                    @endif

                    <div>
                        <x-input-label for="nama" value="Nama Lembaga" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                            :value="old('nama')" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="kode" value="Kode" />
                        <x-text-input id="kode" name="kode" type="text" class="mt-1 block w-full"
                            :value="old('kode')" required maxlength="50" />
                        <x-input-error :messages="$errors->get('kode')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="tingkat" value="Tingkat" />
                        <select id="tingkat" name="tingkat"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                            <option value="">-- Pilih Tingkat --</option>
                            @foreach (['PAUD', 'RA', 'MI', 'MTS', 'MA', 'SD', 'SMP', 'SMA', 'SMK'] as $t)
                                <option value="{{ $t }}" @selected(old('tingkat') == $t)>{{ $t }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('tingkat')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="unit_formal" value="Unit Formal (Sisda)" />
                        <select id="unit_formal" name="unit_formal"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Pilih Unit Formal --</option>
                            @foreach (['PAUD', 'RA', 'MI', 'MTS', 'MA', 'SD', 'SMP', 'SMA', 'SMK'] as $u)
                                <option value="{{ $u }}" @selected(old('unit_formal') == $u)>{{ $u }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Mapping ke field <code>UnitFormal</code> di Sisda API.</p>
                        <x-input-error :messages="$errors->get('unit_formal')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="npsn" value="NPSN" />
                        <x-text-input id="npsn" name="npsn" type="text" class="mt-1 block w-full"
                            :value="old('npsn')" maxlength="20" />
                        <x-input-error :messages="$errors->get('npsn')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="alamat" value="Alamat" />
                        <textarea id="alamat" name="alamat"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="2">{{ old('alamat') }}</textarea>
                        <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="telp" value="Telepon" />
                        <x-text-input id="telp" name="telp" type="text" class="mt-1 block w-full"
                            :value="old('telp')" maxlength="50" />
                        <x-input-error :messages="$errors->get('telp')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email')" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" checked>
                        <x-input-label for="is_active" value="Aktif" />
                    </div>

                    @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdminYayasan())
                        <hr class="border-gray-300">

                        <h3 class="text-lg font-medium text-gray-900">Admin / Petugas Lembaga</h3>
                        <p class="text-sm text-gray-500">User ini akan otomatis dibuat sebagai Admin Lembaga / TU.</p>

                        <div>
                            <x-input-label for="admin_name" value="Nama" />
                            <x-text-input id="admin_name" name="admin_name" type="text" class="mt-1 block w-full"
                                :value="old('admin_name')" required />
                            <x-input-error :messages="$errors->get('admin_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="admin_email" value="Email" />
                            <x-text-input id="admin_email" name="admin_email" type="email" class="mt-1 block w-full"
                                :value="old('admin_email')" required />
                            <x-input-error :messages="$errors->get('admin_email')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="admin_password" value="Password" />
                            <x-text-input id="admin_password" name="admin_password" type="password"
                                class="mt-1 block w-full" required minlength="8" />
                            <x-input-error :messages="$errors->get('admin_password')" class="mt-2" />
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('lembaga.index') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
