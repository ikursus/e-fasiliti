# Reka Bentuk — M03 Direktori Organisasi & Lokasi, M01 Pentadbiran Sistem & Konfigurasi

| Perkara | Butiran |
|---|---|
| Sistem | e-Fasiliti (SPFA) |
| Modul | M03, M01 |
| Tarikh | 8 September 2026 |
| Status | Diluluskan untuk pelan pelaksanaan |
| Rujukan | `docs-claude/01-modules.md`, `04-SRS`, `05-DRD` |

---

## 1. Tujuan dan Skop

Kitaran ini menyiapkan dua modul lapisan asas yang menjadi prasyarat kepada semua modul domain:

- **M03** — hierarki lokasi empat aras dan hierarki organisasi dua aras, berserta pemilih lokasi yang digunakan semula oleh M04 dan M09. Memenuhi FR-ORG-01 hingga FR-ORG-05.
- **M01** — semua tetapan yang boleh diubah tanpa menulis kod. Memenuhi FR-ADM-01 hingga FR-ADM-08.

**Di luar skop:** M04 hingga M15. Tetapan milik modul tersebut dibina sekarang atas keputusan pemilik projek, tetapi tiada penggunanya sehingga modul berkenaan siap.

**Risiko yang diterima:** peraturan tempahan per peranan, ambang daftar masuk, aras keutamaan tiket dan templat notifikasi dibina sebelum penggunanya wujud. Bentuk datanya mungkin perlu dilaraskan apabila M05, M07, M10 dan M14 dibina. Mitigasi: setiap satu berada dalam jadual tersendiri di belakang perkhidmatan konfigurasi, jadi perubahan tidak merebak ke modul lain.

---

## 2. Keadaan Semasa

Sudah wujud dan tidak diubah: pengesahan identiti, pengurusan sesi, CRUD pengguna dan peranan (M02), lapan peranan Spatie, jadual `audit_logs` dengan perakam log masuk, susun atur Blade dengan Tailwind v4 dan Alpine.js. Suite ujian: 44 lulus.

Sudah wujud tetapi tidak lengkap: jadual `organization_units` dan `locations` beserta model dan seeder. Tiada pengawal, tiada antara muka, tiada peraturan hierarki.

---

## 3. Keputusan Reka Bentuk

| # | Keputusan | Sebab |
|---|---|---|
| D1 | Blade dan Alpine sahaja, tiada Livewire atau Inertia | Kekal dengan susunan sedia ada; isipadu lokasi kecil (bawah 500 baris) jadi pokok boleh dihasilkan di pelayan |
| D2 | Kod lokasi unik dalam induk yang sama | Mengikut kamus data DRD bahagian 4.1 |
| D3 | Nyahaktif ialah tindakan utama; padam kekal bersyarat | FR-ORG-03 dan FR-ORG-04; medan `is_active` sudah wujud |
| D4 | Tetapan skalar dalam satu jadual kunci-nilai; senarai dalam jadual tersendiri | Membolehkan pertanyaan baris dan audit per baris; JSON tunggal menyukarkan kedua-duanya |
| D5 | Waktu operasi polimorfik dari awal | FR-ADM-01 memerlukan waktu lalai organisasi dan waktu khusus bilik; M04 tidak perlu jadual baharu |
| D6 | Perakam audit umum berkongsi jadual `audit_logs` | FR-ADM-08 dan FR-AUD; memberi kandungan sebenar kepada M16 tanpa jadual baharu |
| D7 | Pembangunan dipacu ujian | Corak sedia ada dalam projek; peraturan hierarki dan pengesahan silang mudah tersilap |

---

## 4. M03 — Direktori Organisasi & Lokasi

### 4.1 Data

Jadual `locations` kekal, dengan satu migrasi tambahan:

- Gugurkan indeks unik menyeluruh pada `code`.
- Tambah indeks unik gabungan `(parent_id, code)`.
- Kerana MySQL menganggap NULL berbeza antara satu sama lain, keunikan kod bagi aras kampus (induk kosong) dikuatkuasakan oleh peraturan pengesahan aplikasi, bukan oleh indeks.

