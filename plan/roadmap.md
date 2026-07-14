# Roadmap — Aplikasi KBM Multi-Lembaga

Checklist pengembangan berdasarkan prioritas. Centang item yang sudah selesai.

---

## P1 — Foundation

### 1.1 Setup Project

- [x] Setup Laravel 13.x + konfigurasi
- [x] Setup database MySQL
- [x] Setup Vite + Tailwind CSS
- [x] Setup Laravel Breeze (auth scaffold)
- [x] Konfigurasi .env & environment

### 1.2 Database & Model

- [x] Migration tabel: yayasans, lembagas, tahun_ajarans
- [x] Migration tabel: jurusans, kelas, gurus, tugas_tambahans
- [x] Migration tabel: siswas, riwayat_kelas_siswas
- [x] Migration tabel: kelompok_mapels, mapels, pengajaran_mapels
- [x] Migration tabel: cps, tps, atps
- [x] Migration tabel: jadwals
- [x] Migration tabel: presensis, detail_presensis
- [x] Migration tabel: jam_kerja_lembagas, absensi_ptks, agenda_mengajars
- [x] Migration tabel: jenis_nilais, nilais
- [x] Migration tabel: rapors
- [x] Migration tabel: ekskuls, anggota_ekskuls
- [x] Migration tabel: kategoris_pelanggarans, pelanggarans
- [x] Migration tabel: users
- [x] Model + Eloquent relationship semua tabel
- [x] Factory & Seeder data dummy

### 1.3 Auth & RBAC

- [x] Role enum: super_admin, admin_yayasan, kepala_lembaga, admin_lembaga, kurikulum, kesiswaan, guru, siswa, orang_tua
- [x] Middleware CheckRole
- [x] Global scope LembagaScope (multi-tenant)
- [x] Redirect based on role setelah login

---

## P2 — Manajemen Yayasan & Lembaga

### 2.1 Super Admin

- [x] CRUD Yayasan
- [x] Auto-create admin_yayasan user saat tambah yayasan
- [x] User Management per Yayasan (tambah/edit/hapus user tingkat yayasan)
- [x] User Management per Lembaga (tambah/edit/hapus user tingkat lembaga)
- [x] Auto-create admin_lembaga user saat tambah lembaga
- [x] Dashboard Super Admin
- [x] Log aktivitas

### 2.2 Admin Yayasan

- [x] CRUD Lembaga
- [x] CRUD Tahun Ajaran (set active, nonaktifkan jadwal & riwayat tahun lalu)
- [x] Approval Guru Baru (daftar pending, setujui/tolak)
- [x] Approval Satminkal (generate kode satminkal otomatis saat approve)
- [x] Kalender Akademik (hari efektif, libur)

---

## P3 — Master Data

### 3.1 Admin Lembaga — Guru

- [x] CRUD Guru
- [x] TMT (Tanggal Mulai Tugas) field
- [x] Kode Guru Lembaga (generate otomatis)
- [x] Klasifikasi PTK (jenis PTK, satminkal/non)
- [x] Import Guru (XLSX)
- [x] Upload dokumen guru

### 3.2 Admin Yayasan — Approval Guru

- [x] NIY (Nomor Induk Yayasan) generate otomatis saat approve
- [x] Format NIY: YYYY[KodeSisda][NN] — contoh 20260701
- [x] NIY sebagai username login guru
- [x] Kode Sisda (idunit) disimpan di lembaga.kode_sisda

### 3.2 Admin Lembaga — Siswa

- [x] CRUD Siswa
- [x] Import dari Sisda API (mapping + deduplikasi)
- [x] Sync kenaikan kelas via Sisda API (setelah tahun ajaran baru aktif)
- [x] Upload foto siswa
- [x] Mutasi siswa: pindah masuk
- [x] Mutasi siswa: pindah keluar
- [x] Alumni tracking

### 3.3 Admin Lembaga — Kelas & Jurusan

- [x] CRUD Kelas (manual)
- [x] CRUD Jurusan (manual)
- [x] Auto generate kelas & jurusan dari import Sisda

---

## P4 — Akademik

### 4.1 Kurikulum — Mapel

- [x] CRUD Kelompok Mapel
- [x] CRUD Mapel
- [x] Import Mapel (XLSX)
- [x] Penugasan Guru ke Mapel (per kelas & tahun ajaran)
- [x] Import Penugasan Guru ke Mapel (XLSX)

### 4.2 CP / TP / ATP (oleh guru pengampu)

- [x] CRUD CP (Capaian Pembelajaran) — milik guru, terikat mapel
- [x] CRUD TP (Tujuan Pembelajaran) — milik guru
- [x] CRUD ATP (Alur Tujuan Pembelajaran) — milik guru
- [x] Isolasi: guru A tidak bisa edit/melihat CP/TP/ATP guru lain
- [x] Kurikulum & Kepala Lembaga read-only monitoring

### 4.3 Jadwal

