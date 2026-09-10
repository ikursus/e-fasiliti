# 01 — Senarai & Peta Modul

Sistem dipecahkan kepada 17 modul. Setiap modul mempunyai satu tanggungjawab jelas, antara muka yang boleh diterangkan tanpa membaca dalamannya, dan boleh diuji secara berasingan.

---

## 1. Peta Modul

```mermaid
graph TB
    subgraph ASAS["Lapisan Asas (Platform)"]
        M01[M01 Pentadbiran & Konfigurasi]
        M02[M02 Pengguna, Peranan & Akses]
        M03[M03 Direktori Organisasi & Lokasi]
        M16[M16 Jejak Audit & Keselamatan]
    end
    subgraph DOMA["Domain A — Tempahan Bilik Mesyuarat"]
        M04[M04 Katalog Bilik & Sumber]
        M05[M05 Enjin Tempahan & Kalendar]
        M06[M06 Kelulusan & Aliran Kerja]
        M07[M07 Daftar Masuk & Pelepasan Auto]
        M08[M08 Perkhidmatan Sokongan Mesyuarat]
    end
    subgraph DOMB["Domain B — Penyelenggaraan Aset ICT"]
        M09[M09 Inventari & Pendaftaran Aset]
        M10[M10 Tiket Aduan Kerosakan]
        M11[M11 Penyelenggaraan Berjadual]
        M12[M12 Vendor, Kontrak & Waranti]
        M13[M13 Alat Ganti & Stok]
    end
    subgraph RENTAS["Lapisan Perkhidmatan Rentas"]
        M14[M14 Notifikasi & Integrasi]
        M15[M15 Laporan, Dashboard & Analitik]
        M17[M17 Pembantu AI (Chatbot)]
    end

    M02 --> M05
    M02 --> M10
    M03 --> M04
    M03 --> M09
    M04 --> M05
    M05 --> M06
    M05 --> M07
    M05 --> M08
    M09 --> M10
    M09 --> M11
    M09 --> M12
    M10 --> M13
    M11 --> M13
    M05 --> M14
    M10 --> M14
    M05 --> M15
    M10 --> M15
    M16 --> M15
    M01 --> M17
```

---

## 2. Perincian Modul

### Lapisan Asas

#### M01 — Pentadbiran Sistem & Konfigurasi
Menyimpan semua tetapan yang boleh diubah tanpa menulis kod: waktu operasi, tempoh tempahan minimum dan maksimum, ambang tidak hadir, keutamaan tiket dan sasaran SLA, templat notifikasi, kalendar cuti umum, dan senarai nilai rujukan.

- **Antara muka keluar:** perkhidmatan konfigurasi yang dipanggil oleh semua modul lain.
- **Bergantung kepada:** tiada.
- **Rujukan FR:** FR-ADM-01 hingga FR-ADM-08.

#### M02 — Pengguna, Peranan & Kawalan Akses
Pengesahan identiti melalui direktori organisasi atau akaun tempatan, pengurusan sesi, dan kawalan akses berasaskan peranan. Menakrifkan lapan peranan sistem dan kebenaran setiap satu.

- **Antara muka keluar:** token sesi, perkhidmatan semakan kebenaran.
- **Bergantung kepada:** M01, direktori luaran.
- **Rujukan FR:** FR-USR-01 hingga FR-USR-10.

#### M03 — Direktori Organisasi & Lokasi
Hierarki organisasi (bahagian, unit) dan hierarki fizikal (kampus, bangunan, tingkat, ruang). Kedua-dua domain merujuk struktur yang sama, jadi bilik mesyuarat dan komputer dirujuk kepada lokasi yang konsisten.

- **Antara muka keluar:** pertanyaan pokok lokasi dan unit organisasi.
- **Bergantung kepada:** tiada.
- **Rujukan FR:** FR-ORG-01 hingga FR-ORG-05.

