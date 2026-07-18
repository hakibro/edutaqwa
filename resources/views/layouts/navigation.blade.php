<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if (Auth::user()->isSuperAdmin())
                        <x-nav-link :href="route('yayasan.index')" :active="request()->routeIs('yayasan.*')">
                            {{ __('Yayasan') }}
                        </x-nav-link>
                        <x-nav-link :href="route('lembaga.index')" :active="request()->routeIs('lembaga.*')">
                            {{ __('Lembaga') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->isAdminYayasan())
                        <x-nav-link :href="route('lembaga.index')" :active="request()->routeIs('lembaga.*')">
                            {{ __('Lembaga') }}
                        </x-nav-link>
                        <x-nav-link :href="route('tahun-ajaran.index')" :active="request()->routeIs('tahun-ajaran.*')">
                            {{ __('Tahun Ajaran') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->isSuperAdmin())
                        <x-nav-link :href="route('tahun-ajaran.index')" :active="request()->routeIs('tahun-ajaran.*')">
                            {{ __('Tahun Ajaran') }}
                        </x-nav-link>
                        <x-nav-link :href="route('log-aktivitas.index')" :active="request()->routeIs('log-aktivitas.*')">
                            {{ __('Log Aktivitas') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->isAdminYayasan())
                        <x-nav-link :href="route('kalender-akademik.index')" :active="request()->routeIs('kalender-akademik.*')">
                            {{ __('Kalender') }}
                        </x-nav-link>
                        <x-nav-link :href="route('log-aktivitas.index')" :active="request()->routeIs('log-aktivitas.*')">
                            {{ __('Log') }}
                        </x-nav-link>
                    @endif

                    {{-- Akademik — link untuk role Kurikulum & terkait --}}
                    @if (Auth::user()->isKurikulum() ||
                            Auth::user()->isAdminLembaga() ||
                            Auth::user()->isKepalaLembaga() ||
                            Auth::user()->isSuperAdmin() ||
                            Auth::user()->isAdminYayasan())
                        <div class="hidden sm:flex sm:items-center sm:ms-6 relative group">
                            <button
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                {{ __('Akademik') }}
                                <svg class="ms-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div
                                class="absolute top-full left-0 mt-1 w-56 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-50">
                                <div class="py-1">
                                    <a href="{{ route('kelompok-mapel.index') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('kelompok-mapel.*') ? 'bg-gray-50 font-medium' : '' }}">{{ __('Kelompok Mapel') }}</a>
                                    <a href="{{ route('mapel.index') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('mapel.*') ? 'bg-gray-50 font-medium' : '' }}">{{ __('Mapel') }}</a>
                                    <a href="{{ route('pengajaran-mapel.index') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('pengajaran-mapel.*') ? 'bg-gray-50 font-medium' : '' }}">{{ __('Penugasan Guru') }}</a>
                                    <a href="{{ route('jadwal.index') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('jadwal.*') ? 'bg-gray-50 font-medium' : '' }}">{{ __('Jadwal') }}</a>
                                    <a href="{{ route('nilai.jenis-nilai.index') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('nilai.jenis-nilai.*') ? 'bg-gray-50 font-medium' : '' }}">{{ __('Jenis Nilai') }}</a>
                                    <a href="{{ route('nilai.rekap') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('nilai.rekap') ? 'bg-gray-50 font-medium' : '' }}">{{ __('Rekap Nilai') }}</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- CP/TP/ATP — link untuk Guru & Kurikulum --}}
                    @if (auth()->user()->isGuru() ||
                            auth()->user()->isKurikulum() ||
                            auth()->user()->isKepalaLembaga() ||
                            auth()->user()->isAdminLembaga() ||
                            auth()->user()->isSuperAdmin() ||
                            auth()->user()->isAdminYayasan())
                        <div class="hidden sm:flex sm:items-center sm:ms-6 relative group">
                            <button
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                {{ __('Perangkat Ajar') }}
                                <svg class="ms-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div
                                class="absolute top-full left-0 mt-1 w-48 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-50">
                                <div class="py-1">
                                    <a href="{{ route('perangkat-ajar.index') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('perangkat-ajar.*') ? 'bg-gray-50 font-medium' : '' }}">{{ __('Perangkat Ajar') }}</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Penilaian — link untuk Guru --}}
                    @if (auth()->user()->isGuru())
                        <div class="hidden sm:flex sm:items-center sm:ms-6 relative group">
                            <button
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                {{ __('Penilaian') }}
                                <svg class="ms-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div
                                class="absolute top-full left-0 mt-1 w-48 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-50">
                                <div class="py-1">
                                    <a href="{{ route('nilai.index') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('nilai.index') ? 'bg-gray-50 font-medium' : '' }}">{{ __('Input Nilai') }}</a>
                                </div>
                            </div>
                        </div>
                        {{-- Kesiswaan --}}
                        @if (auth()->user()->isKesiswaan() ||
                                auth()->user()->isKepalaLembaga() ||
                                auth()->user()->isAdminLembaga() ||
                                auth()->user()->isGuru())
                            <div class="hidden sm:flex sm:items-center sm:ms-6 relative group">
                                <button
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                    {{ __('Kesiswaan') }}
                                    <svg class="ms-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div
                                    class="absolute top-full left-0 mt-1 w-56 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-50">
                                    <div class="py-1">
                                        <a href="{{ route('kesiswaan.kategori-pelanggaran.index') }}"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('kesiswaan.kategori-pelanggaran*') ? 'bg-gray-50 font-medium' : '' }}">{{ __('Kategori Pelanggaran') }}</a>
                                        <a href="{{ route('kesiswaan.pelanggaran.index') }}"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('kesiswaan.pelanggaran*') ? 'bg-gray-50 font-medium' : '' }}">{{ __('Pelanggaran') }}</a>
                                        <a href="{{ route('kesiswaan.ekskul.index') }}"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('kesiswaan.ekskul*') ? 'bg-gray-50 font-medium' : '' }}">{{ __('Ekskul') }}</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if (Auth::user()->isSuperAdmin())
                <x-responsive-nav-link :href="route('yayasan.index')" :active="request()->routeIs('yayasan.*')">
                    {{ __('Yayasan') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('lembaga.index')" :active="request()->routeIs('lembaga.*')">
                    {{ __('Lembaga') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->isAdminYayasan())
                <x-responsive-nav-link :href="route('lembaga.index')" :active="request()->routeIs('lembaga.*')">
                    {{ __('Lembaga') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('tahun-ajaran.index')" :active="request()->routeIs('tahun-ajaran.*')">
                    {{ __('Tahun Ajaran') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->isSuperAdmin())
                <x-responsive-nav-link :href="route('tahun-ajaran.index')" :active="request()->routeIs('tahun-ajaran.*')">
                    {{ __('Tahun Ajaran') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('log-aktivitas.index')" :active="request()->routeIs('log-aktivitas.*')">
                    {{ __('Log Aktivitas') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->isAdminYayasan())
                <x-responsive-nav-link :href="route('kalender-akademik.index')" :active="request()->routeIs('kalender-akademik.*')">
                    {{ __('Kalender') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('log-aktivitas.index')" :active="request()->routeIs('log-aktivitas.*')">
                    {{ __('Log') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->isKurikulum() ||
                    Auth::user()->isAdminLembaga() ||
                    Auth::user()->isKepalaLembaga() ||
                    Auth::user()->isSuperAdmin() ||
                    Auth::user()->isAdminYayasan())
                <x-responsive-nav-link :href="route('kelompok-mapel.index')" :active="request()->routeIs('kelompok-mapel.*')">
                    {{ __('Kelompok Mapel') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mapel.index')" :active="request()->routeIs('mapel.*')">
                    {{ __('Mapel') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('pengajaran-mapel.index')" :active="request()->routeIs('pengajaran-mapel.*')">
                    {{ __('Penugasan Guru') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('jadwal.index')" :active="request()->routeIs('jadwal.*')">
                    {{ __('Jadwal') }}
                </x-responsive-nav-link>
            @endif

            @if (auth()->user()->isGuru() ||
                    auth()->user()->isKurikulum() ||
                    auth()->user()->isKepalaLembaga() ||
                    auth()->user()->isAdminLembaga() ||
                    auth()->user()->isSuperAdmin() ||
                    auth()->user()->isAdminYayasan())
                <x-responsive-nav-link :href="route('perangkat-ajar.index')" :active="request()->routeIs('perangkat-ajar.*')">
                    {{ __('Perangkat Ajar') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
