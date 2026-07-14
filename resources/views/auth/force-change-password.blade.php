<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold text-gray-900">{{ __('Ganti Password Wajib') }}</h1>
        <p class="mt-2 text-sm text-gray-600">
            {{ __('Ini login pertama Anda. Silakan ganti password default dengan password baru.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.force-update') }}">
        @csrf
        @method('put')

        <!-- Password Baru -->
        <div>
            <x-input-label for="password" :value="__('Password Baru')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus
                autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Konfirmasi Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="w-full justify-center">
                {{ __('Simpan Password Baru') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-4 text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 underline hover:text-gray-700">
                {{ __('Logout') }}
            </button>
        </form>
    </div>
</x-guest-layout>