#### M16 — Jejak Audit & Keselamatan
Merekod setiap perubahan data penting: siapa, bila, nilai sebelum dan selepas. Log tidak boleh dipadam melalui antara muka pengguna.

- **Antara muka keluar:** perkhidmatan rekod audit, dan paparan carian log untuk pentadbir dan juruaudit.
- **Bergantung kepada:** M02.
- **Rujukan FR:** FR-AUD-01 hingga FR-AUD-05.

#### M17 — Pembantu AI (Chatbot)
Pembantu perbualan dalam Bahasa Melayu yang dibina di atas Google AI Studio (Gemini API). Menjawab soalan pengguna tentang prosedur sistem — tempahan bilik, aduan ICT, inventari aset — dengan sejarah perbualan setiap pengguna disimpan dalam sistem, dan tetapan boleh diubah pentadbir tanpa menulis kod.

- **Antara muka keluar:** servis `GeminiChatService` dan tetapan kumpulan `chatbot` (model, suhu, had token, had sejarah, arahan sistem).
- **Bergantung kepada:** M01 (tetapan), M02 (peranan dan kebenaran), direktori luaran (Gemini API).
- **Rujukan FR:** FR-CHB-01 hingga FR-CHB-06.

### Domain A — Tempahan Bilik Mesyuarat

#### M04 — Katalog Bilik & Sumber
Pendaftaran bilik mesyuarat berserta sifatnya: kapasiti, susun atur yang disokong, kemudahan tetap seperti projektor dan papan putih, foto, dan peraturan tempahan khusus bilik. Turut menguruskan sumber boleh tempah lain seperti projektor mudah alih.

- **Antara muka keluar:** carian bilik dengan penapis, dan pengambilan sifat bilik.
- **Bergantung kepada:** M03, M01.
- **Rujukan FR:** FR-BLK-01 hingga FR-BLK-09.

#### M05 — Enjin Tempahan & Kalendar
Inti domain A. Menyemak ketersediaan, mengesan konflik, mencipta tempahan tunggal dan berulang, mengendalikan pembatalan dan pindaan, serta memaparkan kalendar harian, mingguan dan bulanan.

- **Antara muka keluar:** perkhidmatan tempahan dengan operasi semak ketersediaan, cipta, ubah dan batal.
- **Bergantung kepada:** M04, M02, M01, M06.
- **Rujukan FR:** FR-TMP-01 hingga FR-TMP-18.

#### M06 — Kelulusan & Aliran Kerja
Enjin aliran kerja umum yang boleh dikonfigurasi. Menentukan sama ada sesuatu tempahan memerlukan kelulusan, siapa pelulusnya, dan apa yang berlaku jika pelulus tidak bertindak dalam tempoh tertentu.

- **Antara muka keluar:** perkhidmatan aliran kerja dan papan tugas kelulusan.
- **Bergantung kepada:** M02, M01.
- **Rujukan FR:** FR-KLS-01 hingga FR-KLS-07.

#### M07 — Daftar Masuk & Pelepasan Automatik
Menangani masalah bilik ditempah tetapi tidak digunakan. Penempah mengesahkan kehadiran melalui imbasan kod QR di pintu bilik atau butang dalam sistem. Jika tiada pengesahan selepas tempoh anjal, tempahan dilepaskan dan bilik terbuka semula.

- **Antara muka keluar:** perkhidmatan daftar masuk dan tugas berjadual pelepasan.
- **Bergantung kepada:** M05, M14.
- **Rujukan FR:** FR-CHK-01 hingga FR-CHK-06.

#### M08 — Perkhidmatan Sokongan Mesyuarat
Permintaan tambahan yang berkait dengan sesuatu tempahan: susun atur meja, minuman, peralatan tambahan, dan sokongan teknikal semasa mesyuarat. Permintaan ini dihalakan kepada unit yang bertanggungjawab.

- **Antara muka keluar:** permintaan berkait tempahan dan senarai tugas unit sokongan.
- **Bergantung kepada:** M05, M14.
- **Rujukan FR:** FR-SOK-01 hingga FR-SOK-05.

