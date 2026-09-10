# Kandungan PR — Modul Profil (untuk copy-paste ke GitHub)

> Pautan cipta PR: https://github.com/ikursus/e-fasiliti/pull/new/feature/modul-profile
> Base: `main` · Compare: `feature/modul-profile` · 2 commit: `badb2db`, `02d4c0d`

---

## Tajuk PR

```
feat(profile): self-service profile module with photo upload
```

## Huraian PR (paste dalam kotak description)

```markdown
## Ringkasan
Menambah **Modul Profil (self-service)** — setiap pengguna boleh melihat dan mengemas kini
maklumat profil sendiri serta memuat naik gambar profil. **Tiada modul atau fungsi sedia ada
diubah** — semua suntingan pada fail sedia ada bersifat aditif (User model, routes, layout header).

## Perubahan
- **Kemas kini profil** (nama/emel) dengan Form Request validation.
  - Keselamatan: laluan **tiada `{user}` binding** (anti-IDOR) — semua operasi pada `$request->user()`;
    mass-assignment dikunci melalui `safe()->only(['name','email'])` (field sensitif seperti
    `is_active`, `password`, `roles` mustahil diubah melalui profil).
- **Gambar profil**: JPG/JPEG/PNG, maks 2 MB, rate-limit `throttle:10,1`.
  - Disimpan di `storage/app/public/profile/{user_id}/` dengan nama fail unik (`hashName`).
  - Gambar baharu menggantikan lama (store → save DB → delete lama; tiada fail yatim).
  - DB menyimpan **path relatif sahaja** (bukan binary).
- **Paparan**: `User::profilePhotoUrl()` menggunakan `asset('storage/...')` supaya URL mengikut
  hos sebenar aplikasi (vhost/port/subdirektori), bukan APP_URL. Avatar header + pautan
  "Profil Saya" dalam dropdown (aditif).
- **Jejak audit**: `profile.updated` / `profile.photo.updated` direkod ke `audit_logs`.
- **Migration**: `add_profile_photo_to_users_table` (kolum `profile_photo_path` nullable;
  `down()` berfungsi). `storage:link` perlu dijalankan sekali pada persekitaran baharu.

## Ujian
- `php artisan test --compact tests/Feature/ProfileTest.php` → **10 lulus (35 assertions)**
  (auth, validation, bukti mass-assignment selamat, upload/ganti/padam gambar, tolak fail
  bukan imej & >2 MB, audit).
- Suite penuh → **232 lulus, 1 gagal**. Kegagalan tunggal (`AuthenticationTest` throttle)
  telah disahkan **pre-existing pada main** (gagal juga selepas perubahan PR di-stash) —
  bukan daripada PR ini; disiasat berasingan.
- Pint bersih; `php artisan migrate` + `php artisan storage:link` dijalankan pada dev.
- Semakan manual: gambar dipapar (HTTP 200 image/jpeg) melalui kedua-dua
  `localhost/e-fasiliti2/public` dan vhost.

## Cara ujian manual
1. Log masuk → dropdown atas kanan → **"Profil Saya"**.
2. Kemas kini nama/emel → **Simpan Perubahan** → flash status + data tersimpan.
3. Muat naik JPG/PNG ≤ 2 MB → **Simpan Gambar** → gambar dipapar dalam kad + avatar header;
   cuba juga fail `.php` dan fail > 2 MB (ditolak dengan mesej).

## Nota
- `composer.lock` / `package-lock.json` (perubahan pra-sesi, di luar skop) tidak disertakan.
- Paparan gambar memerlukan symlink `public/storage` (`php artisan storage:link`) — telah
  didokumenkan dalam memory-bank.
```
