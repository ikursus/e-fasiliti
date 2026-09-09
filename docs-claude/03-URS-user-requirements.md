# 03 — URS: User Requirements Specification

| Perkara | Butiran |
|---|---|
| Dokumen | User Requirements Specification (URS) |
| Sistem | SPFA — Sistem Pengurusan Fasiliti & Aset ICT |
| Versi | 1.0 (Draf) |
| Tarikh | 7 September 2026 |
| Untuk disahkan oleh | Wakil pengguna setiap persona |

---

## 1. Tujuan Dokumen

Dokumen ini menyatakan keperluan dari sudut pandangan pengguna, dalam bahasa harian mereka. Ia ditulis untuk disemak dan disahkan oleh pengguna sebenar, bukan oleh pasukan teknikal. Setiap keperluan pengguna berpunca daripada satu atau lebih keperluan perniagaan dalam BRS, dan akan diterjemahkan kepada keperluan fungsian dalam SRS.

## 2. Persona Pengguna

### P1 — Kakitangan Umum (Ahmad, Pegawai Tadbir)
Menempah bilik beberapa kali sebulan untuk perbincangan kecil. Melaporkan kerosakan komputer sendiri apabila berlaku. Tidak mahu belajar sistem yang rumit. Menggunakan telefon pintar untuk kebanyakan urusan.

**Keperluan utama:** tempah dalam bawah satu minit, tahu status aduan tanpa perlu bertanya.

### P2 — Setiausaha / Pembantu Tadbir (Siti)
Menempah bilik bagi pihak beberapa pegawai setiap minggu, termasuk mesyuarat berulang. Menguruskan susun atur bilik dan minuman. Perlu melihat kalendar penuh, bukan sekadar bilik kosong.

**Keperluan utama:** tempah bagi pihak orang lain, tempahan berulang, ubah tempahan dengan cepat.

### P3 — Pelulus (Encik Rahman, Ketua Bahagian)
Meluluskan tempahan bilik utama dan bilik VIP. Tidak mahu log masuk semata-mata untuk meluluskan sesuatu; mahu buat melalui e-mel jika boleh.

**Keperluan utama:** senarai tugas kelulusan yang jelas, keputusan pantas, tiada tempahan tersekat kerana beliau bercuti.

### P4 — Pentadbir Fasiliti (Puan Lina)
Memiliki proses tempahan bilik. Menetapkan bilik mana boleh ditempah siapa, waktu operasi, dan peraturan. Perlu laporan penggunaan untuk mesyuarat pengurusan.

**Keperluan utama:** kawalan penuh ke atas katalog bilik dan peraturan, laporan penggunaan tanpa kerja manual.

### P5 — Juruteknik ICT (Faiz)
Menerima tugas pembaikan, bergerak dari satu tingkat ke tingkat lain. Menggunakan telefon di lapangan. Membenci kerja perkeranian yang berulang.

**Keperluan utama:** senarai tugas hari ini, imbas QR untuk buka rekod aset, kemas kini status dalam beberapa ketikan sahaja.

### P6 — Penyelia ICT (Encik Zul)
Mengagihkan tugas, memantau SLA, dan menjawab kepada pengurusan. Perlu tahu tiket mana hampir melebihi sasaran sebelum ia berlaku.

**Keperluan utama:** papan pemuka beban kerja dan SLA, pengagihan tugas pantas, laporan bulanan sedia guna.

### P7 — Pegawai Aset (Puan Nor)
Bertanggungjawab ke atas ketepatan daftar aset, pengauditan tahunan, pelupusan, dan penyelarasan dengan rekod kewangan.

**Keperluan utama:** daftar aset lengkap, sejarah pergerakan, senarai untuk pengauditan fizikal, proses pelupusan berperingkat.

### P8 — Pentadbir Sistem (Unit ICT)
Menyelenggara akaun pengguna, peranan, konfigurasi dan pemulihan sistem.

**Keperluan utama:** konfigurasi tanpa menulis kod, jejak audit, alat sandaran dan pemulihan.

