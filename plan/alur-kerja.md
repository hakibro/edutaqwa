# Alur Kerja — Aplikasi KBM Multi-Lembaga

## 1. Alur Registrasi & Approval Guru

```
1. Admin Lembaga input data guru
   ├── Isi nama, NIP, NUPTK, jenis PTK, status satminkal/non
   ├── Upload dokumen (ijazah, SK, dll) — OPSIONAL
   └── Simpan → status: PENDING_APPROVAL
       │
2. Admin Yayasan lihat daftar approval pending
   ├── Review data guru (termasuk TMT)
   ├── Jika satminkal:
   │   └── Setujui → generate kode guru satminkal (YYS.LBG.NNN)
   ├── Jika non-satminkal:
   │   └── Setujui → generate kode guru lembaga (LBG.NNN)
   ├── Generate NIY (Nomor Induk Yayasan) jika TMT sudah diisi
   │   └── Format: YYYY[KodeSisda][NN] — contoh: 20260701
   │       YYYY = tahun dari TMT, KodeSisda = idunit dari Sisda API, NN = nomor urut
   └── Jika ditolak → isi alasan → status: REJECTED
        │
3. Sistem buat akun User otomatis:
   ├── Email: dari data guru atau nama@edutaqwa.local (auto-dedup)
   ├── Password: config('app.default_password') default 'password123'
   ├── Role: guru
   ├── must_change_password: true
   └── Tampilkan email akun ke Admin Yayasan
        │
4. Guru login dengan email & password default
   ├── Middleware ForcePasswordChange deteksi must_change_password=true
   └── Redirect ke halaman Ganti Password Wajib (tanpa current_password)
        │
5. Guru ganti password:
   ├── Isi password baru + konfirmasi
   ├── Sistem simpan password baru, set must_change_password=false
   └── Redirect ke dashboard guru
```

## 2. Alur Import Siswa dari Sisda API

```
1. Admin Lembaga / Admin Yayasan buka halaman "Sync Siswa"
       │
2. Sistem panggil API Akademik:
   ├── GET https://apiakademik.daruttaqwa.or.id/api/lembaga/{kode_sisda}/kelas
   │   └── Response: { "data": [{ "idkelas", "nama", "tingkat", "jurusan", ... }] }
   └── Per kelas: GET /lembaga/{kode_sisda}/kelas/{idkelas}/siswa
       └── Response: { "data": [{ "idsiswa", "idperson", "nis", "nisn", "nama", "gender", "tgl_masuk", ... }] }
        │
3. Sistem proses per kelas:
   ├── Jurusan → firstOrCreate dari field jurusan API
   ├── Kelas → cocokkan external_id (idkelas) → insert jika baru
   └── Siswa → cocokkan idperson → update jika ada, insert jika baru
        │
4. Sistem auto-assign siswa ke kelas via riwayat_kelas_siswas (tahun ajaran aktif)
   ├── Riwayat.tanggal_masuk diisi dari tgl_masuk API (jika ada)
   ├── nis dikosongkan — diisi manual oleh petugas lembaga
   └── Jika siswa pernah dihapus (soft delete) dan muncul lagi → restore otomatis + is_active=true
        │
5. Setelah semua kelas diproses, sistem soft-delete siswa yang tidak muncul di API:
   ├── Siswa.lembaga_id = lembaga saat ini AND idperson NOT IN daftar idperson dari API
   ├── Set is_active=false + deleted_at=now()
   └── Jika siswa muncul lagi di sync berikutnya → restore otomatis
        │
6. Tampilkan hasil sync:
   ├── "Sync selesai. 200 baru, 3 diperbarui, 0 dilewati, 5 dihapus."
   └── Jika error → tampilkan pesan error
```

## 3. Alur Tahun Ajaran Baru & Kenaikan Kelas

