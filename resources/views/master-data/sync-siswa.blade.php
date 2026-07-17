<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Sync Siswa — Sisda API
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <p class="mb-4 text-gray-600">
                        Sinkronisasi data siswa dari API Akademik
                        (<code>https://apiakademik.daruttaqwa.or.id/api</code>).
                        Data akan diambil berdasarkan <strong>Kode Sisda</strong> yang sudah diset pada masing-masing
                        lembaga.
                    </p>

                    @if ($lembagas->isEmpty())
                        <div class="p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
                            Belum ada lembaga dengan <strong>Kode Sisda</strong> terkonfigurasi.
                            Silakan set <code>kode_sisda</code> di menu Lembaga terlebih dahulu.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Lembaga</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode
                                            Sisda</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($lembagas as $lembaga)
                                        <tr id="row-lembaga-{{ $lembaga->id }}">
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $lembaga->nama }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">{{ $lembaga->kode_sisda }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <button type="button" data-lembaga-id="{{ $lembaga->id }}"
                                                    data-lembaga-nama="{{ $lembaga->nama }}"
                                                    class="btn-sync px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm transition-colors">
                                                    Sync Sekarang
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Sync Progress Overlay --}}
                        <div id="sync-overlay"
                            class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
                            <div class="bg-white rounded-lg shadow-xl p-8 max-w-lg w-full mx-4">
                                <div class="text-center">
                                    <div
                                        class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mb-4">
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Menyinkronkan Data</h3>
                                    <p id="sync-status-text" class="text-sm text-gray-500">Menghubungi API Akademik...
                                    </p>
                                    <div class="mt-4 w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                        <div id="sync-progress-bar"
                                            class="bg-indigo-600 h-2 rounded-full transition-all duration-500 ease-out"
                                            style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Hasil Sync --}}
                        <div id="sync-result" class="hidden mt-6"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const overlay = document.getElementById('sync-overlay');
                const statusText = document.getElementById('sync-status-text');
                const progressBar = document.getElementById('sync-progress-bar');
                const resultDiv = document.getElementById('sync-result');
                let progressInterval = null;

                // Progress simulation steps (real progress from server is binary, so simulate visual)
                const progressSteps = [{
                        pct: 10,
                        text: 'Menghubungi API Akademik...'
                    },
                    {
                        pct: 25,
                        text: 'Mengambil data kelas...'
                    },
                    {
                        pct: 45,
                        text: 'Mengambil data siswa per kelas...'
                    },
                    {
                        pct: 70,
                        text: 'Memproses data siswa...'
                    },
                    {
                        pct: 90,
                        text: 'Memeriksa data yang perlu dihapus...'
                    },
                ];

                document.querySelectorAll('.btn-sync').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const lembagaId = this.dataset.lembagaId;
                        const lembagaNama = this.dataset.lembagaNama;

                        if (!confirm(`Sync data siswa untuk ${lembagaNama} dari Sisda?`)) {
                            return;
                        }

                        // Disable all sync buttons
                        document.querySelectorAll('.btn-sync').forEach(b => b.disabled = true);
                        btn.classList.add('opacity-50', 'cursor-not-allowed');

                        // Show overlay
                        overlay.classList.remove('hidden');
                        statusText.textContent = 'Menghubungi API Akademik...';
                        progressBar.style.width = '0%';
                        resultDiv.classList.add('hidden');
                        resultDiv.innerHTML = '';

                        // Simulate progress
                        let step = 0;
                        progressInterval = setInterval(() => {
                            if (step < progressSteps.length) {
                                progressBar.style.width = progressSteps[step].pct + '%';
                                statusText.textContent = progressSteps[step].text;
                                step++;
                            }
                        }, 800);

                        // Get CSRF token
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                            document.querySelector('input[name="_token"]')?.value;

                        // Do AJAX sync
                        fetch('{{ route('sync-siswa.sync') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({
                                    lembaga_id: lembagaId
                                }),
                            })
                            .then(response => response.json())
                            .then(data => {
                                clearInterval(progressInterval);
                                progressBar.style.width = '100%';

                                if (data.success) {
                                    statusText.textContent = 'Sinkronisasi selesai!';
                                    statusText.classList.add('text-green-600');
                                    statusText.classList.remove('text-gray-500');
                                } else {
                                    statusText.textContent = 'Gagal: ' + (data.message ||
                                        'Unknown error');
                                    statusText.classList.add('text-red-600');
                                    statusText.classList.remove('text-gray-500');
                                }

                                // Delay to show 100%
                                setTimeout(() => {
                                    overlay.classList.add('hidden');
                                    statusText.classList.remove('text-green-600',
                                        'text-red-600');
                                    statusText.classList.add('text-gray-500');
                                    document.querySelectorAll('.btn-sync').forEach(b => {
                                        b.disabled = false;
                                        b.classList.remove('opacity-50',
                                            'cursor-not-allowed');
                                    });
                                    renderResult(data);
                                }, 800);
                            })
                            .catch(err => {
                                clearInterval(progressInterval);
                                statusText.textContent = 'Gagal terhubung ke server.';
                                statusText.classList.add('text-red-600');
                                statusText.classList.remove('text-gray-500');
                                progressBar.style.width = '100%';
                                progressBar.classList.add('bg-red-600');

                                setTimeout(() => {
                                    overlay.classList.add('hidden');
                                    statusText.classList.remove('text-red-600');
                                    statusText.classList.add('text-gray-500');
                                    progressBar.classList.remove('bg-red-600');
                                    document.querySelectorAll('.btn-sync').forEach(b => {
                                        b.disabled = false;
                                        b.classList.remove('opacity-50',
                                            'cursor-not-allowed');
                                    });
                                    resultDiv.classList.remove('hidden');
                                    resultDiv.innerHTML = `
                                <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                    Gagal terhubung ke server. Silakan coba lagi.
                                </div>`;
                                }, 1500);
                            });
                    });
                });

                function renderResult(data) {
                    resultDiv.classList.remove('hidden');

                    const stats = data.stats || {};
                    const details = data.details || {};
                    const created = details.created || [];
                    const updated = details.updated || [];
                    const deleted = details.deleted || [];
                    const restored = details.restored || [];

                    let html = '<div class="space-y-4">';

                    // Summary cards
                    html += '<div class="grid grid-cols-2 md:grid-cols-4 gap-3">';
                    html += statCard('bg-green-50 border-green-300 text-green-800', 'Baru', stats.created || 0);
                    html += statCard('bg-blue-50 border-blue-300 text-blue-800', 'Diperbarui', stats.updated || 0);
                    html += statCard('bg-yellow-50 border-yellow-300 text-yellow-800', 'Dipulihkan', restored.length);
                    html += statCard('bg-red-50 border-red-300 text-red-800', 'Dihapus', stats.deleted || 0);
                    html += '</div>';

                    // Detail sections
                    if (created.length > 0) {
                        html += detailSection('🆕 Siswa Baru', 'text-green-700', 'bg-green-50 border-green-200',
                            created);
                    }
                    if (updated.length > 0) {
                        html += detailSection('🔄 Siswa Diperbarui', 'text-blue-700', 'bg-blue-50 border-blue-200',
                            updated);
                    }
                    if (restored.length > 0) {
                        html += detailSection('♻️ Siswa Dipulihkan', 'text-yellow-700',
                            'bg-yellow-50 border-yellow-200', restored);
                    }
                    if (deleted.length > 0) {
                        html += detailSection('🗑️ Siswa Dihapus (soft-delete)', 'text-red-700',
                            'bg-red-50 border-red-200', deleted);
                    }

                    if (created.length === 0 && updated.length === 0 && restored.length === 0 && deleted.length === 0) {
                        html +=
                            '<div class="p-4 bg-gray-100 border border-gray-300 text-gray-600 rounded text-center">Tidak ada perubahan data.</div>';
                    }

                    html += '</div>';
                    resultDiv.innerHTML = html;
                    resultDiv.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }

                function statCard(className, label, count) {
                    return `
                    <div class="p-3 rounded-lg border ${className} text-center">
                        <div class="text-2xl font-bold">${count}</div>
                        <div class="text-xs font-medium mt-1">${label}</div>
                    </div>`;
                }

                function detailSection(title, titleColor, borderColor, items) {
                    const maxShow = 20;
                    const hidden = items.length - maxShow;
                    let listItems = items.slice(0, maxShow).map(i => `<li class="text-sm py-0.5">${escapeHtml(i)}</li>`)
                        .join('');
                    if (hidden > 0) {
                        listItems += `<li class="text-sm py-0.5 text-gray-400 italic">... dan ${hidden} lainnya</li>`;
                    }
                    return `
                    <div class="border rounded-lg ${borderColor} p-4">
                        <h4 class="font-semibold ${titleColor} mb-2">${title} (${items.length})</h4>
                        <ul class="list-disc list-inside space-y-0.5 max-h-60 overflow-y-auto">${listItems}</ul>
                    </div>`;
                }

                function escapeHtml(text) {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                }
            });
        </script>
    @endpush
</x-app-layout>
