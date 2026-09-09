# 02 — BRS: Business Requirements Specification

| Perkara | Butiran |
|---|---|
| Dokumen | Business Requirements Specification (BRS) |
| Sistem | SPFA — Sistem Pengurusan Fasiliti & Aset ICT |
| Versi | 1.0 (Draf) |
| Tarikh | 7 September 2026 |
| Pelulus | Penaja projek, pemilik proses domain A dan domain B |

---

## 1. Tujuan Dokumen

Dokumen ini menyatakan **apa yang organisasi mahu capai** melalui sistem ini, dinyatakan dalam bahasa perniagaan tanpa merujuk teknologi. Ia menjadi rujukan untuk menilai sama ada pelaburan projek berbaloi, dan menjadi punca kepada semua keperluan pengguna dan keperluan perisian dalam dokumen berikutnya.

## 2. Keadaan Semasa dan Keadaan Sasaran

### Domain A — Tempahan bilik mesyuarat

| Aspek | Keadaan semasa | Keadaan sasaran |
|---|---|---|
| Saluran tempahan | E-mel, telefon, buku log kaunter | Satu portal berpusat |
| Semakan ketersediaan | Manual oleh setiausaha | Serta-merta oleh sistem |
| Pertindihan tempahan | Kerap, diselesaikan secara rundingan | Mustahil secara reka bentuk |
| Bilik ditempah tanpa digunakan | Tidak diukur | Dikesan dan dilepaskan automatik |
| Data penggunaan | Tiada | Laporan penggunaan setiap bilik |

### Domain B — Penyelenggaraan peralatan ICT

| Aspek | Keadaan semasa | Keadaan sasaran |
|---|---|---|
| Saluran aduan | Panggilan telefon, aduan lisan | Tiket bernombor rujukan |
| Rekod aset | Beberapa fail Excel tidak selaras | Satu daftar induk berpusat |
| Pengenalan aset | Cari manual berdasarkan ingatan | Imbas kod QR |
| Ukuran prestasi | Tiada | SLA, purata masa pemulihan, kadar kerosakan |
| Penyelenggaraan pencegahan | Tidak dijadualkan | Perintah kerja dijana automatik |
| Waranti | Tidak dijejaki | Amaran sebelum tamat tempoh |

## 3. Keperluan Perniagaan

Setiap keperluan diberi keutamaan mengikut kaedah MoSCoW: **M** wajib, **S** sepatutnya, **C** boleh, **W** tidak buat kali ini.

### 3.1 Keperluan merentas domain

| ID | Keperluan perniagaan | Keutamaan | Objektif berkait |
|---|---|---|---|
| BR-01 | Organisasi mesti mempunyai satu sistem tunggal untuk kedua-dua fungsi supaya kakitangan tidak perlu mempelajari dua aplikasi berasingan | M | OBJ-01, OBJ-03 |
| BR-02 | Setiap pengguna log masuk menggunakan identiti organisasi sedia ada, tanpa kata laluan baharu | S | Penerimaan pengguna |
| BR-03 | Setiap tindakan penting mesti meninggalkan jejak audit yang boleh diperiksa | M | Keperluan audit dalaman |
| BR-04 | Sistem mesti boleh diakses melalui telefon pintar tanpa pemasangan aplikasi | M | Penerimaan pengguna |
| BR-05 | Pengurusan mesti dapat menjana laporan sendiri tanpa bantuan unit ICT | M | OBJ-06 |
| BR-06 | Data peribadi kakitangan mesti dilindungi mengikut Akta Perlindungan Data Peribadi | M | Pematuhan |
| BR-07 | Sistem mesti berfungsi dalam Bahasa Melayu sebagai bahasa utama | M | Dasar bahasa |

### 3.2 Domain A — Tempahan bilik mesyuarat