```
1. Admin Yayasan buka menu Tahun Ajaran
       │
2. Buat tahun ajaran baru
   ├── Nama: "2026/2027"
   ├── Semester: "Ganjil"
   ├── Tanggal mulai: 15 Juli 2026
   ├── Tanggal selesai: 18 Des 2026
   └── Set active → tahun ajaran sebelumnya otomatis nonaktif
        │
3. Admin Lembaga lakukan sync data siswa dari Sisda API
   ├── Panggil Sisda API dengan parameter NPSN & token
   ├── Sistem tarik data siswa, kelas, jurusan terbaru
   ├── Siswa otomatis masuk ke kelas baru sesuai data Sisda
   │   └── (Sisda sudah menyediakan data kelas per siswa)
   └── Kelas & jurusan baru auto-generate dari hasil sync
        │
4. Admin Lembaga verifikasi hasil sync
   ├── Cek siswa yang tidak naik / lulus (alumni)
   └── Jika ada masalah → koreksi manual
        │
5. Kurikulum mulai atur jadwal untuk tahun ajaran baru
```

## 4. Alur Input Mapel & CP/TP/ATP

```
1. Kurikulum buat Kelompok Mapel (jika belum ada)
       │
2. Kurikulum buat Mapel
   ├── Pilih kelompok mapel
   ├── Nama mapel, kode
   └── Simpan
        │
3. Kurikulum assign guru pengampu ke Mapel
   ├── Pilih mapel + kelas + tahun ajaran
   ├── Pilih satu atau lebih guru
   └── Simpan → setiap guru dapat mengelola CP/TP/ATP sendiri
        │
4. Semua data perangkat ajar (CP/TP/ATP/Modul Ajar) dalam 1 halaman
   ├── Buka menu "Perangkat Ajar" → lihat 4 tab: CP, TP, ATP, Modul Ajar
   ├── Lihat 4 kartu statistik di atas tab (total CP, TP, ATP, Modul Ajar)
   ├── Pilih filter mapel di header tab yang aktif
   └── Pagination per tab
        │
5. Guru buat CP (Capaian Pembelajaran)
   ├── Pilih mapel yang diampu
   ├── Tentukan fase (E, F, dst)
   ├── Deskripsi CP
   └── Simpan → CP terikat ke guru pembuatnya
        │
6. Guru buat TP (Tujuan Pembelajaran)
   ├── Pilih CP miliknya
   ├── Kode TP & deskripsi
   └── Simpan
        │
7. Guru buat ATP (Alur Tujuan Pembelajaran)
   ├── Pilih TP miliknya
   ├── Tentukan minggu ke- & materi
   └── Simpan
        │
8. Guru upload Modul Ajar
   ├── Pilih mapel, judul, deskripsi
   ├── Upload file dokumen (doc/docx/pdf) — opsional
   └── Simpan → file tersimpan di storage/app/public/modul-ajar/
        │
9. Import Excel Komprehensif (CP/TP/ATP — 1 file, 3 sheet)
   ├── Klik tombol "Import Excel" di header halaman (sejajar judul)
   ├── Modal popup muncul: pilih file XLSX + info template + link download template
   ├── Download template XLSX dari link di modal popup
   ├── Template punya 3 sheet:
   │   ├── Sheet CP: mapel_kode (dropdown dari hidden sheet), fase, kode, deskripsi
   │   ├── Sheet TP: cp_kode (tertaut ke sheet CP), kode, deskripsi
   │   └── Sheet ATP: tp_kode (tertaut ke sheet TP), minggu_ke, materi
   ├── Isi sheet CP & TP (wajib), ATP (opsional)
   ├── Upload file → sistem baca per-sheet, validasi kode tertaut, insert
   └── Lihat hasil: "CP: X baru, TP: Y baru, ATP: Z baru, N dilewati"
        │
10. Kurikulum & Kepala Lembaga bisa CRUD semua perangkat ajar
   └── Guru hanya CRUD data milik sendiri (guru_id)
```

**Catatan**: Satu mapel bisa diampu beberapa guru. Masing-masing guru membuat CP/TP/ATP sendiri. Data CP/TP/ATP diisolasi per guru — guru A tidak bisa mengubah punya guru B. Kurikulum & Admin Lembaga bisa CRUD semua.

