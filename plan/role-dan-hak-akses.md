# Role & Hak Akses — Aplikasi KBM Multi-Lembaga

## 1. Daftar Role

| No  | Role             | Scope    | Melekat pada                           |
| --- | ---------------- | -------- | -------------------------------------- |
| 1   | `super_admin`    | Platform | User khusus platform                   |
| 2   | `admin_yayasan`  | Yayasan  | User di level yayasan                  |
| 3   | `kepala_lembaga` | Lembaga  | Guru dengan jabatan Kepala Sekolah     |
| 4   | `admin_lembaga`  | Lembaga  | Staff TU                               |
| 5   | `kurikulum`      | Lembaga  | Wakil Kepala Kurikulum / Tim Kurikulum |
| 6   | `kesiswaan`      | Lembaga  | Wakil Kepala Kesiswaan / Tim Kesiswaan |
| 7   | `guru`           | Lembaga  | Guru mapel / wali kelas / BK           |
| 8   | `siswa`          | Lembaga  | Siswa terdaftar                        |
| 9   | `orang_tua`      | Lembaga  | Orang tua/wali siswa                   |

## 2. Matriks Hak Akses Detail

### 2.1 Master Data

| Fitur             | super_admin | admin_yayasan | kepala_lembaga | admin_lembaga | kurikulum | kesiswaan | guru | siswa | orang_tua |
| ----------------- | :---------: | :-----------: | :------------: | :-----------: | :-------: | :-------: | :--: | :---: | :-------: | --- | ---------- | --- | --- | --- | ---- | --- | --- | --- | --- | --- | --- | --------- | --- | --- | --- | ---- | --- | --- | --- | --- | --- |
| Kelola User       |    CRUD     |    CRUD\*     |       -        |       -       |     -     |     -     |  -   |   -   |     -     |
| Lihat Yayasan     |    CRUD     |       R       |       -        |       -       |     -     |     -     |  -   |   -   |     -     |
| Kelola Lembaga    |    CRUD     |     CRUD      |       R        |       -       |     -     |     -     |  -   |   -   |     -     |
| Tahun Ajaran      |      -      |     CRUD      |       R        |       -       |     -     |     -     |  -   |   -   |     -     |     | Pengumuman | -   | -   | RU  | CRUD | -   | -   | R   | -   | -   |     | Data Guru | -   | R   | R   | CRUD | R   | R   | R   | -   | -   |
| Approval Guru     |      -      |     CRUD      |       -        |       -       |     -     |     -     |  -   |   -   |     -     |
| Jam Kerja Lembaga |      -      |       -       |       R        |     CRUD      |     -     |     -     |  -   |   -   |     -     |
| Data Siswa        |      -      |       -       |       R        |     CRUD      |     R     |   CRUD    |  R   |   R   |     R     |
| Kelas             |      -      |       -       |       R        |     CRUD      |    RU     |     R     |  R   |   R   |     -     |
| Jurusan           |      -      |       -       |       R        |     CRUD      |    RU     |     R     |  R   |   R   |     -     |

### 2.2 Akademik

| Fitur          | super_admin | admin_yayasan | kepala_lembaga | admin_lembaga | kurikulum | kesiswaan | guru | siswa | orang_tua |
| -------------- | :---------: | :-----------: | :------------: | :-----------: | :-------: | :-------: | :--: | :---: | :-------: |
| Kelompok Mapel |      -      |       -       |       R        |       -       |   CRUD    |     -     |  R   |   -   |     -     |
| Mapel          |      -      |       -       |       R        |       -       |   CRUD    |     -     |  R   |   R   |     -     |
| CP/TP/ATP      |      -      |       -       |       R        |       -       |     R     |     -     | CRUD |   -   |     -     |
| Penugasan Guru |      -      |       -       |       R        |       -       |   CRUD    |     -     |  R   |   -   |     -     |
| Jadwal         |      -      |       -       |       R        |       -       |   CRUD    |     -     |  R   |   R   |     -     |

### 2.3 Akademik — Penilaian

| Fitur       | super_admin | admin_yayasan | kepala_lembaga | admin_lembaga | kurikulum | kesiswaan | guru | siswa | orang_tua |
| ----------- | :---------: | :-----------: | :------------: | :-----------: | :-------: | :-------: | :--: | :---: | :-------: |
| Input Nilai |      -      |       -       |       -        |       -       |     -     |     -     | CRUD |   -   |     -     |
| Lihat Nilai |      -      |       -       |       R        |       -       |     R     |     R     |  R   |   R   |     R     |
| Jenis Nilai |      -      |       -       |       -        |       -       |   CRUD    |     -     |  -   |   -   |     -     |
| Raport      |      -      |       -       |       R        |       -       |     -     |     -     |  RU  |   R   |     R     |
| Presensi    |      -      |       -       |       R        |       -       |     -     |     -     | CRUD |   R   |     R     |

### 2.4 Kepegawaian & Kehadiran Guru

| Fitur                    | super_admin | admin_yayasan | kepala_lembaga | admin_lembaga | kurikulum | kesiswaan | guru | siswa | orang_tua |
| ------------------------ | :---------: | :-----------: | :------------: | :-----------: | :-------: | :-------: | :--: | :---: | :-------: |
| Absensi PTK (harian)     |      -      |       -       |       R        |       R       |     -     |     -     | CRUD |   -   |     -     |
| Laporan Absensi PTK      |      -      |       -       |       ✓        |       ✓       |     -     |     -     |  R   |   -   |     -     |
| Agenda Mengajar (Selfie) |      -      |       -       |       R        |       -       |     R     |     -     |  C   |   -   |     -     |
| Monitoring Agenda        |      -      |       -       |       ✓        |       -       |     ✓     |     -     |  -   |   -   |     -     |

