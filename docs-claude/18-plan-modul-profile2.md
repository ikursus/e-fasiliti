# Pelan Pembangunan — Modul Profil 2 (Tukar Kata Laluan)

> Branch: `feature/modul-profile2` · Tarikh: 2026-09-10 · Status: Dilaksanakan

## 1. Objektif
1. **Tukar Kata Laluan** — pengguna menukar kata laluan sendiri di `/profile`: sahkan kata laluan semasa → tetapkan kata laluan baharu (pengesahan + polisi kekuatan).
2. **Paparan lebih tersusun** — halaman profil disusun semula kepada **3 tab** (Alpine.js): *Maklumat Peribadi*, *Gambar Profil*, *Keselamatan*.
3. **Tiada modul lain terjejas** — semua suntingan aditif; tiada skema baharu, tiada dependency baharu, tiada middleware global baharu.

## 2. Keputusan Reka Bentuk (Keselamatan)
- **Pengesahan kata laluan semasa** — corak sama dengan `ConfirmablePasswordController` sedia ada: `Auth::guard('web')->validate([...])` dalam closure validasi, mesej ralat `__('auth.password')` ("Kata laluan yang dimasukkan tidak betul." — `lang/ms/auth.php`).
- **Polisi kekuatan** — `Password::min(8)->mixedCase()->numbers()` + `confirmed`. Tanpa `uncompromised()` (elak panggilan HTTP luaran ke HIBP semasa validasi/ujian).
- **Auto-hash** — `User` mempunyai cast `'hashed'`; nilai cincang bcrypt disimpan secara automatik (NFR-S02). Plaintext tidak pernah disimpan.
- **Throttle** — `throttle:6,1` pada `PUT profile/password` (konsisten dengan `password.confirm.store` dan `forgot-password`).
- **Jejak audit** — `profile.password.updated` melalui kaedah `audit()` swasta sedia ada. Kata laluan **tidak pernah** dimasukkan ke metadata audit.
- **Log keluar sesi lain** — selepas tukar kata laluan, baris jadual `sessions` milik pengguna itu (kecuali sesi semasa) dipadam — selamat kerana `SESSION_DRIVER=database` (dev/prod). `Auth::logoutOtherDevices()` **tidak digunakan** kerana ia memerlukan middleware `AuthenticateSession` global yang mengubah tingkah laku semua modul.
- **Anti-IDOR secara struktur** — laluan dalam kumpulan `profile` sedia ada (tiada `{user}` binding); operasi pada `$request->user()` sahaja.
- Medan kata laluan tidak pernah `old()` semula; `autocomplete="current-password"` / `new-password` pada input.

## 3. Fail Baharu
| Fail | Kandungan |
| --- | --- |
| `app/Http/Requests/Profile/ProfilePasswordRequest.php` | `current_password` (required + closure semak kata laluan semasa), `password` (required, min:8, mixedCase, numbers, confirmed) |
| `docs-claude/18-plan-modul-profile2.md` | Dokumen pelan ini |

## 4. Suntingan Aditif pada Fail Sedia Ada
| Fail | Perubahan |
| --- | --- |
| `app/Http/Controllers/Profile/ProfileController.php` | + kaedah `updatePassword()` — sahkan → simpan → padam sesi lain → audit → redirect + flash |
| `routes/web.php` | + 1 laluan `PUT profile/password` → `profile.password.update` + `throttle:6,1` (tiada import baharu) |
| `resources/views/profile/edit.blade.php` | Susun atur tab; tab awal auto-ikut tab yang mengandungi ralat validasi; kelas per-control `border-slate-300 focus:*` dibuang (rule `.ai/rules/views.md`); kad organisasi kekal baca-sahaja dalam tab peribadi |
| `tests/Feature/ProfileTest.php` | + 8 senario ujian feature |

## 5. Validasi & Keselamatan
- `current_password`: `required`, `string` + closure `Auth::guard('web')->validate()`.
- `password`: `required`, `string`, `Password::min(8)->mixedCase()->numbers()`, `confirmed`.
- `password_confirmation`: sah melalui peraturan `confirmed` (ralat dilaporkan pada medan `password`).
- Laluan dilindungi `auth` (kumpulan sedia ada) + `throttle:6,1`.

## 6. Susun Atur Paparan
- `x-data="{ tab: ... }"` dengan tiga butang tab (`role="tablist"`) dan tiga panel `x-show`.
- Tab awal dipilih pelayan: tab yang mengandungi ralat validasi, jika tiada → *Maklumat Peribadi*.
- Panel: **Peribadi** (borang nama/emel + kad organisasi baca-sahaja), **Gambar** (kad gambar + pratonton Alpine sedia ada), **Keselamatan** (borang kata laluan).

## 7. Ujian (tambahan dalam tests/Feature/ProfileTest.php)
Ikut rule `.ai/rules/tests.md`: kata laluan semasa = literal kilang `password`; kata laluan baharu bentuk jelas palsu cth. `fake-new-password-Aa1`.
1. Tetamu dialih ke log masuk untuk `profile.password.update`.
2. Tukar kata laluan berjaya: hash DB berubah + `Hash::check` lulus + flash status.
3. Kata laluan semasa salah → ralat `current_password`; hash DB tidak berubah.
4. Pengesahan tidak sepadan → ralat `password`.
5. Kekuatan lemah (tiada huruf besar/nombor) → ralat `password`.
6. Sesi semasa kekal log masuk selepas tukar (`assertAuthenticatedAs`).
7. Audit `profile.password.updated` direkod + metadata **tiada** nilai kata laluan.
8. Sesi lain dipadam (ujian dengan `config(['session.driver' => 'database'])` + baris sesi palsu).

## 8. Checkpoint
```
npm run build
php artisan test --compact tests/Feature/ProfileTest.php
vendor/bin/pint --dirty --format agent
# tiada migrate diperlukan — tiada perubahan skema
```

## 9. Perkara yang TIDAK Disentuh
- Aliran reset kata laluan lama (`NewPasswordController` — hanya `required|confirmed`, tiada polisi kekuatan). **Pemerhatian**: polisi tidak konsisten dengan endpoint baharu; penyelarasan dicadangkan sebagai tugasan berasingan atas keputusan pemilik.
- `User` model, layout, middleware, skema, dependency, `lang/ms/validation.php` (mesej validasi kekal Inggeris — konsisten dengan keadaan app semasa; had diketahui).

## 10. Nota Persekitaran
- PHP CLI: `C:\laragonLV\bin\php\php-8.4.25-Win32-vs17-x64\php.exe` (tiada `php` dalam PATH PowerShell).
- Git: `C:\laragonLV\bin\git\cmd\git.exe` (tiada `git` dalam PATH).
- Ujian feature perlu `npm run build` dahulu (manifest Vite).
- Ujian berjalan pada SQLite in-memory (`phpunit.xml`), `SESSION_DRIVER=array` — kecuali ujian #8 yang menetapkan `database` secara eksplisit.
