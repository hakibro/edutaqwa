<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard Super Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Total Yayasan</p>
                    <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Yayasan::count() }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Total Lembaga</p>
                    <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Lembaga::count() }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">Total Pengguna</p>
                    <p class="text-3xl font-bold text-gray-900">{{ \App\Models\User::count() }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Akses Cepat</h3>
                        <div class="space-y-3">
                            <a href="{{ route('yayasan.index') }}"
                                class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100">Kelola
                                Yayasan</a>
                            <a href="{{ route('lembaga.index') }}"
                                class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100">Kelola
                                Lembaga</a>
                            <a href="{{ route('tahun-ajaran.index') }}"
                                class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100">Tahun
                                Ajaran</a>
                            <a href="{{ route('log-aktivitas.index') }}"
                                class="block rounded-md bg-indigo-50 p-3 text-indigo-700 hover:bg-indigo-100">Log
                                Aktivitas</a>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Log Terbaru</h3>
                        <div class="space-y-2 text-sm">
                            @foreach (\App\Models\LogAktivita::with('user')->latest()->take(5)->get() as $log)
                                <div class="border-b border-gray-100 pb-2">
                                    <span class="text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                                    <p class="text-gray-700">{{ $log->deskripsi }}</p>
                                </div>
                            @endforeach
                            @if (\App\Models\LogAktivita::count() === 0)
                                <p class="text-gray-500">Belum ada aktivitas.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