### 2.5 Kesiswaan

| Fitur           | super_admin | admin_yayasan | kepala_lembaga | admin_lembaga | kurikulum | kesiswaan | guru | siswa | orang_tua |
| --------------- | :---------: | :-----------: | :------------: | :-----------: | :-------: | :-------: | :--: | :---: | :-------: |
| Mutasi          |      -      |       -       |       A        |     CRUD      |     -     |    RU     |  -   |   -   |     -     |
| Pelanggaran     |      -      |       -       |       R        |       -       |     -     |   CRUD    |  C   |   R   |     R     |
| Ekstrakurikuler |      -      |       -       |       R        |       -       |     -     |   CRUD    |  R   |   R   |     R     |
| Alumni          |      -      |       -       |       R        |       R       |     -     |   CRUD    |  -   |   -   |     -     |

### 2.6 Laporan

| Fitur                   | super_admin | admin_yayasan | kepala_lembaga | admin_lembaga | kurikulum | kesiswaan | guru | siswa | orang_tua |
| ----------------------- | :---------: | :-----------: | :------------: | :-----------: | :-------: | :-------: | :--: | :---: | :-------: |
| Laporan Yayasan         |      ✓      |       ✓       |       -        |       -       |     -     |     -     |  -   |   -   |     -     |
| Laporan Lembaga         |      -      |       ✓       |       ✓        |       ✓       |     ✓     |     ✓     |  -   |   -   |     -     |
| Laporan Akademik        |      -      |       -       |       ✓        |       -       |     ✓     |     -     |  ✓   |   -   |     -     |
| Laporan Kesiswaan       |      -      |       -       |       ✓        |       -       |     -     |     ✓     |  -   |   -   |     -     |
| Laporan Kehadiran       |      -      |       -       |       ✓        |       ✓       |     -     |     -     |  ✓   |   -   |     -     |
| Laporan Absensi PTK     |      -      |       -       |       ✓        |       ✓       |     -     |     -     |  R   |   -   |     -     |
| Laporan Agenda Mengajar |      -      |       -       |       ✓        |       -       |     ✓     |     -     |  -   |   -   |     -     |

**Legend:**

- **CRUD** = Create, Read, Update, Delete penuh
- **R** = Read only
- **C** = Create only
- **U** = Update only
- **D** = Delete only
- **A** = Approve/Authorize
- **✓** = Akses laporan
- **-** = Tidak ada akses

## 3. Hak Akses Tambahan — Guru dengan Tugas Tambahan

Selain akses standar `guru` di atas, guru dengan tugas tambahan tertentu mendapat akses tambahan:

### 3.1 Wali Kelas

| Fitur                    | Akses Wali Kelas | Keterangan                                    |
| ------------------------ | :--------------: | --------------------------------------------- |
| Dashboard Wali Kelas     |        ✓         | Lihat daftar siswa, presensi, nilai ringkasan |
| Data Siswa (kelas wali)  |        R         | Filter otomatis ke kelas walinya              |
| Presensi (kelas wali)    |        R         | Lihat rekap presensi siswa                    |
| Rapor (kelas wali)       |        RU        | Input catatan wali kelas                      |
| Pelanggaran (kelas wali) |        R         | Lihat & laporkan pelanggaran                  |

- Menu khusus muncul di sidebar & bottom nav jika guru punya tugas tambahan `Wali Kelas` aktif.
- Relasi Wali Kelas ke kelas disimpan via `tugas_tambahans.kelas_id`.
- 1 kelas hanya boleh punya 1 Wali Kelas per tahun ajaran.

### 3.2 BK / Konselor

| Fitur                    | Akses BK | Keterangan                               |
| ------------------------ | :------: | ---------------------------------------- |
| Dashboard BK             |    ✓     | Lihat statistik pelanggaran per kategori |
| Data Siswa (semua kelas) |    R     | Filter per kelas                         |
| Pelanggaran              |   CRUD   | Catat & kelola pelanggaran siswa         |
| Pembinaan                |   CRUD   | Catat tindak lanjut pembinaan            |

- Menu khusus muncul di sidebar & bottom nav jika guru punya tugas tambahan `BK` aktif.

### 3.3 Pembina Ekskul (tunda — prioritas rendah)

- Belum diimplementasikan. Ekskul sudah ada modul di Kesiswaan.

## 4. RBAC Implementation Note

- **Gates & Policies**: Laravel Authorization Gates/Policies untuk tiap fitur.
- **Middleware**: `CheckRole` middleware untuk route grouping.
- **Multi-Guard Auth**: Satu guard (`web`) dengan diferensiasi via role & permission. Tidak perlu multi-guard terpisah — cukup satu tabel `users` dengan kolom `role`.
- **Spatie Permission** (opsional): Jika butuh permission lebih granular di masa depan.

## 4. Hierarki Approval

```
Registrasi Guru Baru
  └── Admin Lembaga input data guru
       └── Admin Yayasan approve → kode guru lembaga & satminkal aktif

Perubahan Data Penting (Nilai final, Rapor final)
  └── Guru input → Kurikulum review → Kepala Lembaga approve

Mutasi Siswa
  └── Admin Lembaga proses → Kesiswaan verifikasi → Kepala Lembaga approve

Jam Kerja Lembaga
  └── Admin Lembaga set jam masuk/pulang → berlaku untuk semua guru

Absensi PTK
  └── Guru check-in/check-out harian → Admin Lembaga/Kepala Lembaga monitor

Agenda Mengajar (Selfie)
  └── Guru selfie saat mengajar → Kurikulum/Kepala Lembaga verifikasi

Tahun Ajaran Baru
  └── Admin Yayasan create & set active → Seluruh lembaga menggunakan
```