- [x] CRUD Jadwal (grid view per kelas)
- [x] Cek bentrok guru & ruangan
- [x] Import jadwal (Excel)
- [ ] Cetak jadwal kelas & guru

---

## P5 — Absensi PTK & Agenda Selfie

### 5.1 Jam Kerja

- [x] Konfigurasi jam masuk/pulang per hari (Admin Lembaga)
- [x] Toleransi keterlambatan (menit)

### 5.2 Absensi PTK

- [x] Check-in (tombol)
- [x] Check-out (tombol)
- [x] Status kehadiran otomatis (tepat waktu / terlambat / pulang awal / tidak absen)
- [x] Riwayat absensi per guru
- [ ] Notifikasi lupa check-in/check-out

### 5.3 Agenda Selfie

- [x] Ambil selfie + metadata (timestamp, GPS, jadwal_id)
- [x] Verifikasi jadwal (hanya bisa saat hari sesuai)
- [x] Galeri agenda per guru
- [x] Monitoring oleh Kurikulum / Kepala Lembaga
- [x] Verifikasi agenda (Kurikulum/Kepala Lembaga)

---

## P6 — Presensi Siswa

### 6.1 Presensi Per Pertemuan

- [x] Guru pilih jadwal → daftar siswa
- [x] Input kehadiran: hadir, sakit, izin, alpha, terlambat
- [x] Input materi pertemuan
- [x] Edit presensi yang sudah ada

### 6.2 Rekap & Notifikasi

- [x] Rekap presensi harian/bulanan (Kurikulum/Kepala Lembaga/Admin Lembaga)
- [ ] Notifikasi alpha > 3 berturut-turut (P7 notifikasi)
- [x] Statistik presensi per kelas (summary di rekap)

---

## P7 — Penilaian

### 7.1 Penilaian

- [x] CRUD Jenis Nilai (Harian, PTS, PAS, UKK) + bobot
- [x] Input Nilai Harian per KD/TP
- [x] Input Nilai PTS
- [x] Input Nilai PAS / UKK
- [x] Finalisasi nilai (kunci)

---

## P8 — Kesiswaan

### 8.1 Pelanggaran

- [x] CRUD Kategori Pelanggaran + poin
- [x] CRUD Pelanggaran (BK/Guru)
- [x] Akumulasi poin per siswa (P9 dashboard)
- [x] Notifikasi batas poin (P9 notifikasi)

### 8.2 Ekstrakurikuler

- [x] CRUD Ekskul
- [x] CRUD Anggota Ekskul
- [ ] Absensi ekskul

---

## P9 — Advance

### 9.1 Dashboard

- [x] Dashboard Super Admin
- [x] Dashboard Admin Yayasan
- [x] Dashboard Kepala Lembaga
- [x] Dashboard Admin Lembaga
- [x] Dashboard Kurikulum
- [x] Dashboard Kesiswaan
- [x] Dashboard Guru
- [x] Dashboard Siswa
- [x] Dashboard Orang Tua

### 9.2 Notifikasi

- [x] Notifikasi in-app

### 9.3 Laporan

- [x] Laporan Akademik (rekap nilai, analisis ketuntasan)
- [x] Laporan Kesiswaan (rekap siswa, mutasi, pelanggaran)
- [x] Laporan Presensi (rekap kehadiran)
- [x] Laporan Absensi PTK
- [x] Laporan Agenda Mengajar
- [x] Export Excel (PDF belum — pakai PhpSpreadsheet untuk XLSX)

---

## P10 — Finalisasi

- [ ] Bug fixing & polish
- [ ] Testing (PHPUnit)
- [ ] Documentation
- [ ] Deployment

---

## P11 — Rapor

- [ ] Generate rapor (hitung nilai akhir)
- [ ] Rapor draft → final
- [ ] Catatan Wali Kelas (sikap spiritual & sosial)
- [ ] Catatan BK
- [ ] Cetak PDF rapor format Kurikulum Merdeka
- [ ] E-Rapor (link digital)

---

## P12 — Notifikasi Opsional

- [ ] Notifikasi email (opsional)
- [ ] Notifikasi WhatsApp (opsional)

---

## Progress

| Phase                   | Total Item | Selesai | %        |
| ----------------------- | ---------- | ------- | -------- |
| P1 Foundation           | -          | -       | 100%     |
| P2 Yayasan & Lembaga    | -          | -       | 100%     |
| P3 Master Data          | -          | -       | 100%     |
| P4 Akademik             | -          | -       | 95%      |
| P5 Absensi PTK & Selfie | -          | -       | 90%      |
| P6 Presensi             | -          | -       | 95%      |
| P7 Penilaian            | -          | -       | 100%     |
| P8 Kesiswaan            | -          | -       | 70%      |
| P9 Advance              | -          | -       | 100%     |
| P10 Finalisasi          | -          | -       | 0%       |
| P11 Rapor               | -          | -       | 0%       |
| **Total**               | **-**      | **-**   | **~80%** |
