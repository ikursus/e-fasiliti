# M04 Katalog Bilik & Sumber — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyiapkan katalog bilik mesyuarat berserta susun atur, kemudahan, peraturan khusus bilik dan waktu operasi khusus bilik, memenuhi FR-BLK-01, FR-BLK-02, FR-BLK-03, FR-BLK-04, FR-BLK-06 dan FR-BLK-08.

**Architecture:** Tiga jadual baharu — `rooms`, `room_layouts`, `room_facilities` — ditambah satu migrasi skema awal `bookings` kerana FR-BLK-08 (nyahaktif bilik mesti memaparkan tempahan akan datang yang terjejas) memerlukan jadual itu walaupun enjin tempahan (M05) belum dibina. Waktu operasi khusus bilik menggunakan lajur pemilik polimorfik sedia ada pada `operating_hours` (keputusan direkod dalam memory-bank), bukan lajur pada `rooms`. Susun atur dan kemudahan merujuk `reference_values` jenis `susun_atur_bilik` dan `kemudahan_bilik` yang sudah dibenih. Nyahaktif ialah tindakan utama; padam disekat oleh semakan rujukan. Kemas kini lokasi baharu menambah baris `bilik` pada `Location::referenceSummary()` — titik sambungan yang direka semasa M03.

**Tech Stack:** Laravel 13, PHP 8.4, Spatie laravel-permission v8, Blade, Tailwind v4, Alpine.js, PHPUnit 12. Ujian berjalan pada SQLite dalam ingatan; pengeluaran pada MySQL.

**Spec:** `docs-claude/04-SRS-software-requirements.md` (§M04), `docs-claude/05-DRD-data-requirements.md` (§4.2 BILIK), `docs-claude/01-modules.md` (§3 matriks peranan)

---

## ✅ Status Pelaksanaan — SIAP pada 9 September 2026 (sesi 1 + 2)

**Branch:** `feature/m04-katalog-bilik`. Komit: `836e1d4` (sesi 1) + `3ab5a34` (sesi 2). **Suite penuh: 256 lulus, 0 gagal** (garis dasar 217).

| Task | Keadaan |
|---|---|
| 0 Pelan | ✅ |
| 1 Migrasi | ✅ dijalankan pada MySQL |
| 2 Enum/model/factory | ✅ |
| 3 Kebenaran `bilik.*` | ✅ kod + ujian. **Seeder TIDAK dijalankan — keputusan pengguna (9 Sep)** |
| 4 `referenceSummary()` | ✅ |
| 5 Form Requests | ✅ diuji dalam `RoomValidationTest` |
| 6 Controller | ✅ diuji dalam `RoomManagementTest` |
| 7 Nyahaktif FR-BLK-08 | ✅ `deactivate.blade.php` + `RoomDeactivationTest` (5 ujian) |
| 8 Views | ✅ index/create/edit/_form (repeater susun atur Alpine, location-picker, waktu operasi 7 hari) |
| 9 Laluan + sidebar | ✅ 7 laluan `admin.rooms.*` (`can:bilik.*`), kumpulan "Fasiliti" dalam sidebar |
| 10 Ujian ciri | ✅ 24 ujian baharu (11+8+5) |
| 11 Checkpoint | ✅ pint + suite + 2 komit. **`db:seed` ditunda atas keputusan pengguna** |
| 12 Dokumen | ✅ STATUS.md + memory-bank |

**Kesan seeder tidak dijalankan:** pelayar masih mempunyai peranan tanpa `bilik.*`, jadi `/admin/rooms` akan memberi **403 kepada semua** sehingga `php artisan db:seed --class=RolesAndPermissionsSeeder` dijalankan suatu hari nanti. Kod, ujian dan laluan lengkap dan selamat.

