# Teknologi & Arsitektur — Aplikasi KBM Multi-Lembaga

## 1. Tech Stack

| Lapisan           | Teknologi                  | Catatan                           |
| ----------------- | -------------------------- | --------------------------------- |
| **Backend**       | Laravel 13.x (PHP 8.3+)    | Framework utama                   |
| **Database**      | MySQL 8.x                  | Relasional                        |
| **Frontend**      | Blade + Tailwind CSS       | Server-side rendering             |
| **JavaScript**    | Alpine.js                  | Interaktivitas tanpa SPA berat    |
| **Build Tool**    | Vite + Laravel Vite Plugin | Sudah terkonfigurasi              |
| **Auth**          | Laravel Breeze             | Scaffolding auth cepat            |
| **API**           | Laravel Sanctum            | API token untuk Sisda integration |
| **Queue**         | Laravel Queue (Database)   | Import Sisda & notifikasi async   |
| **Task Schedule** | Laravel Scheduler          | Cron job internal                 |
| **Testing**       | PHPUnit                    | Unit & feature test               |

## 2. Arsitektur Aplikasi

### 2.1 Struktur Folder (Laravel)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/                        # API controllers (Sisda)
│   │   ├── Yayasan/                    # Admin Yayasan controllers
│   │   ├── Lembaga/                    # Per-lembaga controllers
│   │   │   ├── MasterDataController.php
│   │   │   ├── AkademikController.php
│   │   │   ├── KesiswaanController.php
│   │   │   ├── AbsensiPTKController.php
│   │   │   └── AgendaMengajarController.php
│   │   │   └── ...
│   │   ├── GuruController.php
│   │   └── Auth/
│   ├── Middleware/
│   │   ├── CheckRole.php               # Role-based middleware
│   │   └── LembagaScope.php            # Multi-tenant scope
│   ├── Requests/                       # Form requests
│   └── Resources/                      # API resources
├── Models/
│   ├── Yayasan.php
│   ├── Lembaga.php
│   ├── Guru.php
│   ├── Siswa.php
│   ├── Mapel.php
│   ├── Cp.php / Tp.php / Atp.php
│   ├── Jadwal.php
│   ├── JamKerjaLembaga.php           (konfigurasi jam masuk/pulang)
│   ├── AbsensiPtk.php                (check-in/check-out guru harian)
│   ├── AgendaMengajar.php            (selfie bukti mengajar)
│   └── ...
├── Services/
│   ├── SisdaImportService.php          # Import dari Sisda API
│   ├── KodeGuruService.php             # Generate kode guru
│   ├── RaporService.php                # Generate rapor
│   ├── AbsensiPTKService.php           # Logic check-in/check-out, status
│   └── AgendaMengajarService.php       # Simpan selfie + metadata, verifikasi
├── Policies/                           # Authorization policies
└── Console/
    └── Commands/
        └── ImportSisda.php             # Scheduled import Sisda
```

### 2.2 Multi-Tenant (Lembaga Scope)

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    Model::addGlobalScope(new LembagaScope);
}

// app/Models/Scopes/LembagaScope.php
class LembagaScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->lembaga_id) {
            $builder->where('lembaga_id', auth()->user()->lembaga_id);
        }
    }
}
```

### 2.3 Auth & Guard

- **Satu Guard** (`web`) dengan diferensiasi role.
- **Login** berdasarkan email + password.
- **Middleware `role:admin_yayasan,kurikulum`** di routes.
- **Sanctum** untuk API token (Sisda import).

## 3. Integrasi Sisda API

| Item               | Detail                                                    |
| ------------------ | --------------------------------------------------------- |
| **Metode**         | REST API via HTTP                                         |
| **Auth**           | API Token / JWT                                           |
| **Data**           | JSON: siswa, kelas, jurusan                               |
| **Frekuensi**      | On-demand (manual trigger) + scheduled (harian/mingguan)  |
| **Error Handling** | Log error, notifikasi admin jika gagal                    |
| **Mapping**        | Mapping field Sisda → field lokal di `SisdaImportService` |

## 4. Environment & Deployment

- **Laragon** untuk development (local).
- **Production**: VPS / shared hosting dengan PHP 8.3+ & MySQL.
- **Environment Variables** di `.env` untuk konfigurasi per tenant.

## 5. Keamanan

| Aspek         | Implementasi                                                          |
| ------------- | --------------------------------------------------------------------- |
| Auth          | Laravel Bcrypt password, session-based                                |
| RBAC          | Middleware + Gates/Policies                                           |
| CSRF          | Laravel CSRF protection                                               |
| XSS           | Blade escaping `{{ }}`                                                |
| SQL Injection | Eloquent ORM (parameter binding)                                      |
| Rate Limiting | Laravel throttle middleware                                           |
| Audit Trail   | Log aktivitas di tabel `activity_log` (jika pakai spatie/activitylog) |
