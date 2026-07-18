<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Lihat Dokumen</h2>
            <a href="{{ route('perangkat-ajar.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $modulAjar->judul }}</h3>
                        <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1">
                                <x-heroicon-o-book-open class="w-4 h-4 text-gray-400" />
                                {{ $modulAjar->mapel->nama }}
                            </span>
                            <span class="text-gray-300">&middot;</span>
                            <span>{{ $modulAjar->guru->nama ?? '-' }}</span>
                        </p>
                    </div>
                    <a href="{{ route('perangkat-ajar.modul.download', $modulAjar) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                        Download
                    </a>
                </div>
                <div class="p-6">
                    @php $ext = strtolower(pathinfo($modulAjar->file_path, PATHINFO_EXTENSION)); @endphp

                    @if ($ext === 'pdf')
                        <iframe src="{{ route('perangkat-ajar.modul.view', $modulAjar) }}"
                            class="w-full border-0 rounded-lg" style="height: 80vh;"></iframe>
                    @else
                        <div
                            class="prose prose-sm max-w-none prose-headings:text-gray-900 prose-p:text-gray-700 prose-a:text-indigo-600">
                            {!! $html !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