---

## 3. Keperluan Pengguna

Format: **UR-nn** — sebagai [persona], saya perlu [keperluan], supaya [manfaat]. Setiap keperluan mempunyai kriteria terima yang boleh diuji.

### 3.1 Tempahan bilik mesyuarat

#### UR-01 Melihat ketersediaan bilik
Sebagai kakitangan, saya perlu melihat bilik mana kosong pada tarikh dan masa yang saya mahu, supaya saya tidak perlu bertanya kepada sesiapa.

**Kriteria terima:** Selepas memilih tarikh, masa mula dan tempoh, sistem memaparkan hanya bilik yang benar-benar kosong. Bilik yang telah ditempah tidak muncul dalam senarai. Paparan mengambil masa bawah 2 saat.
**Berpunca daripada:** BR-10.

#### UR-02 Menempah bilik dalam satu minit
Sebagai kakitangan, saya perlu menyiapkan tempahan mudah dalam bawah satu minit, supaya ia tidak menjadi beban.

**Kriteria terima:** Tempahan mudah selesai dalam maksimum 5 skrin atau langkah, dengan medan wajib tidak melebihi enam: tarikh, masa mula, tempoh, bilik, tajuk mesyuarat, bilangan peserta.
**Berpunca daripada:** BR-10.

#### UR-03 Menapis bilik mengikut keperluan
Sebagai kakitangan, saya perlu menapis bilik mengikut kapasiti, lokasi dan kemudahan seperti projektor atau sistem persidangan video, supaya saya memilih bilik yang sesuai.

**Kriteria terima:** Penapis kapasiti minimum, bangunan, tingkat dan kemudahan tersedia dan boleh digabungkan. Hasil dikemas kini tanpa memuat semula halaman.
**Berpunca daripada:** BR-10.

#### UR-04 Menempah bagi pihak orang lain
Sebagai setiausaha, saya perlu membuat tempahan bagi pihak pegawai saya, supaya rekod menunjukkan pegawai tersebut sebagai tuan punya mesyuarat.

**Kriteria terima:** Medan "tempah bagi pihak" tersedia untuk peranan yang dibenarkan. Tempahan memaparkan kedua-dua nama penempah dan tuan punya. Notifikasi dihantar kepada kedua-duanya.
**Berpunca daripada:** BR-16.

#### UR-05 Tempahan berulang
Sebagai setiausaha, saya perlu menempah mesyuarat mingguan sekali sahaja untuk beberapa bulan, supaya saya tidak mengulang kerja yang sama.

**Kriteria terima:** Corak harian, mingguan pada hari tertentu, dan bulanan disokong. Sistem memaparkan tarikh yang berkonflik sebelum pengesahan dan membenarkan pengguna melangkau tarikh tersebut. Pembatalan boleh dibuat untuk satu kejadian atau seluruh siri.
**Berpunca daripada:** BR-13.

#### UR-06 Mengubah atau membatalkan tempahan
Sebagai penempah, saya perlu mengubah masa atau membatalkan tempahan saya, supaya bilik boleh digunakan orang lain.

**Kriteria terima:** Butang ubah dan batal tersedia pada tempahan akan datang milik sendiri. Tempahan berstatus menunggu kelulususan boleh dibatalkan serta-merta tanpa menunggu keputusan pelulus. Pembatalan meminta sebab jika kurang dari 2 jam sebelum masa mula bagi tempahan yang telah disahkan. Semua peserta menerima notifikasi perubahan.
**Berpunca daripada:** BR-10, BR-14, BR-19.

#### UR-07 Menerima pengesahan dan peringatan
Sebagai penempah, saya perlu menerima e-mel pengesahan dan peringatan sebelum mesyuarat, supaya saya tidak terlupa.

**Kriteria terima:** E-mel pengesahan dihantar dalam 1 minit selepas tempahan disahkan. Peringatan dihantar mengikut tetapan sistem, lalai 1 hari dan 1 jam sebelum. E-mel mengandungi fail kalendar yang boleh dibuka dalam Outlook atau Google Calendar.
**Berpunca daripada:** BR-15.

