# 12 — Pelan Ujian & Skrip UAT

| Perkara | Butiran |
|---|---|
| Dokumen | Test Plan and User Acceptance Test Scripts |
| Sistem | SPFA — Sistem Pengurusan Fasiliti & Aset ICT |
| Versi | 1.0 (Draf) |
| Tarikh | 7 September 2026 |

---

## 1. Strategi Ujian

| Aras | Siapa | Bila | Kriteria lulus |
|---|---|---|---|
| Ujian unit | Pembangun | Setiap komit | Semua lulus, liputan logik domain melebihi 80% |
| Ujian integrasi | Pembangun | Setiap komit | Semua aliran utama setiap modul lulus |
| Ujian keserentakan | Pembangun | Sebelum pelepasan modul tempahan | Tiada pertindihan tercipta dalam 1,000 permintaan serentak |
| Ujian sistem | Penguji QA | Akhir setiap fasa | Tiada kecacatan berkeutamaan kritikal atau tinggi terbuka |
| Ujian prestasi | Penguji QA | Sebelum UAT setiap fasa | Semua sasaran NFR prestasi dipenuhi |
| Ujian keselamatan | Pihak ketiga | Sebelum go-live | Tiada penemuan kritikal atau tinggi terbuka |
| Ujian penerimaan pengguna | Wakil pengguna | Sebelum go-live setiap fasa | Semua skrip wajib lulus |

## 2. Persekitaran dan Data Ujian

**Persekitaran UAT** mesti mengandungi data yang menyerupai keadaan sebenar, bukan data contoh yang terlalu bersih. Data ujian yang disediakan:

| Data | Kuantiti | Nota |
|---|---|---|
| Pengguna | 40, meliputi semua lapan peranan | Termasuk seorang pengguna dengan dua peranan |
| Bilik | 12 | Termasuk 2 bilik yang memerlukan kelulusan dan 1 bilik dinyahaktifkan |
| Tempahan sedia ada | 200 merentas 3 bulan | Termasuk tempahan lepas, semasa dan akan datang |
| Aset | 150 | Termasuk aset dalam waranti, luar waranti dan telah dilupuskan |
| Tiket | 60 | Merentas semua status termasuk yang melanggar SLA |
| Item stok | 20 | Termasuk 3 item bawah paras minimum |
| Vendor dan kontrak | 5 vendor, 6 kontrak | Termasuk 1 kontrak yang hampir tamat |

Data peribadi dalam persekitaran UAT mesti ditanpanamakan jika ia berasal daripada data sebenar.

## 3. Kriteria Masuk dan Keluar UAT

**Kriteria masuk:**

- Semua ujian sistem lulus dan laporannya diserahkan.
- Tiada kecacatan kritikal atau tinggi yang terbuka.
- Data ujian dimuatkan dan disahkan.
- Manual pengguna versi draf tersedia.
- Peserta UAT telah menerima taklimat.

**Kriteria keluar:**

- Semua skrip UAT bertanda wajib lulus.
- Kecacatan kritikal dan tinggi ditutup dan diuji semula.
- Kecacatan sederhana dan rendah yang berbaki didokumenkan dengan pelan penyelesaian bertarikh.
- Borang penerimaan ditandatangani oleh pemilik proses setiap domain.

## 4. Kes Ujian Sistem

Ini adalah kes ujian yang dirujuk dalam matriks kebolehjejakan. Setiap satu ditulis dengan prasyarat, langkah dan hasil dijangka yang boleh diperiksa secara objektif.

### 4.1 Tempahan bilik

