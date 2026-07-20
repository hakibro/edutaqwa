@php
    $guru = optional(Auth::user()->guru);
    $isPtk = $guru && $guru->isStruktural();
    $isWaliKelas = $guru && $guru->isWaliKelas();
    $isBk = $guru && $guru->isBK();
    $hasExtraMenu = $isWaliKelas || $isBk;
    $gridCols = match (true) {
        $isPtk && $hasExtraMenu => 'grid-cols-7',
        $isPtk => 'grid-cols-6',
        $hasExtraMenu => 'grid-cols-6',
        default => 'grid-cols-5',
    };
@endphp
<nav
    class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-gray-200 shadow-[0_-2px_10px_rgba(0,0,0,0.06)]">
    <div class="grid {{ $gridCols }} h-16 max-w-lg mx-auto">
        {{-- Dashboard --}}
        <a href="{{ route('guru.dashboard') }}"
            class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition-colors
            {{ request()->routeIs('guru.dashboard') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <x-heroicon-o-home-modern class="h-5 w-5" />
            <span>Dashboard</span>
        </a>

        {{-- Jurnal --}}
        <a href="{{ route('jurnal-mengajar.index') }}"
            class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition-colors
            {{ request()->routeIs('jurnal-mengajar.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <x-heroicon-o-document-text class="h-5 w-5" />
            <span>Jurnal</span>
        </a>

        {{-- Perangkat Ajar --}}
        <a href="{{ route('perangkat-ajar.index') }}"
            class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition-colors
            {{ request()->routeIs('perangkat-ajar.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <x-heroicon-o-book-open class="h-5 w-5" />
            <span>Perangkat</span>
        </a>

        {{-- Jadwal --}}
        <a href="{{ route('guru.jadwal-saya') }}"
            class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition-colors
            {{ request()->routeIs('guru.jadwal-saya') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <x-heroicon-o-calendar-days class="h-5 w-5" />
            <span>Jadwal</span>
        </a>

        {{-- Wali Kelas --}}
        @if ($isWaliKelas)
            <a href="{{ route('guru.wali-kelas') }}"
                class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition-colors
                {{ request()->routeIs('guru.wali-kelas') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                <x-heroicon-o-users class="h-5 w-5" />
                <span>Wali Kelas</span>
            </a>
        @endif

        {{-- BK --}}
        @if ($isBk)
            <a href="{{ route('guru.bk') }}"
                class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition-colors
                {{ request()->routeIs('guru.bk') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                <x-heroicon-o-shield-exclamation class="h-5 w-5" />
                <span>BK</span>
            </a>
        @endif

        {{-- Absensi --}}
        @if ($isPtk)
            <a href="{{ route('absensi-ptk.index') }}"
                class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition-colors
            {{ request()->routeIs('absensi-ptk.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                <x-heroicon-o-check-circle class="h-5 w-5" />
                <span>Absen GTK</span>
            </a>
        @endif

        {{-- Profil + Notif --}}
        <div x-data="{ open: false }" class="relative flex flex-col items-center justify-center">
            <button @click="open = !open"
                class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition-colors
                {{ request()->routeIs('profile.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                <x-heroicon-o-user class="h-5 w-5" />
                <span>Profil</span>
                <span id="notif-badge"
                    class="absolute -top-0.5 right-1/4 hidden rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white min-w-[18px] text-center">
                </span>
            </button>

            <div x-show="open" @click.away="open = false" x-transition x-cloak
                class="absolute bottom-full mb-3 right-0 w-48 bg-white rounded-md shadow-lg border border-gray-200 py-1 z-10">
                <a href="{{ route('profile.edit') }}"
                    class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100">
                    {{ __('Profile') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="block w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-gray-100">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
