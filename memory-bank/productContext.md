# Product Context

- The application is at an early/skeleton stage — no custom domain logic has been implemented yet (only the default Laravel framework migrations: `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`).
- The first reported issue was: accessing the app failed with `SQLSTATE[42S02]: Table 'e-fasiliti.sessions' doesn't exist` because migrations had never been run on the MySQL database while `SESSION_DRIVER=database` was set in `.env`.
- Resolved by running `php artisan migrate`, which creates the `sessions` table (among others).
- Future product decisions (features, UX goals) are still open.