| ID | Kes ujian | Hasil dijangka |
|---|---|---|
| TC-TMP-01 | Cari bilik tersedia untuk esok, 10 pagi, 2 jam | Senarai bilik kosong dipaparkan dalam bawah 2 saat; bilik yang telah ditempah tidak muncul |
| TC-TMP-02 | Tempah bilik tanpa kelulusan | Status disahkan; e-mel pengesahan diterima dalam 1 minit |
| TC-TMP-03 | Tempah bilik yang memerlukan kelulusan | Status menunggu kelulusan; pelulus menerima notifikasi |
| TC-TMP-04 | Tapis mengikut kapasiti 20 dan kemudahan projektor | Hanya bilik memenuhi kedua-dua kriteria dipaparkan |
| TC-TMP-05 | Pinda masa tempahan sedia ada kepada slot kosong | Tempahan dikemas kini; peserta menerima notifikasi perubahan |
| TC-TMP-06 | Cuba tempah slot yang telah ditempah | Ditolak dengan kod TEMPAHAN_KONFLIK dan cadangan slot alternatif dipaparkan |
| TC-TMP-07 | Pinda tempahan kepada slot yang telah ditempah | Ditolak dengan mesej konflik; tempahan asal kekal tidak berubah |
| TC-TMP-08 | Cipta siri mingguan 12 minggu tanpa konflik | 12 tempahan tercipta dengan pengenal siri yang sama |
| TC-TMP-09 | Cipta siri mingguan dengan 2 tarikh berkonflik | Senarai konflik dipaparkan sebelum simpan; selepas melangkau, 10 tempahan tercipta |
| TC-TMP-10 | Setiausaha tempah bagi pihak pegawai | Penempah dan tuan punya direkod berasingan; kedua-dua menerima e-mel |
| TC-TMP-11 | Cuba tempah untuk semalam | Ditolak dengan kod TEMPAHAN_MASA_LEPAS |
| TC-TMP-12 | Cuba tempah pukul 7 malam bagi bilik yang tutup pukul 6 | Ditolak untuk pengguna biasa; dibenarkan untuk pentadbir fasiliti |
| TC-TMP-13 | Cuba tempah 120 hari ke hadapan sebagai kakitangan biasa | Ditolak dengan kod AWALAN_MELEBIHI |
| TC-TMP-14 | Batal tempahan 1 jam sebelum mula | Sebab diminta; rekod ditanda sebagai pembatalan lewat |
| TC-TMP-15 | Tempah dengan 25 peserta pada bilik berkapasiti 18 | Ditolak dengan kod KAPASITI_MELEBIHI dan cadangan bilik lebih besar |
| TC-TMP-16 | Batal tempahan berstatus menunggu kelulususan | Dibatalkan serta-merta tanpa menunggu keputusan pelulus; pelulus dimaklumkan permohonan ditarik balik; slot dibebaskan dan boleh ditempah pengguna lain |
| TC-CONC-01 | Hantar 50 permintaan tempahan serentak untuk slot yang sama | Tepat satu berjaya; 49 lagi menerima ralat konflik; tiada rekod bertindih dalam pangkalan data |

### 4.2 Kelulusan

| ID | Kes ujian | Hasil dijangka |
|---|---|---|
| TC-KLS-01 | Pelulus meluluskan permohonan | Status bertukar kepada disahkan; pemohon menerima e-mel dengan lampiran kalendar |
| TC-KLS-02 | Pelulus menolak tanpa sebab | Sistem menolak penghantaran dan meminta sebab |
| TC-KLS-03 | Pelulus utama menetapkan wakil, permohonan baharu dihantar | Permohonan muncul dalam senarai tugas wakil, bukan pelulus utama |
| TC-KLS-04 | Kelulusan pukal 5 permohonan | Semua 5 diluluskan; laporan ringkasan dipaparkan |

### 4.3 Daftar masuk dan pelepasan

| ID | Kes ujian | Hasil dijangka |
|---|---|---|
| TC-CHK-01 | Imbas kod QR bilik semasa tempahan aktif | Halaman bilik dipaparkan dengan butang daftar masuk aktif |
| TC-CHK-02 | Daftar masuk 5 minit sebelum masa mula | Berjaya; status bertukar kepada daftar masuk |
| TC-CHK-03 | Biarkan tempahan tanpa daftar masuk melepasi tempoh anjal | Status bertukar kepada dilepaskan; peristiwa tidak hadir direkod; slot terbuka semula |
| TC-CHK-04 | Pengguna bukan penempah mengimbas QR | Maklumat tempahan dipaparkan tetapi butang daftar masuk tidak aktif |

