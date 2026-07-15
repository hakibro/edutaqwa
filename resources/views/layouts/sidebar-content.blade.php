<div class="flex-1 flex flex-col min-h-0 bg-white border-r border-gray-200">
    <!-- Logo -->
    <div class="flex items-center h-16 shrink-0 px-4 border-b border-gray-200">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <x-application-logo class="block h-8 w-auto fill-current text-gray-800" />
            <span class="font-semibold text-lg text-gray-800">{{ config('app.name', 'MyDaruttaqwa') }}</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <x-sidebar-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" />
            </svg>
            <span>{{ __('Dashboard') }}</span>
        </x-sidebar-nav-link>

        @if (Auth::user()->isSuperAdmin())
            <x-sidebar-nav-link :href="route('yayasan.index')" :active="request()->routeIs('yayasan.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>{{ __('Yayasan') }}</span>
            </x-sidebar-nav-link>
        @endif

        @if (Auth::user()->isSuperAdmin() || Auth::user()->isAdminYayasan())
            <x-sidebar-nav-link :href="route('lembaga.index')" :active="request()->routeIs('lembaga.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>{{ __('Lembaga') }}</span>
            </x-sidebar-nav-link>
        @endif

        @if (Auth::user()->isSuperAdmin() || Auth::user()->isAdminYayasan())
            <x-sidebar-nav-link :href="route('tahun-ajaran.index')" :active="request()->routeIs('tahun-ajaran.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ __('Tahun Ajaran') }}</span>
            </x-sidebar-nav-link>
        @endif

        @if (Auth::user()->isAdminYayasan())
            <x-sidebar-nav-link :href="route('kalender-akademik.index')" :active="request()->routeIs('kalender-akademik.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>{{ __('Kalender') }}</span>
            </x-sidebar-nav-link>
        @endif

        @if (Auth::user()->isSuperAdmin() || Auth::user()->isAdminYayasan())
            <x-sidebar-nav-link :href="route('log-aktivitas.index')" :active="request()->routeIs('log-aktivitas.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ __('Log') }}</span>
            </x-sidebar-nav-link>
        @endif

        @if (Auth::user()->isAdminYayasan())
            <x-sidebar-nav-link :href="route('guru.approval')" :active="request()->routeIs('guru.approval*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ __('Approval Guru') }}</span>
            </x-sidebar-nav-link>
        @endif

        @if (Auth::user()->isAdminYayasan() || Auth::user()->isAdminLembaga())
            <x-sidebar-nav-link :href="route('sync-siswa.index')" :active="request()->routeIs('sync-siswa.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>{{ __('Sync Sisda') }}</span>
            </x-sidebar-nav-link>
        @endif

        {{-- Master Data --}}
        @if (in_array(Auth::user()->role, [
                'super_admin',
                'admin_yayasan',
                'admin_lembaga',
                'kepala_lembaga',
                'kurikulum',
                'kesiswaan',
            ]))
            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Master Data
            </div>
            <x-sidebar-nav-link :href="route('guru.index')" :active="request()->routeIs('guru.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{{ __('Guru') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('siswa.index')" :active="request()->routeIs('siswa.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
                <span>{{ __('Siswa') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('kelas.index')" :active="request()->routeIs('kelas.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>{{ __('Kelas') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('jurusan.index')" :active="request()->routeIs('jurusan.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span>{{ __('Jurusan') }}</span>
            </x-sidebar-nav-link>
        @endif

        {{-- Akademik --}}
        @if (in_array(Auth::user()->role, ['super_admin', 'admin_yayasan', 'admin_lembaga', 'kepala_lembaga', 'kurikulum']))
            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Akademik
            </div>
            <x-sidebar-nav-link :href="route('kelompok-mapel.index')" :active="request()->routeIs('kelompok-mapel.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span>{{ __('Kelompok Mapel') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('mapel.index')" :active="request()->routeIs('mapel.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>{{ __('Mapel') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('pengajaran-mapel.index')" :active="request()->routeIs('pengajaran-mapel.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{{ __('Penugasan Guru') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('jadwal.index')" :active="request()->routeIs('jadwal.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ __('Jadwal') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('akademik-settings.index')" :active="request()->routeIs('akademik-settings.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{{ __('Settings') }}</span>
            </x-sidebar-nav-link>
            @if (Auth::user()->isAdminLembaga())
                <x-sidebar-nav-link :href="route('jam-kerja.index')" :active="request()->routeIs('jam-kerja.*')">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ __('Jam Kerja') }}</span>
                </x-sidebar-nav-link>
            @endif
        @endif

        {{-- CP/TP/ATP --}}
        @if (in_array(Auth::user()->role, [
                'super_admin',
                'admin_yayasan',
                'admin_lembaga',
                'kepala_lembaga',
                'kurikulum',
                'guru',
            ]))
            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                CP/TP/ATP
            </div>
            <x-sidebar-nav-link :href="route('cp.index')" :active="request()->routeIs('cp.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ __('CP') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('tp.index')" :active="request()->routeIs('tp.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>{{ __('TP') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('atp.index')" :active="request()->routeIs('atp.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span>{{ __('ATP') }}</span>
            </x-sidebar-nav-link>
        @endif

        {{-- Absensi PTK & Agenda Selfie (Guru) --}}
        @if (Auth::user()->isGuru())
            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Kehadiran & Mengajar
            </div>
            <x-sidebar-nav-link :href="route('presensi.index')" :active="request()->routeIs('presensi.*') && !request()->routeIs('presensi.rekap*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ __('Presensi Siswa') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('absensi-ptk.index')" :active="request()->routeIs('absensi-ptk.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ __('Absensi Harian') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('agenda-mengajar.index')" :active="request()->routeIs('agenda-mengajar.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ __('Agenda Selfie') }}</span>
            </x-sidebar-nav-link>
        @endif

        {{-- Monitoring (Kurikulum / Kepala Lembaga / Admin Lembaga) --}}
        @if (Auth::user()->isKurikulum() || Auth::user()->isKepalaLembaga() || Auth::user()->isAdminLembaga())
            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Monitoring
            </div>
            <x-sidebar-nav-link :href="route('presensi.rekap')" :active="request()->routeIs('presensi.rekap*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ __('Rekap Presensi') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('agenda-mengajar.monitoring')" :active="request()->routeIs('agenda-mengajar.monitoring*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>{{ __('Agenda Selfie') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('absensi-ptk.laporan')" :active="request()->routeIs('absensi-ptk.laporan*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ __('Laporan Absensi PTK') }}</span>
            </x-sidebar-nav-link>
        @endif

        {{-- Laporan (P9) --}}
        @if (in_array(Auth::user()->role, [
                'super_admin',
                'admin_yayasan',
                'admin_lembaga',
                'kepala_lembaga',
                'kurikulum',
                'kesiswaan',
                'guru',
            ]))
            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Laporan
            </div>
            <x-sidebar-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ __('Laporan') }}</span>
            </x-sidebar-nav-link>
        @endif

        {{-- Kesiswaan --}}
        @if (Auth::user()->isKesiswaan() ||
                Auth::user()->isKepalaLembaga() ||
                Auth::user()->isAdminLembaga() ||
                Auth::user()->isGuru())
            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Kesiswaan
            </div>
            <x-sidebar-nav-link :href="route('kesiswaan.kategori-pelanggaran.index')" :active="request()->routeIs('kesiswaan.kategori-pelanggaran*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span>{{ __('Kategori Pelanggaran') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('kesiswaan.pelanggaran.index')" :active="request()->routeIs('kesiswaan.pelanggaran*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span>{{ __('Pelanggaran') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('kesiswaan.ekskul.index')" :active="request()->routeIs('kesiswaan.ekskul*') && !request()->routeIs('kesiswaan.anggota-ekskul*')">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{{ __('Ekskul') }}</span>
            </x-sidebar-nav-link>
        @endif
    </nav>

    <!-- User Footer -->
    <div class="shrink-0 border-t border-gray-200 p-4">
        <!-- Notifikasi Bell -->
        <a href="{{ route('notifikasi.index') }}"
            class="flex items-center gap-3 w-full rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 transition mb-2 relative"
            id="notif-bell-link">
            <svg class="h-5 w-5 shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span>{{ __('Notifikasi') }}</span>
            <span id="notif-badge"
                class="ml-auto hidden rounded-full bg-red-500 px-1.5 py-0.5 text-xs font-bold text-white">0</span>
        </a>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                class="flex items-center gap-3 w-full rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                <div class="flex-1 text-left">
                    <div class="font-medium truncate">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</div>
                </div>
                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" x-transition
                class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-md shadow-lg border border-gray-200 py-1 z-10">
                <a href="{{ route('profile.edit') }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    {{ __('Profile') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
