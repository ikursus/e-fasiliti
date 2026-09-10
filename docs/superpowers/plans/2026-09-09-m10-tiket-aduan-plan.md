# Pelan Sambungan — M10 Tiket Aduan Kerosakan (dibuat 9 September 2026)

> **STATUS: SIAP.** M10 dilaksanakan sepenuhnya pada 9 September 2026:
> 275 ujian (817+ assertions) lulus kecuali 1 kegagalan sedia ada yang tidak
> berkaitan (`AuthenticationTest::test_account_is_locked_after_five_failed_attempts`
> gagal kerana `APP_LOCALE=en` dalam `.env` sedangkan ujian menjangka mesej BM
> "Terlalu banyak" — sebelum M10 juga). Migrasi telah dijalankan ke DB
> pembangunan; `RolesAndPermissionsSeeder` dan `SystemConfigurationSeeder`
> telah dijalankan dengan kebenaran pengguna. Dokumen di bawah dikekalkan
> sebagai rekod reka bentuk.

## Keputusan skop (telah dipersetujui pengguna)

- **M10 sahaja** — tiket berfungsi **tanpa aset**. Lajur `tickets.aset_id`
  sengaja nullable dan dikhaskan untuk M09 masa depan (FR-TKT-03, FR-TKT-14,
  FR-TKT-16 ditangguhkan bersama M09/M13). Borang buka tiket ialah "masalah
  umum lokasi" (FR-TKT-04).
- Status `menunggu_alat_ganti` **tidak** dibina (tiada M13). Status
  `menunggu_vendor` **dibina** (FR-TKT-15) tanpa modul vendor penuh — nama
  vendor & no. rujukan disimpan sebagai teks pada tiket, jam SLA dijeda.
- Deviasi matriks yang direkodkan: `pelulus`, `pentadbir-fasiliti`,
  `pegawai-aset` diberi `tiket.lihat-semua` (matriks menulis "R unit"/"R").
  Mesti diletakkan sebagai nota pada `RolesAndPermissionsSeeder` semasa
  mengedit, sama seperti nota M03 sedia ada, supaya ujian kesetiaan matriks
  tidak "membetulkannya" kemudian.

## SUDAH SIAP (fail wujud, perlu disemak / diuji)

Migrasi:
- `database/migrations/2026_09_09_041000_create_notifications_table.php`
- `database/migrations/2026_09_09_041100_create_tickets_table.php`
- `database/migrations/2026_09_09_041200_create_tiket_notes_table.php`

Enum:
- `app/Enums/TicketStatus.php` — baharu, diagih, dalam_tindakan,
  menunggu_vendor, menunggu_pengesahan, ditutup, dibatalkan; label BM + kelas
  badge. Tiada `menunggu_alat_ganti`.
- `app/Enums/TicketPriority.php` — P1–P4, sasaran minit lalai BRS §6,
  `pilihanGangguan()` untuk borang "tahap gangguan".

Model + factory:
- `app/Models/Ticket.php` (cast enum; scope `open`, `urutanTugasan` guna CASE
  bukan FIELD() kerana ujian berjalan SQLite)
- `app/Models/TiketNote.php`
- `database/factories/TicketFactory.php` — state: diagih, dalamTindakan,
  menungguVendor, menungguPengesahan, ditutup, dibatalkan, keutamaan,
  melanggarSla
- `database/factories/TiketNoteFactory.php`

Perkhidmatan (`app/Services/Ticket/`):
- `WorkingCalendar.php` — waktu bekerja + cuti umum (`addWorkingMinutes`,
  `workingMinutesBetween`)
- `SlaCalculator.php` — tulen; `kiraSasaran`, `minitBerlalu`,
  `peratusanTerpakai`, `melanggar`
- `TicketNumberGenerator.php` — `TKT-YYYYMM-nnnnn`; lumba diselesaikan oleh
  indeks unik + cubaan semula dalam `buka()`
- `TicketService.php` — buka, agih, agihPukal, ubahKeutamaan (SLA dikira
  semula + sebab wajib), mulaKerja, simpanKerja, catatNota,
  rujukVendor/sambungSelepasVendor (jeda SLA: `jeda_mula_pada`,
  `minit_jeda_sla`, sasaran digerakkan ke hadapan), tandaSelesai,
  sahkanPenutupan, bukaSemula, tutupAutomatik (3 hari bekerja; audit tanpa
  pengguna), batal, semakAmaranSla (80%/100%, idempoten)
- `TicketNotifier.php` — DB synchronous + e-mel berqueue dari
  `NotificationTemplate`; kegagalan e-mel ditelan (NFR-A05)
- `app/Notifications/TicketEventNotification.php` — saluran database sahaja,
  sengaja tidak berqueue