**Pembetulan persekitaran yang direkodkan:**
- Suite bermula dengan 1 kegagalan sedia ada (`AuthenticationTest` throttle) kerana `.env` kini `APP_LOCALE=en`. Dibaiki dengan `<env name="APP_LOCALE" value="ms"/>` dalam `phpunit.xml`. **Keputusan pengguna tertunggak:** sama ada `.env` mahu dikembalikan kepada `ms` (UI pelayar kini berbahasa Inggeris).
- PHP/git tiada dalam PATH — `C:\laragonNafas\bin\php\php-8.4.25-Win32-vs17-x86\php.exe`, `C:\laragonNafas\bin\git\cmd\git.exe`. Pint: `& $php vendor\laravel\pint\builds\pint --format agent`. Output PowerShell kerap gagal dirakam — alihkan ke fail (`*> storage\logs\diag.log`), baca semula, buang aksara `\0` (log UTF-16).
- Context `AuditRecorder` digabung pada **aras atas** `metadata` (bukan `metadata['context']`).

**Langkah seterusnya projek:** M06 Kelulusan (kitaran pelan dahulu), kemudian M05 Enjin Tempahan. Jalankan seeder kebenaran apabila pengguna sedia.

---

## Nota Persekitaran

**Git tersedia** melalui laluan penuh `C:\laragonNafas\bin\git\cmd\git.exe` (tiada dalam PATH). Kerja M04 berada di branch `feature/m04-katalog-bilik`. Komit dibuat pada checkpoint dengan awalan `M04:`.

Arahan yang digunakan berulang kali:

```powershell
php artisan test
vendor/bin/pint --format agent
& 'C:\laragonNafas\bin\git\cmd\git.exe' add -A
& 'C:\laragonNafas\bin\git\cmd\git.exe' commit -m "M04: ..."
```

- **Checkpoint skema (wajib):** selepas mana-mana tugasan yang menambah jadual, jalankan `php artisan migrate` — ujian membina semula skema SQLite setiap kali, jadi jadual baharu boleh lulus semua ujian sambil tidak wujud dalam MySQL pembangunan. **`migrate:fresh` dan `migrate:rollback` dilarang** — `.env` menunjuk pangkalan data sebenar.
- **`db:seed` hanya dengan kebenaran pengguna.** Task 3 mengubah `RolesAndPermissionsSeeder`; menjalankannya menulis ke data sebenar dan `syncPermissions` membuang pemberian yang ditukar melalui skrin.
- Hook PostToolUse menjalankan Pint selepas setiap suntingan dan membuang import yang belum digunakan — tambah import dan penggunaannya dalam **satu** suntingan.
- Ujian ciri memerlukan manifest Vite (`npm run build` sekali jika tiada).

Garis dasar sebelum bermula: **217 ujian lulus**. Syarat lulus setiap checkpoint: tiada ujian gagal, dan jumlah bertambah dengan ujian baharu tugasan itu.

---

## Struktur Fail

| Fail | Tanggungjawab |
|---|---|
| `database/migrations/*_create_rooms_table.php` | Jadual bilik |
| `database/migrations/*_create_room_layouts_table.php` | Susun atur per bilik |
| `database/migrations/*_create_room_facilities_table.php` | Kemudahan per bilik |
| `database/migrations/*_create_bookings_table.php` | Skema awal tempahan (enjin dalam M05) |
| `app/Enums/BookingStatus.php` | Lapan status kitaran hayat DRD §4.2 |
| `app/Models/Room.php`, `RoomLayout.php`, `RoomFacility.php`, `Booking.php` | Model domain bilik |
| `app/Models/Location.php` (ubah) | `rooms()` + baris `bilik` pada `referenceSummary()` |
| `database/factories/RoomFactory.php`, `RoomLayoutFactory.php`, `BookingFactory.php` | Data ujian |
| `app/Http/Requests/Admin/RoomStoreRequest.php` | Pengesahan cipta bilik |
| `app/Http/Requests/Admin/RoomUpdateRequest.php` | Pengesahan kemas kini (kod unik dikecualikan diri) |
| `app/Http/Controllers/Admin/RoomController.php` | CRUD + nyahaktif dengan pengesahan FR-BLK-08 |
| `resources/views/admin/rooms/*` | Senarai, borang, pengesahan nyahaktif |
| `routes/web.php` (ubah) | Kumpulan `admin.rooms.*` dengan `can:bilik.*` |
| `resources/views/layouts/app.blade.php` (ubah) | Pautan sidebar |
| `database/seeders/RolesAndPermissionsSeeder.php` (ubah) | Kebenaran `bilik.*` |
| `tests/Feature/Admin/PermissionSeedingTest.php` (ubah) | Baris `EXPECTED_GRANTS` |