### 4.4 Aset

| ID | Kes ujian | Hasil dijangka |
|---|---|---|
| TC-AST-01 | Daftar aset baharu dengan semua medan wajib | Aset tercipta dengan nombor pendaftaran dijana; peristiwa direkod dalam sejarah |
| TC-AST-02 | Daftar aset tanpa nombor siri | Diterima kerana nombor siri adalah medan pilihan |
| TC-AST-03 | Daftar aset dengan nombor pendaftaran yang telah wujud | Ditolak dengan kod NO_PENDAFTARAN_WUJUD |
| TC-AST-04 | Import 100 baris Excel dengan 5 baris tidak sah | 95 baris disimpan; laporan ralat menyenaraikan 5 baris beserta sebab setiap satu |
| TC-AST-05 | Eksport senarai aset ditapis mengikut lokasi | Fail Excel mengandungi hanya aset lokasi tersebut dengan semua lajur |
| TC-AST-06 | Jana label QR untuk 20 aset terpilih | PDF dijana dengan 20 label mengandungi kod QR dan nombor pendaftaran |
| TC-AST-07 | Imbas kod QR aset menggunakan kamera telefon | Halaman aset terbuka dalam bawah 3 saat memaparkan waranti dan sejarah |
| TC-AST-08 | Pindah aset ke lokasi dan pemilik baharu | Aset dikemas kini; rekod sejarah mencatat nilai sebelum dan selepas beserta sebab |
| TC-AST-09 | Lupus aset melalui aliran kelulusan | Status bertukar kepada dilupuskan selepas kelulusan; sejarah kekal utuh |
| TC-AST-10 | Cuba buka tiket bagi aset yang telah dilupuskan | Ditolak dengan kod ASET_DILUPUSKAN |

### 4.5 Tiket dan SLA

| ID | Kes ujian | Hasil dijangka |
|---|---|---|
| TC-TKT-01 | Buka tiket melalui imbasan QR | Maklumat aset diisi automatik; nombor tiket dijana dan dipaparkan serta-merta |
| TC-TKT-02 | Buka tiket tanpa aset bagi masalah rangkaian umum | Tiket tercipta dengan lokasi sahaja |
| TC-TKT-03 | Muat naik foto 3 megabait pada tiket | Diterima dan dipaparkan sebagai lampiran |
| TC-TKT-04 | Muat naik fail boleh laksana | Ditolak dengan mesej jenis fail tidak dibenarkan |
| TC-TKT-05 | Semak status tiket sebagai pelapor | Status semasa, juruteknik dan catatan yang boleh dilihat pelapor dipaparkan |
| TC-TKT-06 | Agih tiket kepada juruteknik | Status bertukar kepada diagih; juruteknik menerima notifikasi |
| TC-TKT-07 | Agihan pukal 5 tiket kepada seorang juruteknik | Semua 5 diagihkan; satu notifikasi ringkasan dihantar |
| TC-TKT-08 | Rekod diagnosis, tindakan dan alat ganti | Data disimpan; baki stok berkurang mengikut kuantiti |
| TC-TKT-09 | Pelapor mengesahkan penutupan | Tiket ditutup; pengiraan pematuhan SLA direkod |
| TC-TKT-10 | Tiket menunggu pengesahan melebihi 3 hari bekerja | Ditutup automatik dengan catatan penutupan tanpa pengesahan |
| TC-SLA-01 | Buka tiket P2 pada pukul 4 petang Jumaat | Sasaran pemulihan dikira mengecualikan hujung minggu, jatuh pada Isnin |
| TC-SLA-02 | Ubah keutamaan P3 kepada P1 | Sasaran SLA dikira semula; sebab perubahan direkod |
| TC-SLA-03 | Tukar status kepada menunggu alat ganti selama 2 hari | Jam SLA dijeda; masa jeda dikecualikan daripada pengiraan |
| TC-SLA-04 | Biarkan tiket mencapai 80% masa sasaran | Amaran dihantar kepada penyelia dan juruteknik |
| TC-SLA-05 | Biarkan tiket melepasi sasaran pemulihan | Amaran kedua dihantar; tiket ditandakan melanggar SLA dalam papan pemuka |

