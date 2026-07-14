<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Detail Agenda Mengajar') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-lg sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <img src="{{ Storage::url($agenda->foto_path) }}" alt="Selfie" class="w-full rounded-lg shadow">
                    </div>
                    <table class="min-w-full text-sm">
                        <tr>
                            <td class="py-2 font-medium text-gray-500 w-32">Mapel</td>
                            <td class="py-2 text-gray-900">{{ $agenda->jadwal->mapel->nama ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">Kelas</td>
                            <td class="py-2 text-gray-900">{{ $agenda->kelas->nama }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">Tanggal</td>
                            <td class="py-2 text-gray-900">{{ $agenda->tanggal->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">Jam</td>
                            <td class="py-2 text-gray-900">{{ $agenda->jam_mulai ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">Pertemuan ke-</td>
                            <td class="py-2 text-gray-900">{{ $agenda->pertemuan_ke }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-500">Status</td>
                            <td class="py-2">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $agenda->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $agenda->is_verified ? 'Terverifikasi' : 'Pending' }}
                                </span>
                            </td>
                        </tr>
                        @if ($agenda->latitude)
                            <tr>
                                <td class="py-2 font-medium text-gray-500">Lokasi</td>
                                <td class="py-2 text-gray-900">{{ $agenda->latitude }}, {{ $agenda->longitude }}</td>
                            </tr>
                        @endif
                        @if ($agenda->is_verified)
                            <tr>
                                <td class="py-2 font-medium text-gray-500">Diverifikasi</td>
                                <td class="py-2 text-gray-900">{{ $agenda->verified_at->format('d/m/Y H:i') }} oleh
                                    {{ $agenda->verifikator->name ?? '—' }}</td>
                            </tr>
                        @endif
                    </table>
                    <div class="mt-6">
                        <a href="{{ route('agenda-mengajar.index') }}"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