## 5. Alur Penjadwalan

```
1. Kurikulum buka menu Jadwal → pilih kelas
       │
2. Tampilkan grid jadwal (Senin - Sabtu x jam ke-)
       │
3. Kurikulum atur per slot:
   ├── Pilih hari + jam
   ├── Pilih mapel → otomatis tampilkan guru pengampu
   ├── Pilih guru (filter by mapel)
   ├── Isi ruangan (opsional)
   └── Simpan
        │
4. Validasi sistem:
   ├── Cek bentrok guru (guru tidak bisa 2 tempat di jam sama)
   ├── Cek bentrok ruangan
   └── Jika bentrok → peringatan, tidak bisa simpan
        │
5. Setelah selesai → cetak jadwal kelas & jadwal guru
```

## 6. Alur Jurnal Mengajar (Selfie + Presensi + Materi) — DIGABUNG

```
1. Guru buka menu Jurnal Mengajar
       │
2. Lihat jadwal hari ini → pilih jadwal
       │
3. Wizard 3 Langkah:
       │
   STEP 1 — Selfie:
   ├── Kamera browser aktif / upload file
   ├── GPS auto-fill
   ├── Info jadwal (mapel, kelas, jam ke-)
   └── Klik "Lanjut ke Presensi"
        │
   STEP 2 — Presensi Siswa:
   ├── Daftar siswa kelas tersebut
   ├── Status default: Hadir
   ├── Tombol cepat: "Semua Hadir" / "Semua Tidak Hadir"
   ├── Opsi guru kelas: HANYA Hadir / Tidak Hadir (sakit/izin/alpha ditentukan oleh Validator Presensi)
   └── Klik "Lanjut ke Materi"
        │
   STEP 3 — Materi & Simpan:
   ├── Input materi pertemuan
   ├── Ringkasan (selfie ✓, jumlah siswa, pertemuan ke-)
   └── Klik "Simpan Jurnal Mengajar"
        │
4. Sistem simpan:
   ├── Foto ke storage (jika base64 → decode simpan)
   ├── 1 record di jurnal_mengajars
   ├── N record di detail_jurnal_siswas (batch insert)
   └── Log aktivitas
        │
5. Monitoring (Kurikulum/Kepala Lembaga/Admin Lembaga):
   ├── Filter: guru, tanggal, status verifikasi
   ├── Lihat foto selfie + rekap kehadiran
   ├── Verifikasi jurnal
   └── Export laporan
```

```
1. Admin Lembaga set jam kerja lembaga
   ├── Atur jam masuk & pulang per hari (Senin-Jumat, Sabtu)
   ├── Set toleransi keterlambatan (menit)
   └── Simpan → jadwal kerja berlaku untuk semua guru
        │
2. Guru buka menu Absensi PTK
   ├── Guru struktural (jenis_ptk_id terisi): wajib absen setiap hari
   │   └── Dashboard tampilkan banner merah jika belum absen
   ├── Guru non-struktural: absensi tidak ditampilkan (dashboard, sidebar, bottom nav semua disembunyikan)
        │
3. Check-in (pagi):
   ├── Tombol "Check-in" atau scan QR
   ├── Opsional: foto selfi
   ├── Sistem catat waktu check-in + lokasi GPS
   ├── Bandingkan dengan jam_masuk → hitung keterlambatan
   └── Status: 'tepat_waktu' atau 'terlambat'
        │
4. Check-out (sore):
   ├── Tombol "Check-out"
   ├── Sistem catat waktu check-out
   ├── Bandingkan dengan jam_pulang → deteksi pulang awal
   └── Update status jika 'pulang_awal'
        │
5. Jika guru lupa check-in/check-out:
   ├── Notifikasi pengingat (setelah lewat 30 menit dari jam masuk)
   └── Kepala Lembaga bisa manual edit status
        │
6. Rekap & Laporan:
   ├── Guru lihat riwayat absensi sendiri
   ├── Admin Lembaga / Kepala Lembaga lihat rekap semua guru
   └── Export laporan kehadiran guru bulanan
```