- `app/Mail/TicketEventMail.php` + `resources/views/mail/ticket-event.blade.php`

Form Requests (`app/Http/Requests/Ticket/`): StoreTicketRequest (≤5 medan
wajib; kategori disemak terhadap `reference_values` jenis_kerosakan; lampiran
mimes+max), StoreNoteRequest, StoreWorkRequest, UpdatePriorityRequest,
AssignTicketRequest (assignee mesti berperanan juruteknik; `ids[]` untuk
pukal), ReferVendorRequest, CancelTicketRequest, ReopenTicketRequest.

Pengawal + policy + arahan:
- `app/Policies/TicketPolicy.php` (view / reporterActions / work)
- `app/Http/Controllers/Ticket/MyTicketController.php` — index, create,
  store, show, sahkan, bukaSemula
- `app/Http/Controllers/Ticket/TechnicianTicketController.php` — tugasan,
  mula, simpanKerja, catatan, rujukVendor, sambungVendor, selesai
- `app/Http/Controllers/Ticket/SupervisorTicketController.php` — senarai +
  penapis, agih, agihPukal, keutamaan, batal; beban kerja juruteknik
- `app/Http/Controllers/Ticket/TicketAttachmentController.php`
- `app/Console/Commands/TutupTiketAutomatikCommand.php` (`tiket:tutup-automatik`)
- `app/Console/Commands/AmaranSlaTiketCommand.php` (`tiket:amaran-sla`)
- `routes/console.php` — penjadualan didaftar (setiap 10 minit; harian 07:30)

## BELUM DIBUAT — susunan kerja sesi akan datang

1. **RBAC** — `database/seeders/RolesAndPermissionsSeeder.php`: tambah ke
   `PERMISSIONS`: `tiket.buka`, `tiket.lihat-sendiri`, `tiket.lihat-semua`,
   `tiket.kemas-kini`, `tiket.agih`, `tiket.keutamaan`, `tiket.tutup`.
   Geran:
   - kakitangan/setiausaha: buka, lihat-sendiri
   - pelulus/pentadbir-fasiliti/pegawai-aset: lihat-sendiri, lihat-semua
     (deviasi — rekod sebagai nota dalam seeder)
   - juruteknik: lihat-sendiri, kemas-kini
   - penyelia-ict: semua tujuh
   - pentadbir-sistem: semua (self::PERMISSIONS)
   Kemudian **kemas kini `EXPECTED_GRANTS`** dalam
   `tests/Feature/Admin/PermissionSeedingTest.php` (aturan
   `.ai/rules/seeders.md`).