#### UR-08 Tempahan bilik terkawal melalui kelulusan
Sebagai pelulus, saya perlu melihat dan bertindak ke atas permohonan tempahan bilik di bawah kawalan saya, supaya penggunaan bilik utama terkawal.

**Kriteria terima:** Senarai tugas kelulusan memaparkan permohonan menunggu dengan maklumat penuh. Kelulusan dan penolakan boleh dibuat dari senarai tanpa membuka setiap satu. Penolakan memerlukan sebab. Pelulus ganti boleh ditetapkan semasa bercuti.
**Berpunca daripada:** BR-12.

#### UR-09 Mengesahkan kehadiran
Sebagai penempah, saya perlu mengesahkan bahawa saya benar-benar menggunakan bilik, supaya bilik tidak dilepaskan secara silap.

**Kriteria terima:** Daftar masuk boleh dibuat melalui imbasan kod QR di pintu bilik atau butang dalam sistem, bermula 10 minit sebelum masa mula. Amaran dihantar 5 minit selepas masa mula jika belum daftar masuk. Tempahan dilepaskan selepas tempoh anjal yang dikonfigurasi.
**Berpunca daripada:** BR-14.

#### UR-10 Permintaan sokongan mesyuarat
Sebagai setiausaha, saya perlu memohon susun atur bilik dan minuman semasa membuat tempahan, supaya saya tidak perlu menghantar e-mel berasingan.

**Kriteria terima:** Bahagian permintaan sokongan pilihan dalam borang tempahan. Permintaan muncul dalam senarai tugas unit yang berkenaan. Status permintaan boleh dilihat oleh pemohon.
**Berpunca daripada:** BR-17.

#### UR-11 Mengurus katalog bilik
Sebagai pentadbir fasiliti, saya perlu menambah bilik, menetapkan kapasiti, kemudahan, waktu operasi dan peraturan kelulusan, supaya sistem mencerminkan keadaan sebenar.

**Kriteria terima:** Semua sifat bilik boleh disunting melalui antara muka tanpa bantuan pembangun. Bilik boleh dinyahaktifkan untuk penyelenggaraan tanpa memadam sejarah tempahannya. Tempahan sedia ada yang terjejas ditunjukkan sebelum penyahaktifan disahkan.
**Berpunca daripada:** BR-10, BR-18.

#### UR-12 Laporan penggunaan bilik
Sebagai pentadbir fasiliti, saya perlu melihat kadar penggunaan setiap bilik mengikut bulan, supaya saya boleh mencadangkan perubahan kepada pengurusan.

**Kriteria terima:** Laporan memaparkan jam ditempah, jam hadir, kadar penggunaan dan kadar tidak hadir bagi setiap bilik. Boleh ditapis mengikut julat tarikh, bangunan dan bahagian penempah. Boleh dieksport ke Excel.
**Berpunca daripada:** BR-18, BR-05.

### 3.2 Aset dan penyelenggaraan ICT

#### UR-20 Melaporkan kerosakan dengan cepat
Sebagai kakitangan, saya perlu melaporkan kerosakan peralatan dalam bawah dua minit dan menerima nombor rujukan, supaya saya tahu aduan saya diterima.

**Kriteria terima:** Borang aduan mempunyai maksimum lima medan wajib. Aset boleh dipilih melalui imbasan QR, carian nombor pendaftaran, atau senarai aset yang didaftarkan atas nama saya. Nombor rujukan dipaparkan serta-merta dan dihantar melalui e-mel.
**Berpunca daripada:** BR-22.

#### UR-21 Menyemak status aduan
Sebagai kakitangan, saya perlu melihat status aduan saya tanpa menelefon meja bantuan, supaya saya tahu bila ia akan diselesaikan.

**Kriteria terima:** Halaman "aduan saya" memaparkan status semasa, juruteknik yang ditugaskan, catatan kemajuan yang boleh dilihat pengguna, dan anggaran masa selesai berdasarkan SLA.
**Berpunca daripada:** BR-22, BR-23.

