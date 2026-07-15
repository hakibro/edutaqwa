<?php

use App\Http\Controllers\AbsensiPtkController;
use App\Http\Controllers\AgendaMengajarController;
use App\Http\Controllers\AkademikSettingController;
use App\Http\Controllers\AtpController;
use App\Http\Controllers\CpController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JamKerjaLembagaController;
use App\Http\Controllers\JenisPtkController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KalenderAkademikController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KelompokMapelController;
use App\Http\Controllers\LembagaController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\AnggotaEkskulController;
use App\Http\Controllers\EkskulController;
use App\Http\Controllers\KategoriPelanggaranController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\PengajaranMapelController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SiswaSyncController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\TpController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\YayasanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    return match ($user->role) {
        'super_admin' => redirect()->route('super-admin.dashboard'),
        'admin_yayasan' => redirect()->route('admin-yayasan.dashboard'),
        'kepala_lembaga', 'admin_lembaga', 'kurikulum', 'kesiswaan' => redirect()->route('lembaga.dashboard'),
        'guru' => redirect()->route('guru.dashboard'),
        'siswa' => redirect()->route('siswa.dashboard'),
        'orang_tua' => redirect()->route('orang-tua.dashboard'),
        default => view('dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Role-based dashboards
    Route::middleware('role:super_admin')->get('/super-admin', fn() => view('dashboards.super-admin'))->name('super-admin.dashboard');
    Route::middleware('role:admin_yayasan')->get('/admin-yayasan', fn() => view('dashboards.admin-yayasan'))->name('admin-yayasan.dashboard');
    Route::middleware('role:kepala_lembaga,admin_lembaga,kurikulum,kesiswaan')->get('/lembaga/dashboard', fn() => view('dashboards.lembaga'))->name('lembaga.dashboard');
    Route::middleware('role:guru')->get('/guru/dashboard', fn() => view('dashboards.guru'))->name('guru.dashboard');
    Route::middleware('role:siswa')->get('/siswa/dashboard', fn() => view('dashboards.siswa'))->name('siswa.dashboard');
    Route::middleware('role:orang_tua')->get('/orang-tua/dashboard', fn() => view('dashboards.orang-tua'))->name('orang-tua.dashboard');

    // Yayasan CRUD (Super Admin)
    Route::resource('yayasan', YayasanController::class)->except(['show']);

    // Lembaga CRUD (Super Admin & Admin Yayasan)
    Route::resource('lembaga', LembagaController::class)->except(['show']);

    // User Management (Super Admin & Admin Yayasan)
    Route::middleware('role:super_admin,admin_yayasan')->prefix('user-management')->name('user-management.')->group(function () {
        // Per Yayasan
        Route::get('/yayasan/{yayasan}', [UserManagementController::class, 'indexYayasan'])->name('yayasan');
        Route::get('/yayasan/{yayasan}/create', [UserManagementController::class, 'createYayasan'])->name('yayasan.create');
        Route::post('/yayasan/{yayasan}', [UserManagementController::class, 'storeYayasan'])->name('yayasan.store');
        // Per Lembaga
        Route::get('/lembaga/{lembaga}', [UserManagementController::class, 'indexLembaga'])->name('lembaga');
        Route::get('/lembaga/{lembaga}/create', [UserManagementController::class, 'createLembaga'])->name('lembaga.create');
        Route::post('/lembaga/{lembaga}', [UserManagementController::class, 'storeLembaga'])->name('lembaga.store');
        // Edit/Delete User
        Route::get('/user/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/user/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/user/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    });

    // Tahun Ajaran CRUD (Super Admin & Admin Yayasan)
    Route::resource('tahun-ajaran', TahunAjaranController::class)->except(['show']);

    // Kalender Akademik (Super Admin & Admin Yayasan)
    Route::resource('kalender-akademik', KalenderAkademikController::class)->except(['show']);

    // Log Aktivitas (Super Admin & Admin Yayasan)
    Route::get('/log-aktivitas', [LogAktivitasController::class, 'index'])->name('log-aktivitas.index');

    // Approval Guru (Admin Yayasan only)
    Route::middleware('role:admin_yayasan')->group(function () {
        Route::get('/guru-approval', [GuruController::class, 'approval'])->name('guru.approval');
        Route::put('/guru/{guru}/approve', [GuruController::class, 'approve'])->name('guru.approve');
        Route::put('/guru/{guru}/reject', [GuruController::class, 'reject'])->name('guru.reject');
        Route::post('/guru/bulk-approve', [GuruController::class, 'bulkApprove'])->name('guru.bulk-approve');
        Route::post('/guru/bulk-reject', [GuruController::class, 'bulkReject'])->name('guru.bulk-reject');
    });

    // Sync Siswa dari Sisda API (Admin Yayasan & Admin Lembaga)
    Route::middleware('role:super_admin,admin_yayasan,admin_lembaga')->group(function () {
        Route::get('/sync-siswa', [SiswaSyncController::class, 'index'])->name('sync-siswa.index');
        Route::post('/sync-siswa', [SiswaSyncController::class, 'sync'])->name('sync-siswa.sync');
        Route::post('/sync-siswa/kenaikan-kelas', [SiswaSyncController::class, 'kenaikanKelas'])->name('sync-siswa.kenaikan-kelas');
    });

    // Master Data CRUD (Admin Lembaga, Admin Yayasan, Super Admin)
    Route::middleware('role:super_admin,admin_yayasan,admin_lembaga,kepala_lembaga,kurikulum,kesiswaan')->group(function () {
        Route::resource('guru', GuruController::class)->except(['show']);
        Route::post('guru/import', [GuruController::class, 'import'])->name('guru.import');
        Route::get('guru/template', [GuruController::class, 'template'])->name('guru.template');
        Route::put('guru/{guru}/inline-update', [GuruController::class, 'inlineUpdate'])->name('guru.inline-update');
        Route::post('guru/bulk-update', [GuruController::class, 'bulkUpdate'])->name('guru.bulk-update');
        Route::post('guru/bulk-delete', [GuruController::class, 'bulkDestroy'])->name('guru.bulk-delete');
        Route::get('guru/export', [GuruController::class, 'export'])->name('guru.export');
        Route::resource('siswa', SiswaController::class)->except(['show']);
        Route::resource('kelas', KelasController::class)->except(['show']);
        Route::resource('jurusan', JurusanController::class)->except(['show']);
        Route::resource('jenis-ptk', JenisPtkController::class)->except(['show']);
    });

    // === AKADEMIK — Kurikulum ===
    Route::middleware('role:super_admin,admin_yayasan,kepala_lembaga,admin_lembaga,kurikulum')->group(function () {
        Route::resource('kelompok-mapel', KelompokMapelController::class)->except(['show']);
        Route::resource('mapel', MapelController::class)->except(['show']);
        Route::post('mapel/import', [MapelController::class, 'import'])->name('mapel.import');
        Route::get('mapel/template', [MapelController::class, 'template'])->name('mapel.template');
        Route::resource('pengajaran-mapel', PengajaranMapelController::class)->except(['show']);
        Route::post('pengajaran-mapel/import', [PengajaranMapelController::class, 'import'])->name('pengajaran-mapel.import');
        Route::get('pengajaran-mapel/template', [PengajaranMapelController::class, 'template'])->name('pengajaran-mapel.template');
        // Pengaturan Akademik
        Route::get('/akademik-settings', [AkademikSettingController::class, 'index'])->name('akademik-settings.index');
        Route::put('/akademik-settings', [AkademikSettingController::class, 'update'])->name('akademik-settings.update');
        // Timetable drag-n-drop
        Route::get('/akademik-settings/timetable', [AkademikSettingController::class, 'timetable'])->name('akademik-settings.timetable');
        Route::post('/akademik-settings/timetable', [AkademikSettingController::class, 'saveTimetable'])->name('akademik-settings.timetable.save');
    });

    // === AKADEMIK — CP/TP/ATP (Guru + Kurikulum) ===
    Route::middleware('role:super_admin,admin_yayasan,kepala_lembaga,admin_lembaga,kurikulum,guru')->group(function () {
        Route::resource('cp', CpController::class);
        Route::resource('tp', TpController::class);
        Route::resource('atp', AtpController::class);
    });

    // === AKADEMIK — Jadwal ===
    Route::middleware('role:super_admin,admin_yayasan,kepala_lembaga,admin_lembaga,kurikulum')->group(function () {
        Route::resource('jadwal', JadwalController::class)->except(['show']);
        Route::get('/jadwal-import', [JadwalController::class, 'showImportForm'])->name('jadwal.import.form');
        Route::post('/jadwal-import', [JadwalController::class, 'import'])->name('jadwal.import');
        Route::get('/jadwal-template', [JadwalController::class, 'template'])->name('jadwal.template');
    });

    // === PRESENSI SISWA (Phase 6) ===

    // Presensi — Guru
    Route::middleware('role:guru')->group(function () {
        Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
        Route::get('/presensi/create', [PresensiController::class, 'create'])->name('presensi.create');
        Route::post('/presensi', [PresensiController::class, 'store'])->name('presensi.store');
        Route::get('/presensi/{presensi}', [PresensiController::class, 'show'])->name('presensi.show');
        Route::get('/presensi/{presensi}/edit', [PresensiController::class, 'edit'])->name('presensi.edit');
        Route::put('/presensi/{presensi}', [PresensiController::class, 'update'])->name('presensi.update');
    });

    // Presensi — Rekap (Kurikulum, Kepala Lembaga, Admin Lembaga)
    Route::middleware('role:kurikulum,kepala_lembaga,admin_lembaga')->group(function () {
        Route::get('/presensi-rekap', [PresensiController::class, 'rekap'])->name('presensi.rekap');
    });

    // === PENILAIAN (Phase 7) ===

    // Jenis Nilai CRUD (Kurikulum)
    Route::middleware('role:super_admin,admin_yayasan,kepala_lembaga,admin_lembaga,kurikulum')->prefix('nilai/jenis-nilai')->name('nilai.jenis-nilai.')->group(function () {
        Route::get('/', [NilaiController::class, 'jenisNilaiIndex'])->name('index');
        Route::post('/', [NilaiController::class, 'jenisNilaiStore'])->name('store');
        Route::put('/{jenisNilai}', [NilaiController::class, 'jenisNilaiUpdate'])->name('update');
        Route::delete('/{jenisNilai}', [NilaiController::class, 'jenisNilaiDestroy'])->name('destroy');
    });

    // Input Nilai — Guru
    Route::middleware('role:guru')->group(function () {
        Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai.index');
        Route::get('/nilai/create', [NilaiController::class, 'create'])->name('nilai.create');
        Route::post('/nilai', [NilaiController::class, 'store'])->name('nilai.store');
        Route::get('/nilai/edit', [NilaiController::class, 'edit'])->name('nilai.edit');
        Route::post('/nilai/update', [NilaiController::class, 'update'])->name('nilai.update');
        Route::post('/nilai/finalize', [NilaiController::class, 'finalize'])->name('nilai.finalize');
    });

    // Rekap Nilai (Kurikulum, Kepala Lembaga)
    Route::middleware('role:kurikulum,kepala_lembaga')->group(function () {
        Route::get('/nilai-rekap', [NilaiController::class, 'rekap'])->name('nilai.rekap');
    });

    // === ABSENSI PTK & AGENDA SELFIE (Phase 5) ===

    // Jam Kerja Lembaga (Admin Lembaga only)
    Route::middleware('role:super_admin,admin_yayasan,admin_lembaga')->group(function () {
        Route::put('/jam-kerja/absen-settings', [JamKerjaLembagaController::class, 'updateAbsenSettings'])->name('jam-kerja.absen-settings');
        Route::resource('jam-kerja', JamKerjaLembagaController::class)->except(['show']);
    });

    // Absensi PTK — Check-in/Check-out (Guru)
    Route::middleware('role:guru')->group(function () {
        Route::get('/absensi-ptk', [AbsensiPtkController::class, 'index'])->name('absensi-ptk.index');
        Route::post('/absensi-ptk/check-in', [AbsensiPtkController::class, 'checkIn'])->name('absensi-ptk.check-in');
        Route::post('/absensi-ptk/check-out', [AbsensiPtkController::class, 'checkOut'])->name('absensi-ptk.check-out');
    });

    // Absensi PTK — Laporan (Admin Lembaga, Kepala Lembaga)
    Route::middleware('role:admin_lembaga,kepala_lembaga')->group(function () {
        Route::get('/absensi-ptk/laporan', [AbsensiPtkController::class, 'laporan'])->name('absensi-ptk.laporan');
    });

    // Agenda Mengajar — Selfie (Guru)
    Route::middleware('role:guru')->group(function () {
        Route::get('/agenda-mengajar', [AgendaMengajarController::class, 'index'])->name('agenda-mengajar.index');
        Route::get('/agenda-mengajar/create', [AgendaMengajarController::class, 'create'])->name('agenda-mengajar.create');
        Route::post('/agenda-mengajar', [AgendaMengajarController::class, 'store'])->name('agenda-mengajar.store');
        Route::get('/agenda-mengajar/{agenda}', [AgendaMengajarController::class, 'show'])->name('agenda-mengajar.show');
        Route::delete('/agenda-mengajar/{agenda}', [AgendaMengajarController::class, 'destroy'])->name('agenda-mengajar.destroy');
    });

    // Agenda Mengajar — Monitoring & Verifikasi (Admin/Kepala Lembaga)
    Route::middleware('role:admin_lembaga,kepala_lembaga')->group(function () {
        Route::get('/agenda-mengajar/monitoring', [AgendaMengajarController::class, 'monitoring'])->name('agenda-mengajar.monitoring');
        Route::post('/agenda-mengajar/{agenda}/verify', [AgendaMengajarController::class, 'verify'])->name('agenda-mengajar.verify');
    });

    // === KESISWAAN (P8) ===

    // Kategori Pelanggaran (Kesiswaan, Guru bisa lihat)
    Route::middleware('role:super_admin,admin_yayasan,kepala_lembaga,admin_lembaga,kesiswaan,guru')->prefix('kesiswaan')->name('kesiswaan.')->group(function () {
        Route::resource('kategori-pelanggaran', KategoriPelanggaranController::class)->except(['show', 'create', 'edit']);
    });

    // Pelanggaran (Kesiswaan = full CRUD, Guru = create)
    Route::middleware('role:kesiswaan,guru,kepala_lembaga')->prefix('kesiswaan')->name('kesiswaan.')->group(function () {
        Route::resource('pelanggaran', PelanggaranController::class)->except(['show']);
    });

    // Ekskul (Kesiswaan = full CRUD)
    Route::middleware('role:super_admin,admin_yayasan,kepala_lembaga,admin_lembaga,kesiswaan')->prefix('kesiswaan')->name('kesiswaan.')->group(function () {
        Route::resource('ekskul', EkskulController::class)->except(['show']);
        // Anggota Ekskul — nested under ekskul
        Route::resource('ekskul.anggota-ekskul', AnggotaEkskulController::class)->except(['show', 'edit', 'update']);
    });
});

// === NOTIFIKASI (P9) ===
Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [App\Http\Controllers\NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/{notifikasi}/mark-read', [App\Http\Controllers\NotifikasiController::class, 'markRead'])->name('notifikasi.mark-read');
    Route::post('/notifikasi/mark-all-read', [App\Http\Controllers\NotifikasiController::class, 'markAllRead'])->name('notifikasi.mark-all-read');
    Route::get('/notifikasi-count', [App\Http\Controllers\NotifikasiController::class, 'count'])->name('notifikasi.count');
});

// === LAPORAN (P9) ===
Route::middleware('auth')->prefix('laporan')->name('laporan.')->group(function () {
    Route::get('/', [App\Http\Controllers\LaporanController::class, 'index'])->name('index');

    // Akademik (Nilai)
    Route::get('/akademik', [App\Http\Controllers\LaporanController::class, 'akademik'])->name('akademik');
    Route::get('/akademik/export', [App\Http\Controllers\LaporanController::class, 'exportAkademik'])->name('export-akademik');

    // Kesiswaan
    Route::get('/kesiswaan', [App\Http\Controllers\LaporanController::class, 'kesiswaan'])->name('kesiswaan');
    Route::get('/kesiswaan/export', [App\Http\Controllers\LaporanController::class, 'exportKesiswaan'])->name('export-kesiswaan');

    // Presensi
    Route::get('/presensi', [App\Http\Controllers\LaporanController::class, 'presensi'])->name('presensi');
    Route::get('/presensi/export', [App\Http\Controllers\LaporanController::class, 'exportPresensi'])->name('export-presensi');

    // Absensi PTK
    Route::get('/absensi-ptk', [App\Http\Controllers\LaporanController::class, 'absensiPtk'])->name('absensi-ptk');
    Route::get('/absensi-ptk/export', [App\Http\Controllers\LaporanController::class, 'exportAbsensiPtk'])->name('export-absensi-ptk');

    // Agenda Mengajar
    Route::get('/agenda-mengajar', [App\Http\Controllers\LaporanController::class, 'agendaMengajar'])->name('agenda-mengajar');
    Route::get('/agenda-mengajar/export', [App\Http\Controllers\LaporanController::class, 'exportAgendaMengajar'])->name('export-agenda-mengajar');
});

require __DIR__ . '/auth.php';
