# Active Context

> Penjejak terperinci ialah `docs/superpowers/plans/STATUS.md` — ia mencatat
> tugasan mana yang ditutup, pepijat yang ditemui semakan, dan keputusan yang
> menunggu pengguna. Fail ini menyimpan gambaran keseluruhan sahaja.
> Setakat 9 September 2026: **M02, M03 dan M01 selesai**, 217 ujian lulus.

## Current Focus
- **Lapisan asas selesai: M02, M03, M01, dan asas M16.** Sistem kini mempunyai identiti, direktori dan konfigurasi penuh.
- **M01 selesai untuk fasa 1.** Enam tab konfigurasi, tetapan bercache, kalendar cuti, dan nilai lalai yang boleh terus digunakan.

## Next Steps
- **Matlamat pengguna: lengkapkan modul tempahan bilik**, ikut `docs-claude/`. Skop dipersetujui: susunan kebergantungan penuh, keperluan fasa 1 (W) sahaja.
- Baki fasa 1: **M04 Katalog Bilik → M06 Kelulusan → M05 Enjin Tempahan**, kemudian M14 e-mel.
- **M04, M05 dan M06 belum mempunyai pelan.** Ketiga-tiganya perlu melalui kitaran spec dan pelan sebelum sebarang kod ditulis.
- Semua kebergantungan M01 bagi tempahan sudah sedia: waktu operasi (FR-TMP-06), kalendar cuti, had tempoh dan tempoh awalan per peranan (FR-TMP-08), serta senarai susun atur dan kemudahan bilik untuk M04.

## Recent Changes
- Install `spatie/laravel-permission` v8 + publish config/migration + migrate.
- Migrations baharu: `create_permission_tables`, `create_organization_units_table`, `create_locations_table`, `add_authentication_fields_to_users_table` (`is_active`, `last_login_at/ip`, `organization_unit_id`, `primary_location_id`, `delegate_*`), `create_audit_logs_table`.
- Models: `User` (+`HasRoles`, `isActive()`, `dashboardView()`, `rolePriority()`), `OrganizationUnit`, `Location`, `AuditLog`.
- Auth controllers (`App\Http\Controllers\Auth\*`) + `LoginRequest` (throttle 5/15min) + `LoginAuditLogger` service.
- Middleware: `EnsureValidSessionTimeout`, `EnsureUserIsActive` (web append dalam `bootstrap/app.php`).
- Admin: `UserController`, `RoleController` + Form Requests + views `admin/users/*`, `admin/roles/*`.
- Views: `layouts/app|guest`, `auth/*`, `dashboard/{8 role}` + komponen `ui`/`partials`.
- Seeders: `RolesAndPermissionsSeeder`, `OrganizationStructureSeeder`, `DemoUsersSeeder` (8 akaun demo, 1 per role).
- Lang `ms`/`en`: auth, passwords, validation. `.env`: `APP_NAME=e-Fasiliti`, `APP_LOCALE=ms`.
- Tests: `AuthenticationTest`, `DashboardTest`, `PasswordResetTest` (5), `Admin\UserManagementTest`, `Admin\RoleManagementTest` — **42 passed (152 assertions)**.
- Betulkan: factory `is_active => true`; `User::isActive()` cast bool; `LoginAuditLogger` import `Str`.
- Betulkan reset password: `NewPasswordController` closure `use ($request)` (ErrorException "Undefined variable $request") + simpan `auth_authenticated_at` sebagai `time()` **sebelum** `session()->regenerate()` (selari dengan controller login).
- Selarikan `PasswordResetLinkController` dengan NFR-S02: respons sentiasa sama walaupun emel tidak wujud (elak user enumeration).
- Konfigurasi Mailpit Laragon: `MAIL_MAILER=smtp`, port 1025 (bukan 2525); emel ujian disahkan diterima.
- `npm run build` — perlu sebelum ujian feature (Vite manifest).

## Notes / Gotchas
- Semua bacaan tetapan mesti melalui `setting()` atau `SettingsRepository`, bukan pertanyaan terus ke `system_settings`. Cache dibatalkan hanya oleh `SettingsRepository::set()` dan `flush()`.
- `operating_hours` polimorfik: baris tanpa pemilik ialah waktu lalai organisasi. M04 menambah baris milik bilik pada jadual yang sama.
- Templat notifikasi menolak pemegang tempat di luar lajur `placeholders`. Tambah pemegang tempat baharu pada lajur itu dahulu sebelum menggunakannya.
- Pembantu `setting()` dimuatkan melalui kunci `files` dalam `composer.json`. Selepas menariknya keluar, jalankan `composer dump-autoload`.
- **Cast `date` menulis datetime penuh.** `Holiday::date` disimpan sebagai `2026-05-01 00:00:00`. MySQL memangkasnya ke lajur DATE, SQLite tidak. Jangan sesekali gunakan padanan kesamaan atau `firstOrCreate` pada lajur tarikh; guna `whereDate`. Pepijat ini berlaku dua kali: dalam `HolidayRequest` dan dalam seeder konfigurasi.
- **Jalankan `php artisan migrate` selepas tugasan yang menyentuh skema.** Ujian membina semula skema SQLite setiap kali, jadi jadual baharu boleh lulus semua ujian sambil tidak wujud dalam pelayar. Lihat `.ai/rules/migrations.md`.
- Kod lokasi kini unik dalam induk yang sama, bukan menyeluruh. Keunikan aras kampus dikuatkuasakan dalam `LocationStoreRequest`, bukan indeks, kerana NULL dianggap berbeza oleh MySQL dan SQLite.
- Laluan M03 menggunakan middleware `can:`, bukan `role:`, kerana tiga peranan berkongsi capaian.
- Jangan jalankan `migrate:fresh` atau `migrate:rollback`. Fail `.env` menunjuk pangkalan data pembangunan sebenar. `migrate` biasa dibenarkan dan memang perlu; `db:seed` hanya dengan kebenaran pengguna.
- Ujian feature memerlukan `npm run build` dahulu (manifest Vite) — otherwise render view gagal.
- `assertGuest`/`assertAuthenticatedAs` ialah kaedah `TestCase`, bukan `TestResponse` — jangan rantai selepas `assertRedirect`.
- Spatie v8 middleware namespace: `Spatie\Permission\Middleware\{RoleMiddleware, PermissionMiddleware}` (tiada lagi `PermissionMiddleware` lama).
- `AuditLog::metadata` cast `array`; `user_agent` dipotong 255 (`Str::limit`).
- Sesi admin: jangan padam/deactivate diri sendiri atau admin aktif terakhir (dilindungi dalam controller + diuji).