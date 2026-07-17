# Database Schema — Aplikasi KBM Multi-Lembaga

## 1. Entity Relationship (Textual)

### 1.1 Master Data

```sql
-- === PLATFORM & TENANT ===

CREATE TABLE yayasans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    kode VARCHAR(50) UNIQUE NOT NULL,          -- Kode singkat, utk kode guru satminkal
    alamat TEXT NULL,
    telp VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    logo VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE lembagas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    yayasan_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(255) NOT NULL,
    kode VARCHAR(50) NOT NULL,                  -- Kode singkat di lingkup yayasan
    kode_sisda VARCHAR(10) NULL,                -- Kode dari Sisda API (idunit), utk generate NIY
    sisda_mode BOOLEAN DEFAULT FALSE,          -- TRUE = hanya sync API, sembunyikan tambah manual siswa/kelas/jurusan
    npsn VARCHAR(20) NULL,
    alamat TEXT NULL,
    telp VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    tingkat VARCHAR(50) NOT NULL,               -- 'PAUD', 'RA', 'MI', 'MTS', 'MA', 'SD', 'SMP', 'SMA', 'SMK'
    unit_formal VARCHAR(50) NULL,               -- Mapping ke Sisda API UnitFormal ('SMA', 'SMK', 'MA', dll)
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (yayasan_id) REFERENCES yayasans(id) ON DELETE CASCADE,
    UNIQUE (yayasan_id, kode)
);

> **2026-07-17**: Tambah `sisda_mode BOOLEAN DEFAULT FALSE` — toggle untuk menyembunyikan tombol tambah manual siswa/kelas/jurusan saat mode API Sisda aktif.

-- === TAHUN AJARAN ===

CREATE TABLE tahun_ajarans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    yayasan_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(100) NOT NULL,                 -- '2025/2026'
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,            -- Hanya 1 active per yayasan
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (yayasan_id) REFERENCES yayasans(id) ON DELETE CASCADE,
    UNIQUE (yayasan_id, nama)
);

-- === KALENDER AKADEMIK ===

CREATE TABLE kalender_akademiks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    yayasan_id BIGINT UNSIGNED NOT NULL,
    tanggal DATE NOT NULL,
    label VARCHAR(255) NOT NULL,
    jenis ENUM('efektif', 'libur', 'ujian', 'lainnya') DEFAULT 'libur',
    keterangan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (yayasan_id) REFERENCES yayasans(id) ON DELETE CASCADE,
    UNIQUE (yayasan_id, tanggal)
);

-- === LOG AKTIVITAS (AUDIT TRAIL) ===

CREATE TABLE log_aktivitas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    role VARCHAR(50) NULL,
    aksi VARCHAR(100) NOT NULL,
    deskripsi VARCHAR(500) NOT NULL,
    model_type VARCHAR(100) NULL,
    model_id BIGINT UNSIGNED NULL,
    yayasan_id BIGINT UNSIGNED NULL,
    lembaga_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_log_user (user_id, created_at),
    INDEX idx_log_yayasan (yayasan_id, created_at),
    INDEX idx_log_lembaga (lembaga_id, created_at)
);

-- === JURUSAN ===

CREATE TABLE jurusans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lembaga_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(100) NOT NULL,                 -- 'IPA', 'IPS', 'Bahasa', 'TKJ', dll
    kode VARCHAR(50) NULL,
    external_id VARCHAR(50) NULL UNIQUE,        -- Mapping ke Sisda API (opsional)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lembaga_id) REFERENCES lembagas(id) ON DELETE CASCADE
);

-- === KELAS ===

CREATE TABLE kelas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lembaga_id BIGINT UNSIGNED NOT NULL,
    jurusan_id BIGINT UNSIGNED NULL,
    nama VARCHAR(100) NOT NULL,                 -- 'X IPA 1', 'XI TKJ 2', 'VII-A'
    tingkat VARCHAR(20) NOT NULL,               -- 'X', 'XI', 'XII', 'VII', 'VIII', 'IX', '1', '2', dst'
    external_id VARCHAR(50) NULL UNIQUE,        -- Mapping ke Sisda API idkelasFormal
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lembaga_id) REFERENCES lembagas(id) ON DELETE CASCADE,
    FOREIGN KEY (jurusan_id) REFERENCES jurusans(id) ON DELETE SET NULL
);

-- === GURU ===

CREATE TABLE gurus (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lembaga_id BIGINT UNSIGNED NOT NULL,
    kode_guru_lembaga VARCHAR(50) NULL UNIQUE,  -- Format: [KodeLembaga].[NomorUrut]
    kode_guru_satminkal VARCHAR(50) NULL UNIQUE, -- Format: [KodeYayasan].[KodeLembaga].[NomorUrut]
    niy VARCHAR(20) NULL UNIQUE,                -- Nomor Induk Yayasan, format: YYYY[KodeSisda][NN], generate saat approve
    nama VARCHAR(255) NOT NULL,
    nip VARCHAR(30) NULL,                       -- NIP bagi PNS
    nuptk VARCHAR(30) NULL,
    jenis_ptk VARCHAR(100) NULL,                -- Opsional: 'Guru Mapel', 'Guru Kelas', 'Guru BK', dll
    status_satminkal BOOLEAN DEFAULT FALSE,
    tempat_lahir VARCHAR(100) NULL,
    tanggal_lahir DATE NULL,
    tmt DATE NULL,                              -- Tanggal Mulai Tugas
    alamat TEXT NULL,
    telp VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    foto VARCHAR(255) NULL,
    is_approved BOOLEAN DEFAULT FALSE,          -- Approval Admin Yayasan
    approved_at TIMESTAMP NULL,
    approved_by BIGINT UNSIGNED NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lembaga_id) REFERENCES lembagas(id) ON DELETE CASCADE
);

-- Tugas Tambahan Guru (Wali Kelas, BK)
CREATE TABLE tugas_tambahans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guru_id BIGINT UNSIGNED NOT NULL,
    jenis VARCHAR(50) NOT NULL,                 -- 'wali_kelas', 'bk', 'pembina_ekskul', dll
    keterangan VARCHAR(255) NULL,               -- Kelas yg diampu, ekskul, dll
    tahun_ajaran_id BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_id) REFERENCES gurus(id) ON DELETE CASCADE,
    FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajarans(id) ON DELETE CASCADE
);

-- === SISWA ===

CREATE TABLE siswas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lembaga_id BIGINT UNSIGNED NOT NULL,
    idperson VARCHAR(50) NULL UNIQUE,            -- Mapping ke API Akademik idperson (acuan sync)
    nis VARCHAR(50) NULL,                       -- Nomor Induk Siswa (diisi manual petugas lembaga)
    nisn VARCHAR(20) NULL,                      -- Nomor Induk Siswa Nasional
    nama VARCHAR(255) NOT NULL,
    tempat_lahir VARCHAR(100) NULL,
    tanggal_lahir DATE NULL,
    jenis_kelamin ENUM('L', 'P') NULL,
    alamat TEXT NULL,
    telp VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    foto VARCHAR(255) NULL,
    agama VARCHAR(50) NULL,
    -- Data orang tua
    nama_ayah VARCHAR(255) NULL,
    nama_ibu VARCHAR(255) NULL,
    pekerjaan_ayah VARCHAR(100) NULL,
    pekerjaan_ibu VARCHAR(100) NULL,
    telp_orang_tua VARCHAR(50) NULL,
    -- Status
    status ENUM('aktif', 'alumni', 'pindah', 'keluar', 'dropout') DEFAULT 'aktif',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,                   -- Soft delete (sync: siswa hilang dari API)
    FOREIGN KEY (lembaga_id) REFERENCES lembagas(id) ON DELETE CASCADE,
    UNIQUE (lembaga_id, nis)
);

> **2026-07-17**: Tambah `deleted_at` untuk soft delete. Sync soft-delete + `is_active=false` siswa yang tidak ada di response API. Restore otomatis jika muncul lagi.

-- Riwayat Kelas Siswa per Tahun Ajaran
CREATE TABLE riwayat_kelas_siswas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    siswa_id BIGINT UNSIGNED NOT NULL,
    kelas_id BIGINT UNSIGNED NOT NULL,
    tahun_ajaran_id BIGINT UNSIGNED NOT NULL,
    tanggal_masuk DATE NULL,
    tanggal_keluar DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswas(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
    FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajarans(id) ON DELETE CASCADE,
    UNIQUE (siswa_id, tahun_ajaran_id)
);

-- === MAPEL ===

CREATE TABLE kelompok_mapels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lembaga_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(255) NOT NULL,                 -- 'A. Umum', 'B. Kejuruan', 'Muatan Lokal', dll
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lembaga_id) REFERENCES lembagas(id) ON DELETE CASCADE
);

CREATE TABLE mapels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lembaga_id BIGINT UNSIGNED NOT NULL,
    kelompok_mapel_id BIGINT UNSIGNED NULL,
    nama VARCHAR(255) NOT NULL,
    kode VARCHAR(50) NULL,
    deskripsi TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lembaga_id) REFERENCES lembagas(id) ON DELETE CASCADE,
    FOREIGN KEY (kelompok_mapel_id) REFERENCES kelompok_mapels(id) ON DELETE SET NULL
);

-- Penugasan Guru ke Mapel
CREATE TABLE pengajaran_mapels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mapel_id BIGINT UNSIGNED NOT NULL,
    guru_id BIGINT UNSIGNED NOT NULL,
    tahun_ajaran_id BIGINT UNSIGNED NOT NULL,
    kelas_id BIGINT UNSIGNED NULL,              -- NULL berarti guru pengampu semua kelas utk mapel ini
    jam_per_minggu INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mapel_id) REFERENCES mapels(id) ON DELETE CASCADE,
    FOREIGN KEY (guru_id) REFERENCES gurus(id) ON DELETE CASCADE,
    FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajarans(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE
);

-- CP / TP / ATP (dibuat oleh guru pengampu)
CREATE TABLE cps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mapel_id BIGINT UNSIGNED NOT NULL,
    guru_id BIGINT UNSIGNED NOT NULL,           -- Guru pembuat CP (pemilik)
    fase VARCHAR(20) NOT NULL,                  -- 'A', 'B', 'C', 'D', 'E', 'F'
    kode VARCHAR(50) NULL,
    deskripsi TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mapel_id) REFERENCES mapels(id) ON DELETE CASCADE,
    FOREIGN KEY (guru_id) REFERENCES gurus(id) ON DELETE CASCADE
);

CREATE TABLE tps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cp_id BIGINT UNSIGNED NOT NULL,
    kode VARCHAR(50) NULL,
    deskripsi TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cp_id) REFERENCES cps(id) ON DELETE CASCADE
);

CREATE TABLE atps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tp_id BIGINT UNSIGNED NOT NULL,
    minggu_ke INT NOT NULL,
    materi TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tp_id) REFERENCES tps(id) ON DELETE CASCADE
);

-- === JADWAL ===

CREATE TABLE jadwals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lembaga_id BIGINT UNSIGNED NOT NULL,
    kelas_id BIGINT UNSIGNED NOT NULL,
    mapel_id BIGINT UNSIGNED NOT NULL,
    guru_id BIGINT UNSIGNED NOT NULL,
    tahun_ajaran_id BIGINT UNSIGNED NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    jam_ke TINYINT UNSIGNED NOT NULL COMMENT 'Nomor jam ke- (1=Jam 1, 2=Jam 2, ...), waktu di-resolve dari AkademikSetting timetable',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lembaga_id) REFERENCES lembagas(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
    FOREIGN KEY (mapel_id) REFERENCES mapels(id) ON DELETE CASCADE,
    FOREIGN KEY (guru_id) REFERENCES gurus(id) ON DELETE CASCADE,
    FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajarans(id) ON DELETE CASCADE
);

-- === JAM KERJA LEMBAGA (Konfigurasi) ===

CREATE TABLE jam_kerja_lembagas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lembaga_id BIGINT UNSIGNED NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    jam_masuk TIME NOT NULL,
    jam_pulang TIME NOT NULL,
    toleransi_keterlambatan INT DEFAULT 15,     -- menit toleransi
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lembaga_id) REFERENCES lembagas(id) ON DELETE CASCADE,
    UNIQUE (lembaga_id, hari)
);

-- === PRESENSI ===

CREATE TABLE presensis (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jadwal_id BIGINT UNSIGNED NOT NULL,
    pertemuan_ke INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_mulai TIME NULL,
    jam_selesai TIME NULL,
    materi VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jadwal_id) REFERENCES jadwals(id) ON DELETE CASCADE
);

CREATE TABLE detail_presensis (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    presensi_id BIGINT UNSIGNED NOT NULL,
    siswa_id BIGINT UNSIGNED NOT NULL,
    status ENUM('hadir', 'sakit', 'izin', 'alpha', 'terlambat') NOT NULL,
    keterangan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (presensi_id) REFERENCES presensis(id) ON DELETE CASCADE,
    FOREIGN KEY (siswa_id) REFERENCES siswas(id) ON DELETE CASCADE
);

-- === ABSENSI PTK (Kehadiran Harian Guru) ===

CREATE TABLE absensi_ptks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guru_id BIGINT UNSIGNED NOT NULL,
    lembaga_id BIGINT UNSIGNED NOT NULL,
    tanggal DATE NOT NULL,
    check_in DATETIME NULL,
    check_out DATETIME NULL,
    jam_masuk_set TIME NULL,                 -- Jam masuk yg ditetapkan hari itu
    jam_pulang_set TIME NULL,                -- Jam pulang yg ditetapkan hari itu
    status ENUM('tepat_waktu', 'terlambat', 'pulang_awal', 'tidak_absen', 'libur') DEFAULT 'tidak_absen',
    keterlambatan_menit INT DEFAULT 0,
    lokasi_check_in VARCHAR(255) NULL,        -- GPS / alamat
    lokasi_check_out VARCHAR(255) NULL,
    foto_check_in VARCHAR(255) NULL,          -- Opsional: selfie saat check-in
    foto_check_out VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_id) REFERENCES gurus(id) ON DELETE CASCADE,
    FOREIGN KEY (lembaga_id) REFERENCES lembagas(id) ON DELETE CASCADE,
    UNIQUE (guru_id, tanggal)
);

-- === AGENDA MENGAJAR (Selfie) ===

CREATE TABLE agenda_mengajars (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jadwal_id BIGINT UNSIGNED NOT NULL,
    guru_id BIGINT UNSIGNED NOT NULL,
    kelas_id BIGINT UNSIGNED NOT NULL,
    pertemuan_ke INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_mulai TIME NULL,
    jam_selesai TIME NULL,
    foto_path VARCHAR(255) NOT NULL,          -- Path foto selfie
    latitude VARCHAR(50) NULL,                -- GPS latitude
    longitude VARCHAR(50) NULL,               -- GPS longitude
    metadata TEXT NULL,                        -- JSON: device info, dll
    is_verified BOOLEAN DEFAULT FALSE,        -- Verifikasi oleh kurikulum/kepsek
    verified_at TIMESTAMP NULL,
    verified_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jadwal_id) REFERENCES jadwals(id) ON DELETE CASCADE,
    FOREIGN KEY (guru_id) REFERENCES gurus(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE
);

-- === PENILAIAN ===

CREATE TABLE jenis_nilais (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lembaga_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(100) NOT NULL,                 -- 'Harian', 'PTS', 'PAS', 'UKK'
    bobot DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lembaga_id) REFERENCES lembagas(id) ON DELETE CASCADE
);

CREATE TABLE nilais (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    siswa_id BIGINT UNSIGNED NOT NULL,
    mapel_id BIGINT UNSIGNED NOT NULL,
    guru_id BIGINT UNSIGNED NOT NULL,
    kelas_id BIGINT UNSIGNED NOT NULL,
    tahun_ajaran_id BIGINT UNSIGNED NOT NULL,
    jenis_nilai_id BIGINT UNSIGNED NOT NULL,
    nilai DECIMAL(5,2) NOT NULL,
    keterangan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswas(id) ON DELETE CASCADE,
    FOREIGN KEY (mapel_id) REFERENCES mapels(id) ON DELETE CASCADE,
    FOREIGN KEY (guru_id) REFERENCES gurus(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
    FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajarans(id) ON DELETE CASCADE,
    FOREIGN KEY (jenis_nilai_id) REFERENCES jenis_nilais(id) ON DELETE CASCADE
);

-- === RAPOR ===

CREATE TABLE rapors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    siswa_id BIGINT UNSIGNED NOT NULL,
    kelas_id BIGINT UNSIGNED NOT NULL,
    tahun_ajaran_id BIGINT UNSIGNED NOT NULL,
    semester ENUM('Ganjil', 'Genap') NOT NULL,
    status ENUM('draft', 'final', 'cetak') DEFAULT 'draft',
    catatan_wali_kelas TEXT NULL,
    catatan_bk TEXT NULL,
    tanggal_cetak TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswas(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
    FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajarans(id) ON DELETE CASCADE,
    UNIQUE (siswa_id, tahun_ajaran_id, semester)
);

-- === EKSTRAKURIKULER ===

CREATE TABLE ekskuls (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lembaga_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(255) NOT NULL,
    pembina_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lembaga_id) REFERENCES lembagas(id) ON DELETE CASCADE,
    FOREIGN KEY (pembina_id) REFERENCES gurus(id) ON DELETE SET NULL
);

CREATE TABLE anggota_ekskuls (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ekskul_id BIGINT UNSIGNED NOT NULL,
    siswa_id BIGINT UNSIGNED NOT NULL,
    tahun_ajaran_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ekskul_id) REFERENCES ekskuls(id) ON DELETE CASCADE,
    FOREIGN KEY (siswa_id) REFERENCES siswas(id) ON DELETE CASCADE,
    FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajarans(id) ON DELETE CASCADE
);

-- === PELANGGARAN / TATA TERTIB ===

CREATE TABLE kategoris_pelanggarans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lembaga_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(255) NOT NULL,
    poin INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lembaga_id) REFERENCES lembagas(id) ON DELETE CASCADE
);

CREATE TABLE pelanggarans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    siswa_id BIGINT UNSIGNED NOT NULL,
    kategori_pelanggaran_id BIGINT UNSIGNED NOT NULL,
    guru_id BIGINT UNSIGNED NOT NULL,           -- Pencatat (BK / Wali Kelas)
    deskripsi TEXT NULL,
    tanggal DATE NOT NULL,
    tindakan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswas(id) ON DELETE CASCADE,
    FOREIGN KEY (kategori_pelanggaran_id) REFERENCES kategoris_pelanggarans(id) ON DELETE CASCADE,
    FOREIGN KEY (guru_id) REFERENCES gurus(id) ON DELETE CASCADE
);

-- === USER & AUTH ===

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lembaga_id BIGINT UNSIGNED NULL,            -- NULL untuk Super Admin & Admin Yayasan
    yayasan_id BIGINT UNSIGNED NULL,
    guru_id BIGINT UNSIGNED NULL,
    siswa_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,                  -- 'super_admin', 'admin_yayasan', 'kepala_lembaga',
                                                -- 'admin_lembaga', 'kurikulum', 'kesiswaan',
                                                -- 'guru', 'siswa', 'orang_tua'
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lembaga_id) REFERENCES lembagas(id) ON DELETE CASCADE,
    FOREIGN KEY (yayasan_id) REFERENCES yayasans(id) ON DELETE CASCADE,
    FOREIGN KEY (guru_id) REFERENCES gurus(id) ON DELETE SET NULL,
    FOREIGN KEY (siswa_id) REFERENCES siswas(id) ON DELETE SET NULL
);
```