#### UR-22 Mengesahkan pemulihan
Sebagai pelapor, saya perlu mengesahkan bahawa masalah benar-benar selesai sebelum tiket ditutup, supaya masalah yang belum selesai tidak dianggap selesai.

**Kriteria terima:** Selepas juruteknik menanda kerja selesai, pelapor menerima permintaan pengesahan. Pilihan "sah selesai" atau "masih bermasalah" tersedia. Pilihan kedua membuka semula tiket kepada juruteknik yang sama. Tiket ditutup automatik selepas 3 hari bekerja tanpa maklum balas.
**Berpunca daripada:** BR-22.

#### UR-23 Melihat senarai tugas harian
Sebagai juruteknik, saya perlu melihat senarai tugas saya untuk hari ini disusun mengikut keutamaan dan baki masa SLA, supaya saya tahu apa yang perlu didahulukan.

**Kriteria terima:** Senarai memaparkan keutamaan, lokasi, aset, baki masa SLA dan status. Boleh disusun dan ditapis. Berfungsi dengan baik pada skrin telefon.
**Berpunca daripada:** BR-23, BR-24.

#### UR-24 Mengenal pasti aset melalui imbasan
Sebagai juruteknik, saya perlu mengimbas kod QR pada peralatan untuk membuka rekodnya, supaya saya tidak perlu mencari secara manual.

**Kriteria terima:** Imbasan melalui kamera telefon membuka halaman aset dalam bawah 3 saat. Halaman memaparkan spesifikasi, waranti, pengguna bertanggungjawab, dan sejarah kerja terdahulu.
**Berpunca daripada:** BR-21.

#### UR-25 Merekod kerja pembaikan di lapangan
Sebagai juruteknik, saya perlu merekod diagnosis, tindakan dan alat ganti yang digunakan terus dari telefon, supaya saya tidak perlu menaip semula di pejabat.

**Kriteria terima:** Kemas kini boleh dibuat dari peranti mudah alih. Foto boleh dimuat naik. Alat ganti dipilih daripada senarai stok dan mengurangkan baki stok secara automatik. Masa kerja direkod.
**Berpunca daripada:** BR-23, BR-28.

#### UR-26 Mengagihkan tugas
Sebagai penyelia ICT, saya perlu mengagihkan tiket baharu kepada juruteknik berdasarkan beban kerja semasa dan lokasi, supaya kerja diagihkan secara adil.

**Kriteria terima:** Skrin agihan memaparkan bilangan tiket terbuka setiap juruteknik. Agihan pukal untuk beberapa tiket disokong. Juruteknik menerima notifikasi serta-merta.
**Berpunca daripada:** BR-23.

#### UR-27 Memantau SLA sebelum dilanggar
Sebagai penyelia ICT, saya perlu diberi amaran tentang tiket yang hampir melebihi sasaran, supaya saya boleh bertindak sebelum ia berlaku.

**Kriteria terima:** Papan pemuka memaparkan tiket mengikut status SLA: selamat, hampir melanggar pada 80% masa, dan telah melanggar. Amaran automatik dihantar kepada penyelia pada ambang tersebut.
**Berpunca daripada:** BR-24, BR-25.

#### UR-28 Menyemak waranti sebelum pembaikan
Sebagai juruteknik, saya perlu tahu sama ada aset masih dalam waranti sebelum saya membaiki atau menghantar kepada vendor, supaya organisasi tidak membayar untuk kerja yang sepatutnya percuma.

**Kriteria terima:** Status waranti dipaparkan dengan jelas pada halaman aset dan pada tiket. Sistem memberi amaran jika kerja berbayar direkod terhadap aset dalam waranti.
**Berpunca daripada:** BR-27.

#### UR-29 Menguruskan daftar aset
Sebagai pegawai aset, saya perlu mendaftar, mengemas kini, memindahkan dan melupuskan aset dengan rekod sejarah yang lengkap, supaya daftar aset kekal tepat.

