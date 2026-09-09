# Status Pelaksanaan — kemas kini 9 September 2026

> **SESI AKAN DATANG:** **M10 Tiket Aduan Kerosakan telah SIAP** (lihat
> `docs/superpowers/plans/2026-09-09-m10-tiket-aduan-plan.md`). Satu isu
> sedia ada yang tidak berkaitan M10: `AuthenticationTest::test_account_is_locked_after_five_failed_attempts`
> gagal kerana `APP_LOCALE=en` dalam `.env` sedangkan ujian menjangka mesej
> throttle BM. Modul domain A (M04 bilik / M05 tempahan) adalah calon kerja
> seterusnya. Semua kerja di bawah ini ialah status M01/M03 yang lama.

## Di mana kita berhenti

Melaksanakan pelan M03 secara dipandu subejen: satu subejen pelaksana bagi setiap tugasan, diikuti semakan pematuhan spesifikasi, kemudian semakan kualiti kod.

**Ujian: 147 lulus** (garis dasar sebelum kerja M03 bermula: 44). **M03 selesai sepenuhnya, Task 1–16.**

## Tugasan M03 yang selesai

| # | Tugasan | Status |
|---|---|---|
| 1 | Enum `LocationLevel` | Selesai, kedua-dua semakan lulus |
| 2 | Kilang model lokasi dan unit organisasi | Selesai selepas tiga pembetulan |
| 3 | Migrasi kod lokasi unik dalam induk | Selesai |
| 4 | Kelas `HierarchyRules` | Selesai |
| 5 | Kaedah pokok pada model `Location` | Selesai selepas tiga pembetulan |
| 6 | Kaedah pokok pada model `OrganizationUnit` | Selesai selepas dua pembetulan |
| 7 | Perkhidmatan `AuditRecorder` | **Ditutup.** Kedua-dua semakan lulus; satu pepijat penting ditemui dan dibetulkan |
| 8 | Kebenaran M03 dalam seeder | **Ditutup.** Semakan mengesahkan setiap sel matriks betul; ujian diperkukuh |
| 9 | Form Request lokasi | **Ditutup.** Satu pepijat invarian ditemui dan dibetulkan |
| 10 | Pengawal dan paparan lokasi | **Ditutup.** Satu masalah N+1 ditemui dan dibetulkan |
| 11 | Komponen pemilih lokasi | Selesai |
| 12 | Guna pemilih lokasi dalam borang pengguna | Selesai |
| 13 | Form Request unit organisasi | Selesai, dengan invarian `is_active` diterapkan awal |
| 14 | Pengawal dan paparan unit organisasi | Selesai, dengan konteks audit lataan diterapkan awal |
| 15 | Pautan navigasi dan seeder yang diperluas | Selesai |
| 16 | Kemas kini memory-bank | Selesai |

## Task 9 dan 10 — apa yang dibuat

Pelan menetapkan kedua-dua tugasan ini mesti dilaksana berturut-turut tanpa henti, kerana ujian pengesahan Task 9 memerlukan laluan yang hanya wujud selepas Task 10. Itulah yang dibuat.

**Task 9.** `LocationStoreRequest` dan `LocationUpdateRequest` dalam `app/Http/Requests/Admin/`. Peraturan biasa bagi panjang medan (kod 30 aksara, nama 150 — disahkan sepadan dengan entiti LOKASI dalam `docs-claude/05-DRD-data-requirements.md:104`), kemudian pengesahan `after()` bagi perkara yang peraturan biasa tidak boleh ungkapkan: induk mesti tepat satu aras di atas anak, kod unik dalam induk yang sama, tiada kitaran, dan aras tidak boleh ditukar semasa anak masih wujud.

**Task 10.** `LocationController` dengan index (pokok atau senarai carian), create, store, edit, update, toggle dan destroy. Tujuh laluan dalam kumpulan `admin.locations.*`, setiap satu dikawal `can:lokasi.*` dan bukan `role:`, kerana tiga peranan berkongsi modul ini. Lima paparan Blade termasuk partial pokok rekursif.

**Nota migrasi:** lajur `locations.code` ialah 50 aksara sedangkan DRD menetapkan 30. Pengesahan menguatkuasakan 30, jadi tiada risiko limpahan. Perbezaan ini sedia ada sebelum tugasan ini.

## Task 9 dan 10 — hasil semakan

Dua kebimbangan yang disyaki Kritikal **tidak** menjadi kenyataan, dan penyemak mengesahkannya secara empirik, bukan dengan penaakulan:

- `where('parent_id', null)` memang bertukar menjadi `whereNull` dalam pembina pertanyaan Laravel, jadi keunikan kod pada aras akar benar-benar dikuatkuasakan.
- `ConvertEmptyStringsToNull` memang berada dalam timbunan middleware lalai. `bootstrap/app.php` hanya memanggil `alias()`, `web(append:)` dan `redirectGuestsTo()`, jadi set lalai kekal. Borang pelayar yang menghantar `parent_id=''` berkelakuan sama seperti ujian yang menghantar `null` sebenar.

Muatan awal `with('descendants')` juga disahkan benar-benar menghapuskan N+1 pada pokok: 98 lokasi dirender dalam 9 pertanyaan.

### Pepijat 1 — invarian `is_active` hanya satu arah

