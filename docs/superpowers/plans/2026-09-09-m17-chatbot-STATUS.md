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
- Suite penuh: 48 ujian pertama lulus sebelum proses dipotong; cebisan
  `tests/Feature/Admin` (chunk1) sedang/belum disahkan — **sambung di §3(a)**.

## 3. Langkah sambungan (mengikut turutan)

(a) **Sahkan baki suite penuh** — jalankan dalam cebisan kecil (proses panjang
akan dimatikan oleh sistem):
```
$env:Path = 'C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64;C:\laragon\bin\git\cmd;' + $env:Path
php artisan test --compact --no-ansi tests/Feature/Admin/Settings
php artisan test --compact --no-ansi tests/Feature/Admin
php artisan test --compact --no-ansi tests/Feature   # baki fail akar
```
Semua MESTI hijau sebelum merge. Jika ada gagal, periksa dahulu samada berkaitan
seeder kebenaran (perubahan kita) atau isu lama.

(b) **Seed DB pembangunan** (PERATURAN: minta kebenaran pengguna dulu sebelum
`db:seed`). Diperlukan supaya kebenaran `chatbot.*` dan tetapan lalai wujud
dalam MySQL `e-fasiliti`:
```
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan db:seed --class=SystemConfigurationSeeder --force
php artisan cache:clear
```
Kedua-duanya idempoten (firstOrCreate / syncPermissions).

(c) **`npm run build`** — kelas Tailwind baharu dalam Blade chat perlu dibina
untuk muncul dalam CSS terbina.

(d) **`php artisan config:clear`** — `config/services.php` telah berubah.

(e) **Kunci API**: minta pengguna letak `GEMINI_API_KEY=...` (daripada
https://aistudio.google.com/apikey) dalam `.env`, kemudian uji secara langsung
di `/chatbot`. Tiada kunci diperlukan untuk ujian automatik (`Http::fake()`).

(f) **Kemas kini `docs-claude/`** (belum dibuat) — lihat §4.

(g) **Ujian pelayar manual**: log masuk sebagai pelbagai peranan — pautan
"Pembantu AI" muncul untuk semua; tab "Chatbot" hanya untuk pentadbir-sistem;
hantar mesej; perbualan baharu/padam; throttle 20 mesej/minit.

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

## 4. Pelan kemaskini `docs-claude/` (tambahan sahaja — JANGAN ubah kandungan modul lain)

| Fail | Kemaskini |
|---|---|
| `01-modules.md` | Nod M17 dalam peta mermaid (kumpulan Perkhidmatan Rentas); seksyen "M17 — Pembantu AI (Chatbot)" dalam §2; baris M17 dalam matriks §3 (semua peranan: guna; R8: CRUD tetapan); "16 modul" → "17 modul" |
| `04-SRS-software-requirements.md` | Seksyen `### M17` dengan `FR-CHB-01..06` (format jadual ID/Keperluan/Keutamaan/URS): 01 akses semua peranan, 02 sejarah DB + sejarah terhad, 03 perbualan baharu/padam milik sendiri, 04 tajuk automatik, 05 tetapan tanpa kod + status kunci, 06 kadar had + ralat mesra |
| `05-DRD-data-requirements.md` | Entiti `SESSI_CHATBOT` & `MESEJ_CHATBOT` dalam kamus data §4 (medan Melayu) + 2 relasi ERD |
| `11-RTM-traceability-matrix.md` | Subseksyen "Modul Tambahan (M17)": FR-CHB → TC-CHB |
| `12-test-plan-uat.md` | Seksyen TC-CHB memetakan 4 fail ujian PHPUnit |
| `README.md` | Baris indeks 01: "16 modul" → "17 modul" |
