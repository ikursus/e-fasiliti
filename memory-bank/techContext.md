# Tech Context

- PHP 8.4.14 (Laragon, `C:\laragon\bin\php\php-8.4.14-nts-Win32-vs17-x64\php.exe`)
- Composer 2.8.11
- Laravel framework ^13.17 (`laravel/framework`), `laravel/tinker` ^3.0, `spatie/laravel-permission` 8.3.0
- MySQL via Laragon at `127.0.0.1:3306`, DB `e-fasiliti`, user `root`, no password (per `.env`)
- `.env`: `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, `BROADCAST_CONNECTION=log`, `FILESYSTEM_DISK=local`, `APP_NAME=e-Fasiliti`, `APP_LOCALE=ms`
- Frontend: Vite + Tailwind CSS v4 (`vite.config.js`, `package.json`, font Instrument Sans); Alpine.js untuk dropdown/mobile nav; build wajib (`npm run build`) sebelum ujian feature
- Copy of `.env` vs `.env.example`: `.env` is configured for MySQL; example defaults to sqlite.
- Note: `intl` PHP extension is NOT loaded — number `format()` helper and `db:table` CLI display fail (cosmetic).
- Game plan for fresh installs: `php artisan migrate` → `php artisan db:seed` → `npm run build` sebelum first request/ujian.
- Akaun demo (DemoUsersSeeder): `{role}@e-fasiliti.test` (cth. `admin@e-fasiliti.test` = R8) — semak seeder untuk kata laluan.
- **Emel (Mailpit Laragon)**: `MAIL_MAILER=smtp`, `MAIL_HOST=127.0.0.1`, `MAIL_PORT=1025`, `MAIL_FROM_ADDRESS=noreply@e-fasiliti.test`; UI Mailpit di `http://127.0.0.1:8025`; API semakan `GET http://127.0.0.1:8025/api/v1/messages` (bukan v2). Jika `MAIL_MAILER=log`, emel hanya ditulis ke `storage/logs/laravel.log`.