<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Detail TP') }}</h2>
            <a href="{{ route('tp.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Kembali</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $tp->kode ?? 'TP' }}</h3>
                    <p class="mt-1 text-sm text-gray-600">CP: {{ $tp->cp->kode }} — {{ $tp->cp->mapel->nama }} | Guru:
                        {{ $tp->cp->guru->nama }}</p>
                    <p class="mt-4 text-gray-700">{{ $tp->deskripsi }}</p>

                    <hr class="my-6">

                    <h4 class="text-md font-semibold text-gray-900">ATP (Alur Tujuan Pembelajaran)</h4>
                    @if ($tp->atps->isEmpty())
                        <p class="mt-2 text-sm text-gray-500">Belum ada ATP. <a
                                href="{{ route('atp.create') }}?tp_id={{ $tp->id }}"
                                class="text-indigo-600">Tambah ATP</a></p>
                    @else
                        <div class="mt-4 space-y-2">
                            @foreach ($tp->atps as $atp)
                                <div class="flex items-start justify-between rounded-md border border-gray-200 p-3">
                                    <div>
                                        <span class="font-medium text-gray-900">Minggu {{ $atp->minggu_ke }}:</span>
                                        <span class="text-sm text-gray-700">{{ $atp->materi }}</span>
                                    </div>
                                    <div class="flex gap-2 text-xs">
                                        <a href="{{ route('atp.edit', $atp) }}" class="text-indigo-600">Edit</a>
                                        <form action="{{ route('atp.destroy', $atp) }}" method="POST"
                                            onsubmit="return confirm('Hapus ATP ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600">Hapus</button>
                                        </form>
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
