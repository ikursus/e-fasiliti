# Active Context

> Penjejak terperinci ialah `docs/superpowers/plans/STATUS.md`. Fail ini menyimpan gambaran keseluruhan sahaja.
> Setakat 9 September 2026 (malam): **M02, M03, M01 selesai; modul profil dan chatbot (M17) siap di `main`; M04 Katalog Bilik dalam pembinaan** di branch `feature/m04-katalog-bilik` (sesi 1, **232 ujian lulus**). Pelan profil: `docs-claude/16-plan-modul-profile.md`; pelan M04: `docs/superpowers/plans/2026-09-09-m04-katalog-bilik.md`.

## Current Focus
- **M04 Katalog Bilik — pembinaan bermula (sesi 1 selesai).** Branch `feature/m04-katalog-bilik` (dari `main`): migrasi `rooms`/`room_layouts`/`room_facilities`/`bookings` (dijalankan pada MySQL dev), model + enum + factory, kebenaran `bilik.*` (seeder + `EXPECTED_GRANTS`), `Location::referenceSummary()` + alasan `bilik`, form requests, `Admin\RoomController` (CRUD + audit `room.*`). Baki: views, routes, ujian ciri.
- **Modul Profil selesai di `main`** — self-service profil dengan muat naik foto; `storage:link` dicipta buat pertama kali; `ProfileTest` 10/10 (35 assertions).
- **Chatbot (M17) selesai di `main`** — `GeminiChatService`, tetapan chatbot, jadual `chat_sessions`/`chat_messages`, kebenaran `chatbot.*`, UI chat Alpine.
- **Lapisan asas selesai: M02, M03, M01, dan asas M16.**

## Next Steps (susunan sesi akan datang)
1. **Sambung M04 sesi 2**: views (`admin/rooms/*`) → laluan `admin.rooms.*` + sidebar → ujian ciri (`RoomManagementTest`, `RoomValidationTest`, `RoomDeactivationTest`) → checkpoint pint+test+migrate → komit. Keadaan Task demi Task: blok "Status Pelaksanaan" fail pelan M04.
2. Selepas M04: **M06 Kelulusan → M05 Enjin Tempahan → M14 e-mel** (susunan dipersetujui; setiap satu perlu kitaran pelan dahulu).
3. **Isu `AuthenticationTest` throttle DISELESAKAN dalam sesi M04**: punca ialah `.env` kini `APP_LOCALE=en` (dulu `ms`). Dibaiki dengan `<env name="APP_LOCALE" value="ms"/>` dalam `phpunit.xml`. **Keputusan pengguna tertunggak:** sama ada `.env` dikembalikan kepada `ms`.
4. **Uji manual di pelayar**: profil (`APP_URL` mempengaruhi URL gambar) dan — selepas seeder kebenaran `bilik.*` dijalankan (perlu kebenaran pengguna) — `/admin/rooms`.
- Semua kebergantungan M01 bagi tempahan sudah sedia: waktu operasi (FR-TMP-06), kalendar cuti, had tempoh dan tempoh awalan per peranan (FR-TMP-08), serta senarai susun atur dan kemudahan bilik untuk M04.