## 7B. Alur Validator Presensi Siswa (Perizinan) — ✅ Diimplementasikan 2026-07-23

```
1. Guru dengan tugas tambahan 'Perizinan Siswa' buka menu Perizinan Siswa
       │
2. Halaman Daftar Perizinan:
   ├── Filter per kelas, per tanggal, per jenis (sakit/izin)
   ├── Tabel: tanggal, siswa, kelas, jenis, keterangan, status (applied/pending)
   └── Tombol "Input Perizinan" & hapus perizinan
       │
3. Input Perizinan:
   ├── Pilih kelas → AJAX load daftar siswa
   ├── Pilih siswa (single select)
   ├── Pilih tanggal (single date)
   ├── Pilih jenis: Sakit / Izin
   ├── Isi keterangan (opsional)
   └── Klik "Simpan Perizinan"
       │
4. Sistem proses:
   ├── Insert/update record di tabel perizinan_siswas (unique: siswa_id + tanggal)
   ├── Cek detail_jurnal_siswas untuk siswa + tanggal terkait:
   │   ├── Jika sudah ada record → update status ke sakit/izin, set is_applied=true
   │   └── Jika belum ada → simpan pending (is_applied=false)
   └── Notifikasi ke Wali Kelas (jika siswa di kelas walinya)
       │
5. Alur Harian — Integrasi Jurnal Guru:
   ├── Pagi/Siang: Guru Perizinan input perizinan (siswa sakit/izin)
   ├── Saat mengajar: Guru isi jurnal → hanya pilih Hadir/Tidak Hadir
   ├── Sistem resolve status akhir:
   │   ├── Hadir (dari guru) → status = hadir
   │   ├── Tidak Hadir + ada perizinan → status = sakit/izin (sesuai perizinan)
   │   └── Tidak Hadir + tidak ada perizinan → status = alpha
   └── Wali Kelas lihat rekap presensi siswa walinya (real-time)
       │
6. Hapus Perizinan:
   ├── Balikkan status di detail_jurnal_siswas ke alpha
   └── Hapus record perizinan
```

## 8. Alur Jurnal Mengajar — Monitoring & Verifikasi

```
1. Kurikulum / Kepala Lembaga / Admin Lembaga buka Monitoring Jurnal
       │
2. Filter data:
   ├── Pilih guru
   ├── Rentang tanggal
   └── Status verifikasi (semua / pending / terverifikasi)
        │
3. Lihat grid jurnal:
   ├── Foto selfie (thumbnail)
   ├── Info: guru, mapel, kelas, tanggal, pertemuan
   ├── Rekap kehadiran siswa (H/S/I/A/T)
   └── Status verifikasi
        │
4. Klik jurnal → detail:
   ├── Foto selfie besar
   ├── Info lengkap
   ├── Daftar siswa + status kehadiran
   └── Materi pertemuan
        │
5. Verifikasi:
   ├── Klik "Verifikasi" → is_verified=true
   └── Tercatat: verified_by + verified_at
```

## 10. Alur Penilaian & Rapor

```
1. Guru input nilai (Harian, PTS, PAS, UKK)
   ├── Pilih kelas + mapel + jenis nilai
   ├── Input nilai per siswa
   └── Simpan (sebagai draft)
        │
2. Guru finalisasi nilai
   ├── Review semua nilai
   └── Klik "Final" → nilai terkunci (tidak bisa diedit tanpa approval)
        │
3. Sistem generate rapor
   ├── Hitung nilai akhir = bobot harian + PTS + PAS
   ├── Ambil presensi
   ├── Ambil catatan wali kelas & BK
   └── Generate PDF rapor
        │
4. Wali Kelas review rapor
   ├── Input catatan wali kelas
   ├── Input deskripsi sikap spiritual & sosial
   └── Approve
        │
5. Kurikulum / Kepala Lembaga finalisasi
   └── Approve → rapor final → bisa dicetak & dibagikan
```

