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
- [x] Auto-fill kode_sisda (idunit) dari API Akademik saat pilih unit_formal di form create/edit lembaga
- [x] Import Lembaga dari API Akademik (apiakademik /lembaga) di halaman edit yayasan
- [x] CRUD Tahun Ajaran (set active, nonaktifkan jadwal & riwayat tahun lalu)
- [x] Approval Guru Baru (daftar pending, setujui/tolak)
- [x] Bulk Action Approval (setujui/tolak massal via checkbox)
- [x] Approval Satminkal (kode satminkal ditentukan admin lembaga)
- [x] Kalender Akademik (hari efektif, libur)

---

## P3 — Master Data

### 3.1 Admin Lembaga — Guru

- [x] CRUD Guru
- [x] TMT (Tanggal Mulai Tugas) field
- [x] Kode Guru Lembaga (ditentukan manual oleh admin lembaga)
- [x] Klasifikasi PTK (jenis PTK, satminkal/non)
- [x] Import Guru (XLSX) — support tambah baru & update massal via export-edit-import
- [x] Upload dokumen guru
- [x] Bulk Action Guru (aktifkan/nonaktifkan massal via checkbox)
- [x] Reset Password Guru (admin lembaga reset password user guru, buat akun jika belum ada)

### 3.2 Admin Yayasan — Approval Guru

- [x] NIY (Nomor Induk Yayasan) generate otomatis saat approve
- [x] Format NIY: YYYY[KodeSisda][NN] — contoh 20260701
- [x] NIY sebagai username login guru
- [x] Kode Sisda (idunit) disimpan di lembaga.kode_sisda

### 3.2 Admin Lembaga — Siswa

- [x] CRUD Siswa
- [x] Import dari Sisda API (mapping + deduplikasi)
- [x] ~~Sync kenaikan kelas via Sisda API (setelah tahun ajaran baru aktif)~~ — ditangani Sisda Yayasan
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

### 4.2 Perangkat Ajar (CP / TP / ATP / Modul Ajar) — 1 halaman dengan 4 tab

- [x] CRUD CP (Capaian Pembelajaran) — milik guru, terikat mapel
- [x] CRUD TP (Tujuan Pembelajaran) — milik guru
- [x] CRUD ATP (Alur Tujuan Pembelajaran) — milik guru
- [x] CRUD Modul Ajar — metadata + upload file dokumen (doc/docx/pdf)
- [x] Import Excel Komprehensif — 1 file, 3 sheet: CP (mapel_kode dropdown, fase, kode, deskripsi), TP (cp_kode, kode, deskripsi), ATP (tp_kode, minggu_ke, materi). Sheet CP & TP wajib, ATP opsional. Kode saling tertaut antar sheet.
- [x] Template download dengan dropdown mapel_kode (data validation dari hidden sheet)
- [x] Isolasi: guru A tidak bisa edit/melihat CP/TP/ATP guru lain
- [x] Kurikulum & Kepala Lembaga bisa CRUD semua
- [x] Modal inline untuk CRUD (tidak perlu navigasi halaman terpisah)
- [x] UI enhancement: stat cards, filter mapel di header tiap tab, import excel popup modal di header halaman
- [x] Lihat dokumen — halaman preview PDF/HTML dengan layout konsisten

### 4.3 Jadwal

- [x] CRUD Jadwal (grid view per kelas)
- [x] Cek bentrok guru & ruangan
- [x] Import jadwal (Excel)
- [ ] Cetak jadwal kelas & guru

---

### 4.4 Pengumuman

- [x] CRUD Pengumuman (Admin Lembaga) — Editor.js rich text + gambar
- [x] Popup dashboard guru (Alpine.js modal, session read tracking)

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

### 5.3 Agenda Selfie (DIGABUNG ke P10 Jurnal Mengajar)

- [x] ~~Ambil selfie + metadata~~ → digabung ke jurnal mengajar
- [x] ~~Verifikasi jadwal~~ → digabung ke jurnal mengajar
- [x] ~~Galeri agenda per guru~~ → digabung ke jurnal mengajar
- [x] ~~Monitoring oleh Kurikulum / Kepala Lembaga~~ → digabung ke jurnal mengajar
- [x] ~~Verifikasi agenda~~ → digabung ke jurnal mengajar

---

## P6 — Presensi Siswa (DIGABUNG ke P10 Jurnal Mengajar)

### 6.1 Presensi Per Pertemuan

- [x] ~~Guru pilih jadwal → daftar siswa~~ → digabung ke jurnal mengajar
- [x] ~~Input kehadiran: hadir, sakit, izin, alpha, terlambat~~ → digabung ke jurnal mengajar
- [x] ~~Input materi pertemuan~~ → digabung ke jurnal mengajar
- [x] ~~Edit presensi yang sudah ada~~ → old data tetap bisa diakses via route lama

### 6.2 Rekap & Notifikasi

- [x] ~~Rekap presensi harian/bulanan~~ → digabung ke monitoring jurnal
- [ ] Notifikasi alpha > 3 berturut-turut (P7 notifikasi)
- [x] ~~Statistik presensi per kelas~~ → digabung ke monitoring jurnal

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

## P10 — Jurnal Mengajar (Gabungan Selfie + Presensi)

### 10.1 Fitur Baru

- [x] Model JurnalMengajar (menggabungkan selfie + presensi siswa + materi)
- [x] Model DetailJurnalSiswa (detail kehadiran siswa per jurnal)
- [x] Wizard 3 langkah: Selfie → Presensi Siswa → Materi & Simpan
- [x] Camera capture langsung dari browser (base64) atau upload file
- [x] GPS otomatis dari browser
- [x] Batch insert detail siswa
- [x] Cek duplikat (1 jurnal per jadwal per hari)
- [x] Edit jurnal (materi + presensi) untuk jurnal belum diverifikasi
- [x] Monitoring + filter (guru, tanggal, status verifikasi)
- [x] Verifikasi jurnal oleh Kurikulum/Kepala Lembaga
- [x] Sidebar: "Presensi Siswa" + "Agenda Selfie" diganti "Jurnal Mengajar"
- [x] Backward compat: route lama masih bisa akses data lama

### 10.2 Database

- [x] Migration `jurnal_mengajars` — jadwal_id, guru_id, kelas_id, pertemuan_ke, tanggal, jam_mulai, foto_path, latitude, longitude, materi, is_verified, verified_at, verified_by, metadata
- [x] Migration `detail_jurnal_siswas` — jurnal_mengajar_id, siswa_id, status, keterangan

---

## P11 — Finalisasi

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