---

## Task 1: Migrasi skema

**Files:**
- Create: `database/migrations/2026_09_09_100001_create_rooms_table.php`
- Create: `database/migrations/2026_09_09_100002_create_room_layouts_table.php`
- Create: `database/migrations/2026_09_09_100003_create_room_facilities_table.php`
- Create: `database/migrations/2026_09_09_100004_create_bookings_table.php`

- [ ] `rooms`: `id`, `code` string(30) unique, `name` string(150), `location_id` foreignId → `locations` `restrictOnDelete()`, `base_capacity` unsignedInteger, `requires_approval` boolean default false, `allowed_roles` json nullable, `min_duration_minutes` unsignedInteger default 30, `max_duration_minutes` unsignedInteger default 480, `buffer_before_minutes` / `buffer_after_minutes` unsignedInteger default 0 (lajar sahaja, UI W2), `qr_code` string(100) nullable unique (W3), `is_active` boolean default true, `timestamps`.
- [ ] `room_layouts`: `id`, `room_id` foreignId → `rooms` `cascadeOnDelete()`, `layout_code` string(50), `capacity` unsignedInteger, `is_default` boolean default false, unique(`room_id`,`layout_code`).
- [ ] `room_facilities`: `id`, `room_id` foreignId → `rooms` `cascadeOnDelete()`, `facility_code` string(50), unique(`room_id`,`facility_code`).
- [ ] `bookings`: `id`, `reference_no` string(20) unique, `room_id` foreignId → `rooms` `restrictOnDelete()`, `booked_by_id` / `owner_id` foreignId → `users` `restrictOnDelete()`, `title` string(200), `description` text nullable, `starts_at` / `ends_at` datetime, `participant_count` unsignedInteger, `room_layout_id` foreignId → `room_layouts` nullable `nullOnDelete()`, `status` enum DRD (`draf`,`menunggu_kelulusan`,`disahkan`,`ditolak`,`daftar_masuk`,`selesai`,`dibatalkan`,`dilepaskan`) default `draf`, `cancellation_reason` string(500) nullable, `cancelled_late` boolean default false, `timestamps`, index(`room_id`,`starts_at`).
- [ ] **Checkpoint:** `php artisan migrate`, kemudian `php artisan migrate:status` mengesahkan empat migrasi berjalan; `php artisan test` kekal 217 lulus.

## Task 2: Enum, model dan factory

**Files:**
- Create: `app/Enums/BookingStatus.php` (8 kes + `label()`)
- Create: `app/Models/Room.php`, `app/Models/RoomLayout.php`, `app/Models/RoomFacility.php`, `app/Models/Booking.php`
- Edit: `app/Models/Location.php` — `rooms(): HasMany`
- Create: `database/factories/RoomFactory.php` (states `needsApproval()`, `inactive()`), `RoomLayoutFactory.php` (state `default()`), `BookingFactory.php` (states `upcoming()`, `confirmed()`, `pendingApproval()`)
- Test: `tests/Unit/Models/RoomTest.php`, `tests/Unit/Enums/BookingStatusTest.php`

- [ ] `Room`: `fillable` lengkap, cast `requires_approval`/`is_active` boolean + `allowed_roles` array; `location()`, `layouts()`, `facilities()`, `bookings()`, `operatingHours()` (morphMany `OperatingHour` atas `owner`), `scopeActive()`, `referenceSummary()` (alasan `tempahan` bila ada baris bookings), aksesor `locationFullPath()`.
- [ ] `Booking`: hubungan `room()`, `bookedBy()`, `owner()`, `layout()`; cast `starts_at`/`ends_at` datetime, `status` enum, `cancelled_late` boolean; skop `upcoming()` (bermula selepas sekarang, status `menunggu_kelulusan`/`disahkan`/`daftar_masuk`).
- [ ] **Checkpoint:** `vendor/bin/pint --format agent` + `php artisan test` hijau.

