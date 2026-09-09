# 14 — Daftar Risiko

| Perkara | Butiran |
|---|---|
| Dokumen | Risk Register |
| Sistem | SPFA — Sistem Pengurusan Fasiliti & Aset ICT |
| Versi | 1.0 (Draf) |
| Tarikh | 7 September 2026 |
| Kekerapan semakan | Dwimingguan semasa pembangunan, bulanan selepas go-live |

---

## 1. Kaedah Penilaian

**Kebarangkalian:** Rendah kurang 30%, Sederhana 30% hingga 60%, Tinggi melebihi 60%.

**Kesan:** Rendah menjejaskan jadual kurang seminggu, Sederhana satu hingga tiga minggu atau mengurangkan skop, Tinggi melebihi tiga minggu atau menggagalkan objektif projek.

**Aras risiko:** hasil gabungan kebarangkalian dan kesan, digunakan untuk menentukan keutamaan perhatian pengurusan.

| | Kesan Rendah | Kesan Sederhana | Kesan Tinggi |
|---|---|---|---|
| **Kebarangkalian Tinggi** | Sederhana | Tinggi | Kritikal |
| **Kebarangkalian Sederhana** | Rendah | Sederhana | Tinggi |
| **Kebarangkalian Rendah** | Rendah | Rendah | Sederhana |

## 2. Risiko Projek

### RSK-01 — Kadar penerimaan pengguna rendah

| Perkara | Butiran |
|---|---|
| Kategori | Organisasi |
| Kebarangkalian | Tinggi |
| Kesan | Tinggi |
| Aras | **Kritikal** |
| Pemilik | Penaja projek |

**Keterangan.** Kakitangan terus menggunakan e-mel dan telefon untuk tempahan dan aduan kerana kaedah lama lebih biasa. Sistem menjadi tempat rekod kedua yang diisi selepas fakta, atau tidak diisi langsung. Ini adalah risiko yang paling kerap menggagalkan sistem dalaman, dan ia bukan risiko teknikal.

**Mitigasi.**
- Pekeliling rasmi yang menetapkan tarikh selepas mana sistem adalah satu-satunya saluran sah.
- Reka bentuk yang menjadikan sistem lebih pantas daripada menghantar e-mel. Jika menempah mengambil masa lebih lama daripada menaip e-mel kepada setiausaha, kakitangan akan memilih e-mel.
- Juara pengguna di setiap bahagian.
- Pengukuran kadar penggunaan bulanan dan tindakan susulan terhadap bahagian yang ketinggalan.

**Petunjuk awal.** Kadar tempahan melalui sistem kurang 50% selepas bulan pertama.

### RSK-02 — Data aset sedia ada tidak lengkap atau tidak tepat

| Perkara | Butiran |
|---|---|
| Kategori | Data |
| Kebarangkalian | Tinggi |
| Kesan | Sederhana |
| Aras | **Tinggi** |
| Pemilik | Pegawai aset |

**Keterangan.** Fail Excel sedia ada mengandungi nombor siri yang hilang, lokasi dalam teks bebas yang tidak konsisten, dan aset yang telah lama dilupuskan tetapi masih tersenarai. Migrasi data buruk menghasilkan sistem yang tidak dipercayai, dan kepercayaan sukar dipulihkan.

**Mitigasi.**
- Pembersihan data dimulakan tiga minggu sebelum pembangunan modul aset, bukan pada minggu migrasi.
- Import percubaan berulang dengan laporan ralat terperinci setiap baris.
- Pengauditan fizikal sampel selepas migrasi.
- Terima data tidak lengkap pada medan deskriptif; jangan terima data salah pada medan pengenal.

### RSK-03 — Wakil pengguna tidak tersedia untuk pengesahan dan UAT

| Perkara | Butiran |
|---|---|
| Kategori | Sumber |
| Kebarangkalian | Tinggi |
| Kesan | Sederhana |
| Aras | **Tinggi** |
| Pemilik | Pengurus projek |

**Keterangan.** Pengguna mempunyai kerja harian mereka sendiri. Bengkel pengesahan keperluan dan sesi UAT tertangguh kerana peserta tidak dapat hadir, dan keputusan dibuat oleh pasukan projek tanpa input pengguna sebenar.