Jadual `organization_units` kekal tanpa perubahan skema. Kod unit kekal unik menyeluruh kerana ia dua aras sahaja dan dirujuk terus oleh rekod pengguna.

### 4.2 Model dan peraturan domain

`App\Enums\LocationLevel` — enum bersandar: kampus, bangunan, tingkat, ruang, dengan kaedah aras induk yang dibenarkan dan label paparan.

`Location`:
- Hubungan `parent`, `children`, dan `descendants` (children rekursif).
- Skop `active`. Skop penapis aras tidak dibina kerana tiada pengguna; ia ditambah apabila M04 benar-benar memerlukannya.
- Aksesor laluan penuh, contoh "Kampus Induk / Blok A / Tingkat 3 / Bilik Mesyuarat 1".
- Kaedah `isDescendantOf()` untuk mengesan kitaran.

`OrganizationUnit`: corak sama dengan dua aras. Bahagian tiada induk, unit mesti mempunyai induk bahagian.

**Peraturan hierarki**, dikuatkuasakan dalam Form Request dan diuji secara unit:

1. Aras kampus mesti tiada induk. Aras lain mesti mempunyai induk.
2. Aras induk mesti tepat satu aras di atas aras anak.
3. Induk tidak boleh diri sendiri atau mana-mana keturunan sendiri.
4. Aras tidak boleh ditukar jika rekod sudah mempunyai anak.
5. Kod unik dalam induk yang sama, tidak sensitif huruf besar kecil.

### 4.3 Pemadaman dan penyahaktifan

Kaedah tunggal `Location::referenceSummary()` memulangkan senarai sebab rekod tidak boleh dipadam:

- mempunyai lokasi anak
- dirujuk oleh pengguna melalui `primary_location_id`
- (M04) dirujuk oleh bilik — satu baris ditambah kemudian
- (M09) dirujuk oleh aset — satu baris ditambah kemudian

Pengawal menyekat padam jika senarai tidak kosong, dan memaparkan sebabnya kepada pengguna. Nyahaktif sentiasa dibenarkan; menyahaktifkan induk turut menyahaktifkan keturunannya dalam satu transaksi. Rekod tidak aktif tidak muncul dalam pemilih borang tetapi kekal dipaparkan dalam rekod sejarah, memenuhi FR-ORG-04.

`OrganizationUnit::referenceSummary()` mengikut corak sama: mempunyai unit anak, atau dirujuk oleh pengguna melalui `organization_unit_id`.

### 4.4 Antara muka

| Skrin | Laluan | Kandungan |
|---|---|---|
| Pokok lokasi | `admin/locations` | Pokok rekursif, buka dan tutup Alpine, carian nama atau kod, penapis aktif |
| Cipta dan sunting lokasi | `admin/locations/create`, `admin/locations/{location}/edit` | Borang dengan pilihan aras dan pemilih induk yang ditapis mengikut aras |
| Pokok organisasi | `admin/organization-units` | Sama, dua aras |
| Cipta dan sunting unit | `admin/organization-units/create`, `.../edit` | Borang bahagian dan unit |

**Komponen boleh guna semula** `<x-ui.location-picker>`: turun-turun berjujukan kampus, bangunan, tingkat, ruang. Menerima nilai terpilih, aras minimum yang dibenarkan, dan nama medan. Data pokok dihantar sebagai satu muatan JSON kepada Alpine; pada isipadu di bawah 500 baris ini lebih murah daripada panggilan AJAX bertingkat. Komponen ini ialah antara muka yang dijanjikan kepada M04 dan M09, dan memenuhi FR-ORG-05.

### 4.5 Kebenaran

Nama kebenaran mengikut konvensyen sedia ada dalam `RolesAndPermissionsSeeder`, iaitu Bahasa Melayu bertitik.

| Kebenaran | Peranan |
|---|---|
| `lokasi.lihat` | kelapan-lapan peranan |
| `lokasi.cipta`, `lokasi.kemaskini` | pentadbir sistem, pentadbir fasiliti, pegawai aset |
| `lokasi.padam` | pentadbir sistem |
| `unit-organisasi.lihat` | kelapan-lapan peranan |
| `unit-organisasi.cipta`, `unit-organisasi.kemaskini`, `unit-organisasi.padam` | pentadbir sistem |