`toggle()` melata penyahaktifan ke bawah, tetapi tiada apa-apa menghalang rekod aktif berada di bawah induk yang tidak aktif. Tiga laluan memecahkan jaminan itu, ketiga-tiganya disahkan: cipta anak aktif di bawah induk tidak aktif, sunting anak menjadi aktif, dan toggle anak menjadi aktif. `is_active` hanya pernah disahkan sebagai `['nullable','boolean']`; keadaan induk tidak pernah dirujuk.

Ini penting kerana M04 akan menapis bilik boleh tempah pada `locations.is_active`, jadi setiap laluan itu menghidupkan semula baris yang lataan itu sepatutnya kuburkan.

**Pembetulan:** `validateParentIsActive()` dalam `LocationStoreRequest` (diwarisi oleh `LocationUpdateRequest`) menolak `is_active = true` apabila induk tidak aktif, dan `toggle()` enggan mengaktifkan semula selagi induk masih tidak aktif. Empat ujian baharu.

**Nota pembetulan dokumen:** docblock `toggle()` dalam pelan memetik **FR-ORG-04** bagi lataan ini. Petikan itu salah. FR-ORG-04 (`docs-claude/04-SRS-software-requirements.md:96`) berkenaan penamaan semula lokasi tanpa menjejaskan rekod sejarah, bukan lataan penyahaktifan. Lataan itu keputusan reka bentuk pelan tanpa FR khusus. Docblock sudah dibetulkan supaya tidak memetik FR yang salah.

### Pepijat 2 — `descendantIds()` mengeluarkan satu pertanyaan bagi setiap nod

Rekursi asal linear dengan saiz subpokok, dan ia dipanggil pada laluan permintaan oleh `toggle()`, `parentOptions()` dan pengesahan kitaran. Digantikan dengan jalan aras demi aras (`whereIn('parent_id', ...)`), dihadkan pada bilangan aras hierarki atas sebab keselamatan kitaran yang sama seperti jalan moyang sedia ada.

Diukur sendiri pada direktori 98 lokasi (2 kampus × 3 bangunan × 3 tingkat × 4 ruang):

| Tindakan | Sebelum | Selepas |
|---|---|---|
| Borang sunting kampus | 73 pertanyaan | 10 |
| Toggle kampus | 53 | 8 |
| `descendantIds()` | satu setiap nod | 4 |
| Indeks pokok | 9 | 9 |

### Pepijat 3 — lataan tidak meninggalkan jejak audit

Menyahaktifkan pokok tiga nod menghasilkan tepat **satu** baris `audit_logs`, untuk induk sahaja. Dua keturunan bertukar tidak aktif tanpa sebarang rekod. Ditanya "kenapa tingkat ini tidak aktif?", jejak itu tidak boleh menjawab.

**Pembetulan:** `AuditRecorder::record()` menerima parameter `context` pilihan yang digabungkan ke dalam metadata di aras atas dan **tidak** menyertai perbandingan medan. Ini titik lanjutan yang tepat: `cascaded_ids` ialah fakta tentang operasi, bukan medan pada rekod sasaran, jadi memasukkannya ke dalam `after` akan merosakkan makna before/after. Dua ujian baharu dalam `AuditRecorderTest`.

### Pembetulan lain

- `parentOptions()` tidak lagi menawarkan `ruang` sebagai induk. Tiada apa boleh berada di bawah ruang, jadi menawarkannya hanya membiarkan pengguna memilih pilihan yang pasti ditolak pengesah.
- Pengesahan padam kini menamakan lokasi. Sebelum ini setiap baris dalam pokok padat memberi mesej yang sama untuk padaman keras.
- `test_the_same_code_is_accepted_under_a_different_parent` diperkukuh: `assertSessionHasNoErrors()` sahaja juga lulus pada respons 500.

### Jurang ujian yang ditutup

Penyemak mengesahkan tingkah laku berikut berfungsi tetapi **tidak dilindungi mana-mana ujian** — buang kodnya dan suite tetap hijau. Semuanya kini diuji: kod ditukar huruf besar semasa simpan, kod yang berbeza hanya pada huruf besar/kecil ditolak, `parent_id` kosong daripada pelayar, dan pokok yang dilihat `kakitangan` tidak memaparkan tindakan Edit, Nyahaktif atau Padam.

## Task 7 — ditutup

Semakan kualiti kod menemui satu pepijat penting yang **bukan** ditemui oleh semakan pematuhan sebelumnya.

**Pepijat: petikan data padaman hilang senyap.** `changedFields()` asal hanya mengulang `$after`. Panggilan padaman menghantar `before:` sahaja tanpa `after:`, jadi `$changed` kosong dan `metadata` ditulis sebagai `null`. Petikan rekod yang dipadam hilang sepenuhnya — sedangkan bagi padaman keras, `audit_logs` ialah satu-satunya rekod yang tinggal tentang apa yang dimusnahkan (FR-ADM-08, FR-AUD-01).

Ini disahkan bukan teori. Tiga tapak panggilan dalam pelan sendiri menghantar `before:` sahaja:

- `2026-09-08-m03-direktori-organisasi-lokasi.md:2259` — `location.deleted`
- `2026-09-08-m03-direktori-organisasi-lokasi.md:3341` — `organization_unit.deleted`
- `2026-09-08-m01-konfigurasi-sistem.md:2036` — `holiday.deleted`

