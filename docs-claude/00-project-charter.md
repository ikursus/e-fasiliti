# 00 — Project Charter & Vision/Scope

| Perkara | Butiran |
|---|---|
| Nama projek | SPFA — Sistem Pengurusan Fasiliti & Aset ICT |
| Versi | 1.0 (Draf) |
| Tarikh | 7 September 2026 |
| Penaja projek | Ketua Pegawai Maklumat / Ketua Bahagian Pentadbiran |
| Pemilik proses | Unit ICT (aset & penyelenggaraan), Unit Pentadbiran (bilik mesyuarat) |

---

## 1. Latar Belakang Masalah

Dua proses harian dikendalikan secara manual dan terpisah:

**Tempahan bilik mesyuarat.** Tempahan dibuat melalui e-mel, WhatsApp atau buku log di kaunter. Akibatnya berlaku pertindihan tempahan, bilik ditempah tetapi tidak digunakan, dan tiada data penggunaan untuk merancang keperluan ruang. Setiausaha terpaksa mengesahkan ketersediaan secara manual setiap kali.

**Penyelenggaraan peralatan ICT.** Aduan kerosakan komputer dan pencetak dibuat secara lisan atau melalui panggilan telefon. Tiada nombor rujukan, tiada jejak audit, dan tiada pengukuran tempoh pemulihan. Rekod aset tersebar dalam beberapa fail Excel yang tidak selaras dengan rekod kewangan. Penyelenggaraan pencegahan tidak dijadualkan, jadi kerosakan hanya ditangani selepas berlaku.

## 2. Objektif Projek

| ID | Objektif | Ukuran kejayaan |
|---|---|---|
| OBJ-01 | Menghapuskan pertindihan tempahan bilik | 0 kes pertindihan selepas 3 bulan operasi |
| OBJ-02 | Mengurangkan tempahan tidak hadir (no-show) | Turun 50% berbanding garis dasar dalam 6 bulan |
| OBJ-03 | Memusatkan rekod aset ICT | 100% aset ICT aktif berdaftar dalam sistem sebelum go-live + 1 bulan |
| OBJ-04 | Mengukur dan memenuhi SLA pemulihan kerosakan | 90% tiket P2 ditutup dalam SLA |
| OBJ-05 | Menjadualkan penyelenggaraan pencegahan | 95% jadual PM dilaksanakan pada suku tahun berkenaan |
| OBJ-06 | Menyediakan laporan pengurusan atas permintaan | Laporan bulanan dijana dalam bawah 5 minit tanpa kerja manual |

## 3. Skop

### 3.1 Dalam skop

- Aplikasi web responsif (desktop dan pelayar mudah alih) untuk kegunaan dalaman organisasi.
- Domain 1: tempahan bilik mesyuarat, termasuk katalog bilik, kalendar, kelulusan, daftar masuk dan pelepasan automatik.
- Domain 2: pengurusan aset ICT dan penyelenggaraan, termasuk inventari, tiket kerosakan, penyelenggaraan berjadual, vendor dan waranti, serta alat ganti.
- Modul sokongan: pentadbiran, pengguna dan peranan, notifikasi, laporan, jejak audit.
- Integrasi keluar: e-mel SMTP dan jemputan kalendar format ICS.
- Migrasi data aset sedia ada daripada fail Excel.

### 3.2 Luar skop (fasa ini)

- Aplikasi mudah alih natif untuk iOS dan Android. Pelayar mudah alih mencukupi buat masa ini.
- Integrasi dua hala penuh dengan sistem perakaunan aset kerajaan atau ERP kewangan. Sistem hanya menyimpan medan rujukan.
- Pembayaran, invois vendor dan proses perolehan.
- Pengurusan aset bukan ICT seperti perabot dan kenderaan.
- Kawalan pintu atau papan tanda digital di luar bilik mesyuarat.
- Modul katering dan penyediaan minuman melebihi rekod permintaan mudah.

