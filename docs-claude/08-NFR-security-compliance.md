# 08 — Keperluan Bukan Fungsian, Keselamatan & Pematuhan

| Perkara | Butiran |
|---|---|
| Dokumen | Non-Functional Requirements (NFR) |
| Sistem | SPFA — Sistem Pengurusan Fasiliti & Aset ICT |
| Versi | 1.0 (Draf) |
| Tarikh | 7 September 2026 |

---

## 1. Prestasi

| ID | Keperluan | Kaedah pengukuran |
|---|---|---|
| NFR-P01 | Carian bilik tersedia MESTI kembali dalam 2 saat pada persentil ke-95 dengan 30 bilik dan 90,000 rekod tempahan | Ujian beban automatik |
| NFR-P02 | Muat halaman kalendar mingguan MESTI selesai dalam 3 saat pada persentil ke-95 | Ujian beban automatik |
| NFR-P03 | Penyimpanan tempahan MESTI selesai dalam 1 saat pada persentil ke-95 | Ujian beban automatik |
| NFR-P04 | Carian aset melalui nombor pendaftaran MESTI kembali dalam 1 saat dengan 2,200 aset | Ujian beban automatik |
| NFR-P05 | Halaman aset yang dibuka melalui imbasan QR MESTI dipaparkan dalam 3 saat pada rangkaian mudah alih 4G | Ujian manual pada peranti sebenar |
| NFR-P06 | Sistem MESTI menyokong 100 pengguna aktif serentak tanpa kemerosotan melebihi 20% pada masa tindak balas | Ujian beban |
| NFR-P07 | Penjanaan laporan bulanan MESTI selesai dalam 30 saat; laporan yang lebih besar dijana secara latar dan dihantar melalui e-mel | Ujian prestasi |
| NFR-P08 | Import pukal 1,200 rekod aset MESTI selesai dalam 5 minit | Ujian migrasi |

## 2. Ketersediaan dan Kebolehpercayaan

| ID | Keperluan |
|---|---|
| NFR-A01 | Sistem MESTI tersedia 99.5% dalam waktu bekerja, iaitu tidak melebihi kira-kira 2 jam masa henti sebulan |
| NFR-A02 | Penyelenggaraan berjadual MESTI dilakukan di luar waktu bekerja dengan notis sekurang-kurangnya 3 hari |
| NFR-A03 | Sasaran masa pemulihan selepas kegagalan besar adalah 4 jam |
| NFR-A04 | Sasaran titik pemulihan adalah 1 jam, bermakna kehilangan data maksimum yang boleh diterima adalah satu jam transaksi |
| NFR-A05 | Kegagalan pelayan e-mel TIDAK BOLEH menghalang operasi tempahan atau tiket |
| NFR-A06 | Kegagalan direktori pengguna organisasi MESTI membenarkan pentadbir log masuk melalui akaun tempatan untuk pemulihan |
| NFR-A07 | Tugas latar MESTI selamat untuk dijalankan berulang tanpa kesan berganda |

## 3. Kebolehgunaan

| ID | Keperluan |
|---|---|
| NFR-U01 | Pengguna baharu MESTI dapat menyiapkan tempahan pertama tanpa latihan, berpandukan antara muka sahaja |
| NFR-U02 | Tempahan mudah MESTI boleh diselesaikan dalam maksimum lima langkah |
| NFR-U03 | Laporan kerosakan MESTI boleh diselesaikan dalam maksimum lima medan wajib |
| NFR-U04 | Semua mesej ralat MESTI menerangkan apa yang salah dan apa yang perlu dilakukan seterusnya, dalam Bahasa Melayu |
| NFR-U05 | Antara muka MESTI berfungsi sepenuhnya pada skrin selebar 360 piksel tanpa penatalan mendatar |
| NFR-U06 | Sasaran ketikan pada peranti sentuh MESTI sekurang-kurangnya 44 piksel setiap sisi |
| NFR-U07 | Tindakan yang tidak boleh dibatalkan MESTI meminta pengesahan yang menerangkan akibatnya |
| NFR-U08 | Sistem MESTI menyokong Bahasa Melayu dan Bahasa Inggeris, dengan Bahasa Melayu sebagai lalai |

## 4. Kebolehcapaian

