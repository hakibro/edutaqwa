<nav
    class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-gray-200 shadow-[0_-2px_10px_rgba(0,0,0,0.06)]">
    <div class="grid grid-cols-6 h-16 max-w-lg mx-auto">
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

        {{-- Absensi --}}
        <a href="{{ route('absensi-ptk.index') }}"
            class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition-colors
            {{ request()->routeIs('absensi-ptk.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <x-heroicon-o-check-circle class="h-5 w-5" />
            <span>Absen GTK</span>
        </a>

        {{-- Profil + Notif --}}
        <a href="{{ route('profile.edit') }}"
            class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition-colors relative
            {{ request()->routeIs('profile.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            <x-heroicon-o-user class="h-5 w-5" />
            <span>Profil</span>
            <span id="notif-badge"
                class="absolute -top-0.5 right-1/4 hidden rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white min-w-[18px] text-center">
            </span>
        </a>
    </div>
</nav>
