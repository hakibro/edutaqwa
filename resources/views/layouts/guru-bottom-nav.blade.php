@php
    $guru = optional(Auth::user()->guru);
    $isPtk = $guru && $guru->isStruktural();
    $isWaliKelas = $guru && $guru->isWaliKelas();
    $isBk = $guru && $guru->isBK();
@endphp
<nav class="fixed left-4 right-4 z-40 nav-safe-bottom" x-data="{ menuOpen: false }">
    <div
        class="flex items-center justify-around max-w-lg mx-auto relative bg-white/95 backdrop-blur-sm rounded-full shadow-lg shadow-slate-200/50 border border-slate-200/60 px-2 py-2">

        {{-- Dashboard --}}
        <a href="{{ route('guru.dashboard') }}"
            class="flex items-center justify-center w-11 h-11 rounded-full hover:bg-emerald-50/50 transition
            {{ request()->routeIs('guru.dashboard') ? 'text-emerald-600' : 'text-slate-400 hover:text-slate-600' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
        </a>

        {{-- Jadwal --}}
        <a href="{{ route('guru.jadwal-saya') }}"
            class="flex items-center justify-center w-11 h-11 rounded-full hover:bg-slate-50 transition
            {{ request()->routeIs('guru.jadwal-saya') ? 'text-emerald-600' : 'text-slate-400 hover:text-slate-600' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
        </a>

        {{-- FAB Center --}}
        <div class="relative">
            {{-- FAB menu --}}
            <div x-show="menuOpen"
                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-44 bg-white rounded-xl shadow-lg border border-slate-200 py-1.5 z-40"
                x-transition @click.away="menuOpen = false">
                <a href="{{ route('jurnal-mengajar.index') }}"
                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    Lihat Jurnal
                </a>
                <a href="{{ route('perangkat-ajar.index') }}"
                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    Perangkat Ajar
                </a>
                <a href="{{ route('nilai.index') }}"
                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                    </svg>
                    Input Nilai
                </a>
                <a href="{{ route('jurnal-mengajar.index') }}"
                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    Laporan
                </a>
            </div>
            {{-- FAB button --}}
            <button
                class="w-11 h-11 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full shadow-md shadow-emerald-200 flex items-center justify-center transition active:scale-95"
                @click="menuOpen = !menuOpen">
                <svg class="w-6 h-6 transition-transform" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    fill="none">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </button>
        </div>

        {{-- Absensi (PTK only) / Wali Kelas --}}
        @if ($isPtk)
            <a href="{{ route('absensi-ptk.index') }}"
                class="flex items-center justify-center w-11 h-11 rounded-full hover:bg-slate-50 transition
                {{ request()->routeIs('absensi-ptk.*') ? 'text-emerald-600' : 'text-slate-400 hover:text-slate-600' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                </svg>
            </a>
        @elseif ($isWaliKelas)
            <a href="{{ route('guru.wali-kelas') }}"
                class="flex items-center justify-center w-11 h-11 rounded-full hover:bg-slate-50 transition
                {{ request()->routeIs('guru.wali-kelas') ? 'text-emerald-600' : 'text-slate-400 hover:text-slate-600' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </a>
        @elseif ($isBk)
            <a href="{{ route('guru.bk') }}"
                class="flex items-center justify-center w-11 h-11 rounded-full hover:bg-slate-50 transition
                {{ request()->routeIs('guru.bk') ? 'text-emerald-600' : 'text-slate-400 hover:text-slate-600' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
            </a>
        @endif

        {{-- Profil + Notif --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                class="flex items-center justify-center w-11 h-11 rounded-full hover:bg-slate-50 transition
                {{ request()->routeIs('profile.*') ? 'text-emerald-600' : 'text-slate-400 hover:text-slate-600' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <span id="notif-badge"
                    class="absolute -top-0.5 right-0 hidden rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white min-w-[18px] text-center">
                </span>
            </button>

            <div x-show="open" @click.away="open = false" x-transition x-cloak
                class="absolute bottom-full mb-3 right-0 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-1.5 z-40">
                <a href="{{ route('profile.edit') }}"
                    class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    {{ __('Profile') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="block w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-slate-50">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