Penegasan pelan bagi ketiga-tiganya hanya `assertDatabaseHas(['action' => ...])`, jadi Task 9 dan seterusnya akan lulus di atas jejak audit yang kosong.

**Pembetulan:** `changedFields()` kini mengimbas gabungan kunci kedua-dua belah (`$before + $after`), dan pembantu baharu `pick()` mengisi medan yang tiada pada satu belah dengan `null` supaya `before` dan `after` sentiasa berkongsi kunci yang sama. Dua ujian baharu ditambah: petikan padaman dikekalkan, dan medan yang hanya wujud pada satu belah diisi `null`.

Penegasan ujian 3 juga diperketat daripada `assertArrayNotHasKey('before', ...)` kepada `assertNull($log->metadata)`. Yang lama akan lulus walaupun pelaksanaan menulis bentuk metadata yang salah, asalkan tiada kunci bernama `before`.

**Pepijat `Str::limit` (dari sesi sebelum ini, kekal betul):** `Str::limit($ua, 255)` menambah elipsis tiga aksara selepas memotong, memulangkan 258 aksara ke dalam lajur 255 aksara. Pembetulannya `Str::limit($ua, 255, '')`, dengan ujian regresi 400 aksara. Disahkan semula oleh penyemak: nilai lalai 258, dengan elipsis kosong tepat 255.

## Kontrak `AuditRecorder` yang mesti dipatuhi pemanggil

Medan dibandingkan secara **ketat** (`!==`). Kedua-dua petikan mesti membawa nilai yang sudah ditapis cast Eloquent, bukan input borang mentah.

Ini pepijat sebenar dalam teks pelan, bukan teori. `LocationController::update()` menulis `'parent_id' => $request->input('parent_id')` (rentetan `"3"`) lalu mengambil petikan `after` terus daripada model dalam ingatan, sedangkan `before` dibaca sebagai integer `3` daripada pangkalan data. `Location` tiada cast bagi `parent_id` (`app/Models/Location.php:30-34`), jadi **setiap** kemas kini lokasi akan merekod perubahan palsu `parent_id: 3 → "3"`.

Sudah dibetulkan dalam teks pelan pada dua tapak: `:2204` (lokasi) dan `:3293` (unit organisasi) kini menggunakan `$model->refresh()->only([...])` bagi petikan `after`. Kontrak ini turut didokumenkan dalam docblock `record()`.

Tapak M01 diperiksa dan **selamat** — `payload()` keutamaan tiket menggunakan `$request->integer()`/`boolean()`, templat notifikasi menggunakan `string()`/`boolean()` dengan cast padanan, dan waktu operasi membina kedua-dua belah secara manual sebagai rentetan.

## Task 8 — apa yang dibuat

Kebenaran `lokasi.*` dan `unit-organisasi.*` ditambah ke `PERMISSIONS`, dan `ROLE_PERMISSIONS` ditulis semula mengikut matriks `docs-claude/01-modules.md §3`. Ujian `tests/Feature/Admin/PermissionSeedingTest.php` (5 ujian) lulus.

**Percanggahan yang disengajakan dan perlu keputusan pengguna.** Baris matriks "M03 Lokasi" memberi Pentadbir Fasiliti dan Pegawai Aset **CRU** ke atas keseluruhan M03, dan M03 merangkumi kedua-dua hierarki fizikal **dan** hierarki organisasi. Pelaksanaan sengaja lebih ketat: hanya `pentadbir-sistem` boleh mencipta, mengemas kini atau memadam unit organisasi; semua peranan lain boleh membaca sahaja.

Alasannya carta organisasi ialah data struktur, bukan fasiliti fizikal. Ia datang daripada pelan dan diterima secara sedar, serta didokumenkan dalam docblock `ROLE_PERMISSIONS`. **Jika pengguna mahu mengikut matriks secara literal, tambah `unit-organisasi.cipta` dan `unit-organisasi.kemaskini` kepada `pentadbir-fasiliti` dan `pegawai-aset`, dan kemas kini ujian `test_only_the_system_administrator_may_manage_organisation_units`.**

Perhatikan juga Task 8 meluaskan `tetapan.lihat` kepada `pentadbir-fasiliti`, `penyelia-ict` dan `pegawai-aset`, yang sebelum ini tidak memilikinya. Ini betul mengikut baris matriks "M01 Konfigurasi" (R bagi ketiga-tiganya).

## Task 8 — hasil semakan

Semakan mengesahkan **setiap sel** baris matriks M01, M03, M15 dan M16 dilaksana dengan betul, tiada pemberian lama digugurkan, dan pemalar konsisten dalaman. Kelemahan yang ditemui ialah **kekuatan ujian, bukan ketepatan kod**.

Lima ujian yang dipreskripsikan pelan meninggalkan sebahagian besar matriks tanpa perlindungan. Disahkan dengan `grep`: tiada satu pun ujian dalam keseluruhan suite menyebut `laporan.lihat`, `audit.lihat`, `lokasi.cipta`, `unit-organisasi.cipta` atau `unit-organisasi.padam`. Tiga daripada empat peranan yang **tidak** sepatutnya memiliki `tetapan.lihat` juga tidak diperiksa.

