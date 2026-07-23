# Fitur — Aplikasi KBM Multi-Lembaga

## 1. Modul & Fitur Detail

### 1.1 Manajemen Platform (Super Admin)

| Fitur                | Deskripsi                                                 |
| -------------------- | --------------------------------------------------------- |
| Registrasi Yayasan   | Mendaftarkan yayasan baru + auto-create admin yayasan     |
| Konfigurasi Platform | Pengaturan global (kurikulum default, format kode, dll)   |
| Monitoring Lembaga   | Lihat statistik seluruh lembaga                           |
| Log Aktivitas        | Audit trail seluruh aktivitas penting                     |
| Manajemen Pengguna   | Kelola user per yayasan & per lembaga (tambah/edit/hapus) |

### 1.2 Manajemen Yayasan (Admin Yayasan)

| Fitur              | Deskripsi                                                                           |
| ------------------ | ----------------------------------------------------------------------------------- |
| CRUD Lembaga       | Tambah/sunting/hapus lembaga di bawah yayasan                                       |
| Import Lembaga     | Ambil data lembaga dari API Akademik (apiakademik /lembaga) di halaman edit yayasan |
| Approval Guru Baru | Verifikasi & setujui guru baru sebelum aktif (individual & bulk)                    |
| Approval Satminkal | Verifikasi guru satminkal, kode satminkal ditentukan admin lembaga                  |
| Tahun Ajaran       | CRUD tahun ajaran (mencakup Ganjil & Genap)                                         |
| Kalender Akademik  | Hari efektif, libur, jadwal PTS/PAS tingkat yayasan                                 |

### 1.3 Manajemen Lembaga (Admin Lembaga / TU)

| Fitur                     | Deskripsi                                                                                                                                                                                                         |
| ------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Master Data Guru**      |                                                                                                                                                                                                                   |
| - CRUD Guru               | Tambah/sunting/hapus data guru ✓                                                                                                                                                                                  |
| - Import Guru             | Import massal dari XLSX + update massal via export-edit-import ✓                                                                                                                                                  |
| - Kode Guru               | Kode guru lembaga ditentukan manual oleh admin lembaga saat tambah/edit guru ✓                                                                                                                                    |
| - Klasifikasi PTK         | Jenis PTK dari DB (dikelola admin lembaga), Tugas Tambahan (Guru Mapel, BK, Wali Kelas, dll)                                                                                                                      |
| - Upload Dokumen          | Upload ijazah, SK, sertifikat ✓                                                                                                                                                                                   |
| - Reset Password Guru     | Admin lembaga bisa reset password user guru (buat akun jika belum ada), guru wajib ganti password saat login pertama ✓                                                                                            |
| **Master Data Siswa**     |                                                                                                                                                                                                                   |
| - CRUD Siswa              | Tambah/sunting/hapus data siswa (tombol disembunyikan jika Mode API Sisda ON) ✓                                                                                                                                   |
| - Import Siswa            | Import dari Sisda API (otomatis) ✓                                                                                                                                                                                |
| - Kenaikan Kelas          | Ditangani Sisda Yayasan (fitur dinonaktifkan di app)                                                                                                                                                              |
| - Mutasi Siswa            | Nonaktif — ditangani Sisda API                                                                                                                                                                                    |
| - Alumni Tracking         | Nonaktif — ditangani Sisda API                                                                                                                                                                                    |
| - Upload Foto             | Upload pas foto siswa ✓                                                                                                                                                                                           |
| **Master Data Kelas**     | Auto generate dari import Sisda ✓                                                                                                                                                                                 |
| - CRUD Kelas              | Tambah/sunting/hapus manual (tombol disembunyikan jika Mode API Sisda ON) ✓                                                                                                                                       |
| **Master Data Jurusan**   | Auto generate dari import Sisda ✓                                                                                                                                                                                 |
| - CRUD Jurusan            | Tambah/sunting/hapus manual (tombol disembunyikan jika Mode API Sisda ON) ✓                                                                                                                                       |
| **Konfigurasi Sisda**     |                                                                                                                                                                                                                   |
| - Mode API Sisda          | Toggle di form edit lembaga — jika ON, sembunyikan tombol tambah manual siswa/kelas/jurusan, data hanya via sync API ✓                                                                                            |
| **Konfigurasi Jam Kerja** | Atur jam masuk & pulang guru per hari                                                                                                                                                                             |
| **Pengaturan Akademik**   | Atur jam mulai, durasi KBM/istirahat, daftar kegiatan (nama + durasi masing-masing), hari efektif. Susunan per hari via drag-drop timetable.                                                                      |
| **Pengumuman**            |                                                                                                                                                                                                                   |
| - Kelola Pengumuman       | Admin lembaga membuat/mengedit/menghapus pengumuman menggunakan Editor.js (rich text + gambar).                                                                                                                   |
| - Popup Dashboard Guru    | Guru melihat pengumuman aktif sebagai popup saat login, bisa dismiss. Session-based read tracking.                                                                                                                |
| - Layout Mobile-First     | Dashboard guru pakai layout khusus (guru-layout): bottom navbar 5 item di mobile (`< lg`), sidebar desktop tetap (`lg:`). Nav items: Dashboard, Jurnal, Jadwal, Absensi, Profil. Notifikasi badge di icon Profil. |

