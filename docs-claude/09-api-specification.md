# 09 — Spesifikasi API

| Perkara | Butiran |
|---|---|
| Dokumen | API Specification |
| Sistem | SPFA — Sistem Pengurusan Fasiliti & Aset ICT |
| Versi | 1.0 (Draf) |
| Tarikh | 7 September 2026 |
| Asas URL | `/api/v1` |

---

## 1. Konvensyen Am

| Perkara | Peraturan |
|---|---|
| Format | JSON untuk permintaan dan respons |
| Pengesahan | Token pembawa dalam pengepala `Authorization`, atau kuki sesi bagi klien web |
| Penamaan | Laluan menggunakan kata nama jamak dalam Bahasa Melayu |
| Cap masa | Format ISO 8601 dalam UTC, contohnya `2026-09-10T02:00:00Z` |
| Halaman | Parameter `halaman` dan `per_halaman`, lalai 25, maksimum 100 |
| Susunan | Parameter `susun` dengan awalan tolak untuk menurun, contohnya `susun=-masa_mula` |
| Penapis | Parameter pertanyaan bernama, contohnya `status=disahkan` |
| Had kadar | 120 permintaan seminit setiap pengguna, 20 seminit bagi endpoint penciptaan |

### Struktur respons kejayaan

```json
{
  "data": { },
  "meta": { "halaman": 1, "per_halaman": 25, "jumlah": 137 }
}
```

### Struktur respons ralat

```json
{
  "ralat": {
    "kod": "TEMPAHAN_KONFLIK",
    "mesej": "Bilik ini telah ditempah pada masa tersebut.",
    "butiran": { }
  }
}
```

### Kod status HTTP

| Kod | Makna dalam sistem ini |
|---|---|
| 200 | Berjaya |
| 201 | Rekod dicipta |
| 204 | Berjaya tanpa kandungan |
| 400 | Permintaan tidak sah dari segi format |
| 401 | Tidak disahkan |
| 403 | Tiada kebenaran |
| 404 | Rekod tidak dijumpai |
| 409 | Konflik dengan keadaan semasa, contohnya tempahan bertindih |
| 422 | Pelanggaran peraturan perniagaan atau pengesahan medan |
| 429 | Had kadar melebihi |
| 500 | Ralat pelayan |

### Kod ralat aplikasi

| Kod | Maksud |
|---|---|
| `TEMPAHAN_KONFLIK` | Slot masa telah diambil |
| `TEMPAHAN_LUAR_WAKTU` | Di luar waktu operasi bilik |
| `TEMPAHAN_MASA_LEPAS` | Masa mula telah berlalu |
| `KAPASITI_MELEBIHI` | Bilangan peserta melebihi kapasiti susun atur |
| `TEMPOH_TIDAK_SAH` | Tempoh di luar had minimum atau maksimum |
| `AWALAN_MELEBIHI` | Melebihi had tempoh awalan bagi peranan |
| `ASET_DILUPUSKAN` | Aset tidak boleh menjadi subjek tiket baharu |
| `STOK_TIDAK_CUKUP` | Baki stok kurang daripada kuantiti dikeluarkan |
| `NO_PENDAFTARAN_WUJUD` | Nombor pendaftaran aset telah digunakan |
| `TIADA_KEBENARAN` | Peranan tidak dibenarkan melakukan tindakan ini |

---

## 2. Endpoint Pengesahan

| Kaedah | Laluan | Keterangan |
|---|---|---|
| POST | `/auth/log-masuk` | Log masuk dengan nama pengguna dan kata laluan |
| POST | `/auth/log-keluar` | Tamatkan sesi |
| GET | `/auth/saya` | Maklumat pengguna semasa dan peranannya |
| POST | `/auth/segar` | Segarkan token |

**POST /auth/log-masuk**

```json
{ "nama_pengguna": "ahmad.zaki", "kata_laluan": "..." }
```

Respons 200:

```json
{
  "data": {
    "token": "...",
    "tamat_pada": "2026-09-07T18:00:00Z",
    "pengguna": {
      "id": "9f1c...",
      "nama_penuh": "Ahmad Zaki bin Osman",
      "emel": "ahmad.zaki@contoh.gov.my",
      "peranan": ["R1"],
      "unit_organisasi": "Bahagian Khidmat Pengurusan"
    }
  }
}
```