**Mitigasi.**
- Komitmen masa diperoleh secara bertulis daripada ketua bahagian pada awal projek, bukan diminta apabila diperlukan.
- Sesi dijadualkan sekurang-kurangnya tiga minggu lebih awal.
- Pengganti dinamakan untuk setiap wakil.
- Keputusan yang tertangguh melebihi satu minggu dinaikkan kepada penaja projek.

### RSK-04 — Keadaan perlumbaan menghasilkan tempahan bertindih

| Perkara | Butiran |
|---|---|
| Kategori | Teknikal |
| Kebarangkalian | Sederhana |
| Kesan | Tinggi |
| Aras | **Tinggi** |
| Pemilik | Ketua teknikal |

**Keterangan.** Dua pengguna menghantar permohonan untuk slot yang sama dalam beberapa milisaat. Semakan ketersediaan di lapisan aplikasi lulus bagi kedua-duanya, dan dua tempahan bertindih tercipta. Kegagalan ini merosakkan kepercayaan pengguna terhadap seluruh sistem, kerana ia menyentuh janji asasnya.

**Mitigasi.**
- Kekangan pengecualian pada aras pangkalan data, bukan semakan aplikasi sahaja.
- Ujian keserentakan dengan 50 permintaan serentak sebagai sebahagian daripada kriteria penerimaan modul, bukan ujian tambahan.
- Pengendalian ralat yang menukar konflik menjadi cadangan slot alternatif, supaya pengalaman pengguna kekal munasabah walaupun konflik berlaku.

### RSK-05 — Skop merebak semasa pembangunan

| Perkara | Butiran |
|---|---|
| Kategori | Pengurusan |
| Kebarangkalian | Tinggi |
| Kesan | Sederhana |
| Aras | **Tinggi** |
| Pemilik | Pengurus projek |

**Keterangan.** Semasa demonstrasi, pengguna mencadangkan ciri tambahan yang munasabah secara individu tetapi secara kumulatif menambah berbulan-bulan kerja. Contoh yang sering timbul: integrasi tempat letak kereta, papan tanda digital, ramalan kerosakan.

**Mitigasi.**
- Senarai perkara di luar skop didokumenkan dengan sebab dalam dokumen 00 dan 03, supaya perbincangan tidak berulang.
- Setiap permohonan perubahan dinilai kesannya menggunakan matriks kebolehjejakan sebelum diterima.
- Cadangan yang bernilai dimasukkan ke dalam senarai fasa akan datang, bukan ditolak terus. Ini mengekalkan sokongan pengguna tanpa menjejaskan jadual.

### RSK-06 — Sasaran SLA ditetapkan tanpa data asas

| Perkara | Butiran |
|---|---|
| Kategori | Perniagaan |
| Kebarangkalian | Sederhana |
| Kesan | Sederhana |
| Aras | **Sederhana** |
| Pemilik | Ketua unit ICT |

**Keterangan.** Sasaran SLA ditetapkan berdasarkan aspirasi, bukan keupayaan sebenar unit ICT. Selepas go-live, kadar pelanggaran tinggi, dan pasukan mula mempersoalkan sistem, bukan proses. Alternatifnya, sasaran ditetapkan terlalu longgar dan tidak memacu penambahbaikan.

**Mitigasi.**
- Sasaran permulaan ditetapkan secara konservatif dan disemak selepas tiga bulan data sebenar.
- Sasaran boleh dikonfigurasi tanpa perubahan kod, jadi pelarasan mudah dilakukan.
- Laporan pertama dibaca sebagai penetapan garis dasar, bukan penilaian prestasi.

### RSK-07 — Kod QR rosak, hilang atau tidak boleh diimbas

| Perkara | Butiran |
|---|---|
| Kategori | Operasi |
| Kebarangkalian | Sederhana |
| Kesan | Rendah |
| Aras | **Rendah** |
| Pemilik | Pegawai aset |

**Keterangan.** Label tertanggal, terkoyak, atau ditampal di tempat yang sukar diimbas. Juruteknik kembali kepada carian manual, dan faedah imbasan hilang.

