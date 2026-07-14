# Project Instructions — EduTaqwa KBM Multi-Lembaga

## Plan docs must stay in sync

This project has a `/plan/` folder with planning documents:

| File                         | Isi                                                     |
| ---------------------------- | ------------------------------------------------------- |
| `plan/skema.md`              | Gambaran umum, role, fitur, matriks akses, relasi data  |
| `plan/fitur.md`              | Detail fitur per modul, prioritas development           |
| `plan/database.md`           | SQL DDL, index, multi-tenant strategy                   |
| `plan/role-dan-hak-akses.md` | Role list, matriks CRUD per fitur, hierarki approval    |
| `plan/alur-kerja.md`         | Semua alur kerja (approval guru, jadwal, presensi, dll) |
| `plan/teknologi.md`          | Tech stack, arsitektur Laravel, integrasi Sisda         |
| `plan/tahapan.md`            | Estimasi & timeline development                         |
| `plan/roadmap.md`            | Checklist pengembangan (centang item selesai)           |

**Aturan**: Setiap kali melakukan perubahan kode yang menambah/mengubah/menghapus fitur, database, role, atau alur kerja — WAJIB update dokumen plan yang relevan agar sinkron dengan implementasi.

Caranya:

1. Identifikasi dokumen plan mana yang terdampak perubahan.
2. Update konten sesuai implementasi terbaru.
3. Jangan hapus dokumen plan tanpa persetujuan.