Risiko sebenar: `unit-organisasi.cipta` dan `unit-organisasi.padam` diberi kepada `pentadbir-sistem` secara **tersirat** melalui `self::PERMISSIONS`. Padamkan salah satu daripada pemalar `PERMISSIONS` dan seeder tetap berjalan bersih, semua ujian tetap lulus, dan kegagalan hanya muncul sebagai 403 selepas Task 14 memasang middleware `can:unit-organisasi.*`.

**Pembetulan.** `PermissionSeedingTest` diperluas daripada 5 kepada 9 ujian (107 penegasan):

- `EXPECTED_GRANTS` — grid penuh 8 peranan × senarai kebenaran tepat, **ditulis secara berasingan** daripada seeder. Menerbitkannya daripada pemalar akan menjadikan ujian tautologi. Kerana senarai `pentadbir-sistem` ditulis tangan sebagai 20 item, membuang mana-mana kebenaran daripada `PERMISSIONS` kini **gagal** ujian ini.
- Katalog kebenaran dan bilangan peranan ditegaskan secara langsung (`assertCount(8, ...)` + `assertEqualsCanonicalizing`).
- Setiap ujian bernama kini mengulang **kesemua lapan** peranan dan menegaskan kedua-dua arah, bukan hanya beberapa peranan yang dibenarkan.
- Ujian baharu melindungi `laporan.lihat` dan `audit.lihat`, yang sebelum ini tidak dilindungi langsung.

Tiga docblock juga dibetulkan: docblock kelas tidak lagi mendakwa `laporan.lihat` (M15) ditangguh sedangkan ia sudah wujud; nota ditambah bahawa pemberian M02 kepada tiga peranan ditangguh kerana direktori pengguna masih dikawal `role:` bukan `can:`; dan `run()` kini menyatakan `syncPermissions` membuang suntingan yang dibuat melalui skrin pengurusan peranan.

Invarian ini direkodkan sebagai peraturan projek dalam `.ai/rules/seeders.md`.

## Isu diketahui, belum diputuskan oleh pengguna

**Pepijat elipsis `Str::limit` yang sama masih wujud di dua tempat sedia ada,** kedua-duanya menulis ke lajur `audit_logs.user_agent` 255 aksara yang sama dan membawa risiko "Data too long for column" yang sama pada MySQL mod ketat:

- `app/Services/Auth/LoginAuditLogger.php:23`
- `app/Http/Controllers/Admin/UserController.php:175`, dalam kaedah persendirian `audit()`

Pembetulannya satu baris setiap satu (`Str::limit($value, 255, '')`). Sengaja tidak diubah kerana di luar skop Task 7 dan 8. **Menunggu keputusan pengguna.**

## Cadangan penyemak yang sengaja tidak dilaksanakan (YAGNI)

Direkodkan supaya keputusan ini tidak perlu diterokai semula:

- **`Request` disuntik melalui konstruktor `AuditRecorder`.** Di luar HTTP, Laravel mengikat permintaan sintetik daripada `config('app.url')`, jadi `ip()` menghasilkan `127.0.0.1` — IP palsu yang kelihatan munasabah dalam jejak audit. Semua pemanggil hari ini dan dalam pelan ialah pengawal HTTP, jadi ia belum menjadi masalah. Selesaikan apabila pemanggil konsol, seeder atau baris gilir pertama muncul: selesaikan `Request` secara malas dalam `record()` dan tulis `null` apabila tiada `REMOTE_ADDR`.
- **`record_type` daripada `Str::snake(class_basename())`** mengikat nilai audit kekal kepada nama kelas PHP. Menamakan semula `OrganizationUnit` akan memecahkan sejarah secara senyap. Idiom Laravel ialah `getMorphClass()` dengan peta morph. Putuskan sebelum M16 membina paparan audit di atas `record_type`.
- **Tandatangan `record()` berbeza daripada `LoginAuditLogger`** — `User` bukan-nullable dan `status` dikodkan tetap sebagai `'success'`, walaupun `audit_logs.user_id` nullable dan `status` selebar 20 aksara. Perubahan dimulakan sistem atau percubaan gagal tidak boleh direkod. Cukup untuk keperluan sekarang; catat sebagai titik lanjutan.
- **`action` tidak dilindungi terhadap lajur 60 aksara** (`record_type` pula 100). Semua rentetan tindakan yang dirancang di bawah 30 aksara dan ditetapkan pemaju, jadi risikonya rendah.
- **Metadata padaman menggugurkan medan bernilai `null`.** Bagi `location.deleted` dengan `parent_id: null`, medan itu tidak direkod kerana `null !== null` adalah palsu. Ini disengajakan dan didokumenkan oleh ujian; ketiadaan medan bermakna ia memang tiada nilai.
- **Petikan `after` bagi tapak `created` masih membaca model dalam ingatan**, jadi `parent_id` akan direkod sebagai rentetan `"3"` dan bukan integer `3`. Tiada perubahan palsu terhasil kerana `before` kosong, tetapi jenis data tidak konsisten antara baris `created` dan `updated`. Kosmetik; pertimbangkan sebelum M16 memaparkan metadata.

## Nombor checkpoint dalam pelan tidak lagi tepat

Angka "Expected: N ujian lulus" pada setiap checkpoint M03 ialah **anggaran, bukan syarat lulus**. Jumlah sebenar sudah menyimpang kerana beberapa tugasan menambah ujian regresi yang tidak dirancang: pelan menganggar 74 selepas Task 7, sebenarnya 82; menganggar 79 selepas Task 8, sebenarnya **91**.

