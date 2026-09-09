# STATUS M17 — Pembantu AI (Chatbot, Google AI Studio)

> Dokumen serahan sesi. Dicipta 9 September 2026. Cawangan: **`chatbot`** (jangan
> komit kepada `main`; `main` tidak disentuh sejak cawangan dicipta).

## 1. Keadaan semasa

Kod modul M17 **siap dan lulus ujian**. Lima komit dalam cawangan `chatbot`:

| Komit | Kandungan |
|---|---|
| `ddbc68c` | Migrasi `chat_sessions` + `chat_messages`, model `ChatSession`/`ChatMessage`, enum `ChatRole`, kilang (`ChatSessionFactory`, `ChatMessageFactory`) |
| `69685cb` | `config/services.php` (blok `gemini`), `.env.example`, `app/Services/Chatbot/*` (`GeminiChatService`, `ChatbotReply`, `ChatbotException` + 3 subkelas) |
| `9297965` | Kebenaran `chatbot.guna` (semua 8 peranan) & `chatbot.tetapan` (R8) dalam `RolesAndPermissionsSeeder`, grid `EXPECTED_GRANTS`, tetapan lalai kumpulan `chatbot` dalam `SystemConfigurationSeeder` |
| `7a8c901` | `ChatbotController`, `Admin/Settings/ChatbotSettingsController`, `ChatMessageRequest`, laluan `chatbot.*` + `admin.settings.chatbot`, paparan `chatbot/index.blade.php` (UI Alpine), `admin/settings/chatbot.blade.php`, tab + sidebar |
| `ae57181` | 24 ujian ciri `tests/Feature/Chatbot/*` + pembaikan pepijat arah sejarah |

Fail sedia ada yang disentuh (tambahan sahaja): `routes/web.php`,
`config/services.php`, `.env`/`.env.example`, `resources/views/layouts/app.blade.php`,
`resources/views/admin/settings/_tabs.blade.php`, kedua-dua seeder,
`tests/Feature/Admin/PermissionSeedingTest.php`. **Tiada kod M01–M16 diubah.**

## 2. Yang telah disahkan lulus

- `tests/Feature/Chatbot` — **24/24 lulus** (67 assertion).
- `tests/Feature/Admin/PermissionSeedingTest` — **9/9 lulus**.
- `tests/Feature/Admin/Settings` — **lulus penuh (exit 0)**.
- `tests/Feature/Admin` (semua, termasuk Settings) — **lulus penuh (exit 0)**.
- `tests/Feature` penuh — **186/187 lulus**; satu-satunya kegagalan ialah
  `AuthenticationTest > account is locked after five failed attempts`
  (lihat §7 — pra-wujud, wilayah M02, bukan berkaitan M17).
- `npm run build` — **berjaya (exit 0)**; kelas Tailwind baharu terbina.
- Seeder DB pembangunan dijalankan dengan kebenaran pengguna:
  `RolesAndPermissionsSeeder` + `SystemConfigurationSeeder` + `cache:clear` +
  `config:clear` — semua berjaya.
- Kemaskini `docs-claude/` — **selesai** (§4 di bawah).

## 3. Langkah yang tinggal (hanya tindakan pengguna)