| ID | Keperluan perniagaan | Keutamaan | Objektif berkait |
|---|---|---|---|
| BR-10 | Kakitangan boleh melihat ketersediaan semua bilik mesyuarat dan menempah sendiri tanpa perantara | M | OBJ-01 |
| BR-11 | Sistem mesti menghalang dua tempahan bertindih pada bilik dan masa yang sama | M | OBJ-01 |
| BR-12 | Bilik tertentu yang terhad penggunaannya mesti melalui kelulusan sebelum disahkan | M | Kawalan pentadbiran |
| BR-13 | Tempahan berulang mingguan atau bulanan boleh dibuat sekali sahaja | S | Kecekapan setiausaha |
| BR-14 | Bilik yang ditempah tetapi tidak digunakan mesti dilepaskan supaya orang lain boleh menggunakannya | S | OBJ-02 |
| BR-15 | Peserta mesyuarat menerima jemputan yang boleh dimasukkan ke dalam kalendar peribadi mereka | S | Kemudahan pengguna |
| BR-16 | Setiausaha boleh menempah bagi pihak pegawai atasan | M | Realiti operasi |
| BR-17 | Permintaan sokongan seperti susun atur bilik dan minuman boleh dibuat bersama tempahan | C | Kecekapan operasi |
| BR-18 | Pengurusan boleh melihat kadar penggunaan setiap bilik untuk merancang keperluan ruang | M | OBJ-06 |
| BR-19 | Kakitangan boleh membatalkan tempahan sendiri sebelum tempahan diluluskan atau disahkan, supaya slot dilepaskan untuk kegunaan lain | M | OBJ-01 |

### 3.3 Domain B — Penyelenggaraan peralatan ICT

| ID | Keperluan perniagaan | Keutamaan | Objektif berkait |
|---|---|---|---|
| BR-20 | Setiap peralatan ICT mesti mempunyai rekod tunggal yang mengandungi maklumat perolehan, lokasi dan pengguna bertanggungjawab | M | OBJ-03 |
| BR-21 | Peralatan mesti boleh dikenal pasti di lapangan dalam masa beberapa saat | M | Kecekapan juruteknik |
| BR-22 | Kakitangan boleh melaporkan kerosakan dan menerima nombor rujukan serta-merta | M | OBJ-04 |
| BR-23 | Setiap aduan mesti mempunyai pemilik tugas yang jelas pada bila-bila masa | M | Akauntabiliti |
| BR-24 | Tempoh pemulihan mesti diukur berbanding sasaran yang ditetapkan mengikut keutamaan | M | OBJ-04 |
| BR-25 | Aduan yang hampir melebihi sasaran mesti dinaikkan kepada penyelia secara automatik | S | OBJ-04 |
| BR-26 | Penyelenggaraan pencegahan mesti dijadualkan dan pelaksanaannya dijejaki | S | OBJ-05 |
| BR-27 | Status waranti mesti disemak sebelum kerja pembaikan berbayar dilakukan | M | Penjimatan kos |
| BR-28 | Kos alat ganti dan kos pembaikan mesti dikumpul mengikut aset | S | Keputusan gantian aset |
| BR-29 | Pergerakan dan pertukaran pemilik aset mesti direkod supaya pengauditan aset boleh dijalankan | M | Pematuhan aset |
| BR-30 | Pengurusan boleh melihat aset mana yang paling kerap rosak untuk memandu keputusan perolehan | S | OBJ-06 |
| BR-31 | Prestasi vendor penyelenggaraan boleh dinilai berdasarkan data sebenar | C | Kawalan kontrak |

## 4. Peraturan Perniagaan

Peraturan ini mengikat, dan pelanggarannya mesti dihalang oleh sistem, bukan sekadar diberi amaran.

| ID | Peraturan |
|---|---|
| BRL-01 | Satu bilik hanya boleh mempunyai satu tempahan aktif pada mana-mana julat masa yang bertindih |
| BRL-02 | Tempahan tidak boleh dibuat pada masa yang telah berlalu |
| BRL-03 | Tempahan hanya dibenarkan dalam waktu operasi bilik, kecuali diluluskan pentadbir fasiliti |
| BRL-04 | Tempahan boleh dibuat paling awal 90 hari ke hadapan bagi pengguna biasa |
| BRL-05 | Pembatalan kurang daripada 2 jam sebelum masa mula direkod sebagai pembatalan lewat |
| BRL-06 | Tempahan yang tidak didaftar masuk dalam 15 minit selepas masa mula akan dilepaskan |
| BRL-07 | Bilangan peserta tidak boleh melebihi kapasiti bilik bagi susun atur yang dipilih |
| BRL-08 | Setiap aset mesti mempunyai nombor pendaftaran yang unik dan tidak boleh diguna semula |
| BRL-09 | Aset yang berstatus dilupuskan tidak boleh menjadi subjek tiket baharu |
| BRL-10 | Tiket hanya boleh ditutup selepas pengesahan pengguna pelapor, atau selepas 3 hari bekerja tanpa maklum balas |
| BRL-11 | Keutamaan tiket menentukan sasaran SLA dan tidak boleh diubah selepas tiket ditutup |
| BRL-12 | Kerja pembaikan berbayar terhadap aset dalam waranti memerlukan justifikasi bertulis |
| BRL-13 | Pengeluaran alat ganti mesti dikaitkan dengan satu tiket atau perintah kerja |
| BRL-14 | Tempahan berstatus menunggu kelulususan boleh dibatalkan oleh penempah atau tuan punya pada bila-bila masa sebelum keputusan kelulususan dibuat, dan slot dibebaskan serta-merta |

