---
paths:
  - 'database/migrations/**'
---

# Migrations

## Run migrate at the checkpoint, or the table exists only in tests
Tests run on in-memory SQLite, which rebuilds the schema from scratch every run. The development database does not. A new migration can therefore pass the entire suite while the table does not exist in the browser at all.

This happened: three M01 tables were added, 187 tests were green, and the first settings page load returned "Base table or view not found: 1146 Table 'e-fasiliti.system_settings' doesn't exist".

After any task that adds or alters a table, run `php artisan migrate` as part of the checkpoint, plus `php artisan cache:clear` when the task touches settings or anything else cached. Check `php artisan migrate:status` first if unsure; it only reads.

`migrate` is additive and every migration needs a working `down()`. `migrate:fresh` and `migrate:rollback` stay forbidden here, because .env points at real development data. Do not run `db:seed` without asking the user.