---

## 3. Endpoint Domain A — Tempahan Bilik

### 3.1 Bilik

| Kaedah | Laluan | Peranan | Keterangan |
|---|---|---|---|
| GET | `/bilik` | Semua | Senarai bilik dengan penapis |
| GET | `/bilik/{id}` | Semua | Butiran satu bilik |
| POST | `/bilik` | R4, R8 | Cipta bilik |
| PUT | `/bilik/{id}` | R4, R8 | Kemas kini bilik |
| DELETE | `/bilik/{id}` | R4, R8 | Nyahaktifkan bilik |
| GET | `/bilik/tersedia` | Semua | Carian ketersediaan |
| GET | `/bilik/{id}/kalendar` | Semua | Tempahan bilik dalam julat tarikh |

**GET /bilik/tersedia**

Parameter pertanyaan:

| Parameter | Wajib | Contoh |
|---|---|---|
| `masa_mula` | Ya | `2026-09-10T02:00:00Z` |
| `masa_tamat` | Ya | `2026-09-10T04:00:00Z` |
| `kapasiti_min` | Tidak | `10` |
| `bangunan_id` | Tidak | UUID |
| `kemudahan` | Tidak | `projektor,papan_putih` |
| `susun_atur` | Tidak | `bentuk_u` |

Respons 200:

```json
{
  "data": [
    {
      "id": "3a7e...",
      "kod": "BM-A-301",
      "nama": "Bilik Mesyuarat Utama",
      "lokasi": "Bangunan A, Tingkat 3",
      "kapasiti_asas": 20,
      "susun_atur": [
        { "jenis": "bentuk_u", "kapasiti": 18 },
        { "jenis": "kelas", "kapasiti": 24 }
      ],
      "kemudahan": ["projektor", "papan_putih", "persidangan_video"],
      "perlu_kelulusan": true
    }
  ],
  "meta": { "jumlah": 4 }
}
```

### 3.2 Tempahan

| Kaedah | Laluan | Peranan | Keterangan |
|---|---|---|---|
| GET | `/tempahan` | Semua, skop mengikut peranan | Senarai tempahan |
| GET | `/tempahan/saya` | Semua | Tempahan pengguna semasa |
| GET | `/tempahan/{id}` | Pemilik, R4, R8 | Butiran tempahan |
| POST | `/tempahan` | R1, R2, R4 | Cipta tempahan |
| PUT | `/tempahan/{id}` | Pemilik, R4, R8 | Pinda tempahan |
| POST | `/tempahan/{id}/batal` | Pemilik, R4, R8 | Batalkan dengan sebab(pilihan bagi tempahan menunggu kelulususan)|
| POST | `/tempahan/{id}/daftar-masuk` | Pemilik | Daftar masuk |
| GET | `/tempahan/{id}/ics` | Pemilik, peserta | Muat turun fail kalendar |
| POST | `/tempahan/siri` | R2, R4 | Cipta tempahan berulang |

**POST /tempahan**

```json
{
  "bilik_id": "3a7e...",
  "masa_mula": "2026-09-10T02:00:00Z",
  "masa_tamat": "2026-09-10T04:00:00Z",
  "tajuk": "Mesyuarat Jawatankuasa ICT Bil. 5/2026",
  "keterangan": "Perbincangan pelan digital 2027",
  "bil_peserta": 12,
  "susun_atur_id": "8c2d...",
  "tuan_punya_id": "9f1c...",
  "peserta": [
    { "pengguna_id": "1b4a..." },
    { "emel_luar": "vendor@contoh.com" }
  ],
  "permintaan_sokongan": [
    { "jenis": "minuman", "kuantiti": 12, "butiran": "Tanpa gula" }
  ]
}
```

Respons 201:

```json
{
  "data": {
    "id": "7d3f...",
    "no_rujukan": "TMP-202609-00187",
    "status": "menunggu_kelulusan",
    "bilik": { "kod": "BM-A-301", "nama": "Bilik Mesyuarat Utama" },
    "masa_mula": "2026-09-10T02:00:00Z",
    "masa_tamat": "2026-09-10T04:00:00Z",
    "pelulus": { "nama_penuh": "Rahman bin Ismail" }
  }
}
```

