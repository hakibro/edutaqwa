<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Perangkat Ajar') }}</h2>
            <div class="flex items-center gap-3">
                {{-- Tombol Import Excel --}}
                <button onclick="document.getElementById('import-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition">
                    <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                    Import Excel
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="perangkatAjar()">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            @if (session('success'))
                <div
                    class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800 flex items-center gap-2">
                    <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div
                    class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800 flex items-center gap-2">
                    <x-heroicon-o-exclamation-circle class="w-5 h-5 shrink-0" />
                    {{ session('error') }}
                </div>
            @endif

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                        <x-heroicon-o-document-text class="w-5 h-5 text-indigo-600" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">CP</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $cps->total() }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                        <x-heroicon-o-clipboard-document-check class="w-5 h-5 text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">TP</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $tps->total() }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-amber-600" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">ATP</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $atps->total() }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-rose-100 flex items-center justify-center shrink-0">
                        <x-heroicon-o-book-open class="w-5 h-5 text-rose-600" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Modul Ajar</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $moduls->total() }}</p>
                    </div>
                </div>
            </div>

            {{-- Tab Navigation --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50/50">
                    <nav class="flex" role="tablist">
                        <button @click="tab = 'cp'"
                            :class="tab === 'cp' ? 'border-indigo-500 text-indigo-600 bg-white' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="flex-1 sm:flex-none px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                            <span class="flex items-center justify-center gap-2">
                                <x-heroicon-o-document-text class="w-4 h-4" />
                                CP
                                <span
                                    class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold rounded-full"
                                    :class="tab === 'cp' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500'">{{ $cps->total() }}</span>
                            </span>
                        </button>
                        <button @click="tab = 'tp'"
                            :class="tab === 'tp' ? 'border-emerald-500 text-emerald-600 bg-white' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="flex-1 sm:flex-none px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                            <span class="flex items-center justify-center gap-2">
                                <x-heroicon-o-clipboard-document-check class="w-4 h-4" />
                                TP
                                <span
                                    class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold rounded-full"
                                    :class="tab === 'tp' ? 'bg-emerald-100 text-emerald-600' :
                                        'bg-gray-100 text-gray-500'">{{ $tps->total() }}</span>
                            </span>
                        </button>
                        <button @click="tab = 'atp'"
                            :class="tab === 'atp' ? 'border-amber-500 text-amber-600 bg-white' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="flex-1 sm:flex-none px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                            <span class="flex items-center justify-center gap-2">
                                <x-heroicon-o-chart-bar class="w-4 h-4" />
                                ATP
                                <span
                                    class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold rounded-full"
                                    :class="tab === 'atp' ? 'bg-amber-100 text-amber-600' : 'bg-gray-100 text-gray-500'">{{ $atps->total() }}</span>
                            </span>
                        </button>
                        <button @click="tab = 'modul'"
                            :class="tab === 'modul' ? 'border-rose-500 text-rose-600 bg-white' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="flex-1 sm:flex-none px-5 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                            <span class="flex items-center justify-center gap-2">
                                <x-heroicon-o-book-open class="w-4 h-4" />
                                Modul Ajar
                                <span
                                    class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold rounded-full"
                                    :class="tab === 'modul' ? 'bg-rose-100 text-rose-600' : 'bg-gray-100 text-gray-500'">{{ $moduls->total() }}</span>
                            </span>
                        </button>
                    </nav>
                </div>

                {{-- Tab: CP --}}
                <div x-show="tab === 'cp'" x-cloak>
                    <div
                        class="p-4 border-b border-gray-100 flex flex-wrap gap-3 items-center justify-between bg-white">
                        <div class="flex items-center gap-3">
                            {{-- Filter Mapel --}}
                            <form method="GET" id="filter-cp" class="flex items-center gap-2">
                                <select name="mapel_id" onchange="this.form.submit()"
                                    class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5">
                                    <option value="">Semua Mapel</option>
                                    @foreach ($mapels as $m)
                                        <option value="{{ $m->id }}"
                                            {{ request('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (auth()->user()->isAdminLembaga() || auth()->user()->isGuru())
                                    <select name="guru_id" onchange="this.form.submit()"
                                        class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5">
                                        <option value="">Semua Guru</option>
                                        @foreach ($gurus as $g)
                                            <option value="{{ $g->id }}"
                                                {{ request('guru_id') == $g->id ? 'selected' : '' }}>
                                                {{ $g->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                <a href="{{ route('perangkat-ajar.index') }}"
                                    class="text-xs text-gray-400 hover:text-gray-600 {{ request('mapel_id') || request('guru_id') ? '' : 'invisible' }}">Reset</a>
                            </form>
                        </div>
                        <div class="flex items-center gap-2">
                            @if (auth()->user()->isGuru())
                                <button @click="openModal('cp')"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                                    <x-heroicon-o-plus class="w-4 h-4" />
                                    Tambah CP
                                </button>
                            @endif
                            <span class="text-xs text-gray-400">{{ $cps->total() }} CP</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 p-4">
                        @forelse ($cps as $cp)
                            <div
                                class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-sm transition-shadow">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">{{ $cp->kode ?? '-' }}</span>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">{{ $cp->fase }}</span>
                                    </div>
                                    <span
                                        class="inline-flex items-center justify-center min-w-[28px] h-7 rounded-full text-xs font-bold {{ $cp->tps_count > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400' }}">{{ $cp->tps_count }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ Str::limit($cp->deskripsi, 80) }}
                                </p>
                                <div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
                                    <span>{{ $cp->mapel->nama }}</span>
                                    <span class="text-gray-300">|</span>
                                    <span>{{ $cp->guru->nama }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                    @php $canEdit = auth()->user()->isGuru() && auth()->user()->guru_id === $cp->guru_id; @endphp
                                    @if (!auth()->user()->isGuru() || $canEdit)
                                        <div class="flex items-center gap-3">
                                            <button @click="openModal('cp', {{ $cp->id }})"
                                                class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">Edit</button>
                                            <form action="{{ route('perangkat-ajar.cp.destroy', $cp) }}"
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Hapus CP ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-sm text-red-600 hover:text-red-900 font-medium">Hapus</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-300 text-sm">-</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full flex flex-col items-center gap-2 py-12">
                                <x-heroicon-o-document-text class="w-12 h-12 text-gray-300" />
                                <p class="text-sm text-gray-500">Belum ada data CP.</p>
                                @if (auth()->user()->isGuru())
                                    <button @click="openModal('cp')"
                                        class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">+
                                        Tambah CP pertama</button>
                                @endif
                            </div>
                        @endforelse
                    </div>
                    @if ($cps->hasPages())
                        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                            {{ $cps->appends(request()->except('cp_page'))->links() }}</div>
                    @endif
                </div>

                {{-- Tab: TP --}}
                <div x-show="tab === 'tp'" x-cloak>
                    <div
                        class="p-4 border-b border-gray-100 flex flex-wrap gap-3 items-center justify-between bg-white">
                        <div class="flex items-center gap-3">
                            <form method="GET" id="filter-tp" class="flex items-center gap-2">
                                <select name="mapel_id" onchange="this.form.submit()"
                                    class="rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-1.5">
                                    <option value="">Semua Mapel</option>
                                    @foreach ($mapels as $m)
                                        <option value="{{ $m->id }}"
                                            {{ request('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (auth()->user()->isAdminLembaga() || auth()->user()->isGuru())
                                    <select name="guru_id" onchange="this.form.submit()"
                                        class="rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-1.5">
                                        <option value="">Semua Guru</option>
                                        @foreach ($gurus as $g)
                                            <option value="{{ $g->id }}"
                                                {{ request('guru_id') == $g->id ? 'selected' : '' }}>
                                                {{ $g->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                <a href="{{ route('perangkat-ajar.index') }}"
                                    class="text-xs text-gray-400 hover:text-gray-600 {{ request('mapel_id') || request('guru_id') ? '' : 'invisible' }}">Reset</a>
                            </form>
                        </div>
                        <div class="flex items-center gap-2">
                            @if (auth()->user()->isGuru())
                                <button @click="openModal('tp')"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition">
                                    <x-heroicon-o-plus class="w-4 h-4" />
                                    Tambah TP
                                </button>
                            @endif
                            <span class="text-xs text-gray-400">{{ $tps->total() }} TP</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 p-4">
                        @forelse ($tps as $tp)
                            <div
                                class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-sm transition-shadow">
                                <div class="flex items-start justify-between mb-2">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">{{ $tp->kode ?? '-' }}</span>
                                    <span
                                        class="inline-flex items-center justify-center min-w-[28px] h-7 rounded-full text-xs font-bold {{ $tp->atps_count > 0 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-400' }}">{{ $tp->atps_count }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                    {{ Str::limit($tp->deskripsi, 80) }}</p>
                                <div class="text-xs text-gray-500 mb-3">
                                    <span class="inline-flex items-center gap-1">CP:
                                        <strong>{{ $tp->cp->kode ?? 'CP' }}</strong>
                                        ({{ $tp->cp->mapel->nama }})
                                    </span>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                    @php $canEditTp = !auth()->user()->isGuru() || auth()->user()->guru_id === $tp->cp->guru_id; @endphp
                                    @if ($canEditTp)
                                        <div class="flex items-center gap-3">
                                            <button @click="openModal('tp', {{ $tp->id }})"
                                                class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">Edit</button>
                                            <form action="{{ route('perangkat-ajar.tp.destroy', $tp) }}"
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Hapus TP ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-sm text-red-600 hover:text-red-900 font-medium">Hapus</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-300 text-sm">-</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full flex flex-col items-center gap-2 py-12">
                                <x-heroicon-o-clipboard-document-check class="w-12 h-12 text-gray-300" />
                                <p class="text-sm text-gray-500">Belum ada data TP.</p>
                                @if (auth()->user()->isGuru())
                                    <button @click="openModal('tp')"
                                        class="text-sm text-emerald-600 hover:text-emerald-900 font-medium">+
                                        Tambah TP pertama</button>
                                @endif
                            </div>
                        @endforelse
                    </div>
                    @if ($tps->hasPages())
                        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                            {{ $tps->appends(request()->except('tp_page'))->links() }}</div>
                    @endif
                </div>

                {{-- Tab: ATP --}}
                <div x-show="tab === 'atp'" x-cloak>
                    <div
                        class="p-4 border-b border-gray-100 flex flex-wrap gap-3 items-center justify-between bg-white">
                        <div class="flex items-center gap-3">
                            <form method="GET" id="filter-atp" class="flex items-center gap-2">
                                <select name="mapel_id" onchange="this.form.submit()"
                                    class="rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm py-1.5">
                                    <option value="">Semua Mapel</option>
                                    @foreach ($mapels as $m)
                                        <option value="{{ $m->id }}"
                                            {{ request('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (auth()->user()->isAdminLembaga() || auth()->user()->isGuru())
                                    <select name="guru_id" onchange="this.form.submit()"
                                        class="rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm py-1.5">
                                        <option value="">Semua Guru</option>
                                        @foreach ($gurus as $g)
                                            <option value="{{ $g->id }}"
                                                {{ request('guru_id') == $g->id ? 'selected' : '' }}>
                                                {{ $g->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                <a href="{{ route('perangkat-ajar.index') }}"
                                    class="text-xs text-gray-400 hover:text-gray-600 {{ request('mapel_id') || request('guru_id') ? '' : 'invisible' }}">Reset</a>
                            </form>
                        </div>
                        <div class="flex items-center gap-2">
                            @if (auth()->user()->isGuru())
                                <button @click="openModal('atp')"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 transition">
                                    <x-heroicon-o-plus class="w-4 h-4" />
                                    Tambah ATP
                                </button>
                            @endif
                            <span class="text-xs text-gray-400">{{ $atps->total() }} ATP</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 p-4">
                        @forelse ($atps as $atp)
                            <div
                                class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-sm transition-shadow">
                                <div class="flex items-start justify-between mb-2">
                                    <span
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-700 font-bold text-xs">{{ $atp->minggu_ke }}</span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">{{ $atp->tp->kode ?? 'TP' }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ Str::limit($atp->materi, 80) }}
                                </p>
                                <div class="text-xs text-gray-500 mb-3">
                                    {{ $atp->tp->cp->mapel->nama ?? '-' }}
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                    @php $canEditAtp = !auth()->user()->isGuru() || auth()->user()->guru_id === $atp->tp->cp->guru_id; @endphp
                                    @if ($canEditAtp)
                                        <div class="flex items-center gap-3">
                                            <button @click="openModal('atp', {{ $atp->id }})"
                                                class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">Edit</button>
                                            <form action="{{ route('perangkat-ajar.atp.destroy', $atp) }}"
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Hapus ATP ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-sm text-red-600 hover:text-red-900 font-medium">Hapus</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-300 text-sm">-</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full flex flex-col items-center gap-2 py-12">
                                <x-heroicon-o-chart-bar class="w-12 h-12 text-gray-300" />
                                <p class="text-sm text-gray-500">Belum ada data ATP.</p>
                                @if (auth()->user()->isGuru())
                                    <button @click="openModal('atp')"
                                        class="text-sm text-amber-600 hover:text-amber-900 font-medium">+
                                        Tambah ATP pertama</button>
                                @endif
                            </div>
                        @endforelse
                    </div>
                    @if ($atps->hasPages())
                        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                            {{ $atps->appends(request()->except('atp_page'))->links() }}</div>
                    @endif
                </div>

                {{-- Tab: Modul Ajar --}}
                <div x-show="tab === 'modul'" x-cloak>
                    <div
                        class="p-4 border-b border-gray-100 flex flex-wrap gap-3 items-center justify-between bg-white">
                        <div class="flex items-center gap-3">
                            <form method="GET" id="filter-modul" class="flex items-center gap-2">
                                <select name="mapel_id" onchange="this.form.submit()"
                                    class="rounded-lg border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm py-1.5">
                                    <option value="">Semua Mapel</option>
                                    @foreach ($mapels as $m)
                                        <option value="{{ $m->id }}"
                                            {{ request('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (auth()->user()->isAdminLembaga() || auth()->user()->isGuru())
                                    <select name="guru_id" onchange="this.form.submit()"
                                        class="rounded-lg border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm py-1.5">
                                        <option value="">Semua Guru</option>
                                        @foreach ($gurus as $g)
                                            <option value="{{ $g->id }}"
                                                {{ request('guru_id') == $g->id ? 'selected' : '' }}>
                                                {{ $g->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                <a href="{{ route('perangkat-ajar.index') }}"
                                    class="text-xs text-gray-400 hover:text-gray-600 {{ request('mapel_id') || request('guru_id') ? '' : 'invisible' }}">Reset</a>
                            </form>
                        </div>
                        <div class="flex items-center gap-2">
                            @if (auth()->user()->isGuru())
                                <button @click="openModal('modul')"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 transition">
                                    <x-heroicon-o-plus class="w-4 h-4" />
                                    Tambah Modul
                                </button>
                            @endif
                            <span class="text-xs text-gray-400">{{ $moduls->total() }} Modul</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 p-4">
                        @forelse ($moduls as $modul)
                            <div
                                class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-sm transition-shadow">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="text-sm font-medium text-gray-900 line-clamp-1">{{ $modul->judul }}
                                    </h4>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
                                    <span>{{ $modul->mapel->nama }}</span>
                                    <span class="text-gray-300">|</span>
                                    <span>{{ $modul->guru->nama ?? '-' }}</span>
                                </div>
                                <div class="mb-3">
                                    @if ($modul->file_path)
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('perangkat-ajar.modul.view', $modul) }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition"
                                                target="_blank">Lihat</a>
                                            <a href="{{ route('perangkat-ajar.modul.download', $modul) }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition">Download</a>
                                        </div>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-400">Tanpa
                                            file</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                    @php $canEditModul = !auth()->user()->isGuru() || auth()->user()->guru_id === $modul->guru_id; @endphp
                                    @if ($canEditModul)
                                        <div class="flex items-center gap-3">
                                            <button @click="openModal('modul', {{ $modul->id }})"
                                                class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">Edit</button>
                                            <form action="{{ route('perangkat-ajar.modul.destroy', $modul) }}"
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Hapus Modul Ajar ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-sm text-red-600 hover:text-red-900 font-medium">Hapus</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-300 text-sm">-</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full flex flex-col items-center gap-2 py-12">
                                <x-heroicon-o-book-open class="w-12 h-12 text-gray-300" />
                                <p class="text-sm text-gray-500">Belum ada data Modul Ajar.</p>
                                @if (auth()->user()->isGuru())
                                    <button @click="openModal('modul')"
                                        class="text-sm text-rose-600 hover:text-rose-900 font-medium">+
                                        Tambah Modul pertama</button>
                                @endif
                            </div>
                        @endforelse
                    </div>
                    @if ($moduls->hasPages())
                        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                            {{ $moduls->appends(request()->except('modul_page'))->links() }}</div>
                    @endif
                </div>

            </div>{{-- end of tab container --}}

        </div>{{-- end of max-w-7xl --}}

        {{-- IMPORT EXCEL MODAL --}}
        <div id="import-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" x-data="{ open: false }"
            x-init="$watch('open', v => $el.classList.toggle('hidden', !v))" @keydown.escape.window="open = false; $el.classList.add('hidden')">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity"
                    onclick="document.getElementById('import-modal').classList.add('hidden')"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 z-10 text-left transform transition-all sm:my-8"
                    onclick="event.stopPropagation()">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Import Excel CP / TP / ATP</h3>
                        <button type="button"
                            onclick="document.getElementById('import-modal').classList.add('hidden')"
                            class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form action="{{ route('perangkat-ajar.import') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih File Excel
                                (.xlsx)</label>
                            <input type="file" name="file" accept=".xlsx" required
                                class="mt-0 block w-full text-sm text-gray-600 file:mr-2 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition">
                        </div>
                        <div class="flex items-start gap-2 text-xs text-gray-500 bg-gray-50 rounded-lg p-3">
                            <svg class="w-4 h-4 shrink-0 mt-0.5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Template 3 sheet: <strong>CP</strong> (mapel_kode, fase, kode, deskripsi) →
                                <strong>TP</strong> (cp_kode, kode, deskripsi) → <strong>ATP</strong> (tp_kode,
                                minggu_ke, materi). CP & TP wajib, ATP opsional.</span>
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <a href="{{ route('perangkat-ajar.template') }}"
                                class="text-sm text-indigo-600 hover:text-indigo-900 underline font-medium">Download
                                Template</a>
                            <div class="flex gap-3">
                                <button type="button"
                                    onclick="document.getElementById('import-modal').classList.add('hidden')"
                                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</button>
                                <button type="submit"
                                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500 transition">Import</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- CRUD MODAL --}}
        <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
            @keydown.escape.window="closeModal()">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="closeModal()"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 z-10 text-left transform transition-all sm:my-8"
                    @click.stop="">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900" x-text="modalTitle"></h3>
                        <button type="button" @click="closeModal()"
                            class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Form CP --}}
                    <form x-show="modalType === 'cp'" method="POST" :action="modalAction" class="space-y-4">
                        @csrf
                        <template x-if="modalMethod !== 'POST'"><input type="hidden" name="_method"
                                value="PUT"></template>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mapel</label>
                            <select name="mapel_id" required
                                class="mt-0 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Pilih Mapel</option>
                                @foreach ($mapelOptions as $m)
                                    <option value="{{ $m->id }}">{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fase</label>
                            <input type="text" name="fase" required maxlength="20" placeholder="Fase A"
                                class="mt-0 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                            <input type="text" name="kode" maxlength="50" placeholder="CP-1"
                                class="mt-0 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="deskripsi" required rows="3"
                                class="mt-0 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeModal()"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</button>
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition">Simpan</button>
                        </div>
                    </form>

                    {{-- Form TP --}}
                    <form x-show="modalType === 'tp'" method="POST" :action="modalAction" class="space-y-4">
                        @csrf
                        <template x-if="modalMethod !== 'POST'"><input type="hidden" name="_method"
                                value="PUT"></template>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CP</label>
                            <select name="cp_id" required
                                class="mt-0 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                <option value="">Pilih CP</option>
                                @foreach ($cpOptions as $c)
                                    <option value="{{ $c->id }}">{{ $c->kode ?? 'CP' }} —
                                        {{ $c->mapel->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                            <input type="text" name="kode" maxlength="50" placeholder="TP-1.1"
                                class="mt-0 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="deskripsi" required rows="3"
                                class="mt-0 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"></textarea>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeModal()"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</button>
                            <button type="submit"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500 transition">Simpan</button>
                        </div>
                    </form>

                    {{-- Form ATP --}}
                    <form x-show="modalType === 'atp'" method="POST" :action="modalAction" class="space-y-4">
                        @csrf
                        <template x-if="modalMethod !== 'POST'"><input type="hidden" name="_method"
                                value="PUT"></template>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">TP</label>
                            <select name="tp_id" required
                                class="mt-0 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                                <option value="">Pilih TP</option>
                                @foreach ($tpOptions as $t)
                                    <option value="{{ $t->id }}">{{ $t->kode ?? 'TP' }} —
                                        {{ $t->cp->mapel->nama ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Minggu Ke</label>
                            <input type="number" name="minggu_ke" required min="1"
                                class="mt-0 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Materi</label>
                            <textarea name="materi" required rows="3"
                                class="mt-0 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm"></textarea>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeModal()"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</button>
                            <button type="submit"
                                class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500 transition">Simpan</button>
                        </div>
                    </form>

                    {{-- Form Modul Ajar --}}
                    <form x-show="modalType === 'modul'" method="POST" :action="modalAction"
                        enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <template x-if="modalMethod !== 'POST'"><input type="hidden" name="_method"
                                value="PUT"></template>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mapel</label>
                            <select name="mapel_id" required
                                class="mt-0 block w-full rounded-lg border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm">
                                <option value="">Pilih Mapel</option>
                                @foreach ($mapelOptions as $m)
                                    <option value="{{ $m->id }}">{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                            <input type="text" name="judul" required maxlength="255"
                                class="mt-0 block w-full rounded-lg border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="deskripsi" rows="3"
                                class="mt-0 block w-full rounded-lg border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">File (doc/docx/pdf,
                                opsional)</label>
                            <input type="file" name="file" accept=".doc,.docx,.pdf"
                                class="mt-0 block w-full text-sm text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 transition">
                            <p x-show="modalId && modalType === 'modul'" class="mt-1 text-xs text-gray-500">Kosongkan
                                jika tidak ingin mengubah file.</p>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeModal()"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</button>
                            <button type="submit"
                                class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500 transition">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Embedded data for edit modal
        const __cpData = {!! $cpDataJson->toJson() !!};
        const __tpData = {!! $tpDataJson->toJson() !!};
        const __atpData = {!! $atpDataJson->toJson() !!};
        const __modulData = {!! $modulDataJson->toJson() !!};

        function perangkatAjar() {
            return {
                tab: '{{ session('tab', request()->tab ?? 'cp') }}',
                modalOpen: false,
                modalType: '',
                modalId: null,
                modalAction: '',
                modalMethod: 'POST',
                modalTitle: '',

                openModal(type, id = null) {
                    this.modalType = type;
                    this.modalId = id;
                    const titles = {
                        cp: 'CP',
                        tp: 'TP',
                        atp: 'ATP',
                        modul: 'Modul Ajar'
                    };
                    const routes = {
                        cp: {
                            store: '{{ route('perangkat-ajar.cp.store') }}',
                            update: '{{ route('perangkat-ajar.cp.update', '__ID__') }}'
                        },
                        tp: {
                            store: '{{ route('perangkat-ajar.tp.store') }}',
                            update: '{{ route('perangkat-ajar.tp.update', '__ID__') }}'
                        },
                        atp: {
                            store: '{{ route('perangkat-ajar.atp.store') }}',
                            update: '{{ route('perangkat-ajar.atp.update', '__ID__') }}'
                        },
                        modul: {
                            store: '{{ route('perangkat-ajar.modul.store') }}',
                            update: '{{ route('perangkat-ajar.modul.update', '__ID__') }}'
                        },
                    };

                    if (id) {
                        this.modalTitle = 'Edit ' + titles[type];
                        this.modalAction = routes[type].update.replace('__ID__', id);
                        this.modalMethod = 'PUT';
                    } else {
                        this.modalTitle = 'Tambah ' + titles[type];
                        this.modalAction = routes[type].store;
                        this.modalMethod = 'POST';
                    }

                    // Populate form fields on edit — use setTimeout so x-show renders the form first
                    this.$nextTick(() => {
                        setTimeout(() => {
                            const form = document.querySelector(
                                `form[x-show="modalType === '${type}'"]`);
                            if (!form) return;
                            form.reset();

                            if (id) {
                                const data = {
                                    cp: __cpData,
                                    tp: __tpData,
                                    atp: __atpData,
                                    modul: __modulData
                                } [type]?.[id];
                                if (!data) return;
                                const fields = form.querySelectorAll('[name]');
                                fields.forEach(el => {
                                    const name = el.getAttribute('name');
                                    if (name === '_method' || name === '_token')
                                        return;
                                    if (el.type === 'file') return;
                                    if (data[name] !== undefined) el.value =
                                        data[name];
                                });
                            }
                        }, 50);
                    });

                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                    this.modalType = '';
                    this.modalId = null;
                }
            };
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</x-app-layout>