## 2. Catatan Index

```sql
-- Index penting untuk performa
CREATE INDEX idx_presensi_jadwal ON presensis(jadwal_id, tanggal);
CREATE INDEX idx_nilai_siswa_mapel ON nilais(siswa_id, mapel_id, tahun_ajaran_id);
CREATE INDEX idx_jadwal_kelas ON jadwals(kelas_id, hari);
CREATE INDEX idx_riwayat_kelas_aktif ON riwayat_kelas_siswas(siswa_id, tahun_ajaran_id);
CREATE INDEX idx_users_role ON users(role, lembaga_id);
CREATE INDEX idx_gurus_lembaga ON gurus(lembaga_id, is_active);
CREATE INDEX idx_siswas_lembaga ON siswas(lembaga_id, status);
CREATE INDEX idx_absensi_ptk_tanggal ON absensi_ptks(guru_id, tanggal);
CREATE INDEX idx_absensi_ptk_bulan ON absensi_ptks(lembaga_id, tanggal);
CREATE INDEX idx_agenda_mengajar_jadwal ON agenda_mengajars(jadwal_id, tanggal);
CREATE INDEX idx_agenda_mengajar_guru ON agenda_mengajars(guru_id, tanggal);
```

## 3. Multi-Tenant Strategy

- **Isolasi**: Setiap query utama di-scope dengan `lembaga_id`.
- **Global scope**: Laravel global scope `LembagaScope` otomatis menambahkan `WHERE lembaga_id = ?`.
- **Yayasan scope**: Untuk data yayasan (tahun ajaran), scope via `yayasan_id`.