### 4.6 Stok, vendor dan penyelenggaraan berjadual

| ID | Kes ujian | Hasil dijangka |
|---|---|---|
| TC-STK-01 | Rekod penerimaan 10 unit item stok | Baki bertambah 10; kos seunit disimpan |
| TC-STK-02 | Keluarkan alat ganti tanpa mengaitkan tiket | Ditolak; sistem meminta rujukan tiket atau perintah kerja |
| TC-STK-03 | Keluarkan 5 unit apabila baki hanya 3 | Ditolak dengan kod STOK_TIDAK_CUKUP |
| TC-STK-04 | Keluarkan stok sehingga bawah paras minimum | Amaran e-mel dihantar kepada penyelia |
| TC-VDR-01 | Buka halaman aset dalam waranti | Status waranti dan baki hari dipaparkan dengan jelas |
| TC-VDR-02 | Rekod kos berbayar bagi aset dalam waranti | Amaran dipaparkan; justifikasi bertulis diperlukan sebelum simpan |
| TC-VDR-03 | Semak senarai kontrak tamat dalam 90 hari | Kontrak berkenaan disenaraikan; amaran e-mel dihantar pada 90, 30 dan 7 hari |
| TC-PMV-01 | Cipta pelan suku tahunan bagi kategori pencetak | Pelan disimpan dengan senarai semak |
| TC-PMV-02 | Biarkan tugas latar berjalan pada tarikh awalan | Perintah kerja dijana bagi setiap aset dalam kategori |
| TC-PMV-03 | Lengkapkan senarai semak dengan 1 item gagal | Perintah kerja ditanda selesai; tiket pembetulan boleh dijana daripada item gagal |
| TC-PMV-04 | Semak laporan pematuhan penyelenggaraan | Kadar pematuhan dikira dengan betul mengikut suku tahun |

### 4.7 Laporan, keselamatan dan bukan fungsian