Satu nota amaran sudah ditambah pada bahagian atas pelan M03. Syarat lulus sebenar bagi setiap checkpoint: **tiada ujian gagal, dan jumlah bertambah dengan ujian baharu tugasan itu.** Jangan anggap ketidakpadanan angka sebagai kegagalan.

## Pembetulan yang dibuat pada dokumen pelan itu sendiri

1. Paparan pokok lokasi kini membaca `$node->descendants`, bukan `$node->children`. Muatan awal `->with('descendants')` mengisi hubungan bernama `descendants` sahaja, jadi membaca `children` akan menyebabkan satu pertanyaan bagi setiap nod.
2. `descendants()` dibuang daripada model `OrganizationUnit`, kerana hierarki dua aras tidak boleh mempunyai cucu.
3. Skop `ofLevel` dibuang daripada model `Location` atas sebab sama.
4. Blok kod dan blok ujian Task 7 dalam pelan disegerakkan dengan kod yang benar-benar dihantar, termasuk pembetulan `Str::limit` dan `changedFields` gabungan-kunci. Jangkaan Task 7 dikemas kini kepada 6 ujian dan 82 lulus.
5. Petikan `after` pada `:2204` dan `:3293` ditukar kepada `refresh()->only([...])` seperti diterangkan di atas.

## Matlamat semasa: modul tempahan bilik

Pengguna meminta modul tempahan bilik dilengkapkan, mengikut `docs-claude/`. Dua keputusan skop telah dipersetujui:

1. **Susunan kebergantungan penuh.** M05 bergantung kepada M04, M02, M01 dan M06, jadi urutannya: M03 (selesai) → M01 → M04 → M06 → M05.
2. **Keperluan fasa 1 (W) sahaja.** Item W2, W3 dan P dikecualikan: tempahan berulang, foto bilik, tempoh penyangga, senarai peserta, kelulusan pukal, kelulusan dua aras, peningkatan automatik, daftar masuk dan pelepasan automatik, serta sumber boleh tempah selain bilik.

M04, M05 dan M06 **belum mempunyai pelan**. Ia perlu melalui kitaran spec dan pelan sebelum pelaksanaan.

### Kesan keputusan fasa 1 ke atas pelan M01

Dua daripada 14 tugasan M01 berada di luar fasa 1 dan **sengaja dilangkau**:

| Tugasan | Keperluan | Keutamaan | Keputusan |
|---|---|---|---|
| Task 9 — tab daftar masuk | FR-ADM-04 | **W3** | Antara muka dilangkau. Ia menyokong M07 pelepasan automatik, bukan tempahan fasa 1. |
| Task 10 — aras keutamaan tiket | FR-ADM-05 | **W2** | Dilangkau sepenuhnya. Ia menyokong M08 tiket sokongan, tiada kaitan dengan tempahan. |

Task 13 (seeder konfigurasi) **bergantung kepada kedua-duanya**, jadi ia perlu dipangkas:

- Buang `seedTicketPriorities()` dan ujian `test_it_seeds_ticket_priorities_whose_resolution_exceeds_response`, kerana model `TicketPriority` tidak akan wujud.
- **Kekalkan** dua baris tetapan `checkin.threshold_minutes` dan `checkin.grace_minutes`. Ia hanya baris tetapan skalar, tidak memerlukan antara muka Task 9, dan mengekalkannya menjadikan M07 lebih mudah kelak.

Tugasan M01 yang dilaksanakan: 1–8, 11, 12, 13 (dipangkas), 14.

## Kemajuan M01 — SELESAI

Semua tugasan M01 dalam skop fasa 1 **selesai**. Ujian: **217 lulus**. Pangkalan data pembangunan sudah dimigrasi dan dibenihkan dengan nilai lalai.

| # | Tugasan | Status |
|---|---|---|
| 8 | Peraturan tempahan per peranan, FR-ADM-03 | Selesai |
| 9 | Tab daftar masuk, FR-ADM-04 | **Dilangkau** — W3, fasa 3 |
| 10 | Aras keutamaan tiket, FR-ADM-05 | **Dilangkau** — W2, fasa 2 |
| 11 | Senarai nilai rujukan, FR-ADM-07 | Selesai |
| 12 | Templat notifikasi, FR-ADM-06 | Selesai |
| 13 | Seeder konfigurasi lalai | Selesai, dipangkas, satu pepijat pelan dibetulkan |
| 14 | Kemas kini dokumentasi | Selesai |

### Pepijat pelan kedua daripada punca yang sama

Seeder Task 13 menggunakan `Holiday::firstOrCreate(['date' => ..., 'type' => ...])`. Ini punca yang **sama persis** dengan pepijat `HolidayRequest` dalam Task 7: cast `date` menulis datetime penuh, jadi carian kesamaan tidak menemui baris sedia ada pada SQLite. Larian kedua seeder akan cuba menyisip semula dan melanggar indeks unik `(date, type)`.

Pembetulannya menggantikan `firstOrCreate` dengan semakan `whereDate` diikuti `create`. Ujian idempoten diperluas supaya ia benar-benar mengira cuti, bukan hanya waktu operasi dan templat seperti dalam teks pelan.