### 1.4 Akademik — Kurikulum

| Fitur                       | Deskripsi                                                                                                                                                                                                   |
| --------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Kelola Mapel**            |                                                                                                                                                                                                             |
| - Kelompok Mapel            | A. Umum, B. Kejuruan, Muatan Lokal, dll                                                                                                                                                                     |
| - CRUD Mapel                | Daftar mata pelajaran per lembaga                                                                                                                                                                           |
| - Penugasan Guru            | Assign guru ke mapel per kelas & tahun ajaran                                                                                                                                                               |
| **Perangkat Ajar**          | (CP/TP/ATP/Modul Ajar — 1 halaman dengan 4 tab)                                                                                                                                                             |
| - CP                        | Capaian Pembelajaran per mapel & fase, milik guru                                                                                                                                                           |
| - TP                        | Tujuan Pembelajaran per CP                                                                                                                                                                                  |
| - ATP                       | Alur TP (urutan mingguan)                                                                                                                                                                                   |
| - Modul Ajar                | Upload dokumen (doc/docx/pdf) per mapel, upload manual via form                                                                                                                                             |
| - Import Excel Komprehensif | 1 file XLSX, 3 sheet: CP (mapel_kode dropdown, fase, kode, deskripsi), TP (cp_kode, kode, deskripsi), ATP (tp_kode, minggu_ke, materi). Sheet CP & TP wajib, ATP opsional. Kode saling tertaut antar sheet. |
| - UI: Stat cards            | 4 kartu ringkasan (CP, TP, ATP, Modul Ajar) dengan ikon & warna aksen                                                                                                                                       |
| - UI: Filter per tab        | Dropdown filter mapel di header setiap tab, auto-submit on change                                                                                                                                           |
| - UI: Import popup          | Tombol Import Excel di header halaman (sejajar judul), membuka modal popup                                                                                                                                  |
| - UI: Preview dokumen       | Halaman lihat dokumen dengan preview PDF/HTML, layout konsisten                                                                                                                                             |
| **Jadwal**                  |                                                                                                                                                                                                             |
| - CRUD Jadwal               | Atur jadwal per kelas (hari, jam_ke) — waktu di-resolve dari settings timetable                                                                                                                             |
| - Grid Editor               | Edit jadwal langsung dari grid per kelas — click cell pilih mapel+guru, batch save                                                                                                                          |
| - Import Jadwal             | Import jadwal dari Excel (kolom: kelas, mapel, guru, hari, jam_ke) — backup jurnal dulu, hapus & insert ulang, lalu reassign jurnal ke jadwal baru berdasarkan guru_id+kelas_id+mapel_id ✓                  |
| - Export Jadwal             | Export jadwal ke Excel (format sama template import), siap edit & re-import ✓                                                                                                                               |
| - Cetak Jadwal              | Cetak jadwal kelas & guru                                                                                                                                                                                   |
| - Cek Bentrok               | Validasi bentrok jadwal otomatis (guru + hari + jam_ke)                                                                                                                                                     |
| **Guru Pengganti**          |                                                                                                                                                                                                             |
| - Pengajuan Pengganti       | Guru mengajukan guru pengganti per jadwal & per tanggal (multi-select) + alasan                                                                                                                             |
| - Approval Pengganti        | Kurikulum menyetujui/menolak pengajuan guru pengganti + catatan                                                                                                                                             |
| - Batalkan Pengajuan        | Guru membatalkan pengajuan yang belum diproses                                                                                                                                                              |
| - Riwayat Pengganti         | Guru melihat riwayat pengajuan pengganti (status: diajukan/disetujui/ditolak/dibatalkan)                                                                                                                    |
| - Jurnal Sebagai Pengganti  | Guru pengganti mengisi jurnal mengajar untuk jadwal yang digantikan (badge "Pengganti", metadata is_substitute)                                                                                             |
| - Monitoring Pengganti      | Kurikulum melihat daftar pengganti aktif per tanggal                                                                                                                                                        |
| - Notifikasi                | Notifikasi ke Kurikulum saat ada pengajuan baru; notifikasi ke guru pengaju & guru pengganti saat disetujui/ditolak                                                                                         |
| **Perangkat Ajar v2**       | (Paralel dengan sistem lama — lihat P16 roadmap)                                                                                                                                                            |
| - Konsep Benar              | CP → TP (1:N, TP punya urutan). ATP = header menampung banyak TP via pivot `mapel_atp_tps`. Modul Ajar → TP (M:N via `mapel_modul_tps`).                                                                    |
| - Tabel Baru (prefix mapel) | `mapel_cps`, `mapel_tps`, `mapel_atps`, `mapel_atp_tps`, `mapel_modul_ajars`, `mapel_modul_tps` — paralel dengan tabel lama                                                                                 |
| - UI ATP v2                 | Card per ATP header, expand lihat daftar TP terurut, drag-drop urutan                                                                                                                                       |
| - UI Modul Ajar v2          | Multi-select TP saat create/edit modul                                                                                                                                                                      |
| - Migrasi Data              | Self-service: tombol "Pindahkan Data Saya" — copy dari tabel lama ke baru. ATP lama (per-TP) di-grouping jadi ATP header.                                                                                   |
| - Transisi                  | Dua sistem jalan paralel, menu "Perangkat Ajar (Baru)" di sidebar                                                                                                                                           |