Respons 409 apabila konflik:

```json
{
  "ralat": {
    "kod": "TEMPAHAN_KONFLIK",
    "mesej": "Bilik Mesyuarat Utama telah ditempah pada 10 September 2026, 10:00 hingga 12:00.",
    "butiran": {
      "tempahan_bertindih": ["TMP-202609-00185"],
      "cadangan_slot": [
        { "masa_mula": "2026-09-10T04:30:00Z", "masa_tamat": "2026-09-10T06:30:00Z" },
        { "masa_mula": "2026-09-11T02:00:00Z", "masa_tamat": "2026-09-11T04:00:00Z" }
      ],
      "cadangan_bilik_lain": [
        { "id": "5e8b...", "kod": "BM-B-205", "nama": "Bilik Perbincangan B" }
      ]
    }
  }
}
```

**POST /tempahan/siri**

```json
{
  "bilik_id": "3a7e...",
  "tajuk": "Mesyuarat Mingguan Unit",
  "bil_peserta": 8,
  "masa_mula_harian": "09:00",
  "tempoh_minit": 60,
  "corak": "mingguan",
  "hari_dalam_minggu": ["isnin"],
  "tarikh_mula": "2026-09-14",
  "tarikh_tamat": "2026-12-14",
  "langkau_konflik": true
}
```

Respons 201 menyenaraikan kejadian yang berjaya dan yang dilangkau beserta sebab.

### 3.3 Kelulusan

| Kaedah | Laluan | Peranan | Keterangan |
|---|---|---|---|
| GET | `/kelulusan/menunggu` | R3, R4 | Senarai tugas kelulusan |
| POST | `/kelulusan/{id}/lulus` | R3, R4 | Luluskan |
| POST | `/kelulusan/{id}/tolak` | R3, R4 | Tolak dengan sebab wajib |
| POST | `/kelulusan/pukal` | R3, R4 | Kelulusan beberapa rekod sekaligus |
| POST | `/kelulusan/wakil` | Semua | Tetapkan wakil dalam julat tarikh |

---

## 4. Endpoint Domain B — Aset dan Penyelenggaraan

### 4.1 Aset

| Kaedah | Laluan | Peranan | Keterangan |
|---|---|---|---|
| GET | `/aset` | R5, R6, R7, R8 | Senarai aset dengan penapis |
| GET | `/aset/saya` | Semua | Aset didaftarkan atas nama pengguna |
| GET | `/aset/{id}` | R5, R6, R7, R8 | Butiran aset |
| GET | `/aset/qr/{token}` | Semua | Buka aset melalui imbasan QR |
| POST | `/aset` | R7, R8 | Daftar aset |
| PUT | `/aset/{id}` | R7, R8 | Kemas kini aset |
| POST | `/aset/{id}/pindah` | R7, R8 | Pindah lokasi atau pemilik |
| POST | `/aset/{id}/lupus` | R7, R8 | Mohon pelupusan |
| GET | `/aset/{id}/sejarah` | R5, R6, R7, R8 | Sejarah aset |
| GET | `/aset/{id}/tiket` | R5, R6, R7, R8 | Sejarah tiket aset |
| POST | `/aset/import` | R7, R8 | Import pukal daripada Excel |
| GET | `/aset/eksport` | R6, R7, R8 | Eksport senarai ditapis |
| POST | `/aset/label` | R7, R8 | Jana PDF label QR bagi senarai aset |

**GET /aset/qr/{token}**

Respons 200:

```json
{
  "data": {
    "id": "b2c9...",
    "no_pendaftaran": "ICT/KOMP/2024/0451",
    "kategori": "Komputer Meja",
    "jenama": "Dell",
    "model": "OptiPlex 7010",
    "no_siri": "SN7Y2K9L",
    "lokasi": "Bangunan A, Tingkat 2, Bilik 214",
    "pengguna_bertanggungjawab": "Ahmad Zaki bin Osman",
    "status": "digunakan",
    "waranti": {
      "mula": "2024-03-15",
      "tamat": "2027-03-14",
      "masih_sah": true,
      "baki_hari": 189
    },
    "ringkasan_penyelenggaraan": {
      "jumlah_tiket": 3,
      "tiket_terbuka": 0,
      "kos_terkumpul": 450.00,
      "pm_terakhir": "2026-06-12"
    }
  }
}
```