### Domain B — Penyelenggaraan Aset ICT

#### M09 — Inventari & Pendaftaran Aset ICT
Daftar induk bagi setiap komputer, komputer riba, pencetak, penghala, projektor dan peralatan ICT lain. Menyimpan nombor siri, tarikh perolehan, nilai, waranti, pengguna yang dipertanggungjawab, dan lokasi semasa. Menjana label kod QR dan menyimpan sejarah pergerakan serta pertukaran pemilik.

- **Antara muka keluar:** carian aset, pengambilan aset melalui kod QR, dan sejarah aset.
- **Bergantung kepada:** M03, M02, M12.
- **Rujukan FR:** FR-AST-01 hingga FR-AST-14.

#### M10 — Tiket Aduan Kerosakan
Aliran kerja pembetulan. Pengguna melaporkan kerosakan, sistem memberi nombor rujukan, penyelia mengagihkan kepada juruteknik, juruteknik merekod diagnosis dan tindakan, pengguna mengesahkan pemulihan, dan tiket ditutup. SLA dikira daripada masa laporan hingga penutupan mengikut keutamaan.

- **Antara muka keluar:** perkhidmatan tiket dengan operasi buka, agih, kemas kini dan tutup.
- **Bergantung kepada:** M09, M02, M01, M13, M14.
- **Rujukan FR:** FR-TKT-01 hingga FR-TKT-20.

#### M11 — Penyelenggaraan Berjadual (Preventif)
Pelan penyelenggaraan berkala mengikut kategori aset, contohnya pembersihan pencetak setiap suku tahun atau semakan komputer setiap tahun. Sistem menjana perintah kerja secara automatik mengikut jadual dan menjejaki pematuhan.

- **Antara muka keluar:** penjana perintah kerja berjadual dan senarai semak tugas penyelenggaraan.
- **Bergantung kepada:** M09, M01, M14.
- **Rujukan FR:** FR-PMV-01 hingga FR-PMV-09.

#### M12 — Vendor, Kontrak & Waranti
Rekod pembekal dan syarikat penyelenggaraan, kontrak sokongan, tempoh waranti setiap aset, dan amaran sebelum tamat tempoh. Turut menyimpan prestasi vendor berdasarkan masa tindak balas tiket yang dirujuk kepada mereka.

- **Antara muka keluar:** semakan status waranti aset dan senarai kontrak yang akan tamat.
- **Bergantung kepada:** M09.
- **Rujukan FR:** FR-VDR-01 hingga FR-VDR-08.

#### M13 — Alat Ganti & Stok
Stok item gantian seperti toner, papan kekunci, cakera keras dan RAM. Merekod pengeluaran stok terhadap tiket atau perintah kerja, dan memberi amaran apabila paras stok rendah.

- **Antara muka keluar:** pengeluaran stok berkait tiket dan laporan paras stok.
- **Bergantung kepada:** M10, M11.
- **Rujukan FR:** FR-STK-01 hingga FR-STK-07.

### Lapisan Perkhidmatan Rentas

#### M14 — Notifikasi & Integrasi
Satu-satunya modul yang menghantar mesej keluar. Menyokong e-mel, notifikasi dalam aplikasi, jemputan kalendar format ICS, dan penyambung pilihan kepada Teams atau Telegram. Templat mesej boleh disunting oleh pentadbir.

- **Antara muka keluar:** perkhidmatan penghantaran notifikasi berasaskan peristiwa.
- **Bergantung kepada:** M01.
- **Rujukan FR:** FR-NOT-01 hingga FR-NOT-09.

#### M15 — Laporan, Dashboard & Analitik
Papan pemuka mengikut peranan dan laporan berkanun. Semua laporan boleh dieksport ke Excel dan PDF, dan boleh dijadualkan untuk dihantar melalui e-mel.