| ID | Kes ujian | Hasil dijangka |
|---|---|---|
| TC-LAP-01 | Jana laporan penggunaan bilik untuk sebulan | Jam ditempah, jam hadir, kadar penggunaan dan kadar tidak hadir dipaparkan setiap bilik |
| TC-LAP-02 | Eksport laporan penggunaan ke Excel | Fail dibuka dengan betul dalam Excel; semua lajur lengkap |
| TC-LAP-03 | Jana laporan prestasi SLA | Peratusan pematuhan dan purata masa pemulihan sepadan dengan pengiraan manual sampel |
| TC-LAP-04 | Jana laporan kerosakan mengikut model | Model dengan kerosakan tertinggi disenaraikan dahulu |
| TC-LAP-05 | Jana laporan inventori dan eksport PDF | PDF dijana dengan pemformatan yang kemas dan boleh dicetak |
| TC-LAP-06 | Jadualkan laporan bulanan melalui e-mel | Laporan diterima pada tarikh dijadualkan |
| TC-SEC-01 | Cuba capai tempahan orang lain dengan menukar pengenal dalam URL | Ralat 403 dipaparkan; percubaan direkod dalam log audit |
| TC-SEC-02 | Cuba capai endpoint pentadbiran sebagai kakitangan biasa | Ralat 403 dipaparkan |
| TC-SEC-03 | Hantar 6 percubaan log masuk gagal | Akaun disekat pada percubaan kelima |
| TC-SEC-04 | Biarkan sesi tidak aktif melebihi 30 minit | Sesi tamat; pengguna dialihkan ke skrin log masuk |
| TC-SEC-05 | Eksport data peribadi sendiri | Fail mengandungi hanya data pengguna tersebut |
| TC-SEC-06 | Cuba suntikan SQL pada medan carian | Input dikendalikan sebagai teks; tiada ralat pangkalan data terdedah |
| TC-SEC-07 | Cuba skrip merentas tapak pada medan keterangan tiket | Skrip dipaparkan sebagai teks biasa, tidak dilaksanakan |
| TC-SEC-08 | Semak mesej ralat pelayan | Tiada surih tindanan atau nama jadual didedahkan kepada pengguna |
| TC-AUD-01 | Kemas kini rekod aset dan semak log audit | Nilai sebelum dan selepas direkod dengan pengguna dan cap masa |
| TC-AUD-02 | Cuba padam entri log audit melalui antara muka | Tiada fungsi pemadaman tersedia bagi mana-mana peranan |
| TC-AUD-03 | Cari log audit mengikut pengguna dan julat tarikh | Hasil ditapis dengan betul |
| TC-PRF-01 | Carian bilik dengan 90,000 rekod tempahan | Kembali dalam bawah 2 saat pada persentil ke-95 |
| TC-PRF-02 | 100 pengguna serentak melakukan carian dan tempahan | Masa tindak balas tidak merosot melebihi 20% |
| TC-PRF-03 | Muat halaman kalendar mingguan | Selesai dalam bawah 3 saat |
| TC-PRF-04 | Import pukal 1,200 rekod aset | Selesai dalam bawah 5 minit |
| TC-PRF-05 | Ujian beban dengan 3 kali ganda data anggaran 5 tahun | Sasaran prestasi masih dipenuhi |
| TC-UI-01 | Selesaikan tempahan pada skrin 360 piksel | Tiada penatalan mendatar; semua butang boleh diketik |
| TC-UI-02 | Selesaikan laporan kerosakan pada telefon | Selesai dalam bawah 2 minit tanpa bantuan |
| TC-UI-03 | Semak semua teks antara muka dan e-mel | Semua dalam Bahasa Melayu yang betul tanpa teks lalai bahasa Inggeris |
| TC-A11Y-01 | Navigasi seluruh aliran tempahan menggunakan papan kekunci sahaja | Semua fungsi boleh dicapai; penunjuk fokus jelas |
| TC-A11Y-02 | Semak nisbah kontras semua kombinasi warna status | Semua sekurang-kurangnya 4.5 banding 1 |
| TC-CMP-01 | Uji aliran utama pada Chrome, Edge, Firefox dan Safari | Berfungsi sama pada keempat-empat pelayar |

### 4.8 Pembantu AI (M17 — di luar baseline)

Kes ujian berikut dilaksanakan secara automatik dalam `tests/Feature/Chatbot` (24 kes); API Gemini di"sandarkan" supaya ujian tidak memerlukan kunci sebenar.

| TC | Keadaan ujian | Hasil dijangka |
|---|---|---|
| TC-CHB-01 | Pengguna menghantar mesej pertama dalam perbualan baharu | Balasan dipaparkan; perbualan dinamakan daripada mesej; kedua-dua giliran tersimpan |
| TC-CHB-02 | Pengguna tanpa kebenaran chatbot membuka /chatbot | Akses dinafikan; tetapan chatbot hanya untuk pentadbir sistem |
| TC-CHB-03 | Sejarah melebihi had yang dikonfigurasi | Hanya n mesej terakhir dihantar sebagai konteks |
| TC-CHB-04 | Permintaan ke Gemini diperiksa | Kunci API, arahan sistem, sejarah dan parameter penjanaan dihantar dengan betul |
| TC-CHB-05 | API memulangkan ralat pelayan | Mesej ralat mesra; tiada mesej tersimpan |
| TC-CHB-06 | Chatbot dinyahaktifkan oleh pentadbir | Mesej dinyahaktifkan; tiada mesej tersimpan |
| TC-CHB-07 | Kunci API tidak dikonfigurasi | Mesej ralat konfigurasi; tiada mesej tersimpan |
| TC-CHB-08 | Padam perbualan milik sendiri | Perbualan dan semua mesejnya dipadam |
| TC-CHB-09 | Menghantar mesej / memadam perbualan pengguna lain | Dinafikan (404) |
| TC-CHB-10 | Pentadbir sistem membuka dan menyimpan tetapan baharu | Tetapan tersimpan dan perubahan direkod dalam jejak audit |
| TC-CHB-11 | Suhu melebihi 2 | Ditolak dengan mesej pengesahan; nilai lama kekal |
| TC-CHB-12 | Nama model mengandungi aksara URL | Ditolak dengan mesej pengesahan |
| TC-CHB-13 | Kotak semak aktif dinyahaktif semasa menyimpan | Chatbot menjadi tidak aktif |
| TC-CHB-14 | Mesej kosong atau melebihi 4,000 aksara | Ditolak dengan mesej pengesahan |
| TC-CHB-15 | Setiap satu daripada lapan peranan membuka /chatbot | Semua boleh mengakses (chatbot.guna diberi kepada semua) |