### 4.2 Tiket

| Kaedah | Laluan | Peranan | Keterangan |
|---|---|---|---|
| GET | `/tiket` | R6, R8 | Semua tiket dengan penapis |
| GET | `/tiket/saya` | Semua | Tiket dilaporkan pengguna |
| GET | `/tiket/tugasan-saya` | R5 | Tiket ditugaskan kepada juruteknik |
| GET | `/tiket/{id}` | Pelapor, juruteknik, R6, R8 | Butiran tiket |
| POST | `/tiket` | Semua | Buka tiket |
| POST | `/tiket/{id}/agih` | R6, R8 | Agihkan kepada juruteknik |
| POST | `/tiket/agih-pukal` | R6, R8 | Agihan pukal |
| POST | `/tiket/{id}/keutamaan` | R6, R8 | Ubah keutamaan dengan sebab |
| POST | `/tiket/{id}/mula` | R5 | Mula kerja |
| POST | `/tiket/{id}/catatan` | R5, R6 | Tambah catatan kemajuan |
| PUT | `/tiket/{id}/kerja` | R5 | Rekod diagnosis, tindakan, kos |
| POST | `/tiket/{id}/alat-ganti` | R5 | Rekod pengeluaran alat ganti |
| POST | `/tiket/{id}/rujuk-vendor` | R5, R6 | Rujuk kepada vendor |
| POST | `/tiket/{id}/selesai` | R5 | Tanda kerja selesai |
| POST | `/tiket/{id}/sahkan` | Pelapor | Sahkan penutupan |
| POST | `/tiket/{id}/buka-semula` | Pelapor | Buka semula tiket |

**POST /tiket**

```json
{
  "aset_id": "b2c9...",
  "lokasi_id": "4f7a...",
  "kategori_masalah": "tidak_boleh_hidup",
  "keterangan": "Komputer tidak menyala langsung selepas gangguan bekalan elektrik semalam.",
  "keutamaan_dicadangkan": "P2",
  "telefon_hubungan": "0123456789",
  "lampiran": ["muat-naik/sementara/9a2f.jpg"]
}
```

Respons 201:

```json
{
  "data": {
    "id": "c8d1...",
    "no_tiket": "TKT-202609-00342",
    "status": "baharu",
    "keutamaan": "P2",
    "sasaran_tindak_balas": "2026-09-07T04:30:00Z",
    "sasaran_pemulihan": "2026-09-08T02:30:00Z",
    "dalam_waranti": true
  }
}
```

**PUT /tiket/{id}/kerja**

```json
{
  "diagnosis": "Unit bekalan kuasa rosak akibat lonjakan voltan.",
  "tindakan": "Unit bekalan kuasa digantikan dengan unit baharu daripada stok.",
  "kos_pembaikan": 180.00,
  "masa_kerja_minit": 45,
  "justifikasi_waranti": "Kerosakan akibat lonjakan voltan tidak dilindungi waranti pengeluar."
}
```

### 4.3 Penyelenggaraan berjadual

| Kaedah | Laluan | Peranan | Keterangan |
|---|---|---|---|
| GET | `/pelan-penyelenggaraan` | R6, R8 | Senarai pelan |
| POST | `/pelan-penyelenggaraan` | R6, R8 | Cipta pelan |
| GET | `/perintah-kerja` | R5, R6, R8 | Senarai perintah kerja |
| GET | `/perintah-kerja/tugasan-saya` | R5 | Perintah kerja juruteknik |
| POST | `/perintah-kerja/{id}/agih` | R6, R8 | Agihkan |
| PUT | `/perintah-kerja/{id}/senarai-semak` | R5 | Kemas kini item senarai semak |
| POST | `/perintah-kerja/{id}/selesai` | R5 | Tanda selesai |
| POST | `/perintah-kerja/{id}/jana-tiket` | R5 | Cipta tiket pembetulan daripada item gagal |