## 11. Alur Mutasi Siswa

```
Pindah Keluar:
1. Kesiswaan / Admin Lembaga catat mutasi keluar
   ├── Pilih siswa
   ├── Tanggal keluar, alasan
   ├── Tujuan sekolah (jika pindah)
   └── Simpan → status siswa: 'pindah'
        │
2. Sistem arsipkan riwayat kelas siswa

Pindah Masuk:
1. Kesiswaan / Admin Lembaga input siswa baru
   ├── Cari NISN di database (cegah duplikat nasional)
   ├── Input data siswa
   ├── Tentukan kelas & tahun ajaran
   └── Simpan → status: 'aktif'

Alumni:
1. Akhir tahun ajaran → sistem massal ubah status siswa kelas XII/IX/VI ke 'alumni'
2. Data alumni tersimpan selamanya
```

## 12. Alur Pelanggaran & BK

```
1. BK / Guru catat pelanggaran
   ├── Pilih siswa
   ├── Pilih kategori pelanggaran
   ├── Deskripsi kejadian
   ├── Tindakan (teguran lisan, tertulis, panggilan orang tua, skorsing)
   └── Simpan
        │
2. Akumulasi poin siswa
   │
3. Jika poin mencapai batas tertentu:
   └── Notifikasi ke Wali Kelas, BK, Orang Tua
        │
4. BK bisa lihat history pelanggaran & pembinaan
```

---

## 13. Alur Guru Pengganti (Phase 11)

### 13.1 Pengajuan oleh Guru

```
1. Guru Login → Buka Jurnal/Halaman Jadwal
2. Klik "Ajukan Pengganti" pada jadwal tertentu
3. Isi form:
   ├── Pilih Guru Pengganti (dropdown semua guru di lembaga)
   ├── Pilih Tanggal (multi-select checkbox kalender)
   └── Isi Alasan (wajib)
4. Submit
5. Sistem Insert ke jadwal_pengganti (1 row per tanggal, status: diajukan)
6. Notifikasi ke semua user Kurikulum di lembaga
```

### 13.2 Approval oleh Kurikulum

```
1. Kurikulum Login → Buka Menu "Approval Pengganti"
2. Lihat daftar pengajuan pending (filter: tanggal, guru, status)
3. Klik "Setujui" → Isi catatan (opsional) → Status: disetujui
   └── Atau klik "Tolak" → Isi catatan (wajib) → Status: ditolak
4. Notifikasi ke guru pengaju & guru pengganti
```

### 13.3 Pembatalan oleh Guru

```
1. Guru Login → Buka "Riwayat Pengganti"
2. Lihat pengajuan dengan status "diajukan"
3. Klik "Batalkan" → Status: dibatalkan
   └── Tidak bisa batalkan yang sudah disetujui/ditolak
```

### 13.4 Jurnal oleh Guru Pengganti

```
1. Guru Pengganti Login → Buka Jurnal Mengajar
2. Muncul jadwal tambahan dengan badge "Pengganti — menggantikan [Nama Guru Asli]"
3. Klik "Isi Jurnal" → Wizard 3 Langkah normal
4. Sistem cek: user adalah guru_pengganti_id di jadwal_pengganti dengan status disetujui & tanggal hari ini
5. Jurnal tersimpan dengan metadata: { "is_substitute": true, "guru_asli_id": X }
6. Jurnal muncul di monitoring Kurikulum dengan badge "Pengganti"
```

### 13.5 Monitoring oleh Kurikulum

```
1. Kurikulum Login → Buka Menu "Monitoring Pengganti"
2. Lihat daftar pengganti aktif per tanggal
3. Filter: tanggal, guru asli, guru pengganti
4. Klik jurnal untuk verifikasi
5. Verifikasi jurnal pengganti (flow sama dengan jurnal normal)
```