## 5. Skrip UAT

Skrip ini dijalankan oleh pengguna sebenar, bukan penguji teknikal. Setiap skrip ditulis dalam bahasa yang boleh diikuti tanpa pengetahuan teknikal.

### UAT-01 — Kakitangan menempah bilik (wajib)

**Peserta:** kakitangan umum. **Anggaran masa:** 10 minit.

| Langkah | Tindakan | Jangkaan | Lulus / Gagal | Catatan |
|---|---|---|---|---|
| 1 | Log masuk menggunakan akaun anda | Papan pemuka dipaparkan dengan nama anda | | |
| 2 | Klik Tempah Bilik | Skrin carian dipaparkan | | |
| 3 | Pilih tarikh esok, masa 10 pagi, tempoh 2 jam | Medan diisi | | |
| 4 | Klik Cari Bilik | Senarai bilik kosong dipaparkan dalam beberapa saat | | |
| 5 | Pilih satu bilik dan klik Tempah | Borang tempahan dipaparkan dengan butiran bilik | | |
| 6 | Isi tajuk mesyuarat dan bilangan peserta | Medan diterima | | |
| 7 | Klik Hantar Permohonan | Skrin pengesahan dengan nombor rujukan dipaparkan | | |
| 8 | Semak e-mel anda | E-mel pengesahan diterima dengan lampiran kalendar | | |
| 9 | Buka lampiran kalendar | Acara dimasukkan ke dalam kalendar peribadi anda | | |

**Soalan kepada peserta selepas skrip:** Berapa lama proses ini berbanding kaedah semasa? Adakah mana-mana langkah mengelirukan?

### UAT-02 — Tempahan bilik yang memerlukan kelulusan (wajib)

**Peserta:** kakitangan dan pelulus. **Anggaran masa:** 15 minit.

| Langkah | Pelaku | Tindakan | Jangkaan | Lulus / Gagal |
|---|---|---|---|---|
| 1 | Kakitangan | Tempah bilik yang bertanda perlu kelulusan | Penanda kelulusan dipaparkan sebelum borang diisi | |
| 2 | Kakitangan | Hantar permohonan | Status menunggu kelulusan dipaparkan | |
| 3 | Pelulus | Semak e-mel | Notifikasi permohonan diterima | |
| 4 | Pelulus | Buka senarai tugas kelulusan | Permohonan dipaparkan dengan butiran mencukupi | |
| 5 | Pelulus | Klik Lulus | Pengesahan dipaparkan | |
| 6 | Kakitangan | Semak e-mel | E-mel kelulusan dengan lampiran kalendar diterima | |
| 7 | Kakitangan | Semak Tempahan Saya | Status disahkan | |

### UAT-03 — Setiausaha membuat tempahan berulang (wajib)

**Peserta:** setiausaha. **Anggaran masa:** 15 minit.

| Langkah | Tindakan | Jangkaan | Lulus / Gagal |
|---|---|---|---|
| 1 | Pilih tempahan berulang | Borang siri dipaparkan | |
| 2 | Tetapkan mingguan pada hari Isnin, 3 bulan | Bilangan kejadian dipaparkan | |
| 3 | Pilih bilik dan masa | Sistem menyemak setiap tarikh | |
| 4 | Semak senarai konflik | Tarikh berkonflik disenaraikan dengan jelas | |
| 5 | Pilih langkau tarikh berkonflik dan hantar | Tempahan tercipta untuk tarikh yang tiada konflik | |
| 6 | Batalkan satu kejadian sahaja | Hanya kejadian tersebut dibatalkan; yang lain kekal | |
| 7 | Tempah bagi pihak seorang pegawai | Nama pegawai muncul sebagai tuan punya tempahan | |