**Mitigasi.**
- Label bahan tahan lasak dengan pelekat kuat.
- Nombor pendaftaran dicetak dalam teks pada label yang sama, supaya carian manual masih pantas jika imbasan gagal.
- Prosedur penggantian label yang mudah, dan semakan label semasa penyelenggaraan preventif.

### RSK-08 — Kegagalan integrasi dengan direktori pengguna organisasi

| Perkara | Butiran |
|---|---|
| Kategori | Teknikal |
| Kebarangkalian | Sederhana |
| Kesan | Sederhana |
| Aras | **Sederhana** |
| Pemilik | Ketua teknikal |

**Keterangan.** Akses ke direktori tidak diperoleh tepat pada masa, atau konfigurasinya berbeza daripada yang dijangka. Log masuk tidak berfungsi seperti dirancang.

**Mitigasi.**
- Akaun tempatan dibina sebagai sandaran penuh sejak awal, bukan sebagai tampalan tergesa-gesa.
- Ujian integrasi direktori dijadualkan pada minggu kedua Fasa 1, bukan pada minggu terakhir.
- Sekurang-kurangnya satu akaun pentadbir tempatan sentiasa aktif untuk pemulihan.

### RSK-09 — Prestasi merosot apabila data bertambah

| Perkara | Butiran |
|---|---|
| Kategori | Teknikal |
| Kebarangkalian | Sederhana |
| Kesan | Sederhana |
| Aras | **Sederhana** |
| Pemilik | Ketua teknikal |

**Keterangan.** Carian bilik dan paparan kalendar menjadi perlahan selepas dua tahun data terkumpul. Log audit tumbuh sehingga menjejaskan prestasi keseluruhan pangkalan data.

**Mitigasi.**
- Ujian beban menggunakan data isipadu lima tahun, bukan data ujian kecil.
- Indeks direka semasa reka bentuk pangkalan data, bukan ditambah selepas aduan.
- Jadual log audit dipartisi mengikut bulan sejak permulaan.
- Pemantauan masa tindak balas dengan ambang amaran.

### RSK-10 — Kelemahan keselamatan ditemui lewat

| Perkara | Butiran |
|---|---|
| Kategori | Keselamatan |
| Kebarangkalian | Sederhana |
| Kesan | Tinggi |
| Aras | **Tinggi** |
| Pemilik | Ketua teknikal |

**Keterangan.** Ujian penembusan dijalankan seminggu sebelum go-live dan menemui kelemahan yang memerlukan perubahan seni bina. Pilihan yang tinggal adalah menangguhkan go-live atau melepaskan sistem dengan risiko yang diketahui.

**Mitigasi.**
- Semakan keselamatan dijalankan pada akhir setiap fasa, bukan sekali sahaja sebelum go-live.
- Senarai semak keselamatan dalam dokumen 08 disemak semasa semakan kod.
- Imbasan kerentanan kebergantungan berjalan automatik dalam saluran integrasi berterusan.
- Ujian penembusan dijadualkan sekurang-kurangnya empat minggu sebelum go-live.

### RSK-11 — Kehilangan kakitangan projek utama

| Perkara | Butiran |
|---|---|
| Kategori | Sumber |
| Kebarangkalian | Rendah |
| Kesan | Tinggi |
| Aras | **Sederhana** |
| Pemilik | Pengurus projek |

**Keterangan.** Ketua teknikal atau penganalisis perniagaan meninggalkan projek. Pengetahuan yang tidak didokumenkan hilang bersama mereka.

**Mitigasi.**
- Keputusan reka bentuk didokumenkan beserta sebabnya, bukan hanya hasilnya.
- Semakan kod berpasangan supaya sekurang-kurangnya dua orang memahami setiap bahagian.
- Dokumentasi dikemas kini seiring pembangunan, bukan pada penghujung projek.

### RSK-12 — Notifikasi e-mel disekat atau masuk ke folder spam

| Perkara | Butiran |
|---|---|
| Kategori | Teknikal |
| Kebarangkalian | Sederhana |
| Kesan | Sederhana |
| Aras | **Sederhana** |
| Pemilik | Pentadbir sistem |

