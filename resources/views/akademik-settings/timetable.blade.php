<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Timetable — Susun Jadwal Harian') }}
            </h2>
            <a href="{{ route('akademik-settings.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr;
                Kembali ke Pengaturan</a>
        </div>
    </x-slot>

    <div class="py-12" x-data="timetableApp()" x-init="init()">
        <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <div class="flex gap-6">
                {{-- Sidebar: Source items --}}
                <div class="w-64 shrink-0">
                    <div class="sticky top-4 space-y-6">
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="bg-indigo-50 px-4 py-2 rounded-t-lg border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-indigo-900">Jam KBM</h3>
                            </div>
                            <div class="p-3" id="source-kbm">
                                <div class="source-item cursor-grab rounded-md border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs"
                                    data-tipe="kbm" data-label="Jam KBM" data-durasi="{{ $durasiKbm }}">
                                    <span class="font-medium">Jam KBM</span>
                                    <span class="block text-gray-500">{{ $durasiKbm }} menit</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="bg-amber-50 px-4 py-2 rounded-t-lg border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-amber-900">Istirahat</h3>
                            </div>
                            <div class="p-3" id="source-istirahat">
                                <div class="source-item cursor-grab rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs"
                                    data-tipe="istirahat" data-label="Istirahat" data-durasi="{{ $durasiIstirahat }}">
                                    <span class="font-medium">Istirahat</span>
                                    <span class="block text-gray-500">{{ $durasiIstirahat }} menit</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="bg-emerald-50 px-4 py-2 rounded-t-lg border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-emerald-900">Kegiatan</h3>
                            </div>
                            <div class="p-3 space-y-2" id="source-kegiatan">
                                @foreach ($kegiatanList as $k)
                                    <div class="source-item cursor-grab rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs"
                                        data-tipe="kegiatan" data-label="{{ $k['nama'] }}"
                                        data-durasi="{{ $k['durasi_menit'] }}">
                                        <span class="font-medium">{{ $k['nama'] }}</span>
                                        <span class="block text-gray-500">{{ $k['durasi_menit'] }} menit</span>
                                    </div>
                                @endforeach
                                @if (empty($kegiatanList))
                                    <p class="text-xs text-gray-400 italic px-2">Belum ada kegiatan.</p>
                                @endif
                            </div>
                        </div>

                        <p class="text-xs text-gray-400 italic">Drag item ke kolom hari. Jam dihitung otomatis mulai
                            {{ $jamMulai }}.</p>
                    </div>
                </div>

                {{-- Timetable grid --}}
                <div class="flex-1">
                    <form method="POST" action="{{ route('akademik-settings.timetable.save') }}">
                        @csrf
                        <div class="grid gap-4" style="grid-template-columns: repeat({{ count($hariEfektif) }}, 1fr);">
                            @foreach ($hariEfektif as $hari)
                                <div class="bg-white shadow-sm sm:rounded-lg">
                                    <div class="bg-gray-100 px-4 py-2 rounded-t-lg border-b border-gray-200">
                                        <h3 class="text-sm font-semibold text-gray-900 text-center">{{ $hari }}
                                        </h3>
                                    </div>
                                    <div class="drop-zone p-2 space-y-1 min-h-[300px]" data-hari="{{ $hari }}">
                                        @foreach ($timetable[$hari] ?? [] as $item)
                                            <div class="timetable-item cursor-grab rounded-md border px-3 py-2 text-xs relative {{ $item['tipe'] === 'kbm' ? 'border-indigo-200 bg-indigo-50' : ($item['tipe'] === 'istirahat' ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50') }}"
                                                data-tipe="{{ $item['tipe'] }}" data-label="{{ $item['label'] }}"
                                                data-durasi="{{ $item['durasi_menit'] }}">
                                                <span class="font-medium">{{ $item['label'] }}</span>
                                                <span class="block text-gray-500">
                                                    {{ $item['jam_mulai'] }} - {{ $item['jam_selesai'] }}
                                                    ({{ $item['durasi_menit'] }} mnt)
                                                </span>
                                                <input type="hidden" name="timetable[{{ $hari }}][]"
                                                    value="{{ json_encode(['tipe' => $item['tipe'], 'label' => $item['label'], 'durasi_menit' => $item['durasi_menit']]) }}">
                                                <button type="button"
                                                    class="absolute top-1 right-1 text-red-400 hover:text-red-600 text-xs"
                                                    onclick="this.parentElement.remove(); recalcTimes(this.closest('.drop-zone'))">&times;</button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Simpan Timetable
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        function recalcTimes(zone) {
            const jamMulai = '{{ $jamMulai }}';
            const [h, m] = jamMulai.split(':').map(Number);
            let current = h * 60 + m;
            let kbmCount = 0;

            zone.querySelectorAll('.timetable-item').forEach(item => {
                const tipe = item.dataset.tipe;
                const durasi = parseInt(item.dataset.durasi) || 0;
                const startH = Math.floor(current / 60).toString().padStart(2, '0');
                const startM = (current % 60).toString().padStart(2, '0');
                current += durasi;
                const endH = Math.floor(current / 60).toString().padStart(2, '0');
                const endM = (current % 60).toString().padStart(2, '0');

                // Auto-rename KBM items
                if (tipe === 'kbm') {
                    kbmCount++;
                    item.dataset.label = 'Jam ' + kbmCount;
                    const labelSpan = item.querySelector('span.font-medium');
                    if (labelSpan) labelSpan.textContent = 'Jam ' + kbmCount;
                }

                const timeStr = `${startH}:${startM} - ${endH}:${endM}`;
                const span = item.querySelector('span.block.text-gray-500');
                if (span) {
                    span.textContent = timeStr + ` (${durasi} mnt)`;
                }

                const hidden = item.querySelector('input[type=hidden]');
                if (hidden) {
                    hidden.value = JSON.stringify({
                        tipe: item.dataset.tipe,
                        label: item.dataset.label,
                        durasi_menit: durasi
                    });
                }
            });
        }

        window.timetableApp = function() {
            return {
                init: function() {
                    ['source-kbm', 'source-istirahat', 'source-kegiatan'].forEach(function(id) {
                        var el = document.getElementById(id);
                        if (!el) return;
                        Sortable.create(el, {
                            group: {
                                name: 'timetable',
                                pull: 'clone',
                                put: false
                            },
                            sort: false,
                            animation: 150,
                        });
                    });
                    document.querySelectorAll('.drop-zone').forEach(function(zone) {
                        var hari = zone.dataset.hari;
                        Sortable.create(zone, {
                            group: {
                                name: 'day-' + hari,
                                pull: true,
                                put: function(to, from, dragEl) {
                                    // Only allow drops from source panels (not from other day columns)
                                    return ['source-kbm', 'source-istirahat', 'source-kegiatan']
                                        .includes(from.el.id) ||
                                        from.el === to.el;
                                }
                            },
                            animation: 150,
                            onAdd: function(evt) {
                                var item = evt.item;
                                var tipe = item.dataset.tipe;
                                var label = item.dataset.label;
                                var durasi = item.dataset.durasi;

                                item.className =
                                    'timetable-item cursor-grab rounded-md border px-3 py-2 text-xs relative ' +
                                    (tipe === 'kbm' ? 'border-indigo-200 bg-indigo-50' :
                                        tipe === 'istirahat' ? 'border-amber-200 bg-amber-50' :
                                        'border-emerald-200 bg-emerald-50');
                                item.dataset.tipe = tipe;
                                item.dataset.label = label;
                                item.dataset.durasi = durasi;

                                item.innerHTML =
                                    '<span class="font-medium">' + label + '</span>' +
                                    '<span class="block text-gray-500">' + durasi +
                                    ' mnt</span>' +
                                    '<input type="hidden" name="timetable[' + hari +
                                    '][]" value=\'' + JSON.stringify({
                                        tipe: tipe,
                                        label: label,
                                        durasi_menit: parseInt(durasi)
                                    }) + '\'>' +
                                    '<button type="button" class="absolute top-1 right-1 text-red-400 hover:text-red-600 text-xs" onclick="this.parentElement.remove(); recalcTimes(this.closest(\'.drop-zone\'))">&times;</button>';

                                recalcTimes(zone);
                            },
                            onEnd: function(evt) {
                                recalcTimes(evt.to);
                            },
                        });
                    });
                }
            };
        };
    </script>
</x-app-layout>
