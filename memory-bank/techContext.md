# Tech Context

- **PHP 8.4.25** (Laragon LV): `C:\laragonLV\bin\php\php-8.4.25-Win32-vs17-x64\php.exe`
  - ⚠️ `php` TIADA dalam PATH PowerShell sesi agent — sentiasa guna laluan penuh.
  - ⚠️ `C:\laragon\bin\php` (tanpa LV) hanya ada PHP 8.1.10 → gagal platform check composer (perlu >= 8.4.1). Folder `php-8.4.5` di sana ialah source code, bukan binary.
  - ⚠️ Nota lama (php-8.4.14 di C:\laragon) telah usang — persekitaran berpindah ke `C:\laragonLV`.
- **Git**: `C:\laragon\bin\git\cmd\git.exe` — `git` juga TIADA dalam PATH; guna laluan penuh (atau `&` call operator).
- **Pint**: `& $php vendor/laravel/pint/builds/pint --format agent <fail...>`; `--dirty` gagal kerana `git` tiada dalam PATH Pint (bukan kerana bukan repo git). Nyatakan senarai fail secara eksplisit.
- Composer 2.x via `C:\laragon\bin\composer\composer.phar`.
- Laravel framework ^13.17, `laravel/tinker` ^3.0, `spatie/laravel-permission` 8.x. PHPUnit 12 (`--compact`, atribut `#[DataProvider]`).
- **DB (dev)**: MySQL Laragon; `.env` semasa: `DB_DATABASE=e-fasiliti2`, `SESSION_DRIVER=database`, `FILESYSTEM_DISK=local`, `APP_URL=http://localhost:8000`, `APP_NAME=e-Fasiliti`, `APP_LOCALE=ms`. Root tanpa kata laluan.
  - ⚠️ Nota lama kata DB `e-fasiliti` — kini `e-fasiliti2`. Semak `.env` sebelum menulis arahan DB.
- **Tests**: `phpunit.xml` → SQLite in-memory (`:memory:`) + `RefreshDatabase`; `CACHE_STORE=array` dalam ujian.
- **Frontend**: Vite + Tailwind CSS v4 + Alpine.js; `npm run build` wajib sebelum ujian feature (manifest Vite).
- **Storage**: disk `public` → `storage/app/public`; symlink `public/storage` telah dicipta pada 9 Sep 2026 (`php artisan storage:link`) semasa modul profil. Gambar profil di `storage/app/public/profile/{user_id}/`.
- Game plan fresh install: `php artisan migrate` → `php artisan db:seed` (kebenaran pengguna) → `npm run build`.
- Akaun demo (DemoUsersSeeder): `{role}@e-fasiliti.test` (cth. `admin@e-fasiliti.test` = R8).
- **Emel (Mailpit Laragon)**: `MAIL_MAILER=smtp`, host `127.0.0.1`, port 1025; UI `http://127.0.0.1:8025`; API semakan `GET http://127.0.0.1:8025/api/v1/messages`.
- `intl` extension TIDAK dimuatkan — `format()` & `db:table` paparan CLI gagal (kosmetik).