**Keterangan.** E-mel pengesahan dan peringatan tidak sampai kepada pengguna. Mereka terlepas mesyuarat dan menganggap sistem tidak berfungsi.

**Mitigasi.**
- Konfigurasi rekod pengesahan e-mel domain diselesaikan sebelum go-live.
- Notifikasi dalam aplikasi sebagai saluran kedua yang tidak bergantung kepada e-mel.
- Pemantauan kadar kegagalan penghantaran dengan amaran.
- Ujian penghantaran kepada semua domain penerima semasa UAT.

### RSK-13 — Cetakan dan penampalan label QR tertangguh

| Perkara | Butiran |
|---|---|
| Kategori | Operasi |
| Kebarangkalian | Sederhana |
| Kesan | Rendah |
| Aras | **Rendah** |
| Pemilik | Ketua unit ICT |

**Keterangan.** Kerja menampal label pada 1,200 aset memerlukan masa yang tidak dianggarkan dengan betul. Modul aset dilepaskan tetapi imbasan tidak berfungsi kerana label belum ada.

**Mitigasi.**
- Kerja penampalan dijadualkan sebagai aktiviti projek dengan sumber yang diperuntukkan, bukan diandaikan berlaku sendiri.
- Penampalan bermula dengan aset yang paling kerap bermasalah, iaitu komputer dan pencetak di kawasan operasi utama.
- Carian manual kekal berfungsi sepenuhnya sebagai alternatif.

### RSK-14 — Kekaburan tafsiran dokumen keperluan

| Perkara | Butiran |
|---|---|
| Kategori | Pengurusan |
| Kebarangkalian | Sederhana |
| Kesan | Sederhana |
| Aras | **Sederhana** |
| Pemilik | Penganalisis perniagaan |

**Keterangan.** Keperluan yang boleh ditafsir dengan dua cara dibina mengikut tafsiran pembangun, dan pengguna mendapati ia salah semasa UAT. Pembetulan pada peringkat itu adalah sepuluh kali lebih mahal berbanding semasa fasa keperluan.

**Mitigasi.**
- Isu terbuka disenaraikan secara eksplisit dalam SRS dengan tarikh keputusan yang diperlukan.
- Prototaip boleh klik disahkan oleh pengguna sebelum pembangunan bermula.
- Demonstrasi dwimingguan kepada wakil pengguna sepanjang pembangunan, bukan hanya pada UAT.

## 3. Ringkasan Mengikut Aras

| Aras | Bilangan | ID |
|---|---|---|
| Kritikal | 1 | RSK-01 |
| Tinggi | 5 | RSK-02, RSK-03, RSK-04, RSK-05, RSK-10 |
| Sederhana | 6 | RSK-06, RSK-08, RSK-09, RSK-11, RSK-12, RSK-14 |
| Rendah | 2 | RSK-07, RSK-13 |

Jumlah 14 risiko dikenal pasti.

## 4. Risiko Utama untuk Perhatian Pengurusan

Tiga perkara yang paling berkemungkinan menggagalkan projek ini bukan perkara teknikal.

1. **Penerimaan pengguna (RSK-01).** Sistem yang sempurna secara teknikal tetapi tidak digunakan adalah kegagalan penuh. Ini memerlukan keputusan pentadbiran, bukan penyelesaian kejuruteraan.
2. **Kualiti data aset (RSK-02).** Sistem inventori hanya sebaik data di dalamnya. Kerja pembersihan mesti bermula awal dan dimiliki oleh pegawai aset, bukan pasukan projek.
3. **Ketersediaan wakil pengguna (RSK-03).** Keputusan yang dibuat tanpa pengguna akan diperbetulkan semula selepas UAT, pada kos yang jauh lebih tinggi.

## 5. Templat Kemas Kini Risiko

Setiap semakan dwimingguan mengemas kini jadual berikut.

| ID | Aras semasa | Perubahan sejak semakan lepas | Tindakan mitigasi diambil | Tindakan seterusnya | Pemilik | Tarikh semakan |
|---|---|---|---|---|---|---|
| | | | | | | |