## Task 3: Kebenaran `bilik.*`

**Files:**
- Edit: `database/seeders/RolesAndPermissionsSeeder.php`
- Edit: `tests/Feature/Admin/PermissionSeedingTest.php`

Matriks §3 baris "M04 Bilik": Kakitangan/Setiausaha/Pelulus = **R**; Pentadbir Fasiliti = **CRUD**; Juruteknik/Penyelia ICT/Pegawai Aset = **—**; Pentadbir Sistem = **CRUD**.

- [ ] Tambah `bilik.lihat`, `bilik.cipta`, `bilik.kemaskini`, `bilik.padam` pada `PERMISSIONS`.
- [ ] `ROLE_PERMISSIONS`: tiga peranan baca dapat `bilik.lihat`; `pentadbir-fasiliti` dapat empat-empat; `pentadbir-sistem` mewarisi semua melalui `self::PERMISSIONS`.
- [ ] Kemas kini `EXPECTED_GRANTS` (grid ditulis berasingan daripada seeder) + ujian baharu `test_room_catalog_permissions_follow_the_matrix`.
- [ ] **Checkpoint:** `php artisan test` hijau. **Jangan jalankan seeder lagi** — Task 11, dengan kebenaran pengguna.

## Task 4: Titik sambungan M03 — `referenceSummary()`

**Files:**
- Edit: `app/Models/Location.php`
- Edit: `tests/Feature/Admin/LocationManagementTest.php` (kes baharu: lokasi dengan bilik tidak boleh dipadam)

- [ ] `referenceSummary()` menambah alasan `bilik` apabila `rooms()->exists()` (FR-ORG-03).
- [ ] **Checkpoint:** `php artisan test` hijau termasuk kes baharu.

---

## Task 5: Form Requests

**Files:**
- Create: `app/Http/Requests/Admin/RoomStoreRequest.php`
- Create: `app/Http/Requests/Admin/RoomUpdateRequest.php` (extends Store, corak `LocationUpdateRequest`; kod unik dikecualikan diri)

- [ ] Peraturan: `code` required max:30 unik `rooms`; `name` required max:150; `location_id` required exists + `after()`: lokasi mesti aras `ruang` dan aktif; `base_capacity` required integer min:1; `requires_approval` boolean; `allowed_roles` nullable array, setiap slug wujud dalam jadual `roles`; `min_duration_minutes` / `max_duration_minutes` required integer min:1 + `after()` min < maks; `is_active` boolean; `layouts` required array min:1, setiap baris `layout_code` wujud dalam `reference_values` aktif jenis `susun_atur_bilik`, `capacity` integer min:1, `is_default` boolean + `after()`: tepat satu `is_default`; `facilities` nullable array, setiap kod wujud (aktif, jenis `kemudahan_bilik`).
- [ ] **Checkpoint:** `php artisan test` hijau.

## Task 6: `Admin\RoomController` — CRUD + audit

**Files:**
- Create: `app/Http/Controllers/Admin/RoomController.php`

- [ ] `index`: senarai bilik + carian kod/nama, `with('location.parent.parent.parent')` (elak N+1).
- [ ] `store` / `update` dalam transaksi: tulis bilik + sync susun atur (buang yang ditanggalkan, taip semula yang kekal, kekalkan tepat satu lalai) + sync kemudahan; audit `room.created` / `room.updated` — snapshot `refresh()->only([...])` (peraturan `.ai/rules/admin.md`), konteks `layouts` / `facilities` senarai kod sebelum/selepas.
- [ ] `destroy`: disekat oleh `referenceSummary()`; audit `room.deleted` dengan snapshot sebelum.
- [ ] **Checkpoint:** `vendor/bin/pint --format agent` + `php artisan test` hijau.

