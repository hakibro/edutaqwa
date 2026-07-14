# Tahapan Pengembangan — Aplikasi KBM Multi-Lembaga

## Phase 1: Foundation (Minggu 1-2)

### Tujuan: Basis sistem stabil

| Task                             | Estimasi   | Output                   |
| -------------------------------- | ---------- | ------------------------ |
| Setup Laravel + konfigurasi      | 1 hari     | Project siap             |
| Database migration (semua tabel) | 2 hari     | Semua tabel terbuat      |
| Model + relationship             | 1 hari     | Semua model Eloquent     |
| Auth scaffold (Breeze)           | 1 hari     | Login, register, logout  |
| Middleware CheckRole             | 0.5 hari   | Route protection         |
| Multi-tenant scope               | 0.5 hari   | Global scope lembaga     |
| Factory & Seeder                 | 1 hari     | Data dummy untuk testing |
| **Total**                        | **7 hari** |                          |

## Phase 2: Manajemen Yayasan & Lembaga (Minggu 3-4)

### Tujuan: CRUD yayasan & lembaga, approval workflow

| Task              | Estimasi      | Output                                                        |
| ----------------- | ------------- | ------------------------------------------------------------- |
| CRUD Yayasan      | ✅ 1 hari     | Super admin bisa kelola yayasan                               |
| CRUD Lembaga      | ✅ 1 hari     | Admin yayasan bisa kelola lembaga                             |
| Tahun Ajaran CRUD | ✅ 1 hari     | Tahun ajaran per yayasan, set active, nonaktifkan jadwal lama |
| Kalender Akademik | ✅ 1 hari     | Hari efektif, libur                                           |
| Log Aktivitas     | ✅ 1 hari     | Audit trail dasar                                             |
| **Total**         | **✅ 5 hari** |                                                               |

## Phase 3: Master Data (Minggu 5-6)

### Tujuan: Data inti guru, siswa, kelas, jurusan

| Task                       | Estimasi     | Output                                                   |
| -------------------------- | ------------ | -------------------------------------------------------- |
| CRUD Guru + kode guru      | 2 hari       | Master data guru, kode satminkal                         |
| Approval Guru workflow     | 1 hari       | Flow approve/reject admin yayasan                        |
| Import Siswa via Sisda API | 3 hari       | Integrasi API, mapping, deduplikasi, sync kenaikan kelas |
| CRUD Kelas (manual)        | 1 hari       | Kelola kelas manual                                      |
| CRUD Jurusan (manual)      | 0.5 hari     | Kelola jurusan manual                                    |
| **Total**                  | **7.5 hari** |                                                          |

## Phase 4: Akademik (Minggu 7-9)

### Tujuan: Mapel, CP/TP/ATP, Jadwal

| Task                                 | Estimasi   | Output                      |
| ------------------------------------ | ---------- | --------------------------- |
| Kelompok Mapel CRUD                  | 0.5 hari   | Kategori mapel              |
| Mapel CRUD + penugasan guru          | 1.5 hari   | Mapel + guru pengampu       |
| CP/TP/ATP CRUD (oleh guru)           | 2 hari     | Guru buat CP/TP/ATP sendiri |
| Jadwal (CRUD + grid + bentrok check) | 3 hari     | Jadwal per kelas siap       |
| Import Jadwal (Excel)                | 1 hari     | Import massal jadwal        |
| **Total**                            | **8 hari** |                             |

## Phase 5: Absensi PTK & Agenda Selfie (Minggu 10)

### Tujuan: Kehadiran guru harian & bukti mengajar

| Task                            | Estimasi     | Output                              |
| ------------------------------- | ------------ | ----------------------------------- |
| Konfigurasi Jam Kerja Lembaga   | 0.5 hari     | Admin set jam masuk/pulang per hari |
| Absensi PTK check-in/check-out  | 1.5 hari     | Guru absen harian, status kehadiran |
| Laporan Absensi PTK             | 0.5 hari     | Rekap kehadiran guru                |
| Agenda Selfie (ambil foto)      | 1 hari       | Selfie + metadata otomatis          |
| Monitoring & Verifikasi Agenda  | 0.5 hari     | Kurikulum verifikasi bukti mengajar |
| Notifikasi lupa check-in/agenda | 0.5 hari     | Alert otomatis                      |
| **Total**                       | **4.5 hari** |                                     |

## Phase 6: Presensi (Minggu 11)

