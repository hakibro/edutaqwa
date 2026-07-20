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
            <x-heroicon-o-home-modern class="h-5 w-5 shrink-0" />
            <span>{{ __('Dashboard') }}</span>
        </x-sidebar-nav-link>

        {{-- Absensi Harian & Kehadiran Guru --}}
        @if (Auth::user()->isGuru())
            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Kehadiran & Mengajar
            </div>
            @if (optional(Auth::user()->guru)->isStruktural())
                <x-sidebar-nav-link :href="route('absensi-ptk.index')" :active="request()->routeIs('absensi-ptk.*')">
                    <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
                    <span>{{ __('Absensi GTK') }}</span>
                </x-sidebar-nav-link>
            @endif
            <x-sidebar-nav-link :href="route('guru.jadwal-saya')" :active="request()->routeIs('guru.jadwal-saya')">
                <x-heroicon-o-calendar-days class="h-5 w-5 shrink-0" />
                <span>{{ __('Jadwal Saya') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('jurnal-mengajar.index')" :active="request()->routeIs('jurnal-mengajar.*') && !request()->routeIs('jurnal-mengajar.monitoring*')">
                <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
                <span>{{ __('Jurnal Mengajar') }}</span>
            </x-sidebar-nav-link>
        @endif

        {{-- Guru: Wali Kelas & BK --}}
        @if (Auth::user()->isGuru())
            @php
                $guru = \App\Models\Guru::find(Auth::user()->guru_id);
            @endphp
            @if ($guru && $guru->isWaliKelas())
                <x-sidebar-nav-link :href="route('guru.wali-kelas')" :active="request()->routeIs('guru.wali-kelas')">
                    <x-heroicon-o-users class="h-5 w-5 shrink-0" />
                    <span>{{ __('Wali Kelas') }}</span>
                </x-sidebar-nav-link>
            @endif
            @if ($guru && $guru->isBK())
                <x-sidebar-nav-link :href="route('guru.bk')" :active="request()->routeIs('guru.bk')">
                    <x-heroicon-o-shield-exclamation class="h-5 w-5 shrink-0" />
                    <span>{{ __('BK') }}</span>
                </x-sidebar-nav-link>
            @endif
        @endif

        @if (Auth::user()->isSuperAdmin())
            <x-sidebar-nav-link :href="route('yayasan.index')" :active="request()->routeIs('yayasan.*')">
                <x-heroicon-o-building-office-2 class="h-5 w-5 shrink-0" />
                <span>{{ __('Yayasan') }}</span>
            </x-sidebar-nav-link>
        @endif

        @if (Auth::user()->isSuperAdmin() || Auth::user()->isAdminYayasan())
            <x-sidebar-nav-link :href="route('lembaga.index')" :active="request()->routeIs('lembaga.*')">
                <x-heroicon-o-book-open class="h-5 w-5 shrink-0" />
                <span>{{ __('Lembaga') }}</span>
            </x-sidebar-nav-link>
        @endif

        @if (Auth::user()->isSuperAdmin() || Auth::user()->isAdminYayasan())
            <x-sidebar-nav-link :href="route('tahun-ajaran.index')" :active="request()->routeIs('tahun-ajaran.*')">
                <x-heroicon-o-calendar-days class="h-5 w-5 shrink-0" />
                <span>{{ __('Tahun Ajaran') }}</span>
            </x-sidebar-nav-link>
        @endif

        @if (Auth::user()->isAdminYayasan())
            <x-sidebar-nav-link :href="route('kalender-akademik.index')" :active="request()->routeIs('kalender-akademik.*')">
                <x-heroicon-o-clipboard-document-check class="h-5 w-5 shrink-0" />
                <span>{{ __('Kalender') }}</span>
            </x-sidebar-nav-link>
        @endif

        @if (Auth::user()->isSuperAdmin() || Auth::user()->isAdminYayasan() || Auth::user()->isAdminLembaga())
            <x-sidebar-nav-link :href="route('log-aktivitas.index')" :active="request()->routeIs('log-aktivitas.*')">
                <x-heroicon-o-clock class="h-5 w-5 shrink-0" />
                <span>{{ __('Log') }}</span>
            </x-sidebar-nav-link>
        @endif

        @if (Auth::user()->isAdminYayasan())
            <x-sidebar-nav-link :href="route('guru.approval')" :active="request()->routeIs('guru.approval*')">
                <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
                <span>{{ __('Approval Guru') }}</span>
            </x-sidebar-nav-link>
        @endif

        @if (Auth::user()->isAdminYayasan() || Auth::user()->isAdminLembaga())
            <x-sidebar-nav-link :href="route('sync-siswa.index')" :active="request()->routeIs('sync-siswa.*')">
                <x-heroicon-o-arrow-path class="h-5 w-5 shrink-0" />
                <span>{{ __('Sync Sisda') }}</span>
            </x-sidebar-nav-link>
        @endif

        {{-- Pengumuman --}}
        @if (in_array(Auth::user()->role, ['super_admin', 'admin_yayasan', 'admin_lembaga', 'kepala_lembaga']))
            <x-sidebar-nav-link :href="route('pengumuman.index')" :active="request()->routeIs('pengumuman.*')">
                <x-heroicon-o-megaphone class="h-5 w-5 shrink-0" />
                <span>{{ __('Pengumuman') }}</span>
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
                <x-heroicon-o-users class="h-5 w-5 shrink-0" />
                <span>{{ __('Guru') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('siswa.index')" :active="request()->routeIs('siswa.*')">
                <x-heroicon-o-academic-cap class="h-5 w-5 shrink-0" />
                <span>{{ __('Siswa') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('kelas.index')" :active="request()->routeIs('kelas.*')">
                <x-heroicon-o-building-office-2 class="h-5 w-5 shrink-0" />
                <span>{{ __('Kelas') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('jurusan.index')" :active="request()->routeIs('jurusan.*')">
                <x-heroicon-o-document-text class="h-5 w-5 shrink-0" />
                <span>{{ __('Jurusan') }}</span>
            </x-sidebar-nav-link>
        @endif

        {{-- Akademik --}}
        @if (in_array(Auth::user()->role, ['super_admin', 'admin_yayasan', 'admin_lembaga', 'kepala_lembaga', 'kurikulum']))
            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Akademik
            </div>
            <x-sidebar-nav-link :href="route('kelompok-mapel.index')" :active="request()->routeIs('kelompok-mapel.*')">
                <x-heroicon-o-tag class="h-5 w-5 shrink-0" />
                <span>{{ __('Kelompok Mapel') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('mapel.index')" :active="request()->routeIs('mapel.*')">
                <x-heroicon-o-book-open class="h-5 w-5 shrink-0" />
                <span>{{ __('Mapel') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('pengajaran-mapel.index')" :active="request()->routeIs('pengajaran-mapel.*')">
                <x-heroicon-o-users class="h-5 w-5 shrink-0" />
                <span>{{ __('Penugasan Guru') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('jadwal.index')" :active="request()->routeIs('jadwal.*')">
                <x-heroicon-o-calendar-days class="h-5 w-5 shrink-0" />
                <span>{{ __('Jadwal') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('akademik-settings.index')" :active="request()->routeIs('akademik-settings.*')">
                <x-heroicon-o-cog-6-tooth class="h-5 w-5 shrink-0" />
                <span>{{ __('Settings') }}</span>
            </x-sidebar-nav-link>
            @if (Auth::user()->isAdminLembaga())
                <x-sidebar-nav-link :href="route('jam-kerja.index')" :active="request()->routeIs('jam-kerja.*')">
                    <x-heroicon-o-clock class="h-5 w-5 shrink-0" />
                    <span>{{ __('Jam Kerja') }}</span>
                </x-sidebar-nav-link>
            @endif
        @endif

        {{-- Perangkat Ajar --}}
        @if (in_array(Auth::user()->role, [
                'super_admin',
                'admin_yayasan',
                'admin_lembaga',
                'kepala_lembaga',
                'kurikulum',
                'guru',
            ]))
            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Perangkat Ajar
            </div>
            <x-sidebar-nav-link :href="route('perangkat-ajar.index')" :active="request()->routeIs('perangkat-ajar.*')">
                <x-heroicon-o-document-text class="h-5 w-5 shrink-0" />
                <span>{{ __('Perangkat Ajar') }}</span>
            </x-sidebar-nav-link>
        @endif

        {{-- Monitoring (Kurikulum / Kepala Lembaga / Admin Lembaga) --}}
        @if (Auth::user()->isKurikulum() || Auth::user()->isKepalaLembaga() || Auth::user()->isAdminLembaga())
            <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Monitoring
            </div>
            <x-sidebar-nav-link :href="route('jurnal-mengajar.monitoring')" :active="request()->routeIs('jurnal-mengajar.monitoring*')">
                <x-heroicon-o-document-chart-bar class="h-5 w-5 shrink-0" />
                <span>{{ __('Jurnal Mengajar') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('absensi-ptk.laporan')" :active="request()->routeIs('absensi-ptk.laporan*')">
                <x-heroicon-o-document-chart-bar class="h-5 w-5 shrink-0" />
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
                <x-heroicon-o-document-chart-bar class="h-5 w-5 shrink-0" />
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
                <x-heroicon-o-tag class="h-5 w-5 shrink-0" />
                <span>{{ __('Kategori Pelanggaran') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('kesiswaan.pelanggaran.index')" :active="request()->routeIs('kesiswaan.pelanggaran*')">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0" />
                <span>{{ __('Pelanggaran') }}</span>
            </x-sidebar-nav-link>
            <x-sidebar-nav-link :href="route('kesiswaan.ekskul.index')" :active="request()->routeIs('kesiswaan.ekskul*') && !request()->routeIs('kesiswaan.anggota-ekskul*')">
                <x-heroicon-o-users class="h-5 w-5 shrink-0" />
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
            <x-heroicon-o-bell class="h-5 w-5 shrink-0 text-gray-500" />
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
                <x-heroicon-o-chevron-down class="h-4 w-4 shrink-0 text-gray-400" />
            </button>

            <div x-show="open" @click.away="open = false" x-transition
                class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-md shadow-lg border border-gray-200 py-1 z-10">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
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
