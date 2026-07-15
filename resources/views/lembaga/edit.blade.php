<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Edit Lembaga') }}: {{ $lembaga->nama }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('lembaga.update', $lembaga) }}" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')

                    @if (auth()->user()->isSuperAdmin())
                        <div>
                            <x-input-label for="yayasan_id" value="Yayasan" />
                            <select id="yayasan_id" name="yayasan_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Yayasan --</option>
                                @foreach ($yayasans as $y)
                                    <option value="{{ $y->id }}" @selected(old('yayasan_id', $lembaga->yayasan_id) == $y->id)>{{ $y->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('yayasan_id')" class="mt-2" />
                        </div>
                    @endif

                    <div>
                        <x-input-label for="nama" value="Nama Lembaga" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                            :value="old('nama', $lembaga->nama)" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="kode" value="Kode" />
                        <x-text-input id="kode" name="kode" type="text" class="mt-1 block w-full"
                            :value="old('kode', $lembaga->kode)" required maxlength="50" />
                        <x-input-error :messages="$errors->get('kode')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="kode_sisda" value="Kode Sisda (idunit)" />
                        <x-text-input id="kode_sisda" name="kode_sisda" type="text" class="mt-1 block w-full"
                            :value="old('kode_sisda', $lembaga->kode_sisda)" maxlength="10" />
                        <p class="mt-1 text-xs text-gray-500">Kode <code>idunit</code> dari Sisda API, digunakan untuk
                            generate NIY guru.</p>
                        <x-input-error :messages="$errors->get('kode_sisda')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="tingkat" value="Tingkat" />
                        <select id="tingkat" name="tingkat"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                            <option value="">-- Pilih Tingkat --</option>
                            @foreach (['PAUD', 'RA', 'MI', 'MTS', 'MA', 'SD', 'SMP', 'SMA', 'SMK'] as $t)
                                <option value="{{ $t }}" @selected(old('tingkat', $lembaga->tingkat) == $t)>{{ $t }}
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
                                <option value="{{ $u }}" @selected(old('unit_formal', $lembaga->unit_formal) == $u)>{{ $u }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Mapping ke field <code>UnitFormal</code> di Sisda API.</p>
                        <x-input-error :messages="$errors->get('unit_formal')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="npsn" value="NPSN" />
                        <x-text-input id="npsn" name="npsn" type="text" class="mt-1 block w-full"
                            :value="old('npsn', $lembaga->npsn)" maxlength="20" />
                        <x-input-error :messages="$errors->get('npsn')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="alamat" value="Alamat" />
                        <textarea id="alamat" name="alamat"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="2">{{ old('alamat', $lembaga->alamat) }}</textarea>
                        <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="telp" value="Telepon" />
                        <x-text-input id="telp" name="telp" type="text" class="mt-1 block w-full"
                            :value="old('telp', $lembaga->telp)" maxlength="50" />
                        <x-input-error :messages="$errors->get('telp')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email', $lembaga->email)" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked(old('is_active', $lembaga->is_active))>
                        <x-input-label for="is_active" value="Aktif" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('lembaga.index') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                        <a href="{{ route('user-management.lembaga', $lembaga) }}"
                            class="rounded-md bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                            Kelola Pengguna →
                        </a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>

                <!-- User Management -->
                <div class="border-t border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Pengguna Lembaga</h3>
                        <a href="{{ route('user-management.lembaga.create', $lembaga) }}"
                            class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            + Tambah User
                        </a>
                    </div>

                    @php
                        $users = $lembaga->users()->latest()->get();
                    @endphp

                    @if ($users->isEmpty())
                        <p class="text-sm text-gray-500">Belum ada pengguna untuk lembaga ini. Klik "Tambah User" untuk
                            menambah.</p>
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

@push('scripts')
    <script>
        const API_LEMBAGA = 'https://apiakademik.daruttaqwa.or.id/api/lembaga';

        let lembagaData = [];
        fetch(API_LEMBAGA)
            .then(r => r.json())
            .then(d => {
                lembagaData = d.data || d;
            })
            .catch(() => {});

        document.getElementById('unit_formal')?.addEventListener('change', function() {
            const match = lembagaData.find(item => item.kode === this.value);
            document.getElementById('kode_sisda').value = match ? match.idunit : '';
        });
    </script>
@endpush
