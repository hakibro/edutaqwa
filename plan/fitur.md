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

| Fitur                     | Deskripsi                                                                                                                                    |
| ------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------- |
| **Master Data Guru**      |                                                                                                                                              |
| - CRUD Guru               | Tambah/sunting/hapus data guru ✓                                                                                                             |
| - Import Guru             | Import massal dari XLSX + update massal via export-edit-import ✓                                                                             |
| - Kode Guru               | Kode guru lembaga ditentukan manual oleh admin lembaga saat tambah/edit guru ✓                                                               |
| - Klasifikasi PTK         | Jenis PTK dari DB (dikelola admin lembaga), Tugas Tambahan (Guru Mapel, BK, Wali Kelas, dll)                                                 |
| - Upload Dokumen          | Upload ijazah, SK, sertifikat ✓                                                                                                              |
| **Master Data Siswa**     |                                                                                                                                              |
| - CRUD Siswa              | Tambah/sunting/hapus data siswa (tombol disembunyikan jika Mode API Sisda ON) ✓                                                              |
| - Import Siswa            | Import dari Sisda API (otomatis) ✓                                                                                                           |
| - Kenaikan Kelas          | Ditangani Sisda Yayasan (fitur dinonaktifkan di app)                                                                                         |
| - Mutasi Siswa            | Nonaktif — ditangani Sisda API                                                                                                               |
| - Alumni Tracking         | Nonaktif — ditangani Sisda API                                                                                                               |
| - Upload Foto             | Upload pas foto siswa ✓                                                                                                                      |
| **Master Data Kelas**     | Auto generate dari import Sisda ✓                                                                                                            |
| - CRUD Kelas              | Tambah/sunting/hapus manual (tombol disembunyikan jika Mode API Sisda ON) ✓                                                                  |
| **Master Data Jurusan**   | Auto generate dari import Sisda ✓                                                                                                            |
| - CRUD Jurusan            | Tambah/sunting/hapus manual (tombol disembunyikan jika Mode API Sisda ON) ✓                                                                  |
| **Konfigurasi Sisda**     |                                                                                                                                              |
| - Mode API Sisda          | Toggle di form edit lembaga — jika ON, sembunyikan tombol tambah manual siswa/kelas/jurusan, data hanya via sync API ✓                       |
| **Konfigurasi Jam Kerja** | Atur jam masuk & pulang guru per hari                                                                                                        |
| **Pengaturan Akademik**   | Atur jam mulai, durasi KBM/istirahat, daftar kegiatan (nama + durasi masing-masing), hari efektif. Susunan per hari via drag-drop timetable. |

### 1.4 Akademik — Kurikulum

| Fitur            | Deskripsi                                                                          |
| ---------------- | ---------------------------------------------------------------------------------- |
| **Kelola Mapel** |                                                                                    |
| - Kelompok Mapel | A. Umum, B. Kejuruan, Muatan Lokal, dll                                            |
| - CRUD Mapel     | Daftar mata pelajaran per lembaga                                                  |
| - Penugasan Guru | Assign guru ke mapel per kelas & tahun ajaran                                      |
| **CP/TP/ATP**    | (dibuat oleh guru pengampu)                                                        |
| - CP             | Capaian Pembelajaran per mapel & fase, milik guru                                  |
| - TP             | Tujuan Pembelajaran per CP                                                         |
| - ATP            | Alur TP (urutan mingguan)                                                          |
| **Jadwal**       |                                                                                    |
| - CRUD Jadwal    | Atur jadwal per kelas (hari, jam_ke) — waktu di-resolve dari settings timetable    |
| - Grid Editor    | Edit jadwal langsung dari grid per kelas — click cell pilih mapel+guru, batch save |
| - Import Jadwal  | Import jadwal dari Excel (kolom: kelas, mapel, guru, hari, jam_ke)                 |
| - Cetak Jadwal   | Cetak jadwal kelas & guru                                                          |
| - Cek Bentrok    | Validasi bentrok jadwal otomatis (guru + hari + jam_ke)                            |

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

| Fitur              | Deskripsi                            |
| ------------------ | ------------------------------------ |
| Generate Rapor     | Generate rapor dari nilai & presensi |
| Rapor Draft        | Edit sebelum finalisasi              |
| Rapor Final        | Kunci rapor setelah divalidasi       |
| Catatan Wali Kelas | Input catatan & deskripsi sikap      |
| Catatan BK         | Input catatan pembinaan              |
| Cetak Rapor        | PDF rapor format Kurikulum Merdeka   |
| E-Rapor            | Rapor digital (dibagikan via link)   |

