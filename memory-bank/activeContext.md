# Active Context

> Setakat 9 September 2026: **M02, M03, M01 selesai** dan **MODUL PROFIL siap dibina**
> dalam branch `feature/modul-profile` — 10/10 ujian profil lulus; commit dibuat pada sesi yang sama.
> Pelan penuh: `docs-claude/16-plan-modul-profile.md`.

## Current Focus
- **Modul Profil — pembinaan selesai.** Branch `feature/modul-profile` (dari `main`).
- Checkpoint teknikal lulus: Pint (format bersih), `migrate` (kolum `profile_photo_path` pada dev DB), `storage:link` (symlink `public/storage` dicipta — sebelum ini TIADA), `ProfileTest` 10/10 (35 assertions).
- Suite penuh: **232 lulus, 1 gagal** — kegagalan tunggal (`AuthenticationTest` throttle) disahkan **pre-existing** melalui ujian `git stash push -u` → jalankan test → gagal sama → `git stash pop`. Bukan kesan modul profil.

## Next Steps (susunan sesi akan datang)
1. **Siasat `AuthenticationTest` throttle** (pre-existing, lulus dahulu di bawah sesi lama): semak `composer.lock`/`package-lock.json` yang berubah sebelum sesi modul profil (syak dependency drift selepas `composer update`), baris `lang/ms` `auth.throttle`, dan `RateLimiter` Laravel 13. Keputusan: baiki pada branch ini atau isu berasingan.
2. **Push + MR** branch `feature/modul-profile` ke `main` (git laluan penuh: `C:\laragon\bin\git\cmd\git.exe`).
3. **Uji manual di pelayar**: log masuk → dropdown "Profil Saya" → kemas kini nama/emel → muat naik JPG/PNG → sahkan gambar dipaparkan semula (`/storage/profile/{id}/...`). Nota: `APP_URL=http://localhost:8000` dalam `.env` — laraskan jika hos tempatan berbeza (URL gambar dibina daripadanya).
4. Sambung modul seterusnya (M04 Katalog Bilik) mengikut susunan kebergantungan dalam `docs-claude/`.

## Recent Changes (sesi modul profil, 9 Sep 2026)
- **Baharu**: migration `2026_09_09_074151_add_profile_photo_to_users_table` (kolum `profile_photo_path`, nullable); `app/Http/Requests/Profile/ProfileUpdateRequest.php` (`name` max:200; `email` unique abaikan diri); `app/Http/Requests/Profile/ProfilePhotoRequest.php` (`required,image,mimes:jpg,jpeg,png,max:2048`); `app/Http/Controllers/Profile/ProfileController.php` (`edit/update/updatePhoto` + audit peribadi; `safe()->only(['name','email'])`; foto: store → save → delete lama); `resources/views/profile/edit.blade.php` (kad gambar + pratonton Alpine + kad maklumat peribadi + kad organisasi baca-sahaja); `tests/Feature/ProfileTest.php` (10 senario); `docs-claude/16-plan-modul-profile.md`.
- **Diubah (aditif)**: `app/Models/User.php` (+`'profile_photo_path'` dalam `#[Fillable]`, +`profilePhotoUrl()`); `routes/web.php` (+kumpulan `profile.*` dalam kumpulan `auth` — import + penggunaan dalam satu suntingan); `resources/views/layouts/app.blade.php` (+pautan "Profil Saya" dalam dropdown, +avatar bergambar bila ada).
- **Persekitaran**: `php artisan storage:link` dijalankan (symlink wujud buat pertama kali); `php artisan migrate` dijalankan pada dev DB (`e-fasiliti2`).

## Notes / Gotchas
- Semua bacaan tetapan mesti melalui `setting()` atau `SettingsRepository`, bukan pertanyaan terus ke `system_settings`.
- **Jalankan `php artisan migrate` selepas tugasan yang menyentuh skema** (rule `.ai/rules/migrations.md`); `migrate:fresh`/`rollback` DILARANG — `.env` menunjuk DB dev sebenar.
- Ujian feature perlu `npm run build` dahulu (manifest Vite).
- Import controller + laluan mesti ditulis dalam satu suntingan yang sama (hook Pint membuang import tergantung — rule `.ai/rules/routes.md`).
- Snapshot audit "after" dari model yang di-refresh (rule `.ai/rules/admin.md`) — dipatuhi dalam `ProfileController::update()`.
- `php`/`git` tiada dalam PATH PowerShell — guna laluan penuh (teknik & laluan: `memory-bank/techContext.md`).
- Sesi shell agent ialah Windows PowerShell 5.1: `Set-Content -Encoding UTF8` menulis BOM; buang dengan `[System.IO.File]::WriteAllText($path, [System.IO.File]::ReadAllText($path), (New-Object System.Text.UTF8Encoding($false)))`.
- `Set-Content` tidak mencipta folder — `New-Item -ItemType Directory -Force` dahulu untuk folder baharu.
- `assertGuest`/`assertAuthenticatedAs` ialah kaedah `TestCase`, bukan `TestResponse` — jangan rantai selepas `assertRedirect`.
- Audit `metadata` cast `array`; `user_agent` dipotong 255 (`Str::limit`).
- Spatie v8 middleware namespace: `Spatie\Permission\Middleware\{RoleMiddleware, PermissionMiddleware}`.
- Sesi admin: jangan padam/deactivate diri sendiri atau admin aktif terakhir.
