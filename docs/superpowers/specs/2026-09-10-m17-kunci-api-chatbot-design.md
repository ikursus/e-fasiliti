# Reka Bentuk — M17 Kunci API Pembantu AI Boleh Ditetapkan Dari Skrin Admin

| Perkara | Butiran |
|---|---|
| Sistem | e-Fasiliti (SPFA) |
| Modul | M17 (Pembantu AI) |
| Tarikh | 10 September 2026 |
| Status | Diluluskan untuk pelan pelaksanaan |
| Rujukan | FR-CHB-05, `app/Services/Chatbot/GeminiChatService.php` |

---

## 1. Tujuan dan Skop

Kunci API Gemini kini hanya boleh ditetapkan dengan menyunting fail `.env` di pelayan. Pentadbir sistem tiada akses ke fail itu, jadi mengaktifkan atau menukar kunci memerlukan bantuan pasukan teknikal setiap kali.

Kitaran ini membenarkan kunci ditetapkan, ditukar dan dibuang terus dari `admin/settings/chatbot`, tanpa menyentuh fail `.env`.

**Dalam skop:** simpanan kunci yang disulitkan, keutamaan pangkalan data berbanding persekitaran, antara muka penyuntingan bertopeng, butang uji sambungan, dan penapisan kunci daripada jejak audit.

**Di luar skop:** putaran kunci automatik, berbilang kunci, dan kunci berasingan per peranan atau per unit organisasi. Tiada keperluan untuk itu sekarang.

---

## 2. Keadaan Semasa

`config/services.php` membaca `services.gemini.key` daripada `env('GEMINI_API_KEY')`. Dua tempat menggunakannya: `GeminiChatService::assertUsable()` yang membuang `ChatbotConfigurationException` apabila kunci kosong, dan `GeminiChatService::send()` yang menghantarnya sebagai pengepala `x-goog-api-key`. `ChatbotSettingsController::edit()` menghantar `apiKeyConfigured` ke paparan semata-mata untuk memaparkan sepanduk amaran.

Enam tetapan chatbot yang lain sudah pun berada dalam jadual `system_settings` melalui `SettingsRepository`, dengan jenis nilai `teks`, `nombor`, `boolean` dan `json`.

Tiada penyulitan digunakan di mana-mana dalam aplikasi setakat ini.

---

## 3. Keputusan Reka Bentuk

| # | Keputusan | Sebab |
|---|---|---|
| D1 | Simpan dalam `system_settings` sebagai jenis nilai baharu `rahsia` | Tiada jadual atau lajur baharu diperlukan; kunci mewarisi cache, audit dan skrin yang sedia ada |
| D2 | Sulitkan dengan `Crypt::encryptString` pada tulis, nyahsulit pada baca | Kunci tidak boleh dibaca terus daripada pangkalan data walaupun sandaran bocor |
| D3 | Pangkalan data menang, `.env` jadi sandaran | Pemasangan sedia ada terus berfungsi; pasukan ops masih boleh memaksa satu kunci di peringkat pelayan |
| D4 | Jejak audit merekod bahawa kunci berubah, bukan nilainya | `SettingsRepository::set()` merekod nilai sebelum dan selepas; tanpa penapisan, kunci ditulis sebagai teks biasa ke `audit_logs` |
| D5 | Butang uji menguji kunci yang telah disimpan, bukan yang ditaip | Tiada keraguan kunci mana yang disahkan; juga membolehkan semakan pada bila-bila masa selepas itu |
| D6 | Nyahsulit yang gagal memulangkan kunci kosong | Jika `APP_KEY` diputar, chatbot berhenti dengan mesej konfigurasi yang jelas dan bukannya setiap bacaan tetapan terhempas |

---

## 4. Seni Bina

### 4.1 Lapisan simpanan — `SettingsRepository`

Jenis nilai `rahsia` ditambah kepada empat yang sedia ada.

- `encode()` menerima jenis nilai. Bagi `rahsia`, nilai disulitkan sebelum dikodkan sebagai JSON. Nilai kosong disimpan sebagai rentetan kosong tanpa disulitkan, supaya baris yang belum ditetapkan tidak menyimpan sampah.
- `decode()` bagi `rahsia` menyahsulit dan memulangkan rentetan. `DecryptException` ditangkap dan memulangkan rentetan kosong.
- `set()` menyelesaikan jenis nilai sebelum mengekod, kerana baris sedia ada memiliki metadatanya sendiri dan jenis itu menentukan cara nilai dikodkan.