**Kriteria terima:** Semua operasi tersedia melalui antara muka. Setiap perubahan pemilik dan lokasi disimpan sebagai rekod sejarah dengan tarikh dan pegawai yang bertanggungjawab. Pelupusan memerlukan kelulusan dan sebab.
**Berpunca daripada:** BR-20, BR-29.

#### UR-30 Import dan eksport aset secara pukal
Sebagai pegawai aset, saya perlu memuat naik senarai aset daripada Excel semasa permulaan dan mengeksport untuk pengauditan, supaya saya tidak perlu memasukkan ratusan rekod satu per satu.

**Kriteria terima:** Templat Excel disediakan. Sistem mengesahkan data sebelum menyimpan dan memaparkan senarai baris yang gagal beserta sebabnya. Eksport mengandungi semua medan dan boleh ditapis.
**Berpunca daripada:** BR-20.

#### UR-31 Menjana label kod QR
Sebagai pegawai aset, saya perlu menjana dan mencetak label kod QR untuk sekumpulan aset, supaya setiap peralatan boleh diimbas.

**Kriteria terima:** Label boleh dijana untuk aset terpilih atau seluruh penapis. Format cetakan sesuai dengan kertas label standard. Label mengandungi kod QR, nombor pendaftaran dan nama organisasi.
**Berpunca daripada:** BR-21.

#### UR-32 Menjadualkan penyelenggaraan pencegahan
Sebagai penyelia ICT, saya perlu menetapkan jadual penyelenggaraan berkala mengikut kategori aset dan sistem menjana perintah kerja secara automatik, supaya penyelenggaraan tidak terlepas.

**Kriteria terima:** Pelan boleh ditetapkan mengikut kekerapan bulanan, suku tahunan, setengah tahunan atau tahunan. Perintah kerja dijana mengikut tempoh awalan yang dikonfigurasi. Senarai semak tugas dilampirkan pada setiap perintah kerja.
**Berpunca daripada:** BR-26.

#### UR-33 Menjejaki alat ganti
Sebagai penyelia ICT, saya perlu tahu baki stok alat ganti dan menerima amaran apabila paras rendah, supaya kerja pembaikan tidak tertangguh kerana kehabisan stok.

**Kriteria terima:** Setiap item mempunyai paras minimum yang boleh ditetapkan. Amaran dihantar apabila baki mencapai atau turun bawah paras tersebut. Setiap pengeluaran stok dikaitkan dengan tiket atau perintah kerja.
**Berpunca daripada:** BR-28.

#### UR-34 Laporan penyelenggaraan
Sebagai penyelia ICT, saya perlu menjana laporan bulanan prestasi SLA, purata masa pemulihan dan kerosakan mengikut kategori aset, supaya saya boleh melaporkan kepada pengurusan tanpa menyusun data secara manual.

**Kriteria terima:** Laporan boleh dijana untuk mana-mana julat tarikh. Boleh dieksport ke Excel dan PDF. Boleh dijadualkan untuk dihantar melalui e-mel setiap bulan.
**Berpunca daripada:** BR-24, BR-30, BR-05.

### 3.3 Keperluan pentadbiran dan rentas

#### UR-40 Log masuk dengan identiti organisasi
Sebagai pengguna, saya perlu log masuk menggunakan akaun organisasi sedia ada, supaya saya tidak perlu mengingat kata laluan tambahan.

**Kriteria terima:** Log masuk melalui direktori organisasi berjaya. Akaun tempatan disediakan sebagai sandaran untuk pengguna tanpa akaun direktori.
**Berpunca daripada:** BR-02.

#### UR-41 Menggunakan sistem pada telefon
Sebagai pengguna, saya perlu menggunakan semua fungsi utama pada telefon pintar tanpa memasang aplikasi, supaya saya boleh bertindak di mana sahaja.

**Kriteria terima:** Semua aliran utama boleh diselesaikan pada skrin selebar 360 piksel. Tiada penatalan mendatar. Sasaran ketikan sekurang-kurangnya 44 piksel.
**Berpunca daripada:** BR-04.