### 1.5 Kesiswaan

| Fitur             | Deskripsi                                      |
| ----------------- | ---------------------------------------------- |
| Data Siswa        | Lihat data lengkap siswa per kelas             |
| Pelanggaran       | Catat pelanggaran, poin, pembinaan (dengan BK) |
| Ekstrakurikuler   | Kelola ekskul, anggota, absensi ekskul         |
| Laporan Kesiswaan | Rekap siswa per kelas, mutasi, dll             |

### 1.6 Penilaian (Guru)

| Fitur               | Deskripsi                             |
| ------------------- | ------------------------------------- |
| Input Nilai Harian  | Nilai tugas, ulangan harian per KD/TP |
| Input Nilai PTS     | Nilai Penilaian Tengah Semester       |
| Input Nilai PAS     | Nilai Penilaian Akhir Semester        |
| Input Nilai UKK     | Nilai Ujian Kenaikan Kelas            |
| Analisis Nilai      | Grafik, distribusi nilai, ketuntasan  |
| Cetak Laporan Nilai | Laporan hasil belajar per siswa/kelas |

### 1.7 Rapor

| Fitur               | Deskripsi                                                                                                |
| ------------------- | -------------------------------------------------------------------------------------------------------- |
| Generate Rapor      | Generate rapor dari nilai & presensi                                                                     |
| Rapor Draft         | Edit sebelum finalisasi                                                                                  |
| Rapor Final         | Kunci rapor setelah divalidasi                                                                           |
| Catatan Wali Kelas  | Input catatan & deskripsi sikap                                                                          |
| Catatan BK          | Input catatan pembinaan                                                                                  |
| Cetak Rapor         | PDF rapor format Kurikulum Merdeka                                                                       |
| E-Rapor             | Rapor digital (dibagikan via link)                                                                       |
| **Kokurikuler**     | **Nilai kegiatan di luar mapel akademik — sub dari rapor**                                               |
| - Jenis Kokurikuler | CRUD kategori: Pramuka, PMR, Seni Tari, Olahraga, KIR, Paskibra, dll (per lembaga)                       |
| - Input Nilai       | Guru/Pembina input nilai per siswa per kokurikuler (predikat: Sangat Baik/Baik/Cukup/Kurang + deskripsi) |
| - Tampil di Rapor   | Tercetak di lembar rapor sebagai komponen kokurikuler                                                    |
| - Batch Input       | Input nilai 1 kokurikuler untuk seluruh siswa sekaligus                                                  |
| - Rekap per Siswa   | Wali kelas lihat ringkasan kokurikuler semua siswa di kelasnya                                           |