## Recent Changes (9 Sep 2026)
- **M04 sesi 1 (branch `feature/m04-katalog-bilik`, komit `836e1d4`)**: pelan M04; empat migrasi dijalankan pada MySQL dev; `BookingStatus`, `Room`, `RoomLayout`, `RoomFacility`, `Booking`, `Location::rooms()`, tiga factory; kebenaran `bilik.lihat/cipta/kemaskini/padam` dalam seeder + `EXPECTED_GRANTS` + ujian matriks (**belum db:seed**); `RoomStoreRequest`/`RoomUpdateRequest`; `Admin\RoomController` (CRUD + toggle FR-BLK-08 + audit `room.*`); `phpunit.xml` kunci `APP_LOCALE=ms`.
- **Profil (digabung ke `main`, PR #1)**: migration `profile_photo_path` (nullable); `ProfileUpdateRequest` (`name` max:200; `email` unique abaikan diri); `ProfilePhotoRequest` (`required,image,mimes:jpg,jpeg,png,max:2048`); `ProfileController` (`edit/update/updatePhoto` + audit peribadi; foto: store → save → delete lama); `profile/edit.blade.php` (kad gambar + pratonton Alpine); `routes/web.php` kumpulan `profile.*` dalam kumpulan `auth`; sidebar dropdown "Profil Saya" + avatar bergambar; `User` +`profile_photo_path` fillable, +`profilePhotoUrl()`; `storage:link` dijalankan buat pertama kali; `ProfileTest` (10 senario).
- **Chatbot/M17 (digabung ke `main`, PR #2)**: konfigurasi servis Gemini; jadual `chat_sessions`/`chat_messages`; `GeminiChatService`; `ChatbotController` + `ChatbotSettingsController`; laluan `chatbot.*`; kebenaran `chatbot.guna`/`chatbot.tetapan`; UI chat Alpine; tab tetapan chatbot; kunci Gemini API daripada skrin admin; 24 ujian chatbot.

## Notes / Gotchas
- **Git wujud** di `C:\laragonNafas\bin\git\cmd\git.exe`; PHP 8.4 di `C:\laragonNafas\bin\php\php-8.4.25-Win32-vs17-x86\php.exe`; kedua-duanya **tiada dalam PATH**. Pint: `& $php vendor\laravel\pint\builds\pint --format agent`. Catatan lama "bukan repo git" tamat tempoh.
- Output PowerShell dalam sesi agen kerap gagal dirakam: alihkan output ke fail (`*> storage\logs\diag.log`), baca fail itu, buang aksara `\0` (log UTF-16).
- Sesi shell agent ialah Windows PowerShell 5.1: `Set-Content -Encoding UTF8` menulis BOM; buang dengan `[System.IO.File]::WriteAllText($path, [System.IO.File]::ReadAllText($path), (New-Object System.Text.UTF8Encoding($false)))`.
- `Set-Content` tidak mencipta folder — `New-Item -ItemType Directory -Force` dahulu untuk folder baharu.
- Semua bacaan tetapan mesti melalui `setting()` atau `SettingsRepository`, bukan pertanyaan terus ke `system_settings`. Cache dibatalkan hanya oleh `SettingsRepository::set()` dan `flush()`.
- `operating_hours` polimorfik: baris tanpa pemilik ialah waktu lalai organisasi. M04 menambah baris milik bilik pada jadual yang sama.
- Templat notifikasi menolak pemegang tempat di luar lajur `placeholders`. Tambah pemegang tempat baharu pada lajur itu dahulu.
- Pembantu `setting()` dimuatkan melalui kunci `files` dalam `composer.json` — selepas menariknya keluar, jalankan `composer dump-autoload`.
- **Cast `date` menulis datetime penuh** (`2026-05-01 00:00:00`); MySQL memangkas, SQLite tidak — jangan guna padanan kesamaan atau `firstOrCreate` pada lajur tarikh; guna `whereDate`.
- **Jalankan `php artisan migrate` selepas tugasan yang menyentuh skema** (rule `.ai/rules/migrations.md`); `migrate:fresh`/`rollback` DILARANG — `.env` menunjuk DB dev sebenar; `db:seed` hanya dengan kebenaran pengguna.
- Kod lokasi unik dalam induk yang sama; keunikan dikuatkuasakan dalam `LocationStoreRequest`, bukan indeks (NULL berbeza antara MySQL dan SQLite).
- Laluan M03 guna middleware `can:`, bukan `role:` — tiga peranan berkongsi capaian.
- Ujian feature perlu `npm run build` dahulu (manifest Vite).
- Import controller + laluan mesti dalam satu suntingan yang sama (hook Pint membuang import tergantung — rule `.ai/rules/routes.md`).
- Snapshot audit "after" dari model yang di-refresh (rule `.ai/rules/admin.md`).
- `assertGuest`/`assertAuthenticatedAs` ialah kaedah `TestCase`, bukan `TestResponse` — jangan rantai selepas `assertRedirect`.
- Audit `metadata` cast `array`; `user_agent` dipotong 255 (`Str::limit`). Context audit digabung pada **aras atas** `metadata`.
- Spatie v8 middleware namespace: `Spatie\Permission\Middleware\{RoleMiddleware, PermissionMiddleware}`; **model `Role` ialah `Spatie\Permission\Models\Role`**, bukan `App\Models\Role`.
- Sesi admin: jangan padam/deactivate diri sendiri atau admin aktif terakhir.
- **URL gambar guna `asset(storage/...)`, bukan `Storage::url()`** — `APP_URL` (`localhost:8000`) tidak sama dengan hos Apache sebenar; `Storage::url` jana URL mutlak APP_URL → 404. Dibaiki dalam `User::profilePhotoUrl()`.
- `assertGuest`/`assertAuthenticatedAs` ialah kaedah `TestCase`, bukan `TestResponse` — jangan rantai selepas `assertRedirect`.
- Audit `metadata` cast `array`; `user_agent` dipotong 255 (`Str::limit`).
- Spatie v8 middleware namespace: `Spatie\Permission\Middleware\{RoleMiddleware, PermissionMiddleware}`.
- Sesi admin: jangan padam/deactivate diri sendiri atau admin aktif terakhir.
- **URL gambar guna asset(storage/...), bukan Storage::url()** - APP_URL (.env, localhost:8000) tidak sama dengan hos sebenar Apache (port 80): localhost/e-fasiliti2/public atau e-fasiliti2.test; Storage::url jana URL mutlak APP_URL -> 404. Dibaiki dalam User::profilePhotoUrl().
