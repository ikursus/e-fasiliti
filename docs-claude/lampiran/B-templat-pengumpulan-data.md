# Lampiran B — Templat Pengumpulan Data

Dua templat berikut mesti dilengkapkan sebelum pembangunan modul berkenaan bermula. Kelewatan mengisi templat ini adalah punca kelewatan projek yang paling kerap, kerana ia bergantung kepada pengesahan manusia yang tidak boleh dipercepatkan oleh sistem.

---

## B1 — Templat Senarai Bilik Mesyuarat

**Diisi oleh:** Pentadbir Fasiliti
**Diperlukan sebelum:** Fasa 1, minggu 4
**Format serahan:** Excel dengan lajur berikut

| Lajur | Wajib | Contoh | Nota |
|---|---|---|---|
| Kod bilik | Ya | BM-A-301 | Mesti unik. Cadangan format: jenis, bangunan, tingkat dan nombor |
| Nama bilik | Ya | Bilik Mesyuarat Utama | Nama yang dikenali pengguna |
| Kampus | Ya | Ibu Pejabat | |
| Bangunan | Ya | Bangunan A | |
| Tingkat | Ya | 3 | |
| Kapasiti asas | Ya | 20 | Bilangan kerusi dalam susun atur lalai |
| Susun atur 1 dan kapasiti | Ya | Bentuk U, 18 | Susun atur lalai |
| Susun atur 2 dan kapasiti | Tidak | Kelas, 24 | |
| Susun atur 3 dan kapasiti | Tidak | Teater, 35 | |
| Kemudahan | Ya | Projektor, papan putih, persidangan video | Pisahkan dengan koma |
| Waktu buka | Ya | 08:00 | |
| Waktu tutup | Ya | 18:00 | |
| Hari operasi | Ya | Isnin hingga Jumaat | |
| Perlu kelulusan | Ya | Ya atau Tidak | |
| Pelulus jika perlu | Bersyarat | Ketua Bahagian Pentadbiran | Wajib jika lajur sebelumnya Ya |
| Peranan dibenarkan | Tidak | Semua | Kosongkan jika terbuka kepada semua |
| Tempoh maksimum jam | Tidak | 8 | Kosongkan untuk menggunakan tetapan lalai |
| Penyangga sebelum minit | Tidak | 15 | Untuk penyediaan bilik |
| Penyangga selepas minit | Tidak | 15 | Untuk pembersihan |
| Catatan | Tidak | Bilik VIP, perlu kelulusan Ketua Bahagian | |

**Perkara yang perlu disahkan secara fizikal sebelum mengisi:**

1. Kapasiti sebenar setiap susun atur, dengan mengira kerusi, bukan berdasarkan pelan bangunan lama.
2. Kemudahan yang benar-benar berfungsi. Projektor yang rosak tidak boleh disenaraikan sebagai kemudahan.
3. Bilik yang telah ditukar kegunaan dan tidak lagi berfungsi sebagai bilik mesyuarat.

---

## B2 — Templat Senarai Aset ICT

**Diisi oleh:** Pegawai Aset
**Diperlukan sebelum:** Fasa 2, minggu 4
**Format serahan:** Excel dengan lajur berikut

| Lajur | Wajib | Contoh | Peraturan pengesahan |
|---|---|---|---|
| No. pendaftaran | Ya | ICT/KOMP/2024/0451 | Mesti unik. Baris tanpa nilai akan ditolak |
| Kategori | Ya | Komputer Meja | Mesti sepadan dengan senarai kategori yang dipersetujui |
| Jenama | Ya | Dell | |
| Model | Ya | OptiPlex 7010 | |
| No. siri | Tidak | SN7Y2K9L | Mesti unik jika diisi |
| Tarikh perolehan | Ya | 15/03/2024 | Format hari, bulan, tahun. Tarikh tidak sah akan ditolak |
| Harga perolehan | Tidak | 3500.00 | |
| No. pesanan | Tidak | PO/2024/00187 | Rujukan kepada sistem perolehan |
| Pembekal | Tidak | Syarikat Teknologi Sdn Bhd | |
| Waranti mula | Tidak | 15/03/2024 | Biasanya sama dengan tarikh perolehan |
| Waranti bulan | Tidak | 36 | Bilangan bulan tempoh waranti |
| Kampus | Ya | Ibu Pejabat | Mesti sepadan dengan senarai lokasi |
| Bangunan | Ya | Bangunan A | |
| Tingkat | Ya | 2 | |
| Bilik atau ruang | Ya | 214 | |
| Pengguna bertanggungjawab | Bersyarat | Ahmad Zaki bin Osman | Wajib jika status ialah sedang digunakan |
| Status | Ya | Sedang digunakan | Salah satu daripada: dalam simpanan, sedang digunakan, dalam pembaikan, tidak aktif, dilupuskan |
| Alamat MAC | Tidak | 00:1A:2B:3C:4D:5E | |
| Nama hos | Tidak | PC-A2-214 | |
| Spesifikasi | Tidak | Intel i5, RAM 8GB, SSD 256GB | Teks bebas |
| Catatan | Tidak | Ditukar dari Bahagian Kewangan pada 2025 | |