Kebenaran ditambah kepada `RolesAndPermissionsSeeder` mengikut matriks `01-modules.md` bahagian 3. Laluan menggunakan middleware `can:` bagi setiap kebenaran, bukan `role:` seperti kawasan pentadbiran sedia ada, kerana M03 dikongsi oleh tiga peranan.

---

## 5. M01 — Pentadbiran Sistem & Konfigurasi

### 5.1 Jadual

**`system_settings`** — nilai skalar, mengikut kamus data KONFIGURASI.

| Lajur | Jenis | Nota |
|---|---|---|
| `key` | string 100, kunci utama | contoh `checkin.threshold_minutes` |
| `value` | text | disimpan sebagai JSON |
| `value_type` | enum | teks, nombor, boolean, json |
| `group` | string 50 | menentukan tab antara muka |
| `description` | string 500, boleh kosong | dipaparkan dalam skrin pentadbiran |
| `updated_by` | foreignId pengguna, boleh kosong | |
| timestamps | | |

**`holidays`** — FR-ADM-02. Lajur: `date`, `name`, `type` (cuti_umum atau hari_tanpa_tempahan), `recurs_annually` boolean, `is_active`. Unik pada `(date, type)`.

**`operating_hours`** — FR-ADM-01. Lajur: `owner_type` dan `owner_id` polimorfik boleh kosong (kosong bermakna waktu lalai organisasi), `day_of_week` 0 hingga 6, `opens_at`, `closes_at`, `is_closed` boolean. Unik pada `(owner_type, owner_id, day_of_week)`. Pengesahan: waktu tutup mesti selepas waktu buka apabila hari tidak ditandakan tutup.

**`role_booking_rules`** — FR-ADM-03. Lajur: `role_id` unik, `min_duration_minutes`, `max_duration_minutes`, `max_advance_days`. Pengesahan: tempoh minimum mesti kurang daripada tempoh maksimum.

**`ticket_priorities`** — FR-ADM-05. Lajur: `code` unik, `label`, `response_target_minutes`, `resolution_target_minutes`, `sort_order`, `is_active`. Pengesahan: sasaran pemulihan mesti lebih besar daripada sasaran tindak balas.

**`reference_values`** — FR-ADM-07. Lajur: `type`, `code`, `label`, `sort_order`, `is_active`, `metadata` JSON boleh kosong. Unik pada `(type, code)`. Lima jenis awal: kategori aset, jenis kerosakan, susun atur bilik, kemudahan bilik, unit stok. Enum `ReferenceValueType` menyenaraikan jenis yang sah.

**`notification_templates`** — FR-ADM-06. Lajur: `key`, `channel` (emel atau dalam aplikasi), `locale` (ms atau en), `subject`, `body`, `placeholders` JSON, `is_active`. Unik pada `(key, channel, locale)`. Pemegang tempat yang dibenarkan disenaraikan pada skrin suntingan dan disahkan semasa simpan: pemegang tempat yang tidak dikenali ditolak.

Ambang daftar masuk dan tempoh anjal (FR-ADM-04) disimpan sebagai tetapan skalar dalam `system_settings`, bukan jadual tersendiri, kerana ia dua nombor tunggal.

### 5.2 Perkhidmatan konfigurasi

`App\Services\Configuration\SettingsRepository`:

- `get(string $key, mixed $default = null): mixed` — menyahkod mengikut `value_type`.
- `set(string $key, mixed $value, ?User $actor = null): void` — menulis, merekod audit, membatalkan cache.
- `all(?string $group = null): Collection`.
- Cache: satu entri mengandungi semua tetapan, dibatalkan pada setiap penulisan. Tiada cache per kunci, kerana bilangan tetapan kecil dan satu bacaan memberi semuanya.

Pembantu global `setting()` membungkus repositori supaya Blade dan modul lain tidak perlu menyuntik kelas.

`App\Services\Configuration\HolidayCalendar`:

- `isHoliday(CarbonInterface $date): bool` dan `isBookable(CarbonInterface $date): bool`, mengambil kira cuti berulang tahunan.
- Tidak menyentuh pangkalan data dalam pengiraan; ia menerima koleksi cuti sebagai input supaya boleh diuji unit sepenuhnya.

Modul lain memanggil kelas-kelas ini sahaja. Tiada pengawal atau paparan lain menyentuh jadual konfigurasi secara terus.

### 5.3 Audit, FR-ADM-08

`App\Services\Audit\AuditRecorder` merekod ke jadual `audit_logs` sedia ada:

- `action` — contoh `setting.updated`, `location.created`, `holiday.deleted`
- `record_type` dan `record_id`
- `metadata` — mengandungi `before` dan `after` bagi medan yang berubah sahaja

Digunakan oleh setiap penulisan konfigurasi dan setiap perubahan M03. `LoginAuditLogger` sedia ada tidak diubah. Log tidak boleh dipadam melalui antara muka.

### 5.4 Antara muka

Satu kawasan `admin/settings` dengan tab:

| Tab | Keperluan |
|---|---|
| Umum dan waktu operasi | FR-ADM-01 |
| Cuti umum | FR-ADM-02 |
| Peraturan tempahan | FR-ADM-03 |
| Daftar masuk | FR-ADM-04 |
| Keutamaan tiket | FR-ADM-05 |
| Nilai rujukan | FR-ADM-07 |
| Templat notifikasi | FR-ADM-06 |

Setiap tab ialah laluan tersendiri supaya boleh dipautkan terus dan diuji berasingan. Tab yang tetapannya belum digunakan oleh mana-mana modul memaparkan nota kecil yang menyatakan modul mana akan menggunakannya.

### 5.5 Kebenaran

Kedua-dua kebenaran sudah wujud dalam seeder, tetapi kini diberikan kepada lebih banyak peranan mengikut matriks.

| Kebenaran | Peranan |
|---|---|
| `tetapan.lihat` | pentadbir sistem, pentadbir fasiliti, penyelia ICT, pegawai aset |
| `tetapan.kemaskini` | pentadbir sistem |

---

## 6. Strategi Ujian

Ujian ditulis dahulu, mengikut corak `tests/Feature` sedia ada.

**Ujian unit**

- Peraturan hierarki lokasi: aras induk, pengesanan kitaran, keunikan kod dalam induk.
- `SettingsRepository`: penyahkodan setiap `value_type`, pembatalan cache selepas tulis.
- `HolidayCalendar`: cuti sekali, cuti berulang tahunan, hari tanpa tempahan.
- Pengesahan silang: tempoh minimum berbanding maksimum, waktu buka berbanding tutup, sasaran tindak balas berbanding pemulihan.

**Ujian ciri**

- Setiap skrin M03 dan M01: capaian dibenarkan dan ditolak bagi kelapan-lapan peranan.
- Padam lokasi disekat apabila mempunyai anak atau pengguna yang merujuknya.
- Nyahaktif induk turut menyahaktifkan keturunan.
- Setiap penulisan konfigurasi menghasilkan satu baris `audit_logs` dengan nilai sebelum dan selepas.
- Templat notifikasi menolak pemegang tempat yang tidak dikenali.

**Nota persekitaran:** `npm run build` mesti dijalankan sebelum ujian ciri kerana paparan memerlukan manifest Vite.

---

## 7. Kriteria Penerimaan

1. FR-ORG-01 hingga FR-ORG-05 dan FR-ADM-01 hingga FR-ADM-08 semuanya boleh ditunjukkan melalui antara muka.
2. Suite ujian lulus sepenuhnya, termasuk 44 ujian sedia ada.
3. `vendor/bin/pint --format agent` bersih.
4. Seeder menghasilkan struktur organisasi dan lokasi contoh, cuti umum Malaysia bagi tahun semasa, lima senarai nilai rujukan, dan templat notifikasi asas.
5. Matriks kebenaran dalam `01-modules.md` bahagian 3 dipatuhi bagi M01 dan M03.
6. `memory-bank/progress.md` dan `memory-bank/activeContext.md` dikemas kini.