#### UR-42 Mengkonfigurasi peraturan tanpa pembangun
Sebagai pentadbir sistem, saya perlu mengubah waktu operasi, sasaran SLA, templat notifikasi dan cuti umum melalui antara muka, supaya perubahan dasar tidak memerlukan pelepasan versi baharu.

**Kriteria terima:** Semua parameter yang disenaraikan dalam modul konfigurasi boleh disunting oleh pentadbir. Perubahan berkuat kuasa tanpa memulakan semula sistem, dan direkod dalam jejak audit.
**Berpunca daripada:** BR-05.

#### UR-43 Menyemak jejak audit
Sebagai juruaudit dalaman, saya perlu melihat siapa mengubah apa dan bila, supaya saya boleh menjalankan pengauditan.

**Kriteria terima:** Log boleh dicari mengikut pengguna, jenis rekod, jenis tindakan dan julat tarikh. Nilai sebelum dan selepas dipaparkan. Log tidak boleh disunting atau dipadam melalui antara muka.
**Berpunca daripada:** BR-03.

---

## 4. Matriks Keperluan Pengguna Berbanding Persona

| Keperluan | P1 Kakitangan | P2 Setiausaha | P3 Pelulus | P4 Fasiliti | P5 Juruteknik | P6 Penyelia | P7 Pegawai Aset | P8 Pentadbir |
|---|---|---|---|---|---|---|---|---|
| UR-01 hingga UR-03 | Ya | Ya | — | Ya | — | — | — | — |
| UR-04, UR-05 | — | Ya | — | Ya | — | — | — | — |
| UR-06, UR-07 | Ya | Ya | — | Ya | — | — | — | — |
| UR-08 | — | — | Ya | Ya | — | — | — | — |
| UR-09 | Ya | Ya | — | — | — | — | — | — |
| UR-10 | — | Ya | — | Ya | — | — | — | — |
| UR-11, UR-12 | — | — | — | Ya | — | — | — | Ya |
| UR-20 hingga UR-22 | Ya | Ya | — | — | — | — | — | — |
| UR-23 hingga UR-25 | — | — | — | — | Ya | — | — | — |
| UR-26, UR-27 | — | — | — | — | — | Ya | — | — |
| UR-28 | — | — | — | — | Ya | Ya | — | — |
| UR-29 hingga UR-31 | — | — | — | — | — | — | Ya | Ya |
| UR-32, UR-33 | — | — | — | — | Ya | Ya | — | — |
| UR-34 | — | — | — | — | — | Ya | Ya | — |
| UR-40, UR-41 | Ya | Ya | Ya | Ya | Ya | Ya | Ya | Ya |
| UR-42, UR-43 | — | — | — | — | — | — | — | Ya |

## 5. Keperluan Pengguna yang Ditolak Buat Masa Ini

Perkara berikut dikemukakan semasa perbincangan awal tetapi tidak dimasukkan dalam skop, dengan sebab yang dinyatakan supaya keputusan ini tidak dibincang semula tanpa maklumat baharu.

| Cadangan | Sebab ditolak |
|---|---|
| Papan tanda digital di luar setiap bilik | Kos perkakasan tinggi berbanding faedah. Kod QR bercetak memberi hasil sama untuk daftar masuk. |
| Tempahan tempat letak kereta | Domain berbeza dengan peraturan sendiri. Layak menjadi projek berasingan. |
| Sembang langsung dengan meja bantuan | Menambah keperluan kakitangan berjaga. Tiket dengan notifikasi memadai buat permulaan. |
| Integrasi automatik dengan sistem kewangan aset | Memerlukan perjanjian antara jabatan yang belum wujud. Medan rujukan disimpan supaya penyelarasan manual masih boleh dibuat. |
| Ramalan kerosakan berasaskan pembelajaran mesin | Tiada data sejarah untuk melatih model. Boleh dipertimbangkan selepas 18 bulan data terkumpul. |