### 3.3 Andaian

| ID | Andaian | Kesan jika tidak benar |
|---|---|---|
| ASP-01 | Bilangan pengguna berdaftar 300–800; puncak serentak 100 | Saiz pelayan dan ujian beban perlu dinilai semula |
| ASP-02 | Antara muka dalam Bahasa Melayu, dengan pilihan Bahasa Inggeris | Kerja tambahan pengantarabangsaan |
| ASP-03 | Direktori pengguna sedia ada (LDAP/Active Directory/Entra ID) boleh digunakan untuk log masuk | Perlu bina pengurusan kata laluan tempatan yang penuh |
| ASP-04 | Pelayan SMTP organisasi tersedia untuk notifikasi | Notifikasi perlu saluran alternatif |
| ASP-05 | Setiap aset ICT boleh ditampal label kod QR | Pengenalan aset kembali kepada carian manual |
| ASP-06 | Sistem dihos dalam rangkaian dalaman atau awan tertutup organisasi | Keperluan keselamatan awam yang lebih ketat |

### 3.4 Kekangan

- Data tidak boleh keluar dari sempadan negara tanpa kelulusan; ini menghadkan pilihan hos awan.
- Sistem mesti mematuhi Akta Perlindungan Data Peribadi bagi data kakitangan.
- Tempoh pembangunan sasaran: 6 bulan hingga go-live fasa 1.

## 4. Stakeholder dan Kepentingan

| Stakeholder | Kepentingan utama | Pengaruh |
|---|---|---|
| Penaja projek | Pulangan pelaburan, ketepatan masa penyerahan | Tinggi |
| Kakitangan umum | Tempahan pantas, aduan kerosakan mudah | Sederhana |
| Setiausaha / pembantu tadbir | Tempahan bagi pihak orang lain, susun atur bilik | Tinggi |
| Pentadbir fasiliti | Kawalan penggunaan bilik, laporan penggunaan | Tinggi |
| Juruteknik ICT | Senarai tugas jelas, rekod kerja mudah | Tinggi |
| Penyelia ICT | Agihan tugas, pemantauan SLA | Tinggi |
| Pegawai aset | Ketepatan inventari, pelupusan, pengauditan | Tinggi |
| Vendor penyelenggaraan | Rujukan kerja, rekod waranti | Rendah |
| Audit dalaman | Jejak audit lengkap | Sederhana |

## 5. Kes Perniagaan Ringkas

Anggaran kos manual semasa bagi 500 kakitangan:

| Kos | Anggaran tahunan |
|---|---|
| Masa setiausaha menguruskan tempahan (2 jam/minggu × 10 orang) | ~1,000 jam |
| Masa terbuang akibat pertindihan bilik dan mesyuarat tertunda | ~300 jam |
| Kerugian akibat aset hilang jejak dan pembelian berganda | Berubah-ubah, dianggarkan sederhana |
| Masa henti kerja akibat kerosakan ICT yang lambat dipulihkan | ~800 jam |

Faedah utama bukan penjimatan tunai langsung, tetapi kebolehlihatan. Selepas satu suku tahun beroperasi, organisasi memperoleh data sebenar tentang penggunaan bilik, kadar kerosakan mengikut model peralatan, dan prestasi vendor. Data ini menyokong keputusan perolehan dan perancangan ruang.

## 6. Kriteria Kejayaan Projek

Projek dianggap berjaya apabila kesemua berikut dicapai:

1. Kesemua keperluan fungsian keutamaan wajib dalam SRS lulus UAT.
2. Sekurang-kurangnya 80% tempahan bilik dibuat melalui sistem dalam bulan kedua selepas go-live.
3. Kesemua aset ICT aktif berdaftar dan berlabel QR.
4. Laporan SLA bulanan dijana daripada sistem tanpa penyusunan manual.
5. Tiada isu keselamatan berkeutamaan tinggi yang belum ditutup semasa penyerahan.
