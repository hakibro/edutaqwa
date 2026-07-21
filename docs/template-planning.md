# Percakapan: Template Tampilan EduTaqwa

**Tanggal:** 2026-07-21
**Sesi:** Planning template UI untuk project EduTaqwa KBM Multi-Lembaga

---

## Ringkasan Keputusan

Template tampilan dibuat di **project terpisah** (hybrid approach) sebelum implementasi di EduTaqwa. Data pakai **static dummy** langsung di HTML.

---

## 1. Konteks Project

**My Darut Taqwa** — Aplikasi manajemen KBM multi-lembaga di bawah yayasan.

### Tech Stack

- Laravel 13.x, PHP 8.3+
- Tailwind CSS v4 (Vite plugin, `@theme` di CSS, no `tailwind.config.js`)
- Alpine.js
- Blade UI Kit Heroicons
- MySQL 8.x

### 10 Role

1. Super Admin
2. Admin Yayasan
3. Kepala Lembaga
4. Admin Lembaga/TU
5. Kurikulum
6. Kesiswaan
7. Guru
8. Validator Presensi
9. Siswa
10. Orang Tua/Wali

---

## 2. Hal yang Disampaikan ke Frontend Dev

### A. Tech Stack & Build

- Tailwind v4: `@tailwindcss/vite`, theme via CSS `@theme`, no JS config
- Font: Figtree via Bunny Fonts
- Icons: Blade UI Kit Heroicons (`<x-heroicon-o-...>`)
- JS: Alpine.js CDN

### B. 3 Layout Utama

| Layout            | Gunakan untuk                                 |
| ----------------- | --------------------------------------------- |
| `app.blade.php`   | Semua role — sidebar desktop + overlay mobile |
| `guest.blade.php` | Login/register — centered card                |
| `guru.blade.php`  | Dashboard guru — mobile-first bottom navbar   |

### C. Responsive

- `< lg`: Bottom navbar (guru), hamburger menu, stacked
- `lg:`: Sidebar fixed, multi-column, full tables

### D. Role-Based UI Patterns

- **Guru**: Mobile-first, stat cards, quick chips, absensi banner
- **Kurikulum**: Grid editor jadwal, drag-drop timetable, CP/TP/ATP stat cards
- **Admin**: CRUD tables, import/export, bulk actions
- **Validator Presensi**: Perizinan form, bulk select, calendar view

### E. Component Patterns

- Stat cards (4 grid)
- Alert banner (absensi status)
- Quick action chips
- Data table + pagination
- Modal/popup (import, preview)
- Wizard stepper (3 langkah jurnal)
- Grid editor (click cell → select)

### F. Khusus Guru

- Camera capture: kamera depan, no file upload
- GPS auto-fill, wajib
- Presensi cepat: "Semua Hadir" / "Semua Tidak Hadir"
- Bottom nav 5 item: Dashboard, Jurnal, Jadwal, Absensi, Profil

---

## 3. Keputusan: Template Terpisah vs Langsung Views

| Aspek        | Langsung Views      | Template Terpisah |
| ------------ | ------------------- | ----------------- |
| Kecepatan    | Cepat               | 1-2 hari setup    |
| Iterasi flow | Susah               | Mudah             |
| Refactor     | Berisiko (20+ file) | Aman              |
| Demo         | Butuh backend       | Static HTML cukup |

**Pilihan:** Template terpisah (hybrid approach).

**Alasan:**

- 10 role → konsistensi krusial
- Flownya masih iterasi
- Guru mobile-first → butuh prototype
- Wizard & grid editor kompleks

**Hybrid steps:**

1. Template project (layouts, components, dummy pages)
2. Port ke EduTaqwa (replace dummy dengan Blade + Eloquent)

---

## 4. Strategi Data: Static Dummy

- Data tulis langsung di HTML
- Tidak pakai JSON mock (keputusan user)
- Contoh: `<p class="text-3xl font-bold">4</p>`

### Aturan Port

| Template        | EduTaqwa                        |
| --------------- | ------------------------------- |
| Hardcode number | `{{ $jadwalHariIni->count() }}` |
| Static list     | `@foreach` Eloquent             |
| Mock status     | Helper/enum                     |
| Dummy user      | `auth()->user()`                |

### Tips Port

1. Nama variabel mock = nama variabel controller
2. No logic di template, hanya tampilan
3. Tailwind classes sama persis untuk copy-paste

---

## 5. Struktur Template Project

```
template-edutaqwa/
├── index.html              # Landing/demo selector
├── layouts/
│   ├── app.html
│   ├── guru.html
│   └── guest.html
├── components/
│   ├── stat-card.html
│   ├── data-table.html
│   ├── modal.html
│   ├── step-wizard.html
│   └── alert-banner.html
├── pages/
│   ├── dashboard-guru.html
│   ├── dashboard-kurikulum.html
│   ├── dashboard-admin.html
│   ├── jurnal-wizard.html
│   ├── jadwal-grid.html
│   └── master-data.html
├── css/
│   └── app.css             # Copy dari EduTaqwa
└── js/
    └── app.js              # Alpine.js CDN
```

### Setup Minimal

- Tailwind v4 CDN atau Vite vanilla
- Alpine.js CDN
- Heroicons inline SVG
- Figtree font Bunny Fonts

---

## 6. Next Steps

1. Buat folder `template-edutaqwa/`
2. Setup Vite + Tailwind v4 + Alpine.js
3. Copy `app.css` dari EduTaqwa
4. Buat 3 layout utama
5. Buat component dasar
6. Buat 1 dummy dashboard per role
7. Test responsive
8. Port ke EduTaqwa

---

## File Referensi di Project

- `plan/skema.md` — Role, fitur, relasi data
- `plan/fitur.md` — Detail fitur per modul
- `plan/teknologi.md` — Tech stack, arsitektur
- `resources/views/layouts/app.blade.php` — Layout utama
- `resources/views/dashboards/guru.blade.php` — Contoh dashboard guru
- `resources/css/app.css` — Tailwind v4 theme
- `composer.json` — Dependencies (Heroicons, Laravel 13)
- `package.json` — Vite, Tailwind v4, Alpine.js