| ID | Keperluan |
|---|---|
| NFR-C01 | Antara muka MESTI mematuhi WCAG 2.1 aras AA bagi kontras warna, nisbah sekurang-kurangnya 4.5 banding 1 untuk teks biasa |
| NFR-C02 | Maklumat TIDAK BOLEH disampaikan melalui warna sahaja; status tempahan dan tiket mesti mempunyai label teks |
| NFR-C03 | Semua fungsi utama MESTI boleh dicapai menggunakan papan kekunci sahaja |
| NFR-C04 | Semua medan borang MESTI mempunyai label yang berkaitan secara program |
| NFR-C05 | Imej yang membawa maklumat MESTI mempunyai teks alternatif |

## 5. Keselamatan

### 5.1 Pengesahan dan sesi

| ID | Keperluan |
|---|---|
| NFR-S01 | Semua komunikasi MESTI menggunakan TLS 1.2 atau lebih tinggi |
| NFR-S02 | Kata laluan akaun tempatan MESTI disimpan menggunakan fungsi cincang moden yang direka untuk kata laluan, dengan garam unik setiap rekod |
| NFR-S03 | Sesi MESTI tamat selepas 30 minit tidak aktif dan 8 jam secara mutlak |
| NFR-S04 | Kuki sesi MESTI ditanda HttpOnly, Secure dan SameSite |
| NFR-S05 | Akaun MESTI disekat selepas lima percubaan gagal dalam 15 minit |
| NFR-S06 | Pengesahan dua faktor MESTI tersedia untuk peranan pentadbir sistem |

### 5.2 Kebenaran

| ID | Keperluan |
|---|---|
| NFR-S10 | Setiap permintaan API MESTI disemak kebenarannya pada pelayan; menyembunyikan butang pada antara muka tidak dikira sebagai kawalan |
| NFR-S11 | Pengguna TIDAK BOLEH mencapai rekod di luar skopnya dengan menukar pengenal dalam URL |
| NFR-S12 | Kenaikan keistimewaan MESTI hanya boleh dilakukan oleh pentadbir sistem dan direkod dalam jejak audit |

### 5.3 Perlindungan aplikasi

| ID | Keperluan |
|---|---|
| NFR-S20 | Semua input pengguna MESTI disahkan pada pelayan tanpa mengira pengesahan pada klien |
| NFR-S21 | Sistem MESTI menggunakan pertanyaan berparameter; penggabungan rentetan SQL dilarang |
| NFR-S22 | Semua output MESTI dilepaskan mengikut konteks untuk mengelakkan skrip merentas tapak |
| NFR-S23 | Semua borang yang mengubah keadaan MESTI dilindungi token anti pemalsuan permintaan merentas tapak |
| NFR-S24 | Muat naik fail MESTI dihadkan mengikut jenis dan saiz, disimpan di luar direktori web, dan dihidangkan melalui pengawal yang menyemak kebenaran |
| NFR-S25 | Nama fail muat naik MESTI dijana semula, bukan menggunakan nama asal daripada pengguna |
| NFR-S26 | Kebergantungan pihak ketiga MESTI diimbas untuk kerentanan yang diketahui sekurang-kurangnya bulanan |
| NFR-S27 | Rahsia seperti kata laluan pangkalan data dan kunci API TIDAK BOLEH disimpan dalam repositori kod |

### 5.4 Pemantauan keselamatan

| ID | Keperluan |
|---|---|
| NFR-S30 | Percubaan log masuk gagal MESTI direkod dengan alamat IP |
| NFR-S31 | Percubaan capaian tanpa kebenaran MESTI direkod dan mencetuskan amaran jika berulang |
| NFR-S32 | Log audit MESTI tidak boleh diubah melalui aplikasi oleh mana-mana peranan |
| NFR-S33 | Ujian penembusan MESTI dijalankan sebelum go-live dan setiap tahun selepas itu |

## 6. Pematuhan Perlindungan Data Peribadi

| ID | Keperluan |
|---|---|
| NFR-D01 | Sistem MESTI mengumpul hanya data peribadi yang diperlukan untuk operasi: nama, e-mel rasmi, telefon pejabat, unit organisasi |
| NFR-D02 | Notis privasi MESTI dipaparkan semasa log masuk pertama |
| NFR-D03 | Pengguna MESTI boleh melihat dan mengeksport data peribadi sendiri |
| NFR-D04 | Data pengguna yang telah berhenti MESTI ditanpanamakan selepas tempoh simpanan, dengan rekod transaksi dikekalkan untuk statistik |
| NFR-D05 | Data peribadi TIDAK BOLEH dihantar ke perkhidmatan pihak ketiga tanpa kelulusan bertulis |
| NFR-D06 | Data sandaran MESTI tertakluk kepada perlindungan yang sama seperti data langsung |

