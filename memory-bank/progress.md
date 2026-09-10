# Progress

## What works
- **Manual authentication (M02, sebahagian)** — log masuk/logout, reset kata laluan, confirm-password; throttle 5 gagal/15 min (FR-USR-10) via `RateLimiter` dalam `LoginRequest`.
- **Spatie laravel-permission v8** terpasang; 8 role SRS (R1–R8) + permission awal direkod melalui `RolesAndPermissionsSeeder`.
- **Middleware**: `EnsureValidSessionTimeout` + `EnsureUserIsActive` didaftarkan dalam `bootstrap/app.php` (web append).
- **Audit login/logout** (FR-AUD-03): `LoginAuditLogger` → `audit_logs`; `last_login_at/ip` dikemas kini.
- **Dashboard per role**: `DashboardController` pilih view `dashboard.{role}`; fallback `kakitangan`.
- **Admin CRUD (R8 sahaja)**: `Admin\UserController` + `Admin\RoleController` (audit penuh, perlindungan admin terakhir & diri sendiri).
- **Layout Tailwind v4**: `layouts/app` (sidebar gelap + topbar + Alpine.js), `layouts/guest`, komponen `ui`/`partials`.
- **Lang**: `lang/ms` + `lang/en`; `APP_LOCALE=ms`.
- **M03 — Direktori Organisasi & Lokasi (siap)** — CRUD lokasi/unit, nyahaktif melata, `<x-ui.location-picker>`, kebenaran `lokasi.*` / `unit-organisasi.*`.
- **Perakam audit umum** — `App\Services\Audit\AuditRecorder` (sebelum/selepas medan berubah + `context`).
- **M01 — Konfigurasi (siap fasa 1)** — `system_settings` + `SettingsRepository` bercache + pembantu `setting()`; enam tab pentadbiran; `HolidayCalendar`; `SystemConfigurationSeeder`.
- **MODUL PROFIL (siap pada 9 Sep 2026, branch `feature/modul-profile`)** — pelan penuh di `docs-claude/16-plan-modul-profile.md`:
  - Kemas kini maklumat diri (nama/emel) dengan Form Request validation; kunci IDOR (tiada `{user}` binding; hanya `$request->user()`); mass-assignment dilindungi (`safe()->only`).
  - Muat naik gambar profil JPG/JPEG/PNG maks 2 MB; simpan di `storage/app/public/profile/{user_id}/` dengan nama unik (`hashName`); gambar baharu menggantikan lama (padam selepas DB berjaya); DB simpan path relatif sahaja.
  - `User::profilePhotoUrl()`; avatar bergambar + pautan "Profil Saya" dalam dropdown header.
  - Audit `profile.updated` / `profile.photo.updated` ke `audit_logs`.
  - Route `profile.edit|update|photo.update` (middleware `auth`; `throttle:10,1` pada foto).
  - **Ujian: `tests/Feature/ProfileTest.php` — 10 lulus (35 assertions)**. Suite penuh: 232 lulus, 1 gagal (pre-existing — lihat Known issues).
  - `php artisan migrate` (kolum `profile_photo_path`) dan `php artisan storage:link` telah dijalankan pada DB dev.

## What's left to build
- Modul domain (M04–M15): bilik, tempahan, kelulusan, daftar masuk, sokongan, aset, tiket, penyelenggaraan, vendor, stok, notifikasi, laporan.
- Tetapan M01 bagi M05, M07 dan M14 belum ada penggunanya; sahkan bentuk data semasa dibina.
- **Dilangkau (skop fasa 1):** tab daftar masuk (FR-ADM-04) dan keutamaan tiket (FR-ADM-05).
- Audit penuh M16 (UI pentadbiran + lebih banyak event).
- 2FA (FR-USR-09) — ditangguh (keputusan pengguna).
- ~~Nama penuh, unit organisasi, lokasi utama dalam UI profil~~ ✅ diselesaikan oleh modul profil (kad "Maklumat Organisasi" baca-sahaja).

## Known issues
- ⚠️ **`AuthenticationTest` ujian throttle log masuk GAGAL** — disahkan **pre-existing**: gagal juga selepas semua perubahan modul profil di-stash (9 Sep 2026). Simptom: selepas 5 percubaan log masuk gagal, error sesi tidak mengandungi "Terlalu banyak". Fail auth tidak disentuh oleh modul profil. Sasaran siasatan: perubahan `composer.lock`/`package-lock.json` yang belum commit (syak dependency drift), baris `lang/ms` auth, atau tingkah laku `RateLimiter` Laravel 13.
- `intl` PHP extension missing → `db:table` & number formatting fail dalam CLI (kosmetik).
- Terminal sesi agent ialah Windows PowerShell 5.1 — tiada `utf8NoBOM`; `UTF8` menulis BOM; buang BOM via .NET `File.WriteAllText` + `UTF8Encoding($false)`.
- Terminal sesi agent kerap gagal capture output (shell integration) — abaikan "exit code 1" jika output sebenar jelas.
- Pint `--dirty` gagal kerana `git` tiada dalam PATH — guna senarai fail eksplisit (lihat techContext.md).
- `php`/`git` tiada dalam PATH PowerShell — guna laluan penuh (lihat techContext.md).
- `Set-Content` PowerShell tidak mencipta folder — `New-Item -ItemType Directory -Force` dahulu.
