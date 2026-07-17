<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Reset Password') }}: {{ $guru->nama }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('guru.reset-password.update', $guru) }}" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')

                    <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800 border border-yellow-200">
                        <strong>Info Akun Guru:</strong>
                        <ul class="mt-1 list-disc list-inside text-xs">
                            <li>Nama: {{ $guru->nama }}</li>
                            <li>Kode Guru: {{ $guru->kode_guru_lembaga }}</li>
                            <li>NIY: {{ $guru->niy ?: '-' }}</li>
                            @if ($user)
                                <li>Username: {{ $user->username }}</li>
                                <li>Status Akun: <span
                                        class="{{ $user->is_active ? 'text-green-700' : 'text-red-700' }} font-semibold">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </li>
                            @else
                                <li class="text-red-600 font-semibold">Belum memiliki akun user. Password baru akan
                                    membuat akun otomatis.</li>
                            @endif
                        </ul>
                    </div>

                    <div>
                        <x-input-label for="password" value="Password Baru" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required
                            autofocus />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        <p class="mt-1 text-xs text-gray-500">Minimal 6 karakter.</p>
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Konfirmasi Password" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                            class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="rounded-md bg-blue-50 p-4 text-sm text-blue-800 border border-blue-200">
                        Guru akan diminta mengganti password saat login pertama kali setelah reset.
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('guru.index') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                        <x-primary-button>Reset Password</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