**Peraturan pembersihan data sebelum serahan:**

1. **Buang aset yang telah dilupuskan secara fizikal** tetapi masih dalam senarai. Jika ia perlu direkod untuk tujuan audit, tandakan statusnya sebagai dilupuskan, jangan biarkan sebagai sedang digunakan.
2. **Selaraskan nama lokasi.** Jika satu tingkat dirujuk sebagai "Tingkat 2", "T2" dan "Aras 2" dalam baris berbeza, pilih satu bentuk dan gunakan secara konsisten.
3. **Padankan nama pengguna dengan direktori organisasi.** Nama singkatan seperti "Ahmad" tidak mencukupi jika terdapat beberapa Ahmad. Gunakan nama penuh seperti dalam rekod sumber manusia.
4. **Jangan reka data yang tidak diketahui.** Nombor siri yang tidak dapat dipastikan hendaklah dibiarkan kosong, bukan diisi dengan nilai anggaran. Medan kosong boleh dilengkapkan kemudian; medan yang salah akan kekal salah tanpa disedari.

**Jadual pemetaan kategori.** Lengkapkan jadual ini supaya kategori dalam fail sedia ada dapat dipetakan kepada kategori sistem.

| Kategori dalam fail sedia ada | Kategori sistem |
|---|---|
| PC, Desktop, Komputer | Komputer Meja |
| Laptop, Notebook | Komputer Riba |
| Printer, Pencetak | Pencetak |
| | |

**Jadual pemetaan lokasi.** Lengkapkan jadual ini bagi setiap bentuk penulisan lokasi yang wujud dalam fail sedia ada.

| Lokasi dalam fail sedia ada | Kampus | Bangunan | Tingkat | Ruang |
|---|---|---|---|---|
| Blok A Tkt 2 Bilik 214 | Ibu Pejabat | Bangunan A | 2 | 214 |
| | | | | |

---

## B3 — Templat Pelan Penyelenggaraan Pencegahan

**Diisi oleh:** Penyelia ICT
**Diperlukan sebelum:** Fasa 3, minggu 2

| Kategori aset | Kekerapan | Tempoh awalan hari | Senarai semak tugas | Anggaran masa setiap unit |
|---|---|---|---|---|
| Pencetak | Suku tahunan | 14 | Bersihkan mekanisme kertas; semak kualiti cetakan; kemas kini pemacu; semak paras toner | 30 minit |
| Komputer meja | Tahunan | 30 | Bersihkan habuk dalaman; semak kesihatan cakera; kemas kini sistem pengendalian; semak sandaran data pengguna; sahkan label QR | 45 minit |
| Penghala dan suis | Setengah tahunan | 21 | Semak suhu dan pengudaraan; kemas kini perisian tegar; semak log ralat; sahkan konfigurasi disandar | 60 minit |
| Projektor | Suku tahunan | 14 | Bersihkan penapis; semak jam lampu; semak kualiti imej | 20 minit |
| | | | | |

**Nota penting.** Jumlah masa penyelenggaraan pencegahan mesti dikira terhadap kapasiti sebenar juruteknik. Jika 1,200 komputer memerlukan 45 minit setiap satu setahun, itu adalah 900 jam kerja, iaitu lebih separuh masa kerja tahunan seorang juruteknik. Jadual yang tidak boleh dilaksanakan akan menghasilkan kadar pematuhan yang rendah dan laporan yang mengelirukan. Lebih baik menetapkan kekerapan yang realistik dan mematuhinya.
