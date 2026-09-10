# Kandungan PR — Modul Profil 2 (untuk copy-paste ke GitHub)

> Pautan cipta PR: https://github.com/ikursus/e-fasiliti/pull/new/feature/modul-profile2
> Base: `main` · Compare: `feature/modul-profile2`

---

## Tajuk PR

```
feat(profile): password change + tabbed profile page layout
```

## Huraian PR (paste dalam kotak description)

```markdown
## Ringkasan
Menambah **Modul Profil 2** — fungsi **tukar kata laluan** di halaman `/profile`
diserta susun atur semula halaman profil kepada **3 tab yang lebih kemas**
(Maklumat Peribadi · Gambar Profil · Keselamatan). **Tiada modul atau fungsi sedia
ada diubah** — semua suntingan bersifat aditif; tiada skema baharu, tiada dependency
baharu, tiada middleware global baharu.

## Perubahan
- **Tukar kata laluan** (`PUT profile/password`, `profile.password.update`, `throttle:6,1`
  selari `password.confirm.store` / `forgot-password`).
  - Kata laluan **semasa wajib disahkan** — corak sama dengan
    `ConfirmablePasswordController` (`Auth::guard('web')->validate()`), mesej ralat
    Bahasa Melayu dari `lang/ms/auth.php`.
  - Polisi kekuatan: `Password::min(8)->mixedCase()->numbers()` + `confirmed`
    (tanpa `uncompromised()` — tiada panggilan HTTP luaran semasa validasi).
  - Auto-hash bcrypt melalui cast `'hashed'` sedia ada pada model `User` (NFR-S02);
    plaintext tidak pernah disimpan.
  - **Sesi lain dilog keluar** selepas pertukaran — baris `sessions` milik pengguna
    itu (kecuali sesi semasa) dipadam. `Auth::logoutOtherDevices()` tidak digunakan
    kerana ia memerlukan middleware `AuthenticateSession` global yang mengubah
    tingkah laku semua modul.
  - Anti-IDOR secara struktur: laluan dalam kumpulan `profile` sedia ada —
    tiada `{user}` binding; operasi pada `$request->user()` sahaja.
  - Jejak audit `profile.password.updated` — **kata laluan tidak pernah** dimasukkan
    ke metadata audit.
- **Paparan tab (Alpine.js)**: tab awal dipilih pelayan mengikut tab yang mengandungi
  ralat validasi; kad organisasi kekal baca-sahaja dalam tab Peribadi; kad gambar
  + pratonton Alpine kekal dalam tab Gambar.
- **Kemas kini gaya borang** mematuhi `.ai/rules/views.md`: kelas per-control
  `border-slate-300 focus:*` dibuang (frame/fokus kini dari lapisan asas `app.css`);
  `autocomplete="current-password" / "new-password"` pada medan kata laluan.

## Fail
- **Baharu**: `app/Http/Requests/Profile/ProfilePasswordRequest.php`,
  `docs-claude/18-plan-modul-profile2.md`, `docs-claude/19-pr-description-modul-profile2.md`.
- **Diubah (aditif)**: `ProfileController` (+`updatePassword`), `routes/web.php` (+1 laluan),
  `resources/views/profile/edit.blade.php` (susun atur tab), `tests/Feature/ProfileTest.php`
  (+8 senario).

## Ujian
- `php artisan test --compact tests/Feature/ProfileTest.php` → **18 lulus (57 assertions)**
  (tetamu dialih; tukar berjaya + hash berubah; kata laluan semasa salah ditolak;
  pengesahan tak sepadan; kekuatan lemah ditolak; sesi semasa kekal log masuk;
  audit tanpa nilai kata laluan; sesi lain dipadam — ujian dengan driver `database`).
- Suite penuh → **406 lulus, 0 gagal (1321 assertions)** — tiada regresi.
- Pint bersih; **tiada migration** diperlukan (tiada perubahan skema).

## Cara ujian manual
1. Log masuk → dropdown atas kanan → **"Profil Saya"**.
2. Tab **Keselamatan** → isi Kata Laluan Semasa + Kata Laluan Baharu (min 8 aksara,
   huruf besar + huruf kecil + nombor) + pengesahan → **Tukar Kata Laluan** →
   flash status; log keluar dan log masuk semula dengan kata laluan baharu.
3. Cubaan negatif: kata laluan semasa salah (mesej BM "Kata laluan yang dimasukkan
   tidak betul."), pengesahan tak sepadan, kata laluan lemah — semua ditolak dan
   tab Keselamatan terbuka secara automatik berserta ralat.

## Nota
- Polisi kekuatan aliran reset kata laluan lama (`NewPasswordController`) masih
  `required|confirmed` sahaja — penyelarasan dicadangkan sebagai tugasan berasingan.
- Pemadaman sesi lain berkesan pada `SESSION_DRIVER=database` (dev/prod); pada ujian
  (array driver) ia diuji menerusi konfigurasi eksplisit dalam ujian.
- Mesej validasi kekal dalam Bahasa Inggeris (tiada `lang/ms/validation.php`) —
  konsisten dengan keadaan aplikasi semasa.
```