| Task                            | Estimasi     | Output                |
| ------------------------------- | ------------ | --------------------- |
| Presensi per pertemuan          | 1.5 hari     | Guru absen per jadwal |
| Rekap presensi                  | 0.5 hari     | Laporan kehadiran     |
| Notifikasi alpha berturut-turut | 0.5 hari     | Alert otomatis        |
| **Total**                       | **2.5 hari** |                       |

## Phase 7: Penilaian (Minggu 12-13)

### Tujuan: Input nilai, belum termasuk rapor

| Task               | Estimasi   | Output                  |
| ------------------ | ---------- | ----------------------- |
| Jenis Nilai CRUD   | 0.5 hari   | Konfigurasi bobot nilai |
| Input Nilai Harian | 2 hari     | Guru input nilai per KD |
| Input PTS/PAS      | 1 hari     | Nilai ujian             |
| Finalisasi nilai   | 0.5 hari   | Kunci nilai             |
| **Total**          | **4 hari** |                         |

## Phase 8: Kesiswaan (Minggu 14-16)

| Task                 | Estimasi   | Output                            |
| -------------------- | ---------- | --------------------------------- |
| Mutasi Siswa         | 1.5 hari   | Pindah masuk/keluar, alumni       |
| Pelanggaran & Poin   | 1.5 hari   | Catat pelanggaran, akumulasi poin |
| Ekstrakurikuler CRUD | 1 hari     | Ekskul + anggota                  |
| **Total**            | **4 hari** |                                   |

## Phase 9: Dashboard & Notifikasi (Minggu 17)

| Task                  | Estimasi   | Output                                |
| --------------------- | ---------- | ------------------------------------- |
| Dashboard per role    | 2 hari     | Widget relevan per role               |
| Notifikasi (in-app)   | 1 hari     | Notifikasi internal                   |
| Notifikasi (email/WA) | 1 hari     | Integrasi email & WhatsApp (opsional) |
| **Total**             | **4 hari** |                                       |

## Phase 10: Laporan & Finalisasi (Minggu 18-19)

| Task                | Estimasi   | Output                           |
| ------------------- | ---------- | -------------------------------- |
| Laporan Akademik    | 2 hari     | Rekap nilai, analisis ketuntasan |
| Laporan Kesiswaan   | 1 hari     | Rekap siswa, mutasi, pelanggaran |
| Laporan Presensi    | 1 hari     | Rekap kehadiran                  |
| Export Excel/PDF    | 1 hari     | Download laporan                 |
| Bug fixing & polish | 3 hari     | Stabilisasi                      |
| **Total**           | **8 hari** |                                  |

## Phase 11: Rapor (Minggu 20-21)

### Tujuan: Generate & cetak rapor (prioritas akhir)

| Task                    | Estimasi     | Output                               |
| ----------------------- | ------------ | ------------------------------------ |
| Generate Rapor          | 2 hari       | Perhitungan nilai akhir              |
| Raport PDF (cetak)      | 2 hari       | Template PDF rapor Kurikulum Merdeka |
| Catatan Wali Kelas & BK | 0.5 hari     | Input catatan rapor                  |
| E-Rapor                 | 1 hari       | Rapor digital via link               |
| **Total**               | **5.5 hari** |                                      |

---

## Ringkasan Timeline

| Phase                           | Durasi   | Cumulative               |
| ------------------------------- | -------- | ------------------------ |
| P1: Foundation                  | 7 hari   | 7 hari                   |
| P2: Yayasan & Lembaga           | 5 hari   | 12 hari                  |
| P3: Master Data                 | 7.5 hari | 19.5 hari                |
| P4: Akademik                    | 8 hari   | 27.5 hari                |
| P5: Absensi PTK & Agenda Selfie | 4.5 hari | 32 hari                  |
| P6: Presensi                    | 2.5 hari | 34.5 hari                |
| P7: Penilaian                   | 4 hari   | 38.5 hari                |
| P8: Kesiswaan                   | 4 hari   | 42.5 hari                |
| P9: Dashboard & Notifikasi      | 4 hari   | 46.5 hari                |
| P10: Laporan & Finalisasi       | 8 hari   | 54.5 hari                |
| P11: Rapor                      | 5.5 hari | **60 hari (~12 minggu)** |

> **Catatan**: Estimasi berdasarkan kerja full-time 1 developer. Bisa lebih cepat jika tim > 1 orang.
