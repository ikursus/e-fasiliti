# System Patterns

- **Framework:** Laravel framework v13 (new-generation PHP framework; config files use `return [...]` arrays with `env()` / `storage_path()` / `database_path()` helpers; classes `use Illuminate\Support\Str`, `Illuminate\Database\Migrations\Migration`, `Illuminate\Support\Facades\Schema`).
- **Migrations:** PHP files in `database/migrations/` using anonymous `return new class extends Migration` syntax with `Schema::create(...)` + `Blueprint`.
- **CLI:** `php artisan` (Tinker REPL available via `laravel/tinker`).
- **Session storage:** database driver (`config/session.php` → `sessions` table).
- **DB config:** `config/database.php`, default connection driven by `.env` (`DB_CONNECTION=mysql`).
- **Authentication:** manual (tiada starter kit) — controllers dalam `App\Http\Controllers\Auth\*`, `LoginRequest` memegang validasi + throttle `RateLimiter::tooManyAttempts('login', 5)`; service `App\Services\Auth\LoginAuditLogger` menulis `audit_logs`.
- **Authorization:** Spatie `laravel-permission` v8 — 8 role SRS (R1–R8); middleware alias `role`/`permission` didaftar dalam `bootstrap/app.php`; super admin R8 (`pentadbir-sistem`) bypass via `Gate::before` dalam `AppServiceProvider`.
- **Middleware web append:** `EnsureValidSessionTimeout` (30 min idle / 8 jam mutlak) dan `EnsureUserIsActive` didaftarkan melalui `$middleware->web(append: [...])` dalam `bootstrap/app.php`.
- **Dashboard per role:** `DashboardController` (single-action) memilih view `dashboard.{role}` daripada `User::rolePriority()` (keutamaan tetap; fallback `kakitangan`).
- **View organization:** feature/modul-based — `layouts/`, `auth/`, `dashboard/{role}.blade.php`, `admin/{users,roles}/*`, komponen `components/ui` + `components/partials`; layout `app` (sidebar gelap + topbar, Alpine.js) & `guest`.
- **Testing:** PHPUnit feature tests dalam `tests/Feature`; data-provider untuk matriks role→dashboard; perlukan `npm run build` sebelum run (Vite manifest).