### 1.8 Presensi (DIGABUNG ke 1.11 Jurnal Mengajar)

| Fitur                  | Deskripsi                                                                   |
| ---------------------- | --------------------------------------------------------------------------- |
| Presensi Per Pertemuan | Guru isi kehadiran per jadwal — sekarang bagian dari wizard Jurnal Mengajar |
| Presensi Harian        | Rekap kehadiran harian siswa — sekarang bagian dari Jurnal Mengajar         |
| Laporan Presensi       | Rekap bulanan/semester                                                      |
| Statistik Presensi     | % kehadiran, keterlambatan, dll                                             |

### 1.9 Absensi PTK (Kehadiran Harian Guru)

| Fitur                     | Deskripsi                                        |
| ------------------------- | ------------------------------------------------ |
| **Konfigurasi Jam Kerja** | Admin Lembaga set jam masuk & pulang per hari    |
| **Setting Absensi**       | Lokasi absen, radius GPS, toggle wajib selfie    |
| Check-in                  | Guru check-in saat tiba (QR / tombol)            |
| Check-out                 | Guru check-out saat pulang                       |
| Status Kehadiran          | Tepat waktu, Terlambat, Pulang awal, Tidak absen |
| Multi Sesi                | Jam berbeda per hari (Senin-Jumat vs Sabtu)      |
| Riwayat Absensi           | Rekap harian/bulanan per guru                    |
| Laporan Absensi PTK       | Export rekap kehadiran guru                      |

### 1.8B Dashboard Wali Kelas v2 (Redesign Komprehensif)

Halaman wali kelas saat ini hanya menampilkan stat cards + tabel presensi + quick links. Versi baru mengubahnya menjadi dashboard komprehensif dengan 5 area utama, memberi wali kelas visibilitas penuh terhadap siswa di kelasnya.

| Fitur                         | Deskripsi                                                                                              |
| ----------------------------- | ------------------------------------------------------------------------------------------------------ |
| **Ringkasan Kelas**           | Stat cards: jumlah siswa (L/P), wali kelas, tahun ajaran, badge semester aktif                         |
| **Presensi & Kehadiran**      | Grafik batang/bulan hadir vs tidak hadir, filter bulan, highlight siswa bermasalah (>3 alpha berturut) |
| **Rapor & Nilai**             | Quick view nilai akhir per mapel per siswa, rata-rata kelas, link ke halaman rapor penuh               |
| **Tata Tertib & Pelanggaran** | Daftar pelanggaran terbaru di kelas, total poin per siswa, threshold warning (poin >50)                |
| **Perizinan Siswa**           | Riwayat sakit/izin siswa di kelas, badge "Sakit Hari Ini" / "Izin Hari Ini", filter tanggal            |
| **Profil Siswa Detail**       | Klik baris siswa → panel detail: foto, NISN, kontak orang tua, riwayat kelas, ringkasan presensi       |
| **Quick Actions**             | Tombol aksi cepat: input rapor, catat pelanggaran, lihat presensi, hubungi orang tua                   |
| **Tabel Siswa Interaktif**    | Sort, search, filter jenis kelamin, highlight baris siswa alpha >3, badge status                       |
| **Mobile Responsive**         | Card stack view di mobile, tab navigasi antar area, swipe antar siswa                                  |

