<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Notifikasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">Semua Notifikasi</h3>
                        <form action="{{ route('notifikasi.mark-all-read') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                Tandai Semua Dibaca
                            </button>
                        </form>
                    </div>

                    @if ($notifikasis->isNotEmpty())
                        <div class="space-y-1">
                            @foreach ($notifikasis as $notif)
                                @php
                                    $bgColor = match ($notif->tipe) {
                                        'warning' => 'bg-yellow-50 border-yellow-200',
                                        'danger' => 'bg-red-50 border-red-200',
                                        'success' => 'bg-green-50 border-green-200',
                                        default => 'bg-white border-gray-100',
                                    };
                                    $iconColor = match ($notif->tipe) {
                                        'warning' => 'text-yellow-600',
                                        'danger' => 'text-red-600',
                                        'success' => 'text-green-600',
                                        default => 'text-blue-600',
                                    };
                                @endphp
                                <div
                                    class="rounded-lg border {{ $bgColor }} p-4 {{ !$notif->is_read ? 'ring-1 ring-indigo-200' : '' }}">
                                    <div class="flex items-start gap-3">
                                        @if (!$notif->is_read)
                                            <div class="mt-1 h-2 w-2 shrink-0 rounded-full bg-indigo-500"></div>
                                        @else
                                            <div class="mt-1 h-2 w-2 shrink-0 rounded-full bg-gray-300"></div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <p
                                                    class="text-sm font-semibold text-gray-900 {{ !$notif->is_read ? '' : 'text-gray-500' }}">
                                                    {{ $notif->judul }}
                                                </p>
                                                <span class="text-xs text-gray-400 ml-2 shrink-0">
                                                    {{ $notif->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">{{ $notif->pesan }}</p>
                                            @if (!$notif->is_read)
                                                <form action="{{ route('notifikasi.mark-read', $notif) }}"
                                                    method="POST" class="mt-2">
                                                    @csrf
                                                    <button type="submit"
                                                        class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                                        Tandai Dibaca →
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $notifikasis->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <p class="mt-3 text-sm text-gray-500">Tidak ada notifikasi.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
