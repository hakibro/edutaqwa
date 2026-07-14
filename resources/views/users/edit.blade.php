<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Edit User') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('user-management.update', $user) }}" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')

                    <div>
                        <x-input-label for="name" value="Nama" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            :value="old('name', $user->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email', $user->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Password (kosongkan jika tidak diubah)" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                            minlength="8" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="role" value="Role" />
                        <select id="role" name="role"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                            <option value="super_admin" @selected(old('role', $user->role) == 'super_admin')>Super Admin</option>
                            <option value="admin_yayasan" @selected(old('role', $user->role) == 'admin_yayasan')>Admin Yayasan</option>
                            <option value="admin_lembaga" @selected(old('role', $user->role) == 'admin_lembaga')>Admin Lembaga / TU</option>
                            <option value="kepala_lembaga" @selected(old('role', $user->role) == 'kepala_lembaga')>Kepala Lembaga</option>
                            <option value="kurikulum" @selected(old('role', $user->role) == 'kurikulum')>Kurikulum</option>
                            <option value="kesiswaan" @selected(old('role', $user->role) == 'kesiswaan')>Kesiswaan</option>
                            <option value="guru" @selected(old('role', $user->role) == 'guru')>Guru</option>
                            <option value="siswa" @selected(old('role', $user->role) == 'siswa')>Siswa</option>
                            <option value="orang_tua" @selected(old('role', $user->role) == 'orang_tua')>Orang Tua</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked(old('is_active', $user->is_active))>
                        <x-input-label for="is_active" value="Aktif" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="javascript:history.back()"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