(e) **Kunci API**: letak `GEMINI_API_KEY=...` (daripada
https://aistudio.google.com/apikey) dalam `.env`, kemudian uji secara langsung
di `/chatbot`. Tiada kunci diperlukan untuk ujian automatik (`Http::fake()`).

(f) **Ujian pelayar manual**: log masuk sebagai pelbagai peranan — pautan
"Pembantu AI" muncul untuk semua; tab "Chatbot" hanya untuk pentadbir-sistem;
hantar mesej; perbualan baharu/padam; throttle 20 mesej/minit.

(g) **Keputusan pengguna**: `main` telah maju 3 komit sejak cawangan `chatbot`
dicipta (`4db04e1`, `de04511`, `bfe337a` — perubahan direktori/lokasi dan cache
SettingsRepository). Bila sedia, merge `chatbot` → `main` (atau rebase) perlu
dilakukan secara sedar; tiada konflik dijangka kerana fail berbeza, kecuali
`app/Services/Configuration/SettingsRepository.php` (main mengubah fail itu;
cawangan chatbot TIDAK mengubahnya — selamat).

## 5. Rujukan teknikal & perangkap persekitaran

- **PATH sesi** (git & php tiada dalam PATH PowerShell):
  `$env:Path = 'C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64;C:\laragon\bin\git\cmd;' + $env:Path`
- **Pint**: `vendor\bin\pint.bat --dirty --format agent` (perlu git dalam PATH).
- **Hook PostToolUse Pint** menghapus import yang belum digunakan pada saat
  suntingan — tambah import + sekurang-kurangnya satu kegunaan dalam SUNTINGAN
  YANG SAMA, atau semak semula bahagian atas fail selepas itu (pernah berlaku:
  import `ChatbotSettingsController` terhapus lalu berganda semasa diletak semula).
- **Hubungan `ChatSession::messages()`** secara sedia `oldest()` — jangan rantaikan
  `latest()` di atasnya (menjadi ORDER BY kedua sahaja). Sentiasa pertanya
  `ChatMessage::query()` secara langsung bila perlu urutan lain.
- **Ujian**: seed kedua-dua seeder (`RolesAndPermissionsSeeder` +
  `SystemConfigurationSeeder`) dalam `setUp()`; `Http::fake(['generativelanguage.googleapis.com/*' => ...])`
  untuk mengelak panggilan sebenar.
- **Sistem reka bentuk**: API REST `generateContent` v1beta dipilih (masih
  disokong penuh mengikut dokumen Google Sept 2026) dan stateless — sejarah
  dimiliki `chat_messages`. Servis diasingkan dalam `GeminiChatService`
  supaya boleh bertukar kepada Interactions API tanpa menyentuh controller.
- Tetapan admin boleh ubah: `chatbot.enabled/model/temperature/max_output_tokens/
  max_history/system_prompt` (kumpulan `chatbot` dalam `system_settings`,
  melalui `SettingsRepository` — audit automatik). Kunci API kekal dalam `.env`.

## 6. Kemaskini `docs-claude/` yang telah dilakukan

| Fail | Kemaskini |
|---|---|
| `01-modules.md` | Nod M17 dalam mermaid + `M01 --> M17`; seksyen M17 dalam §2; legenda **G** + baris matriks M17 dalam §3; baris "Dibina (tambahan)" dalam §4; "16 modul" → "17 modul" |
| `04-SRS-software-requirements.md` | Seksyen `### M17` dengan `FR-CHB-01..06`; baris M17 dalam jadual ringkasan §4 (jumlah 152 → 158) + nota luar-baseline |
| `05-DRD-data-requirements.md` | 2 relasi ERD; entiti `SESSI_CHATBOT` & `MESEJ_CHATBOT` dalam §4.4 |
| `11-RTM-traceability-matrix.md` | Subseksyen 5.1 "Modul Tambahan M17 (Luar Baseline)": FR-CHB → TC-CHB, semua lulus |
| `12-test-plan-uat.md` | Subseksyen 4.8 dengan TC-CHB-01 hingga TC-CHB-15 |
| `README.md` | Baris indeks 01: "16 modul" → "17 modul" |

## 7. Penemuan penting (luar skop M17 — untuk tindakan pemilik M02)

- `AuthenticationTest > test_account_is_locked_after_five_failed_attempts`
  GAGAL secara konsisten (jika dijalankan bersendirian mahupun dalam suite).
  Punca: `APP_LOCALE=en` dalam `.env` menjadikan `trans('auth.throttle')`
  memulangkan teks Inggeris, manakala ujian menjangka "Terlalu banyak"
  (fail `lang/ms/auth.php`). Bukti pra-wujud: `git diff main..chatbot
  --name-only` — tiada fail auth/locale diubah dalam cawangan chatbot.
  Pembaikan yang dicadangkan (keputusan pemilik): tetapkan `APP_LOCALE=ms`
  (selari dengan BR-07 Bahasa Melayu utama) atau kemas kini jangkaan ujian.
- `main` telah maju 3 komit sejak cawangan `chatbot` bercabang. Lihat §3(g).

## 4. Pelan kemaskini `docs-claude/` (tambahan sahaja — JANGAN ubah kandungan modul lain)

| Fail | Kemaskini |
|---|---|
| `01-modules.md` | Nod M17 dalam peta mermaid (kumpulan Perkhidmatan Rentas); seksyen "M17 — Pembantu AI (Chatbot)" dalam §2; baris M17 dalam matriks §3 (semua peranan: guna; R8: CRUD tetapan); "16 modul" → "17 modul" |
| `04-SRS-software-requirements.md` | Seksyen `### M17` dengan `FR-CHB-01..06` (format jadual ID/Keperluan/Keutamaan/URS): 01 akses semua peranan, 02 sejarah DB + sejarah terhad, 03 perbualan baharu/padam milik sendiri, 04 tajuk automatik, 05 tetapan tanpa kod + status kunci, 06 kadar had + ralat mesra |
| `05-DRD-data-requirements.md` | Entiti `SESSI_CHATBOT` & `MESEJ_CHATBOT` dalam kamus data §4 (medan Melayu) + 2 relasi ERD |
| `11-RTM-traceability-matrix.md` | Subseksyen "Modul Tambahan (M17)": FR-CHB → TC-CHB |
| `12-test-plan-uat.md` | Seksyen TC-CHB memetakan 4 fail ujian PHPUnit |
| `README.md` | Baris indeks 01: "16 modul" → "17 modul" |