2. **Tetapan SLA + templat** — `database/seeders/SystemConfigurationSeeder.php`:
   tambah `sla.P1..P4.tindak_balas_minit` & `pemulihan_minit` (group `sla`,
   nilai sama dengan lalai enum), kemas kini docblock (nota "ticket priorities
   deliberately not seeded" sudah lapuk — FR-ADM-05 kini dilaksanakan).
   Tambah templat `tiket.diagih`, `tiket.selesai`, `tiket.sla_amaran`,
   `tiket.sla_langgar` — pastikan semua pemegang tempat diisytihar (ujian
   seeder menyemak). JANGAN jalankan `db:seed` tanpa kebenaran pengguna;
   `TicketService` ada fallback lalai jika baris tiada, jadi dev DB kekal
   berfungsi tanpa seed.
3. **Routes** — `routes/web.php`: kumpulan `tiket.*` dalam middleware `auth`.
   ATURAN: import pengawal + sekurang-kurangnya satu penggunaan dalam
   SUNTINGAN YANG SAMA (hook PostToolUse membuang import yang tiada guna).
   Laluan dicadang:
   - GET `tiket-saya`, `tiket-saya/cipta`; POST store → MyTicketController
     (store: `can:tiket.buka`)
   - GET `tiket/{ticket}` (policy view); POST `tiket/{ticket}/sahkan` dan
     `buka-semula` (policy reporterActions)
   - GET `tugasan`; POST `tiket/{ticket}/mula`, PUT `tiket/{ticket}/kerja`,
     POST `catatan`, `rujuk-vendor`, `sambung-vendor`, `selesai` →
     `can:tiket.kemas-kini` (policy work sebagai lapisan kedua)
   - GET `tiket` (semua) `can:tiket.lihat-semua`; POST `tiket/{ticket}/agih`
     dan `tiket/agih-pukal` `can:tiket.agih`; POST `tiket/{ticket}/keutamaan`
     `can:tiket.keutamaan`; POST `tiket/{ticket}/batal` `can:tiket.agih`
   - GET `tiket/{ticket}/lampiran/{indeks}`
   Nama: `tiket.index/create/store/show/sahkan/buka-semula`, `tiket.tugasan`,
   `tiket.mula/kerja/catatan/rujuk-vendor/sambung-vendor/selesai`,
   `tiket.senarai/agih/agih-pukal/keutamaan/batal`, `tiket.lampiran`.
   **`TicketNotifier` memanggil `route('tiket.show', $tiket)` — laluan ini
   wajib wujud sebelum apa-apa aliran diuji.**
4. **Sidebar** — `resources/views/layouts/app.blade.php`: bahagian "Tiket
   ICT": Aduan Saya (`tiket.lihat-sendiri`), Tugasan (`tiket.kemas-kini`),
   Semua Tiket (`tiket.lihat-semua`).
5. **Views** (`resources/views/tiket/`): `index.blade.php` (aduan saya +
   kiraan status), `create.blade.php` (lokasi select dengan `fullPath`,
   kategori, tahap gangguan radio, keterangan, telefon, lampiran),
   `show.blade.php` (butiran + badge status/keutamaan + % SLA + timeline
   nota + seksyen tindakan ikut peranan: pelapor sahkan/buka semula;
   juruteknik borang kerja/nota/vendor/selesai; penyelia
   agih/keutamaan/batal), `tugasan.blade.php` (susun keutamaan + baki SLA),
   `senarai.blade.php` (penapis, ringkasan status, beban kerja, checkbox
   agih pukal), komponen `ticket-status-badge` & `ticket-priority-badge`
   (label teks + warna, NFR-C02), keadaan kosong ikut UI spec §7, mesej BM
   ikut UI spec §6.

6. **Ujian** — Unit: `tests/Unit/Services/WorkingCalendarTest.php`,
   `tests/Unit/Services/SlaCalculatorTest.php` (hujung minggu, cuti, jeda
   vendor, % terpakai). Feature `tests/Feature/Ticket/`:
   TicketOpeningTest (nombor rujukan, audit, kategori tidak sah ditolak,
   fail .exe ditolak), TicketAssignmentTest (agih, pukal, beban kerja,
   bukan-juruteknik ditolak), TicketWorkflowTest (mula→kerja→selesai→
   sahkan; buka semula → juruteknik sama; nota dalaman tidak dilihat
   pelapor), TicketSlaTest (keutamaan dikira semula + sebab; amaran 80/100
   idempoten; jeda vendor menggeser sasaran), TicketAutoCloseTest (3 hari
   bekerja, idempoten), TicketPermissionTest (pelapor lain → 403, URL-id
   TC-SEC-01). Guna `Notification::fake()` / `Mail::fake()`; seed
   `RolesAndPermissionsSeeder`; cipta baris `OperatingHour` (atau seed
   konfigurasi) untuk kalendar — tanpa itu kalendar kosong bermakna tiada
   masa bekerja.
7. **Semakan kod** yang diketahui perlu dilihat semasa lulus:
   - `MyTicketController`: import `AuditRecorder` & `SettingsRepository`
     mungkin tidak digunakan; `ReferenceValueType` dirujuk FQCN inline.
   - `TechnicianTicketController`: import `TicketStatus` mungkin tidak
     digunakan.
   - Jalankan Pint kemudian baca semula kepala setiap fail yang disunting
     (hook boleh membuang import).
8. **Checkpoint wajib** (aturan `.ai/rules/migrations.md`; `php` tiada dalam
   PATH shell ini — guna
   `D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe`):
   ```
   vendor/bin/pint --format agent      # BUKAN --dirty (bukan repo git)
   php artisan test                    # ralat Vite manifest? npm run build
   php artisan migrate                 # bukan migrate:fresh/rollback
   php artisan cache:clear             # sebab sentuh tetapan
   php artisan migrate:status          # sahkan 3 jadual baharu ada
   ```

## Nota reka bentuk yang perlu dikekalkan

- Jeda SLA: `jeda_mula_pada` ditetapkan semasa masuk `menunggu_vendor`;
  semasa keluar, `minit_jeda_sla` bertambah dan kedua-dua sasaran digerakkan
  ke hadapan dengan minit bekerja yang sama — peratusan kekal konsisten
  tanpa rekod sejarah jeda.
- `tutupAutomatik` dan `semakAmaranSla` selamat larian berulang (semak
  status / cap masa amaran sebelum bertindak, NFR-A07).
- E-mel kekal best-effort; notifikasi dalam aplikasi ialah saluran sandaran
  dan sengaja synchronous.
- Jadual log audit tumbuh cepat — partisi bulanan (SDD §6.3) ialah kerja
  kemudian, bukan sekarang.
- Susunan tugasan guna `CASE` SQL (bukan `FIELD()`) supaya MySQL dan SQLite
  kedua-duanya berfungsi.