### 4.4 Stok, vendor dan kontrak

| Kaedah | Laluan | Peranan | Keterangan |
|---|---|---|---|
| GET | `/stok` | R5, R6, R8 | Senarai item stok dan baki |
| POST | `/stok` | R6, R8 | Daftar item stok |
| POST | `/stok/{id}/terima` | R6, R8 | Rekod penerimaan |
| POST | `/stok/{id}/laras` | R6, R8 | Pelarasan dengan sebab wajib |
| GET | `/stok/{id}/pergerakan` | R6, R8 | Sejarah pergerakan |
| GET | `/vendor` | R6, R7, R8 | Senarai vendor |
| POST | `/vendor` | R7, R8 | Daftar vendor |
| GET | `/kontrak` | R6, R7, R8 | Senarai kontrak |
| GET | `/kontrak/akan-tamat` | R6, R7, R8 | Kontrak tamat dalam tempoh ditapis |

---

## 5. Endpoint Rentas

### 5.1 Laporan

| Kaedah | Laluan | Peranan | Keterangan |
|---|---|---|---|
| GET | `/laporan/penggunaan-bilik` | R4, R8 | Kadar penggunaan mengikut bilik |
| GET | `/laporan/tempahan` | R4, R8 | Laporan tempahan terperinci |
| GET | `/laporan/sla` | R6, R8 | Prestasi SLA |
| GET | `/laporan/kerosakan` | R6, R7, R8 | Kerosakan mengikut kategori dan model |
| GET | `/laporan/inventori` | R6, R7, R8 | Inventori aset |
| GET | `/laporan/kos-penyelenggaraan` | R6, R7, R8 | Kos mengikut aset dan unit |

Semua endpoint laporan menerima parameter `dari`, `hingga`, dan `format` dengan nilai `json`, `xlsx` atau `pdf`. Permintaan format fail bagi julat besar dikembalikan sebagai tugas latar dengan pengenal tugas, dan fail dihantar melalui e-mel apabila siap.

### 5.2 Notifikasi

| Kaedah | Laluan | Keterangan |
|---|---|---|
| GET | `/notifikasi` | Notifikasi pengguna semasa |
| POST | `/notifikasi/{id}/tanda-dibaca` | Tanda satu notifikasi dibaca |
| POST | `/notifikasi/tanda-semua-dibaca` | Tanda semua dibaca |

### 5.3 Pentadbiran

| Kaedah | Laluan | Peranan | Keterangan |
|---|---|---|---|
| GET | `/konfigurasi` | R8 | Semua tetapan |
| PUT | `/konfigurasi/{kunci}` | R8 | Kemas kini satu tetapan |
| GET | `/log-audit` | R8, juruaudit | Carian log audit |
| GET | `/rujukan/{jenis}` | Semua | Senarai nilai rujukan seperti kategori aset |
| GET | `/kesihatan` | Terbuka | Semakan kesihatan sistem untuk pemantauan |

---

## 6. Nota Pelaksanaan

**Idempoten.** Endpoint penciptaan tempahan dan tiket menerima pengepala `Idempotency-Key` secara pilihan. Permintaan berulang dengan kunci yang sama mengembalikan rekod asal, bukan mencipta rekod kedua. Ini melindungi daripada ketikan berganda pada sambungan mudah alih yang perlahan.

**Muat naik fail.** Fail dimuat naik ke `/muat-naik` terlebih dahulu, yang mengembalikan laluan sementara. Laluan tersebut kemudian dirujuk dalam permintaan penciptaan tiket atau catatan. Fail sementara yang tidak dirujuk dalam 24 jam dipadam oleh tugas latar.

**Zon waktu.** Semua cap masa dihantar dan diterima dalam UTC. Penukaran ke waktu tempatan dilakukan oleh klien. Ini mengelakkan kekeliruan semasa pemaparan kalendar merentas peranti.

**Dokumentasi hidup.** Spesifikasi OpenAPI dijana daripada kod dan dihidangkan pada `/api/dokumentasi`, supaya ia tidak menjadi lapuk berbanding pelaksanaan sebenar.
