<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Detail CP') }}</h2>
            <a href="{{ route('cp.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Kembali</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $cp->kode }} — {{ $cp->mapel->nama }}</h3>
                    <p class="mt-1 text-sm text-gray-600">Fase: {{ $cp->fase }} | Guru: {{ $cp->guru->nama }}</p>
                    <p class="mt-4 text-gray-700">{{ $cp->deskripsi }}</p>

                    <hr class="my-6">

                    <h4 class="text-md font-semibold text-gray-900">TP (Tujuan Pembelajaran)</h4>
                    @if ($cp->tps->isEmpty())
                        <p class="mt-2 text-sm text-gray-500">Belum ada TP. <a
                                href="{{ route('tp.create') }}?cp_id={{ $cp->id }}" class="text-indigo-600">Tambah
                                TP</a></p>
                    @else
                        <div class="mt-4 space-y-4">
                            @foreach ($cp->tps as $tp)
                                <div class="rounded-md border border-gray-200 p-4">
                                    <h5 class="font-medium text-gray-900">{{ $tp->kode ?? 'TP' }}</h5>
                                    <p class="text-sm text-gray-700">{{ $tp->deskripsi }}</p>
                                    @if ($tp->atps->isNotEmpty())
                                        <div class="mt-2">
                                            <span class="text-xs font-medium text-gray-500">ATP:</span>
                                            <ul class="mt-1 space-y-1">
                                                @foreach ($tp->atps as $atp)
                                                    <li class="text-sm text-gray-700">
                                                        <span class="font-medium">Minggu {{ $atp->minggu_ke }}:</span>
                                                        {{ Str::limit($atp->materi, 100) }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <div class="mt-2">
                                        <a href="{{ route('tp.edit', $tp) }}"
                                            class="text-xs text-indigo-600 hover:text-indigo-900">Edit TP</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
