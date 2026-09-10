# Active Context

> Penjejak terperinci ialah `docs/superpowers/plans/STATUS.md` — ia mencatat
> tugasan mana yang ditutup, pepijat yang ditemui semakan, dan keputusan yang
> menunggu pengguna. Fail ini menyimpan gambaran keseluruhan sahaja.
> Setakat 9 September 2026 (malam): **M02, M03, M01 selesai; modul profil dan
> chatbot (M17) siap di `main`; M04 Katalog Bilik SIAP** (sesi 1 + 2), **256
> ujian lulus**, branch `feature/m04-katalog-bilik`. Seeder kebenaran ditunda
> atas keputusan pengguna.

## Current Focus
- **M04 Katalog Bilik siap sepenuhnya dari segi kod** — migrasi, model, kebenaran, form requests, controller, 5 views, 7 laluan `admin.rooms.*`, 38 ujian baharu. Ringkasan: STATUS.md §M04 dan blok "✅ Status Pelaksanaan" fail pelan M04.
- **Modul Profil selesai di `main`** — self-service profil dengan muat naik foto; `storage:link` dicipta buat pertama kali; `ProfileTest` 10/10.
- **Chatbot (M17) selesai di `main`** — `GeminiChatService`, tetapan chatbot, jadual `chat_sessions`/`chat_messages`, kebenaran `chatbot.*`, UI chat Alpine.
- **Lapisan asas selesai: M02, M03, M01, dan asas M16.** Sistem kini mempunyai identiti, direktori dan konfigurasi penuh.
- **M01 selesai untuk fasa 1.** Enam tab konfigurasi, tetapan bercache, kalendar cuti, dan nilai lalai yang boleh terus digunakan.

## Next Steps
- **Matlamat pengguna: lengkapkan modul tempahan bilik**, ikut `docs-claude/`. Skop dipersetujui: susunan kebergantungan penuh, keperluan fasa 1 (W) sahaja.
- **M04 selesai — baki fasa 1: M06 Kelulusan → M05 Enjin Tempahan → M14 e-mel.** Susunan dipersetujui. M06 perlu kitaran spec dan pelan dahulu.
- **Tertunggak keputusan pengguna:** (1) jalankan `db:seed --class=RolesAndPermissionsSeeder` — tanpanya `/admin/rooms` 403 di pelayar; (2) `.env` kini `APP_LOCALE=en` — kembalikan kepada `ms`?
- Semua kebergantungan M01 bagi tempahan sudah sedia: waktu operasi (FR-TMP-06), kalendar cuti, had tempoh dan tempoh awalan per peranan (FR-TMP-08), serta senarai susun atur dan kemudahan bilik untuk M04.

## Recent Changes
- **M04 sesi 2 (9 Sep):** 5 views `admin/rooms/*` (repeater susun atur Alpine, `<x-ui.location-picker>`, waktu operasi 7 hari, `deactivate.blade.php` FR-BLK-08); 7 laluan `admin.rooms.*` (`can:bilik.*`) + sidebar "Fasiliti"; 24 ujian ciri baharu (`RoomManagementTest` 11, `RoomValidationTest` 8, `RoomDeactivationTest` 5); **256 ujian lulus**; komit `3ab5a34`. **db:seed ditunda — keputusan pengguna.** Pembetulan: import `Role` mesti dari `Spatie\Permission\Models\Role`.
- **Profil (digabung ke `main`, PR #1):** self-service profil dengan muat naik foto; `profile_photo_path`; `ProfileController` + `ProfileTest` 10/10; `storage:link`; `User::profilePhotoUrl()` guna `asset(storage/...)`.
- **Chatbot/M17 (digabung ke `main`, PR #2):** `GeminiChatService`, `chat_sessions`/`chat_messages`, `ChatbotController` + `ChatbotSettingsController`, laluan `chatbot.*`, kebenaran `chatbot.guna`/`chatbot.tetapan`, UI chat Alpine, kunci Gemini API daripada skrin admin; 24 ujian chatbot.
- **M04 sesi 1 (9 Sep, branch `feature/m04-katalog-bilik`):** pelan `2026-09-09-m04-katalog-bilik.md`; migrasi `rooms`/`room_layouts`/`room_facilities`/`bookings` (dijalankan pada MySQL); `BookingStatus` + `Room`/`RoomLayout`/`RoomFacility`/`Booking` + `Location::rooms()` + 3 factory; kebenaran `bilik.*` dalam seeder + `EXPECTED_GRANTS`; `referenceSummary()` + alasan `bilik`; `RoomStoreRequest`/`RoomUpdateRequest`; `Admin\RoomController` (CRUD + toggle FR-BLK-08 + audit `room.*`). Komit `836e1d4`.
- **phpunit.xml:** kunci `APP_LOCALE=ms` — `.env` telah berubah kepada `en` dan mematahkan ujian throttle secara konsisten. Keputusan pengguna tertunggak: kembalikan `.env` kepada `ms`?
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
- **Git wujud** di `C:\laragonNafas\bin\git\cmd\git.exe`; PHP 8.4 di `C:\laragonNafas\bin\php\php-8.4.25-Win32-vs17-x86\php.exe`; kedua-duanya **tiada dalam PATH**. Pint: `& $php vendor\laravel\pint\builds\pint --format agent`. Catatan lama "bukan repo git" tamat tempoh.
- Output PowerShell dalam sesi agen kerap gagal dirakam: alihkan output ke fail (`*> storage\logs\diag.log`), baca fail itu, buang aksara `\0` (log UTF-16).
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