### 1.10 Agenda Mengajar (Selfie) — DIGABUNG ke 1.11 Jurnal Mengajar

| Fitur             | Deskripsi                                            |
| ----------------- | ---------------------------------------------------- |
| **Ambil Selfie**  | Sekarang bagian dari wizard Jurnal Mengajar (Step 1) |
| Metadata Otomatis | Timestamp, lokasi GPS, jadwal_id melekat di foto     |
| Verifikasi Jadwal | Selfie hanya bisa saat jam sesuai jadwal             |
| Galeri Agenda     | Riwayat foto mengajar per guru per pertemuan         |
| Monitoring        | Kepala Lembaga / Kurikulum lihat bukti mengajar      |
| Export            | Unduh arsip foto per mapel/periode                   |

### 1.11 Jurnal Mengajar (Phase 10 — Gabungan Selfie + Presensi)

| Fitur                  | Deskripsi                                                                                                                                                           |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Wizard 3 Langkah**   | Step 1: Selfie, Step 2: Presensi Siswa, Step 3: Materi & Simpan                                                                                                     |
| Camera Capture         | Foto dari kamera depan device (wajib), tanpa upload file                                                                                                            |
| GPS Otomatis           | Geolokasi auto-fill dari browser, wajib diisi                                                                                                                       |
| Presensi Cepat         | "Semua Hadir" / "Semua Tidak Hadir" button                                                                                                                          |
| **Presensi Sederhana** | Guru kelas hanya memilih **Hadir** atau **Tidak Hadir** per siswa. Status sakit/izin ditentukan oleh Validator Presensi. Siswa tidak hadir tanpa perizinan = Alpha. |
| Materi Pertemuan       | Input materi yang diajarkan                                                                                                                                         |
| **ATP Opsional**       | Pilih ATP (Alur Tujuan Pembelajaran) terkait pertemuan — tampilkan info CP & TP. Opsional, tidak semua guru punya ATP.                                              |
| Cek Duplikat           | 1 jurnal per jadwal per hari                                                                                                                                        |
| Edit Jurnal            | Edit materi + presensi + ATP untuk jurnal belum diverifikasi                                                                                                        |
| Monitoring             | Filter guru, tanggal, status verifikasi                                                                                                                             |
| **Monitoring v2**      | **Peningkatan view monitoring agar petugas mudah memahami keseluruhan data dalam satu halaman:**                                                                    |
|                        | - Summary Cards: Total Jurnal, Terverifikasi, Belum Verifikasi, Belum Mengisi (klik = auto-filter)                                                                  |
|                        | - Progress Bar per Kelas: % jurnal terisi vs total jadwal (contoh: "X-A: 5/8")                                                                                      |
|                        | - Tabel Compact: 1 baris per guru+kelas, tampilkan semua info (kelas, guru, mapel, jam, status, ringkasan presensi) tanpa accordion                                 |
|                        | - Highlight Warna: merah muda (pending), hijau muda (verified), merah (belum mengisi)                                                                               |
|                        | - Quick Filter Chips: Hari Ini, Kemarin, Minggu Ini, Belum Verifikasi, per tingkat kelas                                                                            |
|                        | - Statistik Presensi Inline: Hadir: X, Tidak Hadir: Y (Sakit: A, Izin: B) per jurnal                                                                                |
|                        | - Timestamp & Badge ATP: jam mengajar + badge 📚 ATP jika ada                                                                                                       |
|                        | - Export ke Excel untuk data yang sedang difilter                                                                                                                   |
|                        | - Auto-refresh toggle (setiap 5 menit)                                                                                                                              |
|                        | - Responsive Card View di mobile                                                                                                                                    |
| Verifikasi             | Kurikulum/Kepala Lembaga verifikasi jurnal (individual, bulk, undo)                                                                                                 |
| Backward Compat        | Data lama (agenda_mengajars, presensis) tetap bisa diakses                                                                                                          |