- **Antara muka keluar:** perkhidmatan laporan dan antara muka eksport.
- **Bergantung kepada:** semua modul data.
- **Rujukan FR:** FR-LAP-01 hingga FR-LAP-12.

---

## 3. Matriks Peranan Berbanding Modul

Legenda: **C** cipta, **R** baca, **U** kemas kini, **D** padam, **A** lulus atau sahkan, **G** guna (sesi chatbot), **—** tiada akses.

| Modul | Kakitangan | Setiausaha | Pelulus | Pentadbir Fasiliti | Juruteknik | Penyelia ICT | Pegawai Aset | Pentadbir Sistem |
|---|---|---|---|---|---|---|---|---|
| M01 Konfigurasi | — | — | — | R | — | R | R | CRUD |
| M02 Pengguna | R sendiri | R sendiri | R sendiri | R | R sendiri | R | R | CRUD |
| M03 Lokasi | R | R | R | CRU | R | R | CRU | CRUD |
| M04 Bilik | R | R | R | CRUD | — | — | — | CRUD |
| M05 Tempahan | CRU sendiri | CRU bagi pihak | R unit | CRUD semua | — | — | — | R |
| M06 Kelulusan | R sendiri | R | A | A | — | — | — | CRUD aliran |
| M07 Daftar masuk | U sendiri | U bagi pihak | — | R | — | — | — | R |
| M08 Sokongan | C sendiri | C | R | RU | R | — | — | R |
| M09 Aset | R sendiri | R | — | R | RU | R | CRUD | CRUD |
| M10 Tiket | CR sendiri | CR | R unit | R | RU ditugaskan | CRUA semua | R | CRUD |
| M11 Penyelenggaraan | — | — | — | — | RU ditugaskan | CRUD | R | CRUD |
| M12 Vendor | — | — | — | — | R | CRU | CRUD | CRUD |
| M13 Stok | — | — | — | — | RU keluaran | CRUD | R | CRUD |
| M14 Notifikasi | R sendiri | R | R | R | R | R | R | CRUD templat |
| M15 Laporan | R sendiri | R unit | R unit | R fasiliti | R sendiri | R semua ICT | R aset | R semua |
| M16 Audit | — | — | — | — | — | R skop ICT | R skop aset | R semua |
| M17 Pembantu AI | G | G | G | G | G | G | G | G + CRUD tetapan |

---

## 4. Cadangan Fasa Pelaksanaan

| Fasa | Modul | Hasil boleh guna | Anggaran tempoh |
|---|---|---|---|
| Fasa 1, Asas dan Tempahan | M01, M02, M03, M04, M05, M06, M14 (e-mel), M16 | Tempahan bilik berfungsi penuh dengan kelulusan dan notifikasi | 10 minggu |
| Fasa 2, Aset dan Tiket | M09, M10, M13, M15 (laporan teras) | Inventori aset berlabel QR dan helpdesk kerosakan dengan SLA | 10 minggu |
| Fasa 3, Pematangan | M07, M08, M11, M12, M14 (ICS), M15 (analitik penuh) | Pelepasan automatik, penyelenggaraan preventif, vendor, papan pemuka | 8 minggu |
| Fasa 4, Pilihan | Aplikasi mudah alih natif, integrasi kewangan, papan tanda bilik | Mengikut keputusan selepas kajian faedah | Belum ditetapkan |
| Dibina (tambahan) | M17 Pembantu AI | Chatbot Google AI Studio untuk semua peranan, tetapan tanpa kod | Selesai (cawangan `chatbot`) |

Prinsip pemecahan fasa: setiap fasa mesti menghasilkan sesuatu yang boleh digunakan sepenuhnya oleh sekurang-kurangnya satu kumpulan pengguna. Fasa 1 memberi nilai kepada semua kakitangan. Fasa 2 memberi nilai kepada unit ICT. Fasa 3 memperbaiki kualiti data dan mengurangkan kerja manual yang berbaki.