### UAT-10 — Kakitangan melaporkan kerosakan (wajib)

**Peserta:** kakitangan umum, menggunakan telefon sendiri. **Anggaran masa:** 10 minit.

| Langkah | Tindakan | Jangkaan | Lulus / Gagal |
|---|---|---|---|
| 1 | Buka sistem pada telefon dan log masuk | Antara muka mudah alih dipaparkan dengan betul | |
| 2 | Klik Lapor Kerosakan | Pilihan imbas QR dipaparkan | |
| 3 | Imbas kod QR pada komputer anda | Maklumat peralatan dipaparkan dan diisi automatik | |
| 4 | Pilih jenis masalah dan taip keterangan | Medan diterima | |
| 5 | Ambil dan lampirkan foto | Foto dimuat naik | |
| 6 | Hantar aduan | Nombor rujukan dipaparkan serta-merta | |
| 7 | Semak e-mel | Pengesahan dengan nombor rujukan dan anggaran masa selesai diterima | |
| 8 | Buka Aduan Saya | Aduan disenaraikan dengan status semasa | |

### UAT-11 — Juruteknik melaksanakan pembaikan (wajib)

**Peserta:** juruteknik, menggunakan telefon kerja. **Anggaran masa:** 20 minit.

| Langkah | Tindakan | Jangkaan | Lulus / Gagal |
|---|---|---|---|
| 1 | Log masuk pada telefon | Papan pemuka juruteknik dipaparkan | |
| 2 | Buka Tugasan Saya | Senarai disusun mengikut keutamaan dan baki SLA | |
| 3 | Buka satu tiket | Butiran aset, pelapor dan baki SLA dipaparkan | |
| 4 | Klik Mula Kerja | Status bertukar kepada dalam tindakan | |
| 5 | Imbas QR aset di lokasi | Sistem mengesahkan aset yang betul | |
| 6 | Rekod diagnosis dan tindakan | Medan diterima | |
| 7 | Tambah alat ganti daripada stok | Baki stok berkurang secara automatik | |
| 8 | Ambil foto selepas pembaikan | Foto dimuat naik | |
| 9 | Klik Tanda Kerja Selesai | Status bertukar kepada menunggu pengesahan | |

**Soalan kepada peserta:** Berapa banyak ketikan diperlukan berbanding borang kertas semasa? Adakah skrin boleh dibaca dalam pencahayaan pejabat biasa?

### UAT-12 — Penyelia mengagih dan memantau SLA (wajib)

**Peserta:** penyelia ICT. **Anggaran masa:** 20 minit.

| Langkah | Tindakan | Jangkaan | Lulus / Gagal |
|---|---|---|---|
| 1 | Buka papan pemuka | Jumlah tiket mengikut status dipaparkan | |
| 2 | Semak senarai tiket hampir melanggar SLA | Tiket disenaraikan dengan baki masa | |
| 3 | Buka tiket baharu dan ubah keutamaan | Sasaran SLA dikira semula dan dipaparkan | |
| 4 | Agih tiket kepada juruteknik | Beban kerja setiap juruteknik dipaparkan semasa memilih | |
| 5 | Agih 3 tiket sekaligus | Semua diagihkan dalam satu tindakan | |
| 6 | Jana laporan SLA bulan lepas | Laporan dipaparkan dalam bawah 30 saat | |
| 7 | Eksport ke Excel | Fail dibuka dengan betul | |

### UAT-13 — Pegawai aset menguruskan inventori (wajib)

**Peserta:** pegawai aset. **Anggaran masa:** 25 minit.