Corak ini kini direkodkan dalam `memory-bank/activeContext.md`: **jangan sesekali gunakan padanan kesamaan atau `firstOrCreate` pada lajur tarikh.**

### Nilai lalai yang dibenihkan ke pangkalan data pembangunan

| Jadual | Baris |
|---|---|
| `system_settings` | 5 |
| `operating_hours` | 7 |
| `holidays` | 5 |
| `reference_values` | 21 |
| `notification_templates` | 3 |

`role_booking_rules` sengaja kosong: nilai lalainya wujud dalam pengawal, dan barisnya dicipta apabila pentadbir menyimpan tab itu buat kali pertama.

### Bahagian 1 (Task 1–7)

| # | Tugasan | Status |
|---|---|---|
| 1 | Jadual dan model tetapan skalar | Selesai |
| 2 | Perkhidmatan tetapan bertaip dan bercache | Selesai |
| 3 | Pembantu global `setting()` | Selesai |
| 4 | Rangka kawasan tetapan dan kawalan capaian | Selesai |
| 5 | Waktu operasi, FR-ADM-01 | Selesai |
| 6 | Kalendar cuti umum, FR-ADM-02 | Selesai |
| 7 | Tab cuti umum | Selesai, satu pepijat pelan dibetulkan |

### Pepijat pelan yang ditemui dalam Task 7

`HolidayRequest` asal menggunakan `Rule::unique('holidays', 'date')->where('type', ...)`. Ia **tidak berfungsi**, dan cara kegagalannya berbahaya.

Cast `date` pada model menulis datetime penuh, jadi nilai tersimpan ialah `2026-05-01 00:00:00`. MySQL memangkasnya ke dalam lajur DATE, jadi padanan kesamaan berjaya di pengeluaran. SQLite mengekalkan bahagian masa, jadi padanan gagal senyap, permintaan lolos ke indeks unik pangkalan data, dan ralat pengesahan bertukar menjadi 500.

Disahkan sendiri dengan menyiasat nilai tersimpan: `RAW_STORED=[2026-05-01 00:00:00]`, padanan kesamaan 0 baris, `whereDate` 1 baris.

Pembetulannya menggantikan `Rule::unique` dengan semakan `whereDate` dalam `after()`, yang berkelakuan sama pada kedua-dua pemacu. Satu ujian tambahan mengesahkan tarikh yang sama masih dibenarkan bagi **jenis** yang berbeza, iaitu sempadan yang peraturan asal sepatutnya jaga.

### Tambahan di luar teks pelan

Beberapa ujian ditambah kerana teks pelan meninggalkan tingkah laku penting tanpa perlindungan:

- `SettingsRepository` — menulis kunci sedia ada tidak boleh mengubah jenis atau kumpulannya. Pelan menghantar `$valueType` dan `$group` pada setiap tulisan, jadi tanpa ujian ini satu panggilan yang cuai boleh mengklasifikasikan semula baris tetapan.
- Waktu operasi — hari beroperasi mesti membawa kedua-dua waktu buka dan tutup.
- Cuti umum — jejak audit padaman mengekalkan petikan rekod yang dipadam.

## Tugasan berikutnya

**M04 Katalog Bilik.** Ia belum mempunyai pelan, jadi langkah seterusnya ialah kitaran spec kemudian pelan, bukan kod.

Selepas M04: M06 Kelulusan, kemudian M05 Enjin Tempahan.

Semua kebergantungan M01 bagi tempahan sudah sedia dan dibenihkan:

| Keperluan tempahan | Disokong oleh |
|---|---|
| FR-TMP-06 tolak tempahan di luar waktu operasi | `operating_hours`, baris tanpa pemilik |
| Tarikh tidak boleh ditempah | `HolidayCalendar::isBookable()` |
| FR-TMP-08 had tempoh awalan per peranan | `role_booking_rules` |
| FR-BLK-03 susun atur bilik | `reference_values` jenis `susun_atur_bilik` |
| FR-BLK-04 kemudahan bilik | `reference_values` jenis `kemudahan_bilik` |
| Waktu operasi khusus bilik | Lajur pemilik polimorfik pada `operating_hours` |

## Kerja susulan yang dicatat tetapi sengaja ditangguh

Daripada semakan Task 9 dan 10. Tiada satu pun menghalang penutupan; semuanya masalah penskalaan atau kemasan, bukan ketepatan:

