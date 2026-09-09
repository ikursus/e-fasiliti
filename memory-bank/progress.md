# Progress

## What works
- **Manual authentication (M02, sebahagian)** — log masuk/logout, reset kata laluan, confirm-password; throttle 5 gagal/15 min (FR-USR-10) via `RateLimiter` dalam `LoginRequest`.
- **Spatie laravel-permission v8** terpasang; 8 role SRS (R1–R8) + permission awal (`users.manage`, `roles.manage`, `dashboards.view.*`) direkod melalui `RolesAndPermissionsSeeder`.
- **Middleware**: `EnsureValidSessionTimeout` (30 min tak aktif / 8 jam mutlak) + `EnsureUserIsActive` (deaktif akaun → logout automatik) didaftarkan dalam `bootstrap/app.php` ($middleware->web append).
- **Audit login/logout** (FR-AUD-03): `LoginAuditLogger` → `audit_logs` table; `last_login_at`/`last_login_ip` dikemas kini.
- **Dashboard per role**: `DashboardController` pilih view `dashboard.{role}` mengikut keutamaan role; fallback `kakitangan`.
- **Admin CRUD (R8 sahaja)**: `Admin\UserController` (cari/penapis role, deactivate, lindungi admin terakhir & diri sendiri, audit `user.created|updated|deleted`) + `Admin\RoleController` (CRUD + syncPermissions, blok padam role masih diguna).
- **Layout Tailwind v4**: `layouts/app` (sidebar gelap + topbar + Alpine.js), `layouts/guest`, komponen `ui`/`partials` (stat-card, badge, empty-state, dsb.).
- **Lang**: `lang/ms` + `lang/en` (auth, passwords, validation); `APP_LOCALE=ms`, `APP_NAME=e-Fasiliti`.
- **Ujian: 42 lulus (152 assertions)** — AuthenticationTest (7), DashboardTest (8, data-provider 8 role), PasswordResetTest (5), Admin\UserManagementTest (10), Admin\RoleManagementTest (7), ExampleTest (1) + lain-lain.
- `npm run build` dijalankan — manifest Vite wujud (wajib sebelum ujian feature/render view).

- **M03 — Direktori Organisasi & Lokasi (siap)** — pokok lokasi empat aras dengan buka/tutup Alpine, CRUD lokasi dan unit organisasi, nyahaktif melata ke keturunan, padam disekat oleh `referenceSummary()`, kod lokasi unik dalam induk (DRD §4.1), komponen `<x-ui.location-picker>` dipakai borang pengguna. Kebenaran `lokasi.*` dan `unit-organisasi.*` mengikut matriks modul §3.
- **Perakam audit umum** — `App\Services\Audit\AuditRecorder` merekod nilai sebelum/selepas bagi medan yang berubah sahaja (FR-ADM-08), dengan parameter `context` bagi fakta operasi seperti `cascaded_ids`.
- **Invarian aktif dua arah** — rekod tidak boleh aktif di bawah induk yang tidak aktif, dikuatkuasakan dalam Form Request dan dalam tindakan toggle bagi kedua-dua hierarki.

- **M01 — Pentadbiran Sistem & Konfigurasi (siap untuk fasa 1)** — `system_settings` kunci-nilai dengan `SettingsRepository` bercache dan pembantu `setting()`; enam tab pentadbiran: umum, waktu operasi, cuti umum, peraturan tempahan per peranan, nilai rujukan lima senarai, dan templat notifikasi dengan pengesahan pemegang tempat. Setiap penulisan diaudit dengan nilai sebelum/selepas (FR-ADM-08).
- **`HolidayCalendar`** — `isHoliday()` dan `isBookable()`, menyokong cuti berulang tahunan; logik tulen tanpa pangkalan data supaya M05 boleh guna semula.
- **`SystemConfigurationSeeder`** — nilai lalai boleh guna: waktu operasi tujuh hari, lima cuti umum berulang, 21 nilai rujukan termasuk susun atur dan kemudahan bilik yang diperlukan M04, dan tiga templat notifikasi.

## What's left to build
- Modul domain (M04–M15): bilik, tempahan, kelulusan, daftar masuk, sokongan, aset, tiket, penyelenggaraan, vendor, stok, notifikasi, laporan. Permission per modul akan ditambah pada seeder mengikut matriks `docs-claude/01-modules.md §3`.
- Tetapan M01 bagi M05, M07 dan M14 sudah wujud tetapi belum ada penggunanya. Sahkan bentuk datanya semasa modul berkenaan dibina.
- **Dilangkau atas keputusan skop fasa 1:** tab daftar masuk (FR-ADM-04, W3) dan keutamaan tiket (FR-ADM-05, W2). Baris tetapan `checkin.*` sudah dibenihkan; model `TicketPriority` belum wujud.
- Audit penuh M16 (audit_logs sedia; perlu UI pentadbiran + lebih banyak event).
- 2FA (FR-USR-09) — ditangguh ke peringkat lanjutan (keputusan pengguna).
- Nama penuh, unit organisasi, lokasi utama pengguna belum dipaparkan dalam UI profil (data sedia dalam skema).

## Known issues
- `intl` PHP extension missing → `db:table` output & number formatting fail dalam CLI (kosmetik).
- Terminal PowerShell sesi ini kerap gagal capture output (shell integration); guna `| Out-String` atau `Select-Object -Last N`.
- Pint `--dirty` tidak berfungsi (bukan repo git) — guna `vendor/bin/pint --format agent`.