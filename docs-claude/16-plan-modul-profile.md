# Pelan Pembangunan — Modul Profil

> Branch: `feature/modul-profile` · Tarikh: 2026-09-09 · Status: Dilaksanakan

## 1. Objektif
Menambah Modul Profil ke sistem e-Fasiliti **tanpa mengubah modul atau fungsi lain**:

1. **Kemas Kini Profil** — pengguna melihat & mengemas kini maklumat sendiri (validation + simpan ke DB). Pengguna **tidak boleh** mengubah profil pengguna lain.
2. **Kemas Kini Gambar Profil** — muat naik dan tukar gambar profil; format JPG/JPEG/PNG; validasi jenis & saiz (maks 2 MB); gambar baharu menggantikan gambar lama.
3. **Penyimpanan Gambar** — `storage/app/public/profile/{user_id}/`, nama fail unik, DB simpan path sahaja (bukan binary), gambar boleh dipaparkan semula.

## 2. Keputusan Reka Bentuk (idea terbaik)
- **Anti-IDOR secara struktur**: laluan profil **tiada `{user}` binding**. Semua operasi hanya pada `$request->user()`. Mustahil menyasarkan pengguna lain walaupun memanipulasi permintaan.
- **Mass assignment protection**: controller guna `$request->safe()->only(['name', 'email'])`. Field sensitif (`is_active`, `password`, `roles`, `organization_unit_id`, dst.) tidak boleh disentuh melalui profil.
- **Nama fail unik**: `$file->store("profile/{id}", 'public')` → `hashName()` Laravel (rawak + extension sebenar). DB simpan **path relatif** (cth. `profile/5/Ab3xK9....jpg`).
- **Penggantian atomik**: simpan fail baharu → kemas kini DB → padam fail lama. Tiada fail yatim jika DB gagal.
- **Avatar fallback**: papar huruf pertama nama (corak sedia ada header) apabila tiada gambar.
- **Rate limit**: `throttle:10,1` pada laluan muat naik gambar (konsisten `throttle:6,1` log masuk).
- **Jejak audit**: `profile.updated` / `profile.photo.updated` direkod ke `AuditLog` (corak sedia ada). Snapshot "after" diambil dari model yang di-refresh (rule `.ai/rules/admin.md`).
- **Tiada dependency baharu**: tiada `intervention/image`; tiada penukaran saiz imej (fail ≤ 2 MB).

## 3. Fail Baharu
| Fail | Kandungan |
| --- | --- |
| `database/migrations/2026_09_09_074151_add_profile_photo_to_users_table.php` | Kolum `profile_photo_path` (string, nullable) + `down()` berfungsi |
| `app/Http/Requests/Profile/ProfileUpdateRequest.php` | `name` (required, max:200), `email` (required, lowercase, email, unik abaikan diri) |
| `app/Http/Requests/Profile/ProfilePhotoRequest.php` | `photo` → `required, image, mimes:jpg,jpeg,png, max:2048` |
| `app/Http/Controllers/Profile/ProfileController.php` | `edit()`, `update()`, `updatePhoto()` — semuanya pada pengguna yang sedang log masuk |
| `resources/views/profile/edit.blade.php` | Kad gambar (pratonton Alpine.js + fallback initial) + kad maklumat peribadi + kad maklumat organisasi (baca sahaja) |
| `tests/Feature/ProfileTest.php` | 10 senario ujian feature (PHPUnit) |

## 4. Suntingan Aditif pada Fail Sedia Ada
| Fail | Perubahan |
| --- | --- |
| `app/Models/User.php` | + `'profile_photo_path'` dalam `#[Fillable]`, + method `profilePhotoUrl()` |
| `routes/web.php` | + 1 kumpulan laluan `profile.*` dalam kumpulan `auth` (import + penggunaan dalam suntingan yang sama — rule `.ai/rules/routes.md`) |
| `resources/views/layouts/app.blade.php` | + pautan "Profil Saya" dalam dropdown + avatar bergambar apabila ada |

## 5. Validasi & Keselamatan
- Profil: `name` required max:200; `email` required, lowercase, email, max:255, unik dalam `users` (abaikan rekod sendiri).
- Gambar: `required`, `image`, `mimes:jpg,jpeg,png`, `max:2048` (KB). `accept=.jpg,.jpeg,.png` pada input. Fail disimpan melalui `Storage::disk('public')`.
- Laluan dilindungi middleware `auth`; tiada kebenaran khusus diperlukan (semua pengguna berdaftar & aktif).

## 6. Paparan Semula Gambar
- `profilePhotoUrl()` pada model `User` → `Storage::disk('public')->url($path)` → `{APP_URL}/storage/profile/{id}/{fail}`.
- Memerlukan `php artisan storage:link` (symlink `public/storage` belum wujud sebelum ini).

## 7. Ujian (tests/Feature/ProfileTest.php)
1. Tetamu dialih ke log masuk.
2. Pengguna berdaftar melihat halaman profil sendiri.
3. Kemas kini nama/emel berjaya + flash status.
4. Validasi: nama kosong & emel duplikat ditolak.
5. Field sensitif (`is_active`, `password`) tidak boleh diubah melalui kemas kini profil.
6. Muat naik JPG berjaya: fail wujud di `profile/{id}/`, DB simpan path, URL mengandungi `/storage/profile/`.
7. Gambar baharu menggantikan gambar lama (fail lama dipadam).
8. Fail bukan imej (`.php`) ditolak.
9. Fail > 2 MB ditolak.
10. Tindakan profil direkod dalam `audit_logs`.

## 8. Checkpoint
```
vendor/bin/pint --dirty --format agent
php artisan migrate            # dilarang fresh/rollback — data dev sebenar
php artisan storage:link
php artisan test --compact tests/Feature/ProfileTest.php
```

## 9. Perkara yang TIDAK Disentuh
- Tiada perubahan pada modul admin/auth/dashboard/settings.
- Tiada dependency composer/npm baharu.
- Tiada perubahan skema lain selain 1 kolum nullable.
- Semua suntingan fail sedia ada bersifat aditif (additive).

## 10. Nota Persekitaran
- PHP CLI: `C:\laragonLV\bin\php\php-8.4.25-Win32-vs17-x64\php.exe` (tiada `php` dalam PATH PowerShell; `C:\laragon\bin\php` hanya ada 8.1 yang gagal platform check composer).
- Git: `C:\laragon\bin\git\cmd\git.exe` (tiada `git` dalam PATH).
- `APP_URL=http://localhost:8000` — URL gambar dibina daripadanya; laraskan sekiranya hos tempatan berbeza.
