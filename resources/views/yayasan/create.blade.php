<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tambah Yayasan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('yayasan.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="nama" value="Nama Yayasan" />
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
                        <x-input-label for="email" value="Email Yayasan" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email')" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" checked>
                        <x-input-label for="is_active" value="Aktif" />
                    </div>

                    <hr class="border-gray-300">

                    <h3 class="text-lg font-medium text-gray-900">Admin Yayasan</h3>
                    <p class="text-sm text-gray-500">User ini akan otomatis dibuat sebagai admin yayasan.</p>

                    <div>
                        <x-input-label for="admin_name" value="Nama Admin" />
                        <x-text-input id="admin_name" name="admin_name" type="text" class="mt-1 block w-full"
                            :value="old('admin_name')" required />
                        <x-input-error :messages="$errors->get('admin_name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="admin_email" value="Email Admin" />
                        <x-text-input id="admin_email" name="admin_email" type="email" class="mt-1 block w-full"
                            :value="old('admin_email')" required />
                        <x-input-error :messages="$errors->get('admin_email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="admin_password" value="Password Admin" />
                        <x-text-input id="admin_password" name="admin_password" type="password"
                            class="mt-1 block w-full" required minlength="8" />
                        <x-input-error :messages="$errors->get('admin_password')" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('yayasan.index') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