- **Carian tidak dipaginasi.** `index()` menggunakan `->get()` tanpa had, dan paparan memanggil `fullPath()` bagi setiap padanan sedangkan hanya satu aras induk dimuat awal. Kosnya berkadar dengan bilangan cabang berbeza, bukan bilangan baris, kerana Eloquent berkongsi satu contoh induk. Tetap: istilah carian yang luas memulangkan setiap baris. Tambah `paginate(50)` dan `with('parent.parent.parent')`.
- **Cast `parent_id`.** Menambah `'parent_id' => 'integer'` kepada `Location::casts()` akan menjadikan `refresh()` dalam `store()` dan `update()` tidak perlu lagi, membuang dua SELECT tambahan, dan menjadikan paksaan `(string)` dalam `_form.blade.php` tidak perlu. Eloquent melangkau cast pada nilai null, jadi baris akar kekal null. Kekalkan `refresh()` sehingga cast itu ditambah.
- **`orderBy('level')` menyusun berbeza antara MySQL dan SQLite.** MySQL menyusun `ENUM` mengikut indeks pengisytiharan, iaitu urutan hierarki yang dikehendaki. SQLite merender lajur itu sebagai `varchar` dan menyusun mengikut abjad: `bangunan, kampus, ruang, tingkat`. Pengeluaran betul, tetapi persekitaran ujian tidak akan sesekali dapat mengesahkannya, jadi **tiada ujian boleh melindungi susunan itu**. Susun secara eksplisit dengan ungkapan `CASE`, atau tambah aksesor `level_order` dan susun dalam PHP.
- **Tiada sandaran keunikan aras akar pada pangkalan data.** Indeks `(parent_id, code)` tidak boleh menguatkuasakan keunikan akar kerana NULL dianggap berbeza, jadi dua POST serentak boleh lulus semakan aplikasi dan kedua-duanya dimasukkan. Kebarangkalian sangat rendah untuk skrin pentadbir. Lajur terjana `COALESCE(parent_id, 0)` dalam indeks unik akan menutupnya.
- **`whereRaw('LOWER(code) = ?')` berlebihan pada MySQL.** `config/database.php` menetapkan `utf8mb4_unicode_ci`, yang sudah tidak peka huruf besar/kecil, jadi `LOWER()` tidak memberi apa-apa di sana dan menghalang penggunaan indeks bagi bahagian kod. Awalan `parent_id` masih menyempitkannya, jadi ini bukan masalah praktikal pada skala direktori. Mengekalkannya memberi mudah alih jika kolasi berubah.
- **Penulisan audit `toggle()` berada di luar transaksinya.** Boleh dipertahankan: `store()`, `update()` dan `destroy()` semuanya merekod di luar sebarang transaksi, jadi `toggle()` konsisten dengan modul. Risikonya sempit — penyisipan audit yang gagal meninggalkan perubahan keadaan tanpa jejak. Jika mahu atomik, alihkan `record()` ke dalam penutupan untuk **kesemua empat** tindakan, bukan satu sahaja.
- **Ketidakseragaman gaya.** `_node.blade.php` menggunakan `auth()->user()->can(...)` manakala `index.blade.php` menggunakan `@can`. Hasil sama; pilih satu.

## Keputusan yang menunggu pengguna

1. **Skop tulis unit organisasi** — ikut pelan (hanya `pentadbir-sistem`, seperti dilaksana sekarang) atau ikut matriks secara literal (tambah `unit-organisasi.cipta` dan `unit-organisasi.kemaskini` kepada `pentadbir-fasiliti` dan `pegawai-aset`). Jika mahu kekal seperti sekarang, pertimbangkan memecahkan baris "M03 Lokasi" dalam `docs-claude/01-modules.md:193` kepada dua baris, atau menambah nota kaki, supaya pemeriksaan kesetiaan matriks pada masa depan tidak "membetulkan" kod ini semula. Dokumen `docs-claude/` sengaja tidak disunting kerana ia spesifikasi pengguna.
2. **Pepijat elipsis `Str::limit`** dalam dua fail sedia ada, seperti dicatat di atas.

## Peraturan persekitaran untuk subejen

Sertakan semula peraturan ini dalam setiap arahan subejen:

- Projek ini bukan repositori git. Tiada arahan git, tiada komit. Titik semak menggantikan langkah komit.
- **`php artisan migrate` kini sebahagian daripada checkpoint** bagi mana-mana tugasan yang menambah atau mengubah jadual. Lihat bahagian di bawah.
- Jangan jalankan `php artisan tinker`, `migrate:fresh`, atau `migrate:rollback`. Fail `.env` menunjuk kepada pangkalan data MySQL pembangunan sebenar yang mengandungi data benih. Satu subejen pernah menulis empat baris ujian ke dalamnya sebelum memadamnya semula.
- Jangan jalankan `db:seed` tanpa kebenaran pengguna. Seeder struktur ialah pengecualian yang sudah disahkan idempoten, tetapi ia tetap menulis ke data sebenar.
- Ujian berjalan pada SQLite dalam ingatan melalui `php artisan test`.
- Format dengan `vendor/bin/pint --format agent`, bukan `--dirty`, yang memerlukan git.
- Hook PostToolUse menjalankan Pint selepas setiap suntingan dan membuang import yang belum digunakan. Tulis fail sekali gus, kemudian baca semula untuk mengesahkan import masih ada.
- **Perangkap ini benar-benar berlaku semasa Task 10.** Import `LocationController` ditambah ke `routes/web.php` dalam satu suntingan, sebelum laluan yang menggunakannya ditambah dalam suntingan kedua. Pada saat itu import kelihatan tidak digunakan, jadi hook membuangnya. Laluan kemudian menyelesaikan `LocationController::class` kepada namespace global dan setiap ujian gagal dengan `ReflectionException: Class "LocationController" does not exist`. Tambah import dan penggunaannya dalam **satu** suntingan, atau semak semula kepala fail selepasnya.
- Ujian ciri memerlukan manifest Vite. Jika `php artisan test` gagal dengan ralat "Vite manifest not found", jalankan `npm run build` sekali sahaja.

## Migrasi mesti dijalankan pada checkpoint, bukan ditunggu sehingga gagal