### 1.8 Presensi

| Fitur                  | Deskripsi                       |
| ---------------------- | ------------------------------- |
| Presensi Per Pertemuan | Guru isi kehadiran per jadwal   |
| Presensi Harian        | Rekap kehadiran harian siswa    |
| Laporan Presensi       | Rekap bulanan/semester          |
| Statistik Presensi     | % kehadiran, keterlambatan, dll |

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
| Notifikasi                | Peringatan jika lupa check-in/check-out          |

### 1.10 Agenda Mengajar (Selfie)

| Fitur             | Deskripsi                                        |
| ----------------- | ------------------------------------------------ |
| **Ambil Selfie**  | Guru foto selfie di depan kelas saat mengajar    |
| Metadata Otomatis | Timestamp, lokasi GPS, jadwal_id melekat di foto |
| Verifikasi Jadwal | Selfie hanya bisa saat jam sesuai jadwal         |
| Galeri Agenda     | Riwayat foto mengajar per guru per pertemuan     |
| Monitoring        | Kepala Lembaga / Kurikulum lihat bukti mengajar  |
| Export            | Unduh arsip foto per mapel/periode               |

### 1.11 Dashboard

| Role           | Konten Dashboard                                                    |
| -------------- | ------------------------------------------------------------------- |
| Super Admin    | Jumlah yayasan, lembaga, pengguna aktif, storage                    |
| Admin Yayasan  | Statistik per lembaga, approval pending, tahun ajaran aktif         |
| Kepala Lembaga | Grafik kinerja akademik, presensi, absensi PTK, peringkat kelas     |
| Admin Lembaga  | Jumlah guru/siswa aktif, status import, jadwal hari ini             |
| Kurikulum      | Progress CP/TP/ATP, jadwal, distribusi mapel                        |
| Kesiswaan      | Jumlah siswa per kelas, grafik mutasi, statistik pelanggaran        |
| Guru           | Jadwal mengajar hari ini, daftar kelas, rekap presensi, absensi PTK |
| Siswa          | Jadwal hari ini, nilai terbaru, notifikasi                          |
| Orang Tua      | Presensi anak, nilai anak, jadwal, pelanggaran                      |

### 1.12 Notifikasi

| Tipe                      | Penerima                  | Trigger                     |
| ------------------------- | ------------------------- | --------------------------- |
| Approval Guru Pending     | Admin Yayasan             | Guru baru daftar            |
| Approval Disetujui        | Guru                      | Admin yayasan approve       |
| Jadwal Hari Ini           | Guru, Siswa               | Setiap hari jam 06:00       |
| Nilai Diinput             | Siswa, Orang Tua          | Guru input nilai            |
| Pelanggaran               | Orang Tua, BK             | Pelanggaran dicatat         |
| Presensi Alpha > 3        | Wali Kelas, BK, Orang Tua | Akumulasi alpha             |
| Jadwal PTS/PAS            | Semua                     | Mendekati PTS/PAS           |
| Lupa Check-in/Check-out   | Guru, Kepala Lembaga      | Lewat jam tanpa absen       |
| Selfie Agenda Belum Diisi | Guru, Kurikulum           | Jadwal selesai tanpa selfie |

## 2. Prioritas Fitur Berdasarkan Urutan Development

| Phase                         | Fitur                                                                                                 |
| ----------------------------- | ----------------------------------------------------------------------------------------------------- |
| **P1 — Foundation**           | Auth & RBAC, Multi-tenant, Manajemen Yayasan, Manajemen Lembaga, Master Data (Guru, Siswa via import) |
| **P2 — Core Akademik**        | Mapel + CP/TP/ATP, Jadwal                                                                             |
| **P3 — Absensi PTK & Selfie** | Konfigurasi Jam Kerja, Absensi PTK check-in/check-out, Agenda Selfie                                  |
| **P4 — Presensi**             | Presensi per pertemuan, rekap, notifikasi alpha                                                       |
| **P5 — Penilaian**            | Nilai Harian, PTS, PAS, UKK                                                                           |
| **P6 — Kesiswaan**            | Mutasi, Pelanggaran, Ekstrakurikuler, Alumni                                                          |
| **P7 — Advanced**             | Notifikasi, Dashboard, Kalender Akademik, Laporan-laporan                                             |
| **P8 — Rapor**                | Generate rapor, cetak PDF, E-Rapor                                                                    |