| Langkah | Tindakan | Jangkaan | Lulus / Gagal |
|---|---|---|---|
| 1 | Daftar satu aset baharu | Nombor pendaftaran dijana mengikut format organisasi | |
| 2 | Muat naik fail Excel 20 baris dengan 3 baris salah | 17 disimpan; laporan ralat jelas menyatakan sebab bagi 3 baris | |
| 3 | Jana label QR untuk 10 aset | PDF label dijana dan boleh dicetak | |
| 4 | Cetak dan tampal satu label, kemudian imbas | Halaman aset yang betul terbuka | |
| 5 | Pindah aset ke pengguna lain | Sejarah merekod pemilik lama dan baharu | |
| 6 | Mohon pelupusan satu aset | Aliran kelulusan bermula | |
| 7 | Eksport senarai aset untuk pengauditan | Fail Excel mengandungi semua medan yang diperlukan | |

### UAT-14 — Pentadbir fasiliti mengurus bilik dan laporan (wajib)

**Peserta:** pentadbir fasiliti. **Anggaran masa:** 20 minit.

| Langkah | Tindakan | Jangkaan | Lulus / Gagal |
|---|---|---|---|
| 1 | Tambah bilik baharu dengan dua susun atur | Bilik disimpan dengan kapasiti berbeza setiap susun atur | |
| 2 | Tetapkan bilik memerlukan kelulusan | Tetapan disimpan dan berkuat kuasa serta-merta | |
| 3 | Nyahaktifkan bilik yang mempunyai tempahan akan datang | Senarai tempahan terjejas dipaparkan sebelum pengesahan | |
| 4 | Jana laporan penggunaan bulan lepas | Kadar penggunaan setiap bilik dipaparkan | |
| 5 | Tapis laporan mengikut bangunan | Hasil ditapis dengan betul | |
| 6 | Eksport ke Excel dan PDF | Kedua-dua format dijana dengan betul | |

## 6. Pengelasan dan Pengurusan Kecacatan

| Keutamaan | Takrif | Sasaran penyelesaian semasa UAT |
|---|---|---|
| Kritikal | Sistem tidak boleh digunakan, kehilangan data, atau kelemahan keselamatan | 1 hari bekerja |
| Tinggi | Fungsi utama tidak berfungsi tanpa jalan sementara | 2 hari bekerja |
| Sederhana | Fungsi tidak berfungsi tetapi ada jalan sementara | 5 hari bekerja |
| Rendah | Isu kosmetik atau kesulitan kecil | Selepas go-live |

Setiap kecacatan direkod dengan langkah untuk menghasilkan semula, hasil dijangka, hasil sebenar, tangkapan skrin, pelayar dan peranti. Kecacatan tanpa langkah penghasilan semula tidak boleh ditutup sebagai tidak dapat dihasilkan semula tanpa perbincangan dengan pelapor.

## 7. Borang Penerimaan

```
BORANG PENERIMAAN UJIAN PENERIMAAN PENGGUNA

Sistem      : SPFA — Sistem Pengurusan Fasiliti & Aset ICT
Fasa        : ______________________
Tempoh UAT  : ____________ hingga ____________

Ringkasan keputusan
  Jumlah skrip wajib          : ______
  Skrip lulus                 : ______
  Skrip gagal                 : ______
  Kecacatan kritikal terbuka  : ______
  Kecacatan tinggi terbuka    : ______
  Kecacatan sederhana terbuka : ______

Keputusan
  [ ] Diterima tanpa syarat
  [ ] Diterima dengan syarat, kecacatan berbaki diselesaikan mengikut
      jadual yang dilampirkan
  [ ] Tidak diterima, ujian semula diperlukan

Syarat atau catatan
  ____________________________________________________________
  ____________________________________________________________

Pemilik proses domain tempahan bilik
  Nama      : ______________________
  Tandatangan : ______________  Tarikh : ____________

Pemilik proses domain penyelenggaraan ICT
  Nama      : ______________________
  Tandatangan : ______________  Tarikh : ____________

Pengurus Projek
  Nama      : ______________________
  Tandatangan : ______________  Tarikh : ____________
```
