<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Edit Jam Kerja') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-lg sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('jam-kerja.update', $jamKerja) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Hari</label>
                            <input type="text" value="{{ $jamKerja->hari }}" disabled
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm text-sm">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Jam Masuk</label>
                            <input type="time" name="jam_masuk" value="{{ old('jam_masuk', $jamKerja->jam_masuk) }}"
                                required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @error('jam_masuk')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Jam Pulang</label>
                            <input type="time" name="jam_pulang"
                                value="{{ old('jam_pulang', $jamKerja->jam_pulang) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @error('jam_pulang')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Toleransi Keterlambatan
                                (menit)</label>
                            <input type="number" name="toleransi_keterlambatan"
                                value="{{ old('toleransi_keterlambatan', $jamKerja->toleransi_keterlambatan) }}"
                                min="0" max="120"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @error('toleransi_keterlambatan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-6">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1"
                                    {{ $jamKerja->is_active ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                <span class="text-sm font-medium text-gray-700">Aktif</span>
                            </label>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Simpan
                            </button>
                            <a href="{{ route('jam-kerja.index') }}"
                                class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
