# Skema Aplikasi KBM Multi-Lembaga

## 1. Gambaran Umum

Aplikasi manajemen Kegiatan Belajar Mengajar (KBM) untuk multi-lembaga di bawah naungan yayasan. Satu **Yayasan** dapat memiliki banyak **Lembaga** (sekolah/madrasah). Setiap lembaga menjalankan KBM secara independen namun tetap di bawah pengawasan yayasan.

---

## 2. Role & Hierarki Pengguna

```
PLATFORM
├── Super Admin           (pemilik platform, kelola yayasan & semua user)
│
YAYASAN
├── Admin Yayasan         (kelola lembaga, user admin lembaga, approval guru, tahun ajaran)
│
LEMBAGA (Sekolah/Madrasah)
├── Kepala Lembaga        (approval strategis, monitoring)
├── Admin Lembaga/TU      (master data: guru, siswa, kelas, jurusan)
├── Kurikulum             (mapel, jadwal, CP/TP/ATP)
├── Kesiswaan             (data siswa, mutasi, pelanggaran, alumni)
├── Guru
│   ├── Guru Biasa
│   ├── Wali Kelas        (tugas tambahan)
│   ├── BK/Konselor       (tugas tambahan)
│   ├── Validator Presensi (tugas tambahan — permission fungsional)
│   ├── Satminkal         (PTK tetap di lembaga)
│   └── Non-Satminkal     (PTK tidak tetap)
├── Siswa                 (melihat jadwal, nilai, rapor)
└── Orang Tua/Wali        (memonitor perkembangan siswa)
```

---

## 3. Fitur-Fitur

### 3.1 Daftar Lengkap Fitur

| Fitur                                   | Role Terkait                 | Prioritas |
| --------------------------------------- | ---------------------------- | --------- |
| Manajemen Pengguna & RBAC               | Super Admin, Admin Yayasan\* | 🔴 Tinggi |
| Autentikasi (Multi-Role)                | Semua                        | 🔴 Tinggi |
| CRUD Yayasan                            | Super Admin                  | 🔴 Tinggi |
| CRUD Lembaga                            | Admin Yayasan                | 🔴 Tinggi |
| Tahun Ajaran                            | Admin Yayasan                | 🔴 Tinggi |
| Approval Guru (baru, satminkal)         | Admin Yayasan                | 🔴 Tinggi |
| NIY (generate otomatis saat approve)    | Admin Yayasan                | 🔴 Tinggi |
| Kode Guru Lembaga & Satminkal           | Admin Lembaga                | 🔴 Tinggi |
| Master Data Guru                        | Admin Lembaga                | 🔴 Tinggi |
| Master Data Siswa (import Sisda API)    | Admin Lembaga                | 🔴 Tinggi |
| Master Data Kelas (auto dari import)    | Admin Lembaga                | 🔴 Tinggi |
| Master Data Jurusan (auto dari import)  | Admin Lembaga                | 🔴 Tinggi |
| Kelola Mapel (kelompok, penugasan guru) | Kurikulum                    | 🔴 Tinggi |
| Detail Mapel (CP, TP, ATP)              | Guru                         | 🔴 Tinggi |
| Jadwal Per Kelas                        | Kurikulum                    | 🔴 Tinggi |
| Absensi PTK — Check-in/out Harian       | Guru                         | 🔴 Tinggi |
| Jurnal Mengajar (Selfie + Presensi)     | Guru                         | 🔴 Tinggi |
| Validator Presensi Siswa                | Guru (Validator Presensi)    | 🟡 Sedang |
| Perizinan Siswa (Sakit/Izin)            | Guru (Validator Presensi)    | 🟡 Sedang |
| Penilaian (Harian, PTS, PAS, UKK)       | Guru                         | 🔴 Tinggi |
| Rapor (Cetak, E-Rapor)                  | Guru, Kurikulum              | 🔴 Tinggi |
| Kalender Akademik                       | Admin Yayasan                | 🟡 Sedang |
| Dashboard Per Role                      | Semua                        | 🟡 Sedang |
| Manajemen Ekstrakurikuler               | Kesiswaan                    | 🟡 Sedang |
| Pelanggaran & Tata Tertib               | Kesiswaan, BK                | 🟡 Sedang |
| Mutasi Siswa                            | Kesiswaan, Admin Lembaga     | 🟡 Sedang |
| Laporan Akademik                        | Kurikulum, Kepala Lembaga    | 🟡 Sedang |
| Notifikasi (In-App)                     | Semua                        | 🟡 Sedang |

---