Ujian berjalan pada SQLite dalam ingatan, yang membina semula skema daripada kosong setiap kali. Pangkalan data pembangunan tidak. Jadi migrasi baharu boleh **lulus setiap ujian sambil tidak wujud langsung** di dalam pelayar.

Inilah yang berlaku. Tiga jadual M01 dicipta, 187 ujian hijau, kemudian pengguna membuka satu halaman tetapan dan mendapat:

```
SQLSTATE[42S02]: Base table or view not found: 1146
Table 'e-fasiliti.system_settings' doesn't exist
```

Sejak 9 September 2026, checkpoint bagi mana-mana tugasan yang menyentuh skema ialah:

```bash
vendor/bin/pint --format agent
php artisan test
php artisan migrate        # bukan migrate:fresh
php artisan cache:clear    # jika tugasan itu menyentuh tetapan atau apa-apa yang dicache
```

`migrate` bersifat menambah dan setiap migrasi mesti mempunyai `down()` yang berfungsi. `migrate:fresh` dan `migrate:rollback` kekal dilarang: kedua-duanya memusnahkan data sebenar.

Semak dengan `php artisan migrate:status` dahulu jika ragu-ragu. Ia hanya membaca.

---

## M04 Katalog Bilik — sesi 1 selesai 9 September 2026, sambung pada sesi seterusnya

**Branch:** `feature/m04-katalog-bilik`. **Ujian: 232 lulus, 0 gagal** (garis dasar 217; +1 kegagalan sedia ada dibaiki; +14 ujian baharu Tasks 1–4).

**Sumber kebenaran keadaan tugasan:** blok "⏸️ Status Pelaksanaan" di bahagian atas `2026-09-09-m04-katalog-bilik.md` — ia menyenaraikan keadaan setiap Task dan langkah sambungan dalam susunan.

### Yang telah siap (kod Tasks 1–7)
- Migrasi `rooms`, `room_layouts`, `room_facilities`, `bookings` — **dijalankan pada MySQL** (`php artisan migrate`, aditif; tiada migrate:fresh).
- `BookingStatus` (8 status DRD + `slotHolding()`), model `Room` / `RoomLayout` / `RoomFacility` / `Booking` (skop `upcoming`), `Location::rooms()`, tiga factory baharu.
- Kebenaran `bilik.lihat/cipta/kemaskini/padam`: `RolesAndPermissionsSeeder` + baris `EXPECTED_GRANTS` + ujian `test_room_catalog_permissions_follow_the_matrix`. **Seeder belum dijalankan pada DB sebenar** — perlu kebenaran pengguna (Task 11).
- `Location::referenceSummary()` menambah alasan `bilik` (FR-ORG-03) + ujian unit.
- `RoomStoreRequest` / `RoomUpdateRequest`: kod unik tak peka huruf, lokasi mesti aras `ruang` dan aktif, min<maks, tepat satu susun atur lalai, kod daripada `reference_values` aktif, `days.*` tujuh hari pada kemas kini.
- `Admin\RoomController` — CRUD + `toggle` dengan skrin pengesahan FR-BLK-08 + audit `room.created|updated|activated|deactivated|deleted`.

### Pembetulan persekitaran yang berlaku dalam sesi ini
- **`.env` telah berubah kepada `APP_LOCALE=en`** (memori mencatat `ms`), menyebabkan `AuthenticationTest::test_account_is_locked_after_five_failed_attempts` gagal secara konsisten. Dibaiki dengan `<env name="APP_LOCALE" value="ms"/>` dalam `phpunit.xml`. **Keputusan tertunggak pengguna:** sama ada `.env` mahu dikembalikan kepada `ms`.
- **Catatan "bukan repositori git" tidak lagi tepat** — repo git wujud (2 komit di `main` sebelum M04) dan binary berada di `C:\laragonNafas\bin\git\cmd\git.exe`. PHP 8.4 di `C:\laragonNafas\bin\php\php-8.4.25-Win32-vs17-x86\php.exe`. Kedua-duanya tiada dalam PATH.
- Pint: `& $php vendor\laravel\pint\builds\pint --format agent`. Rakaman output PowerShell kerap gagal: alihkan `*> storage\logs\diag.log`, baca fail, buang aksara `\0` (UTF-16).

### Seterusnya (susunan pelaksanaan)
1. Task 7 view: `resources/views/admin/rooms/deactivate.blade.php` (senarai tempahan terjejas + butang sah).
2. Task 8 views: `admin/rooms/index|create|edit|_form` — corak `admin/locations/*`, `<x-ui.location-picker>`, repeater susun atur Alpine, checkbox kemudahan (benih: TEATER/KELAS/U, PROJEKTOR/PAPAN-PUTIH), jadual waktu operasi 7 hari (corak `admin/settings/operating-hours`).
3. Task 9: import `RoomController` + laluan `admin.rooms.*` (`can:bilik.*`) dalam **satu suntingan** `routes/web.php`; pautan sidebar kumpulan "Fasiliti".
4. Task 10: `RoomManagementTest`, `RoomValidationTest`, `RoomDeactivationTest` — seed `SystemConfigurationSeeder` untuk kod rujukan.
5. Task 11: checkpoint `pint` → `php artisan test` → komit `M04:` → **tanya pengguna** sebelum `db:seed --class=RolesAndPermissionsSeeder`.
6. Task 12: kemas kini memory-bank selepas M04 selesai.

