<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Edit Yayasan') }}: {{ $yayasan->nama }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('yayasan.update', $yayasan) }}" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')

                    <div>
                        <x-input-label for="nama" value="Nama Yayasan" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                            :value="old('nama', $yayasan->nama)" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="kode" value="Kode" />
                        <x-text-input id="kode" name="kode" type="text" class="mt-1 block w-full"
                            :value="old('kode', $yayasan->kode)" required maxlength="50" />
                        <x-input-error :messages="$errors->get('kode')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="alamat" value="Alamat" />
                        <textarea id="alamat" name="alamat"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="2">{{ old('alamat', $yayasan->alamat) }}</textarea>
                        <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="telp" value="Telepon" />
                        <x-text-input id="telp" name="telp" type="text" class="mt-1 block w-full"
                            :value="old('telp', $yayasan->telp)" maxlength="50" />
                        <x-input-error :messages="$errors->get('telp')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email', $yayasan->email)" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked(old('is_active', $yayasan->is_active))>
                        <x-input-label for="is_active" value="Aktif" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('yayasan.index') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>

                <!-- User Management -->
                <div class="border-t border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Pengguna Yayasan</h3>
                        <a href="{{ route('user-management.yayasan.create', $yayasan) }}"
                            class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            + Tambah User
                        </a>
                    </div>

                    @php
                        $users = $yayasan->users()->whereNull('lembaga_id')->get();
                    @endphp

                    @if ($users->isEmpty())
                        <p class="text-sm text-gray-500">Belum ada pengguna untuk yayasan ini.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500">Nama</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500">Email</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500">Role</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                                        <th class="px-4 py-2 text-right font-medium text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($users as $u)
                                        <tr>
                                            <td class="px-4 py-2">{{ $u->name }}</td>
                                            <td class="px-4 py-2">{{ $u->email }}</td>
                                            <td class="px-4 py-2">{{ $u->role }}</td>
                                            <td class="px-4 py-2">
                                                <span
                                                    class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $u->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                    {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-right">
                                                <a href="{{ route('user-management.edit', $u) }}"
                                                    class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                <form action="{{ route('user-management.destroy', $u) }}"
                                                    method="POST" class="inline"
                                                    onsubmit="return confirm('Hapus user ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="ml-2 text-red-600 hover:text-red-900">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