## Task 7: Nyahaktif dengan pengesahan FR-BLK-08

**Files:**
- Edit: `app/Http/Controllers/Admin/RoomController.php` (`toggle`)
- Create: `resources/views/admin/rooms/deactivate.blade.php`

- [ ] Nyahaktif bilik yang ada tempahan akan datang aktif tanpa `confirm` → render skrin pengesahan yang menyenaraikan tempahan terjejas (`Booking::scopeUpcoming()`); tiada perubahan state.
- [ ] Dengan `confirm=1` → nyahaktif + audit `room.deactivated` (konteks bilangan tempahan terjejas); pengaktifan semula tidak melalui skrin pengesahan.
- [ ] **Checkpoint:** `php artisan test` hijau.

## Task 8: Views

**Files:**
- Create: `resources/views/admin/rooms/index.blade.php`, `create.blade.php`, `edit.blade.php`, `_form.blade.php`

- [ ] Index: jadual kod/nama/lokasi penuh/kapasiti/status/tindakan + carian; badge aktif/tidak aktif.
- [ ] Borang: medan asas, checkbox 8 peranan bagi `allowed_roles`, repeater susun atur Alpine (jenis/kapasiti/lalai), checkbox kemudahan daripada `reference_values`, jadual waktu operasi 7 hari (corak `admin/settings/operating-hours`) pada edit.
- [ ] Guna semula `<x-ui.location-picker>` untuk medan lokasi.
- [ ] **Checkpoint:** `npm run build` (jika perlu) + `php artisan test` hijau.

## Task 9: Laluan + sidebar

**Files:**
- Edit: `routes/web.php` (import `RoomController` + kumpulan `admin.rooms.*` **dalam satu suntingan** — peraturan hook Pint)
- Edit: `resources/views/layouts/app.blade.php` (kumpulan "Fasiliti" dengan `@can('bilik.lihat')`)

- [ ] Laluan: `GET rooms` (`can:bilik.lihat`), `GET rooms/create` + `POST rooms` (`can:bilik.cipta`), `GET rooms/{room}/edit` + `PUT rooms/{room}` (`can:bilik.kemaskini`), `PATCH rooms/{room}/toggle` (`can:bilik.kemaskini`), `DELETE rooms/{room}` (`can:bilik.padam`).
- [ ] **Checkpoint:** `php artisan test` hijau; `php artisan route:list` mengesahkan laluan.

## Task 10: Ujian ciri penuh

**Files:**
- Create: `tests/Feature/Admin/RoomManagementTest.php` (akses per peranan, CRUD, audit, padam disekat)
- Create: `tests/Feature/Admin/RoomValidationTest.php` (kod unik, aras `ruang`, min<maks, tepat satu lalai, kod rujukan tidak sah)
- Create: `tests/Feature/Admin/RoomDeactivationTest.php` (FR-BLK-08 dua cabang)

- [ ] Ketiga-tiga lulus; juruteknik/penyelia-ict/pegawai-aset 403; kakitangan/setiausaha/pelulus boleh `index` sahaja; pentadbir-fasiliti dan pentadbir-sistem CRUD penuh.
- [ ] **Checkpoint:** `php artisan test` hijau + `vendor/bin/pint --format agent`.

## Task 11: Checkpoint skema + seeder

- [ ] `php artisan migrate` (empat jadual baharu dalam MySQL `e-fasiliti`).
- [ ] **Tanya pengguna** sebelum `php artisan db:seed --class=RolesAndPermissionsSeeder`.
- [ ] Komit `M04: ...` pada branch `feature/m04-katalog-bilik`.

## Task 12: Kemas kini dokumen projek

- [ ] `memory-bank/progress.md`, `memory-bank/activeContext.md`, `docs/superpowers/plans/STATUS.md` — catat siapnya M04, pembetulan fakta git (repo wujud, git di `bin\git`), dan langkah seterusnya (M06).