## 7. Kebolehselenggaraan

| ID | Keperluan |
|---|---|
| NFR-M01 | Kod MESTI mengikut satu panduan gaya yang dikuatkuasakan secara automatik dalam saluran integrasi berterusan |
| NFR-M02 | Logik domain MESTI mempunyai liputan ujian unit melebihi 80% |
| NFR-M03 | Setiap peraturan perniagaan MESTI dilaksanakan di satu tempat sahaja |
| NFR-M04 | Perubahan skema pangkalan data MESTI melalui fail migrasi berversi, bukan perubahan manual |
| NFR-M05 | Sistem MESTI menyediakan endpoint semakan kesihatan untuk pemantauan automatik |
| NFR-M06 | Log aplikasi MESTI berstruktur dan mengandungi pengenal jejak bagi setiap permintaan |
| NFR-M07 | Dokumentasi API MESTI dijana daripada kod dan sentiasa selaras dengan pelaksanaan |

## 8. Kebolehskalaan

| ID | Keperluan |
|---|---|
| NFR-K01 | Seni bina MESTI membenarkan penambahan pelayan aplikasi kedua tanpa perubahan kod, bermakna keadaan sesi tidak disimpan dalam ingatan pelayan |
| NFR-K02 | Sistem MESTI kekal dalam sasaran prestasi sehingga tiga kali ganda isipadu data yang dianggarkan selepas lima tahun |
| NFR-K03 | Jadual log audit MESTI dipartisi mengikut tempoh untuk mengelakkan kemerosotan prestasi |

## 9. Keserasian

| ID | Keperluan |
|---|---|
| NFR-B01 | Sistem MESTI berfungsi pada dua versi terkini Chrome, Edge, Firefox dan Safari |
| NFR-B02 | Sistem MESTI berfungsi pada Safari iOS dan Chrome Android versi terkini |
| NFR-B03 | Eksport Excel MESTI boleh dibuka dalam Microsoft Excel 2016 dan ke atas serta LibreOffice |
| NFR-B04 | Fail kalendar ICS MESTI boleh diimport ke Microsoft Outlook dan Google Calendar |

## 10. Sokongan dan Operasi

| ID | Keperluan |
|---|---|
| NFR-O01 | Manual pengguna MESTI disediakan dalam Bahasa Melayu, dipecahkan mengikut peranan |
| NFR-O02 | Manual pentadbir MESTI merangkumi konfigurasi, sandaran, pemulihan dan penyelesaian masalah lazim |
| NFR-O03 | Latihan MESTI diberikan kepada setiap kumpulan peranan sebelum go-live |
| NFR-O04 | Tempoh jaminan sokongan selepas go-live adalah sekurang-kurangnya 6 bulan |
| NFR-O05 | Kod sumber dan dokumentasi teknikal MESTI diserahkan kepada organisasi |

## 11. Senarai Semak Keselamatan Sebelum Go-Live

Setiap item mesti ditandakan lulus dengan bukti sebelum sistem dilepaskan kepada pengguna.

- [ ] Semua endpoint menguatkuasakan semakan kebenaran sisi pelayan, disahkan melalui ujian automatik
- [ ] Percubaan mencapai rekod orang lain dengan menukar pengenal URL ditolak
- [ ] TLS dikonfigurasi dengan sijil sah dan versi protokol lama dilumpuhkan
- [ ] Tiada rahsia dalam repositori kod, disahkan melalui imbasan
- [ ] Imbasan kerentanan kebergantungan bersih atau risiko baki didokumenkan
- [ ] Muat naik fail diuji dengan fail berbahaya dan jenis tidak dibenarkan
- [ ] Ujian suntikan SQL dan skrip merentas tapak dijalankan pada semua borang
- [ ] Sandaran diuji dengan pemulihan sebenar ke persekitaran ujian
- [ ] Akaun lalai dan kata laluan contoh dibuang
- [ ] Log audit disahkan merekod semua operasi kritikal
- [ ] Mesej ralat tidak mendedahkan surih tindanan atau struktur pangkalan data
- [ ] Ujian penembusan selesai dan penemuan berkeutamaan tinggi ditutup