## 4. Matriks Relasi Role

| Role               | Yayasan | Lembaga | Master Data | Mapel | Jadwal | CP/TP/ATP | Jurnal | Absensi PTK | Penilaian | Rapor | Kesiswaan | Dashboard | Laporan |
| ------------------ | :-----: | :-----: | :---------: | :---: | :----: | :-------: | :----: | :---------: | :-------: | :---: | :-------: | :-------: | :-----: |
| Super Admin        |    ✓    |    -    |      -      |   -   |   -    |     -     |   -    |      -      |     -     |   -   |     -     |     -     |    -    |
| Admin Yayasan      |    ✓    |    ✓    |      -      |   -   |   -    |     -     |   -    |      -      |     -     |   -   |     -     |     -     |    ✓    |
| Kepala Lembaga     |    -    |    ✓    |      R      |   R   |   R    |     R     |   R    |      R      |     R     |   R   |     R     |     ✓     |    ✓    |
| Admin Lembaga      |    -    |    -    |      ✓      |   -   |   -    |     -     |   -    |      R      |     -     |   -   |     R     |     -     |    ✓    |
| Kurikulum          |    -    |    -    |      R      |   ✓   |   ✓    |     -     |   R    |      -      |     -     |   -   |     -     |     ✓     |    ✓    |
| Kesiswaan          |    -    |    -    |      R      |   -   |   -    |     -     |   -    |      -      |     -     |   -   |     ✓     |     ✓     |    ✓    |
| Guru               |    -    |    -    |      -      |   R   |   R    |     ✓     |   ✓    |      ✓      |     ✓     |  RU   |     C     |     ✓     |    R    |
| Validator Presensi |    -    |    -    |      -      |   -   |   -    |     -     |   -    |      -      |     -     |   -   |     -     |     ✓     |    ✓    |
| Siswa              |    -    |    -    |      -      |   R   |   R    |     -     |   R    |      -      |     R     |   R   |     -     |     ✓     |    -    |
| Orang Tua          |    -    |    -    |      -      |   -   |   -    |     -     |   R    |      -      |     R     |   R   |     R     |     ✓     |    -    |

**Legend:** ✓ = Kelola Penuh / CRUD | R = Read Only | C = Create only | U = Update only

---

## 5. Relasi Data Utama

```
Yayasan
 └── Lembaga (punya unit_formal untuk mapping Sisda API)
      ├── TahunAjaran
      ├── Jurusan (auto-create dari import Sisda)
      ├── Kelas (auto-create dari import API via external_id)
      │    └── Siswa (terdaftar di kelas per tahun ajaran, import via Sisda API)
      ├── Guru
      │    ├── TugasTambahan (Wali Kelas, BK)
      │    └── PTK (Satminkal / Non-Satminkal)
      ├── Mapel
      │    ├── KelompokMapel
      │    ├── Pengajar (relasi guru - mapel)
      │    └── CP (Capaian Pembelajaran)
      │         ├── TP (Tujuan Pembelajaran)
      │         │    └── ATP (Alur Tujuan Pembelajaran)
      │         └── Guru (relasi guru - cp)
      ├── Jadwal
      ├── AkademikSetting          (konfigurasi jam mulai, durasi KBM/istirahat/kegiatan, hari efektif, timetable per hari via drag-drop)
      ├── JamKerjaLembaga          (konfigurasi jam masuk/pulang)
      ├── JurnalMengajar           (selfie + presensi siswa + materi)
      │    └── DetailJurnalSiswa   (kehadiran per siswa — hanya Hadir/Tidak Hadir dari guru kelas)
      ├── PerizinanSiswa           (pengajuan sakit/izin oleh validator presensi, auto-override detail_jurnal_siswas)
      ├── AbsensiPTK               (check-in/check-out guru harian)
      ├── Penilaian
      │    ├── NilaiHarian
      │    ├── NilaiPTS
      │    └── NilaiPAS
      ├── Raport
      ├── Ekstrakurikuler
      └── Pelanggaran
```

---

## 6. Keterangan Tambahan

### 6.1 Kode Guru

- **Kode Guru Lembaga**: Ditentukan manual oleh admin lembaga saat tambah/edit guru. Unik per lembaga. Contoh: GRU001, SMA-001, dsb.
- **Kode Guru Satminkal**: Ditentukan manual oleh admin lembaga. Diisi jika guru berstatus PTK Tetap. Unik per yayasan.

### 6.2 Guru — Klasifikasi

