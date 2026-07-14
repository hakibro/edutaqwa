<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Lembaga;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Super Admin
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@edutaqwa.test',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Yayasan + Admin Yayasan
        $yayasan = Yayasan::factory()->create([
            'nama' => 'Yayasan Al-Ihsan',
            'kode' => 'YI01',
        ]);

        User::factory()->create([
            'name' => 'Admin Yayasan',
            'email' => 'admin.yayasan@edutaqwa.test',
            'password' => bcrypt('password'),
            'role' => 'admin_yayasan',
            'yayasan_id' => $yayasan->id,
            'is_active' => true,
        ]);

        // Lembaga
        $lembaga = Lembaga::factory()->create([
            'yayasan_id' => $yayasan->id,
            'nama' => 'MI Al-Ihsan',
            'kode' => 'MIA',
            'tingkat' => 'MI',
            'unit_formal' => 'MI',
        ]);

        // Tambah lembaga kedua untuk variasi
        $lembaga2 = Lembaga::factory()->create([
            'yayasan_id' => $yayasan->id,
            'nama' => 'SMA Al-Ihsan',
            'kode' => 'SMAA',
            'tingkat' => 'SMA',
            'unit_formal' => 'SMA',
        ]);

        // Tahun Ajaran Aktif (mencakup Ganjil & Genap)
        $tahunAjaran = \App\Models\TahunAjaran::create([
            'yayasan_id' => $yayasan->id,
            'nama' => '2025/2026',
            'tanggal_mulai' => '2025-07-14',
            'tanggal_selesai' => '2026-06-30',
            'is_active' => true,
        ]);

        // Kepala Lembaga
        $kepala = Guru::factory()->create([
            'lembaga_id' => $lembaga->id,
            'nama' => 'Kepala MI Al-Ihsan',
            'email' => 'kepala@edutaqwa.test',
            'is_approved' => true,
        ]);
        User::factory()->create([
            'name' => 'Kepala MI Al-Ihsan',
            'email' => 'kepala@edutaqwa.test',
            'password' => bcrypt('password'),
            'role' => 'kepala_lembaga',
            'lembaga_id' => $lembaga->id,
            'guru_id' => $kepala->id,
            'is_active' => true,
        ]);

        // Admin Lembaga
        User::factory()->create([
            'name' => 'Admin Lembaga',
            'email' => 'admin.lembaga@edutaqwa.test',
            'password' => bcrypt('password'),
            'role' => 'admin_lembaga',
            'lembaga_id' => $lembaga->id,
            'is_active' => true,
        ]);

        // Kurikulum
        $guruKurikulum = Guru::factory()->create([
            'lembaga_id' => $lembaga->id,
            'nama' => 'WK Kurikulum',
            'email' => 'kurikulum@edutaqwa.test',
            'is_approved' => true,
        ]);
        User::factory()->create([
            'name' => 'WK Kurikulum',
            'email' => 'kurikulum@edutaqwa.test',
            'password' => bcrypt('password'),
            'role' => 'kurikulum',
            'lembaga_id' => $lembaga->id,
            'guru_id' => $guruKurikulum->id,
            'is_active' => true,
        ]);

        // Kesiswaan
        $guruKesiswaan = Guru::factory()->create([
            'lembaga_id' => $lembaga->id,
            'nama' => 'WK Kesiswaan',
            'email' => 'kesiswaan@edutaqwa.test',
            'is_approved' => true,
        ]);
        User::factory()->create([
            'name' => 'WK Kesiswaan',
            'email' => 'kesiswaan@edutaqwa.test',
            'password' => bcrypt('password'),
            'role' => 'kesiswaan',
            'lembaga_id' => $lembaga->id,
            'guru_id' => $guruKesiswaan->id,
            'is_active' => true,
        ]);

        // Guru
        $guruUser = Guru::factory()->create([
            'lembaga_id' => $lembaga->id,
            'nama' => 'Guru Biasa',
            'is_approved' => true,
            'email' => 'guru@edutaqwa.test',
        ]);
        User::factory()->create([
            'name' => 'Guru Biasa',
            'email' => 'guru@edutaqwa.test',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'lembaga_id' => $lembaga->id,
            'guru_id' => $guruUser->id,
            'is_active' => true,
        ]);

        // Additional dummy data — lembaga kedua
        User::factory()->create([
            'name' => 'Admin SMA Al-Ihsan',
            'email' => 'admin.sma@edutaqwa.test',
            'password' => bcrypt('password'),
            'role' => 'admin_lembaga',
            'lembaga_id' => $lembaga2->id,
            'is_active' => true,
        ]);

        // Jurusan untuk SMA
        $jurusanIpa = \App\Models\Jurusan::create(['lembaga_id' => $lembaga2->id, 'nama' => 'IPA', 'kode' => 'IPA']);
        $jurusanIps = \App\Models\Jurusan::create(['lembaga_id' => $lembaga2->id, 'nama' => 'IPS', 'kode' => 'IPS']);

        // Kelas
        \App\Models\Kelas::create(['lembaga_id' => $lembaga->id, 'nama' => 'I-A', 'tingkat' => '1']);
        \App\Models\Kelas::create(['lembaga_id' => $lembaga->id, 'nama' => 'I-B', 'tingkat' => '1']);
        \App\Models\Kelas::create(['lembaga_id' => $lembaga->id, 'nama' => 'II-A', 'tingkat' => '2']);
        \App\Models\Kelas::create(['lembaga_id' => $lembaga2->id, 'jurusan_id' => $jurusanIpa->id, 'nama' => 'X IPA 1', 'tingkat' => 'X']);
        \App\Models\Kelas::create(['lembaga_id' => $lembaga2->id, 'jurusan_id' => $jurusanIps->id, 'nama' => 'X IPS 1', 'tingkat' => 'X']);

        // Siswa dummy untuk MI
        \App\Models\Siswa::create(['lembaga_id' => $lembaga->id, 'nis' => '1001', 'nama' => 'Ahmad Siswa', 'jenis_kelamin' => 'L', 'status' => 'aktif', 'is_active' => true]);
        \App\Models\Siswa::create(['lembaga_id' => $lembaga->id, 'nis' => '1002', 'nama' => 'Budi Santoso', 'jenis_kelamin' => 'L', 'status' => 'aktif', 'is_active' => true]);
        \App\Models\Siswa::create(['lembaga_id' => $lembaga->id, 'nis' => '1003', 'nama' => 'Siti Aisyah', 'jenis_kelamin' => 'P', 'status' => 'aktif', 'is_active' => true]);
        \App\Models\Siswa::create(['lembaga_id' => $lembaga2->id, 'nis' => '2001', 'nama' => 'Doni Prasetyo', 'jenis_kelamin' => 'L', 'status' => 'aktif', 'is_active' => true]);

        // Guru pending approval
        Guru::factory()->create([
            'lembaga_id' => $lembaga->id,
            'nama' => 'Guru Baru Pending',
            'email' => 'guru.pending@edutaqwa.test',
            'is_approved' => false,
            'is_active' => true,
            'status_satminkal' => true,
        ]);
        Guru::factory()->create([
            'lembaga_id' => $lembaga->id,
            'nama' => 'Guru Baru Pending 2',
            'email' => 'guru.pending2@edutaqwa.test',
            'is_approved' => false,
            'is_active' => true,
            'status_satminkal' => false,
        ]);

        // Additional dummy data
        Yayasan::factory(2)->create();
        Lembaga::factory(3)->create();
        Guru::factory(5)->create();
    }
}
