<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Lihat Dokumen</h2>
            <a href="{{ route('perangkat-ajar.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
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
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                {{ $modulAjar->mapel->nama }}
                            </span>
                            <span class="text-gray-300">&middot;</span>
                            <span>{{ $modulAjar->guru->nama ?? '-' }}</span>
                        </p>
                    </div>
                    <a href="{{ route('perangkat-ajar.modul.download', $modulAjar) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
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