Cache tidak berubah. Ia menyimpan lajur `value` mentah, iaitu teks yang telah disulitkan, dan penyahsulitan berlaku pada baca.

### 4.2 Penapisan audit — `SettingsRepository`

Apabila jenis yang diselesaikan ialah `rahsia`, `set()` memanggil `AuditRecorder` dengan `context` sahaja dan tanpa muatan sebelum atau selepas:

```
context: ['key' => $key, 'value_redacted' => true]
```

`AuditRecorder` sudah menyimpan `context` walaupun tiada medan berubah, jadi putaran kunci tetap meninggalkan jejak.

### 4.3 Penyelesai kunci — `GeminiChatService`

Satu kaedah persendirian menentukan kunci: nilai pangkalan data jika ada, jika tidak `config('services.gemini.key')`. Kunci itu kekal persendirian dalam kelas ini. Dua kaedah awam baharu:

- `hasApiKey(): bool` dan `apiKeyIsFromSettings(): bool` untuk pengawal dan paparan.
- `maskedApiKey(): string` supaya kunci penuh tidak pernah meninggalkan kelas ini.
- `testConnection(): void` yang menghantar satu permintaan minimum ke Gemini. Kegagalan dibuang sebagai `ChatbotConfigurationException` atau `ChatbotApiException`, iaitu hierarki pengecualian sedia ada yang sudah membawa mesej Bahasa Melayu selamat untuk dipaparkan. Tiada jenis hasil baharu diperlukan.

### 4.4 Laluan

Satu laluan baharu dalam kumpulan `admin.settings`:

```
POST admin/settings/chatbot/test  →  admin.settings.chatbot.test
```

Kebenaran `can:chatbot.tetapan` dan `throttle:10,1`, kerana ia memanggil perkhidmatan luar.

### 4.5 Antara muka

Sepanduk amaran "letakkan dalam `.env`" digantikan dengan:

- Medan kata laluan `chatbot_api_key` yang sentiasa bermula kosong.
- Baris status dengan tiga keadaan: ditetapkan melalui skrin ini, diambil daripada fail persekitaran, atau belum ditetapkan.
- Kunci semasa dipaparkan bertopeng pada beberapa aksara terakhir sahaja. Nilai penuh tidak pernah sampai ke pelayar.
- Petunjuk "biarkan kosong untuk kekalkan kunci semasa".
- Kotak semak buang kunci, yang mengosongkan nilai pangkalan data dan kembali kepada fail persekitaran.
- Butang uji sambungan dalam borang berasingan.

Pengesahan: `nullable`, `string`, `max:255`, dan corak aksara yang selamat untuk pengepala HTTP. Nilai dipangkas sebelum disimpan.

---

## 5. Perubahan Sokongan

- Migrasi menambah `rahsia` kepada enum `system_settings.value_type`. Tanpa ini MySQL dan kekangan semak SQLite menolak baris baharu itu.
- `SystemConfigurationSeeder` menambah baris `chatbot.api_key` berjenis `rahsia` dengan nilai kosong, supaya jenisnya diisytiharkan di tempat yang sama dengan enam tetapan chatbot yang lain.
- Komen dalam `config/services.php` tidak lagi mendakwa kunci hanya wujud dalam persekitaran.
- Mesej `ChatbotConfigurationException` merujuk skrin tetapan, bukan fail `.env`.

---

## 6. Ujian

| Lapisan | Yang disahkan |
|---|---|
| `SettingsRepositoryTest` | Kunci pergi balik dengan betul; lajur tersimpan bukan teks biasa; baris audit tidak mengandungi kunci |
| `ChatbotSettingsTest` | Menyimpan kunci; medan kosong mengekalkan kunci sedia ada; buang kunci kembali kepada persekitaran; halaman tidak pernah memaparkan kunci penuh; peranan tanpa kebenaran ditolak |
| `ChatbotSettingsTest` | Titik akhir uji sambungan pada kejayaan dan kegagalan yang dipalsukan dengan `Http::fake` |
| `GeminiChatServiceTest` | Kunci pangkalan data mengatasi kunci persekitaran |

---

## 7. Risiko

| Risiko | Mitigasi |
|---|---|
| `APP_KEY` diputar selepas kunci disimpan | Nyahsulit yang gagal memulangkan kosong, jadi skrin melaporkan kunci belum ditetapkan dan pentadbir memasukkannya semula |
| Kunci bocor melalui log ralat | Kunci hanya dihantar sebagai pengepala; `GeminiChatService` sudah merekod status dan badan respons sahaja, bukan permintaan |
| Kunci bocor melalui jejak audit | Ditangani oleh D4 |