| Klasifikasi   | Keterangan                                                 |
| ------------- | ---------------------------------------------------------- |
| Biasa         | Guru reguler tanpa tugas tambahan                          |
| Wali Kelas    | Tugas tambahan sebagai wali kelas tertentu                 |
| BK            | Tugas tambahan sebagai pembimbing konseling                |
| Satminkal     | Satuan Administrasi Pangkal — guru PNS/GTY terdaftar resmi |
| Non-Satminkal | Guru tidak tetap / honorer                                 |

### 6.3 CP, TP, ATP

- **CP** (Capaian Pembelajaran): Kompetensi yang dicapai di akhir fase.
- **TP** (Tujuan Pembelajaran): Tujuan spesifik per kompetensi.
- **ATP** (Alur Tujuan Pembelajaran): Urutan TP dalam satu fase.

### 6.4 Absensi PTK (Kehadiran Harian Guru)

Guru melakukan check-in & check-out setiap hari kerja.

| Atribut    | Keterangan                                       |
| ---------- | ------------------------------------------------ |
| Jam Masuk  | Di-set oleh Admin Lembaga (misal 07:00)          |
| Jam Pulang | Di-set oleh Admin Lembaga (misal 15:00)          |
| Toleransi  | Kelonggaran keterlambatan (menit) — konfigurabel |
| Check-in   | Guru scan QR / klik tombol saat tiba             |
| Check-out  | Guru scan QR / klik tombol saat pulang           |
| Lokasi     | Opsional — catat GPS untuk verifikasi            |
| Status     | Tepat waktu, Terlambat, Pulang awal, Tidak absen |
| Multi Sesi | Bisa berbeda jam masuk/pulang per hari (shift)   |

Jam kerja bisa berbeda per hari (Senin-Jumat vs Sabtu) atau per jenis guru (satminkal vs non-satminkal).

### 6.5 Jurnal Mengajar (Selfie + Presensi Siswa + Materi)

Wizard 3 langkah menggabungkan selfie, presensi siswa, dan materi pertemuan dalam satu alur.

| Atribut     | Keterangan                                                                                                                     |
| ----------- | ------------------------------------------------------------------------------------------------------------------------------ |
| Step 1      | Selfie — foto di depan kelas (kamera browser / upload)                                                                         |
| Step 2      | Presensi Siswa — guru kelas hanya memilih **Hadir** atau **Tidak Hadir** (sakit/izin/alpha ditentukan oleh Validator Presensi) |
| Step 3      | Materi Pertemuan — isi materi yang diajarkan                                                                                   |
| Metadata    | Otomatis: timestamp, lokasi (GPS), jadwal_id                                                                                   |
| Verifikasi  | Foto dicocokkan dengan jadwal & wajah guru                                                                                     |
| Frekuensi   | 1x per pertemuan per jadwal                                                                                                    |
| Penyimpanan | File di storage `storage/app/public/agenda/`                                                                                   |
| Duplikat    | Unique constraint (jadwal_id, tanggal)                                                                                         |

### 6.6 Validator Presensi Siswa & Perizinan

Guru dengan tugas tambahan **Validator Presensi** (permission `validator_presensi_siswa`) mengelola perizinan siswa.

| Atribut          | Keterangan                                                                 |
| ---------------- | -------------------------------------------------------------------------- |
| Permission       | `validator_presensi_siswa` — disimpan di `tugas_tambahans.permissions`     |
| Fungsi           | Mencatat perizinan sakit/izin siswa per tanggal                            |
| Auto-Override    | Set perizinan → otomatis update `detail_jurnal_siswas.status` di hari itu  |
| Alur Jurnal Guru | Guru kelas isi jurnal: hanya pilihan **Hadir** / **Tidak Hadir**           |
|                  | Status sakit/izin/alpha ditentukan oleh Validator Presensi                 |
|                  | Jika siswa tidak hadir & tidak ada perizinan → status = Alpha              |
| Rekap Perizinan  | Validator Presensi lihat rekap perizinan per kelas, per tanggal, per siswa |
| Notifikasi       | Notifikasi ke Wali Kelas jika siswa sakit/izin                             |

---

## 7. Catatan Teknis

- **Multi-Tenant**: Data antar lembaga diisolasi (scope by `lembaga_id`).
- **Sisda API Import**: Siswa, kelas, jurusan diambil dari API eksternal Sisda. Sistem harus handle mapping & deduplikasi.
- **Fleksibel Kurikulum**: Mendukung Kurikulum Merdeka & kurikulum lain.
- **Audit Trail**: Semua perubahan data penting tercatat (siapa, kapan, apa).