### 1.11B Validator Presensi Siswa (Phase 12 — Perizinan) ✅ Diimplementasikan 2026-07-23

| Fitur                 | Deskripsi                                                                     |
| --------------------- | ----------------------------------------------------------------------------- |
| **Permission Guru**   | `perizinan_siswa` — dikenali via `tugas_tambahans.jenis` = 'Perizinan Siswa'  |
| Daftar Perizinan      | Filter per kelas, tanggal, jenis (Sakit/Izin)                                 |
| Input Perizinan       | Pilih kelas → AJAX load siswa → pilih siswa, tanggal, jenis, keterangan       |
| Auto-Override         | Set perizinan → otomatis update `detail_jurnal_siswas.status` jika jurnal ada |
| Hapus Perizinan       | Balikkan status jurnal ke alpha                                               |
| Notifikasi Wali Kelas | Otomatis kirim notifikasi saat siswa diset sakit/izin                         |
| **Integrasi Jurnal**  | Guru isi jurnal: hanya Hadir/Tidak Hadir. Perizinan tentukan sakit/izin.      |
|                       | Tidak hadir + tidak ada perizinan → otomatis Alpha.                           |

### 1.11 Dashboard

| Role           | Konten Dashboard                                                                                |
| -------------- | ----------------------------------------------------------------------------------------------- |
| Super Admin    | Jumlah yayasan, lembaga, pengguna aktif, storage                                                |
| Admin Yayasan  | Statistik per lembaga, approval pending, tahun ajaran aktif                                     |
| Kepala Lembaga | Grafik kinerja akademik, presensi, absensi PTK, peringkat kelas                                 |
| Admin Lembaga  | Jumlah guru/siswa aktif, status import, jadwal hari ini                                         |
| Kurikulum      | Progress CP/TP/ATP, jadwal, distribusi mapel                                                    |
| Kesiswaan      | Jumlah siswa per kelas, grafik mutasi, statistik pelanggaran                                    |
| Guru           | Jadwal hari ini (link buat jurnal), jurnal terbaru, rekap jurnal (harian/mingguan), absensi PTK |
| Siswa          | Jadwal hari ini, nilai terbaru, notifikasi                                                      |
| Orang Tua      | Presensi anak, nilai anak, jadwal, pelanggaran                                                  |

### 1.12 Notifikasi (Phase 13 — Terpusat)

| Tipe                         | Penerima                     | Trigger                                 |
| ---------------------------- | ---------------------------- | --------------------------------------- |
| Approval Guru Pending        | Admin Yayasan                | Guru baru daftar                        |
| Approval Disetujui           | Guru                         | Admin yayasan approve                   |
| Jadwal Hari Ini              | Guru, Siswa                  | Setiap hari jam 06:00                   |
| Nilai Diinput                | Siswa, Orang Tua             | Guru input nilai                        |
| Pelanggaran — Batas Poin     | Orang Tua, BK                | Poin pelanggaran melebihi batas         |
| Presensi Alpha > 3           | Wali Kelas, BK, Orang Tua    | Akumulasi alpha berturut-turut          |
| Jadwal PTS/PAS               | Semua                        | Mendekati PTS/PAS                       |
| Lupa Check-in/Check-out      | Guru, Kepala Lembaga         | Lewat jam tanpa absen                   |
| Pengajuan Guru Pengganti     | Kurikulum                    | Guru ajukan pengganti                   |
| Approval Pengganti Disetujui | Guru Pengaju, Guru Pengganti | Kurikulum setujui pengajuan             |
| Approval Pengganti Ditolak   | Guru Pengaju                 | Kurikulum tolak pengajuan               |
| Perizinan Siswa              | Wali Kelas                   | Siswa di kelas walinya diset sakit/izin |
| Siswa Alpha > 3 (Validator)  | Validator Presensi           | Siswa alpha > 3 hari tanpa perizinan    |
| Email (opsional)             | Semua                        | Ringkasan mingguan                      |
| WhatsApp (opsional)          | Semua                        | Notifikasi penting                      |

