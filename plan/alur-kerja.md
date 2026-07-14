# Alur Kerja — Aplikasi KBM Multi-Lembaga

## 1. Alur Registrasi & Approval Guru

```
1. Admin Lembaga input data guru
   ├── Isi nama, NIP, NUPTK, jenis PTK, status satminkal/non
   ├── Upload dokumen (ijazah, SK, dll) — OPSIONAL
   └── Simpan → status: PENDING_APPROVAL
       │
2. Admin Yayasan lihat daftar approval pending
   ├── Review data guru
   ├── Jika satminkal:
   │   └── Setujui → generate kode guru satminkal (YYS.LBG.NNN)
   ├── Jika non-satminkal:
   │   └── Setujui → generate kode guru lembaga (LBG.NNN)
   └── Jika ditolak → isi alasan → status: REJECTED
        │
3. Sistem kirim notifikasi ke guru
   └── Jika disetujui → guru bisa login & akses fitur
```

## 2. Alur Import Siswa dari Sisda API

```
1. Admin Lembaga / Admin Yayasan buka halaman "Sync Siswa"
       │
2. Sistem panggil API Sisda: GET https://api.daruttaqwa.or.id/sisda/v1/siswa
   ├── Response: JSON array siswa dengan UnitFormal, idkelasFormal, KelasFormal, idperson, dll
   └── Tidak perlu parameter — API mengembalikan semua data
        │
3. Sistem filter berdasarkan UnitFormal (cocokkan dengan lembaga.unit_formal):
   ├── Jurusan → extract dari nama kelas → firstOrCreate
   ├── Kelas → cocokkan external_id (idkelasFormal) → insert jika baru
   └── Siswa → cocokkan external_id (idperson) atau nis → update jika ada, insert jika baru
        │
4. Sistem auto-assign siswa ke kelas via riwayat_kelas_siswas (tahun ajaran aktif)
        │
5. Tampilkan hasil sync:
   ├── "Sync selesai. 200 baru, 3 diperbarui, 0 dilewati."
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
4. Guru (pengampu) buat CP (Capaian Pembelajaran)
   ├── Pilih mapel yang diampu
   ├── Tentukan fase (E, F, dst)
   ├── Deskripsi CP
   └── Simpan → CP terikat ke guru pembuatnya
        │
5. Guru buat TP (Tujuan Pembelajaran)
   ├── Pilih CP miliknya
   ├── Kode TP & deskripsi
   └── Simpan
        │
6. Guru buat ATP (Alur Tujuan Pembelajaran)
   ├── Pilih TP miliknya
   ├── Tentukan minggu ke- & materi
   └── Simpan
        │
7. Kurikulum & Kepala Lembaga bisa lihat semua CP/TP/ATP
   └── Monitoring tanpa edit
```

**Catatan**: Satu mapel bisa diampu beberapa guru. Masing-masing guru membuat CP/TP/ATP sendiri. Data CP/TP/ATP diisolasi per guru — guru A tidak bisa mengubah punya guru B. Kurikulum hanya Read.

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

## 6. Alur Presensi

```
1. Guru buka menu Presensi
       │
2. Pilih jadwal hari ini (atau jadwal spesifik)
       │
3. Tampilkan daftar siswa kelas tersebut
       │
4. Guru input kehadiran per siswa:
   ├── Hadir (default)
   ├── Sakit
   ├── Izin
   ├── Alpha
   └── Terlambat (opsional input menit)
        │
5. Guru input materi pertemuan
       │
6. Simpan → presensi tersimpan
       │
7. Jika siswa alpha > 3 kali berturut-turut:
   └── Notifikasi ke Wali Kelas, BK, Orang Tua
```

## 7. Alur Absensi PTK (Kehadiran Harian Guru)

```
1. Admin Lembaga set jam kerja lembaga
   ├── Atur jam masuk & pulang per hari (Senin-Jumat, Sabtu)
   ├── Set toleransi keterlambatan (menit)
   └── Simpan → jadwal kerja berlaku untuk semua guru
        │
2. Guru buka menu Absensi PTK
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

## 8. Alur Agenda Mengajar (Selfie)

```
1. Guru buka jadwal hari ini
       │
2. Pilih jadwal yg sedang berlangsung
       │
3. Sebelum/tengah mengajar:
   ├── Klik "Ambil Selfie"
   ├── Kamera aktif → guru foto diri di depan kelas
   ├── Sistem attach metadata:
   │   ├── Timestamp otomatis
   │   ├── GPS lokasi
   │   ├── ID jadwal, mapel, kelas
   └── Simpan → foto tersimpan di storage
        │
4. Sistem verifikasi:
   ├── Cocokkan waktu selfie dengan jam jadwal
   ├── Jika di luar jam → peringatan (tetap bisa simpan)
   └── Jika sesuai → status: 'verified'
        │
5. Monitoring:
   ├── Kurikulum / Kepala Lembaga lihat galeri agenda
   ├── Lihat jumlah selfie per guru per bulan
   ├── Deteksi jadwal tanpa selfie → notifikasi
   └── Export arsip foto untuk evaluasi
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