## 5. Petunjuk Prestasi Utama

| KPI | Formula | Sasaran | Kekerapan |
|---|---|---|---|
| Kadar penggunaan bilik | Jam ditempah dan hadir dibahagi jam tersedia | 55% hingga 75% | Bulanan |
| Kadar tidak hadir | Tempahan tanpa daftar masuk dibahagi jumlah tempahan | Bawah 10% | Bulanan |
| Kadar tempahan kendiri | Tempahan dibuat sendiri dibahagi jumlah tempahan | Melebihi 80% | Bulanan |
| Pematuhan SLA tiket | Tiket ditutup dalam sasaran dibahagi jumlah tiket ditutup | Melebihi 90% | Bulanan |
| Purata masa pemulihan | Jumlah masa pemulihan dibahagi bilangan tiket ditutup | Mengikut keutamaan | Bulanan |
| Kadar penyelesaian kali pertama | Tiket selesai tanpa agihan semula dibahagi jumlah tiket | Melebihi 70% | Bulanan |
| Pematuhan penyelenggaraan preventif | Perintah kerja siap dibahagi perintah kerja dijadualkan | Melebihi 95% | Suku tahunan |
| Ketepatan inventori | Aset disahkan semasa audit dibahagi rekod aset | Melebihi 98% | Tahunan |

## 6. Sasaran SLA Cadangan

| Keutamaan | Takrif | Masa tindak balas | Masa pemulihan |
|---|---|---|---|
| P1 Kritikal | Perkhidmatan terhenti bagi banyak pengguna, contohnya pelayan cetak utama gagal | 30 minit | 4 jam bekerja |
| P2 Tinggi | Seorang pengguna tidak dapat bekerja langsung, contohnya komputer tidak boleh dihidupkan | 2 jam | 1 hari bekerja |
| P3 Sederhana | Kerja terjejas tetapi ada jalan sementara, contohnya pencetak jam kertas berulang | 4 jam | 3 hari bekerja |
| P4 Rendah | Permintaan kecil atau gangguan minor, contohnya tetikus perlu diganti | 1 hari bekerja | 5 hari bekerja |

Sasaran ini adalah cadangan permulaan. Nilai muktamad ditetapkan dalam modul konfigurasi dan boleh diubah tanpa perubahan kod.

## 7. Kebergantungan Organisasi

Perkara berikut berada di luar kawalan pasukan projek tetapi menentukan kejayaannya.

| Kebergantungan | Pemilik | Kesan jika lewat |
|---|---|---|
| Senarai bilik dan kapasiti yang disahkan | Pentadbir fasiliti | Modul bilik tidak boleh diuji |
| Data aset ICT sedia ada dibersihkan | Pegawai aset | Migrasi data tertangguh |
| Akses ke direktori pengguna organisasi | Unit rangkaian | Log masuk terpaksa menggunakan akaun tempatan |
| Kelulusan sasaran SLA | Ketua unit ICT | Modul tiket tidak boleh dikonfigurasi |
| Label kod QR dicetak dan ditampal | Unit ICT | Kelebihan imbasan tidak dapat direalisasikan |
| Pekeliling penggunaan sistem kepada semua kakitangan | Pentadbiran | Kadar penerimaan rendah |

## 8. Pengurusan Perubahan Organisasi

Sistem ini mengubah cara kerja harian dua kumpulan pengguna. Perkara berikut perlu dirancang seiring dengan pembangunan.

1. **Pekeliling rasmi** yang menetapkan sistem sebagai satu-satunya saluran sah untuk tempahan bilik selepas tarikh tertentu. Tanpa ini, saluran lama akan kekal digunakan.
2. **Latihan mengikut peranan**, bukan latihan umum. Kakitangan biasa memerlukan 20 minit. Juruteknik dan pentadbir memerlukan sesi penuh.
3. **Tempoh berjalan seiring** selama satu bulan, di mana kedua-dua kaedah lama dan baharu diterima, untuk mengumpul isu sebenar.
4. **Juara pengguna** di setiap bahagian yang boleh membantu rakan sekerja tanpa perlu menghubungi meja bantuan.