### 1.8 Menu Khusus Guru — Tugas Tambahan

| Fitur                  | Role        | Deskripsi                                                                   |
| ---------------------- | ----------- | --------------------------------------------------------------------------- |
| **Wali Kelas**         | Wali Kelas  | Dashboard monitoring siswa per kelas: presensi, nilai ringkasan, link cepat |
| - Daftar Siswa         | Wali Kelas  | Lihat daftar siswa di kelas walinya (foto, NISN, nama, JK)                  |
| - Ringkasan Presensi   | Wali Kelas  | Rekap hadir/sakit/izin/alpha bulan berjalan                                 |
| - Link Rapor           | Wali Kelas  | Link ke input nilai & catatan wali kelas di rapor                           |
| - Link Pelanggaran     | Wali Kelas  | Link ke halaman pelanggaran siswa kelas walinya                             |
| **Dashboard BK**       | BK/Konselor | Dashboard monitoring pelanggaran siswa di seluruh lembaga                   |
| - Statistik            | BK          | Total pelanggaran, poin, siswa bermasalah, siswa bersih                     |
| - Filter Kelas         | BK          | Filter siswa per kelas                                                      |
| - Filter TA            | BK          | Filter per tahun ajaran                                                     |
| - Daftar Siswa         | BK          | Semua siswa dengan jumlah pelanggaran & poin, link ke detail                |
| - Pelanggaran/Kategori | BK          | Statistik pelanggaran per kategori (bar chart sederhana)                    |

### 1.9 Navigasi Guru Dinamis

| Fitur                 | Deskripsi                                                           |
| --------------------- | ------------------------------------------------------------------- |
| Bottom Nav (Mobile)   | Item Wali Kelas / BK muncul otomatis jika guru punya tugas tambahan |
| Sidebar (Desktop)     | Sama, menu muncul dinamis di sidebar bagian "Kehadiran & Mengajar"  |
| Kolom grid bottom nav | Otomatis menyesuaikan (5/6/7 kolom tergantung ada menu tambahan)    |

## 2. Prioritas Fitur Berdasarkan Urutan Development

| Phase                         | Fitur                                                                                                 |
| ----------------------------- | ----------------------------------------------------------------------------------------------------- |
| **P1 — Foundation**           | Auth & RBAC, Multi-tenant, Manajemen Yayasan, Manajemen Lembaga, Master Data (Guru, Siswa via import) |
| **P2 — Core Akademik**        | Mapel + CP/TP/ATP, Jadwal                                                                             |
| **P3 — Absensi PTK & Selfie** | Konfigurasi Jam Kerja, Absensi PTK check-in/check-out, Agenda Selfie                                  |
| **P4 — Presensi**             | Presensi per pertemuan, rekap                                                                         |
| **P5 — Penilaian**            | Nilai Harian, PTS, PAS, UKK                                                                           |
| **P6 — Kesiswaan**            | Mutasi, Pelanggaran, Ekstrakurikuler, Alumni                                                          |
| **P7 — Advanced**             | Dashboard, Kalender Akademik, Laporan-laporan                                                         |
| **P8 — Rapor**                | Generate rapor, cetak PDF, E-Rapor                                                                    |
| **P9 — Notifikasi**           | Notifikasi terpusat (in-app, email, WhatsApp)                                                         |

# Belum Pernah disentuh
