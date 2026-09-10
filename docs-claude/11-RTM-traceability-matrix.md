# 11 — RTM: Matriks Kebolehjejakan Keperluan

| Perkara | Butiran |
|---|---|
| Dokumen | Requirements Traceability Matrix (RTM) |
| Sistem | SPFA — Sistem Pengurusan Fasiliti & Aset ICT |
| Versi | 1.0 (Draf) |
| Tarikh | 7 September 2026 |

---

## 1. Tujuan

Matriks ini menjejaki setiap keperluan perniagaan hingga ke kes ujian yang membuktikan ia dipenuhi. Ia menjawab dua soalan yang sering ditanya lewat dalam projek: adakah semua yang dijanjikan telah dibina, dan adakah ada yang dibina tanpa diminta.

**Cara membaca:** BR adalah keperluan perniagaan, UR keperluan pengguna, FR keperluan fungsian, UC use case, dan TC kes ujian dalam dokumen 12.

## 2. Matriks Utama

| BR | Keperluan perniagaan | UR | FR | UC | TC | Fasa |
|---|---|---|---|---|---|---|
| BR-01 | Satu sistem tunggal untuk dua fungsi | Semua | Semua modul | Semua | TC-INT-01 | 1–3 |
| BR-02 | Log masuk identiti organisasi | UR-40 | FR-USR-01, FR-USR-02 | — | TC-USR-01, TC-USR-02 | 1 |
| BR-03 | Jejak audit boleh diperiksa | UR-43 | FR-AUD-01 hingga FR-AUD-05 | — | TC-AUD-01 hingga TC-AUD-03 | 1 |
| BR-04 | Boleh diakses melalui telefon | UR-41 | NFR-U05, NFR-U06 | UC-04, UC-10, UC-12 | TC-UI-01, TC-UI-02 | 1 |
| BR-05 | Laporan dijana sendiri | UR-12, UR-34, UR-42 | FR-LAP-01 hingga FR-LAP-12 | — | TC-LAP-01 hingga TC-LAP-06 | 1–3 |
| BR-06 | Perlindungan data peribadi | — | NFR-D01 hingga NFR-D06 | — | TC-SEC-05 | 1 |
| BR-07 | Bahasa Melayu utama | — | FR-NOT-09, NFR-U08 | — | TC-UI-03 | 1 |
| BR-10 | Tempah sendiri tanpa perantara | UR-01, UR-02, UR-03, UR-06 | FR-TMP-01 hingga FR-TMP-03, FR-TMP-12 | UC-01, UC-02 | TC-TMP-01 hingga TC-TMP-05 | 1 |
| BR-11 | Halang tempahan bertindih | UR-01 | FR-TMP-04 | UC-01 | TC-TMP-06, TC-TMP-07, TC-CONC-01 | 1 |
| BR-12 | Bilik terkawal melalui kelulusan | UR-08 | FR-KLS-01 hingga FR-KLS-06 | UC-03 | TC-KLS-01 hingga TC-KLS-04 | 1 |
| BR-13 | Tempahan berulang sekali sahaja | UR-05 | FR-TMP-10, FR-TMP-11 | UC-01 | TC-TMP-08, TC-TMP-09 | 2 |
| BR-14 | Lepaskan bilik tidak digunakan | UR-09 | FR-CHK-01 hingga FR-CHK-06 | UC-04, UC-17 | TC-CHK-01 hingga TC-CHK-04 | 3 |
| BR-15 | Jemputan kalendar peribadi | UR-07 | FR-NOT-03, FR-NOT-04 | UC-01 | TC-NOT-01, TC-NOT-02 | 2 |
| BR-16 | Tempah bagi pihak pegawai | UR-04 | FR-TMP-09 | UC-01 | TC-TMP-10 | 1 |
| BR-17 | Permintaan sokongan bersama tempahan | UR-10 | FR-SOK-01 hingga FR-SOK-05 | UC-01 | TC-SOK-01, TC-SOK-02 | 3 |
| BR-18 | Kadar penggunaan bilik | UR-12 | FR-LAP-04, FR-LAP-05 | — | TC-LAP-01, TC-LAP-02 | 2–3 |
| BR-19 | Batal tempahan sebelum kelulususan | UR-06 | FR-TMP-14 | UC-02 | TC-TMP-16 | 1 |
| BR-20 | Rekod tunggal setiap peralatan | UR-29, UR-30 | FR-AST-01 hingga FR-AST-07, FR-AST-12 | UC-14 | TC-AST-01 hingga TC-AST-05 | 2 |
| BR-21 | Kenal pasti peralatan dalam saat | UR-24, UR-31 | FR-AST-08, FR-AST-09, FR-TKT-03 | UC-10, UC-12 | TC-AST-06, TC-AST-07 | 2 |
| BR-22 | Aduan dengan nombor rujukan | UR-20, UR-21, UR-22 | FR-TKT-01 hingga FR-TKT-06, FR-TKT-17 | UC-10, UC-13 | TC-TKT-01 hingga TC-TKT-05 | 2 |
| BR-23 | Pemilik tugas jelas setiap masa | UR-23, UR-26 | FR-TKT-09, FR-TKT-10, FR-TKT-12 | UC-11, UC-12 | TC-TKT-06 hingga TC-TKT-08 | 2 |
| BR-24 | Tempoh pemulihan diukur | UR-27, UR-34 | FR-TKT-07, FR-TKT-20, FR-LAP-07 | UC-18 | TC-SLA-01 hingga TC-SLA-05 | 2 |
| BR-25 | Eskalasi automatik hampir langgar | UR-27 | FR-TKT-20 | UC-18 | TC-SLA-04, TC-SLA-05 | 2 |
| BR-26 | Penyelenggaraan pencegahan dijadualkan | UR-32 | FR-PMV-01 hingga FR-PMV-09 | UC-16 | TC-PMV-01 hingga TC-PMV-04 | 3 |
| BR-27 | Semak waranti sebelum kerja berbayar | UR-28 | FR-VDR-04, FR-TKT-16 | UC-12 | TC-VDR-01, TC-VDR-02 | 2–3 |
| BR-28 | Kos alat ganti dikumpul mengikut aset | UR-25, UR-33 | FR-TKT-13, FR-TKT-14, FR-STK-01 hingga FR-STK-07 | UC-12 | TC-STK-01 hingga TC-STK-04 | 2 |
| BR-29 | Pergerakan aset direkod | UR-29 | FR-AST-06, FR-AST-14 | UC-15 | TC-AST-08, TC-AST-09 | 2 |
| BR-30 | Aset paling kerap rosak | UR-34 | FR-LAP-08 | — | TC-LAP-04 | 2 |
| BR-31 | Prestasi vendor dinilai | — | FR-VDR-07 | — | TC-VDR-03 | Pilihan |

## 3. Liputan Peraturan Perniagaan

| Peraturan | FR yang menguatkuasakan | Kekangan data | TC |
|---|---|---|---|
| BRL-01 Tiada pertindihan tempahan | FR-TMP-04 | DI-01 | TC-TMP-06, TC-CONC-01 |
| BRL-02 Tiada tempahan masa lepas | FR-TMP-05 | — | TC-TMP-11 |
| BRL-03 Dalam waktu operasi | FR-TMP-06 | — | TC-TMP-12 |
| BRL-04 Had tempoh awalan | FR-TMP-08, FR-ADM-03 | — | TC-TMP-13 |
| BRL-05 Pembatalan lewat direkod | FR-TMP-14 | — | TC-TMP-14 |
| BRL-06 Pelepasan tanpa daftar masuk | FR-CHK-05 | — | TC-CHK-03 |
| BRL-07 Kapasiti tidak dilebihi | FR-TMP-07 | DI-03 | TC-TMP-15 |
| BRL-08 Nombor pendaftaran unik | FR-AST-02 | DI-04 | TC-AST-03 |
| BRL-09 Aset dilupuskan tiada tiket baharu | FR-AST-14 | DI-09 | TC-AST-10 |
| BRL-10 Penutupan selepas pengesahan | FR-TKT-17, FR-TKT-18 | — | TC-TKT-09, TC-TKT-10 |
| BRL-11 Keutamaan menentukan SLA | FR-TKT-07, FR-TKT-08 | DI-08 | TC-SLA-01 |
| BRL-12 Kerja berbayar dalam waranti | FR-TKT-16 | — | TC-VDR-02 |
| BRL-13 Pengeluaran stok berkait tiket | FR-STK-03 | DI-06 | TC-STK-02 |
| BRL-14 Batal pra-kelulususan | FR-TMP-14 | — | TC-TMP-16 |

## 4. Liputan Keperluan Bukan Fungsian

| Kategori NFR | Bilangan keperluan | Kaedah pengesahan | TC |
|---|---|---|---|
| Prestasi | 8 | Ujian beban automatik | TC-PRF-01 hingga TC-PRF-05 |
| Ketersediaan | 7 | Ujian pemulihan bencana dan semakan konfigurasi | TC-AVL-01, TC-AVL-02 |
| Kebolehgunaan | 8 | Ujian pengguna berpandu dengan lima peserta setiap peranan | TC-UI-01 hingga TC-UI-04 |
| Kebolehcapaian | 5 | Alat audit automatik dan ujian papan kekunci manual | TC-A11Y-01, TC-A11Y-02 |
| Keselamatan | 20 | Ujian penembusan dan senarai semak sebelum go-live | TC-SEC-01 hingga TC-SEC-08 |
| Perlindungan data | 6 | Semakan pematuhan dan ujian penanpanamaan | TC-SEC-05 |
| Kebolehselenggaraan | 7 | Semakan kod dan laporan liputan ujian | Semakan proses |
| Kebolehskalaan | 3 | Ujian beban dengan tiga kali ganda data | TC-PRF-05 |
| Keserasian | 4 | Ujian merentas pelayar | TC-CMP-01 |

## 5. Jurang dan Nota

Perkara berikut dikenal pasti semasa penyediaan matriks ini dan memerlukan perhatian.

| Nota | Butiran | Tindakan |
|---|---|---|
| J-01 | BR-31 prestasi vendor hanya mempunyai satu FR pilihan dan tiada UR | Sahkan sama ada keperluan ini kekal dalam skop atau digugurkan |
| J-02 | Tiada BR yang secara langsung memandu keperluan pengurusan stok | BR-28 diperluaskan untuk merangkumi stok. Sahkan dengan pemilik proses |
| J-03 | FR-BLK-09 sumber boleh tempah selain bilik tiada UR | Ini adalah keperluan yang timbul daripada perbincangan teknikal. Sahkan keperluan sebenar sebelum membina |
| J-04 | FR-TKT-11 cadangan juruteknik automatik tiada BR | Keperluan bertaraf pilihan. Boleh digugurkan tanpa kesan kepada objektif |

Setiap jurang mesti diselesaikan sebelum baseline keperluan ditetapkan. Keperluan tanpa punca perniagaan adalah skop tambahan yang perlu dibuang atau dibenarkan secara eksplisit.

### 5.1 Modul Tambahan M17 — Pembantu AI (Luar Baseline)

Modul M17 (chatbot Google AI Studio) dibina selepas baseline sebagai tambahan yang diminta secara berasingan. Ia tidak berpunca daripada BR atau UR sedia ada; jejakannya dikekalkan di bawah supaya setiap FR masih berakhir dengan kes ujian.

| FR | Keperluan ringkas | TC | Status |
|---|---|---|---|
| FR-CHB-01 | Laman chat dengan sejarah dalam DB bagi semua peranan | TC-CHB-01, TC-CHB-02, TC-CHB-15 | Lulus |
| FR-CHB-02 | Sejarah terhad dihantar ke Gemini; balasan dan token disimpan | TC-CHB-03, TC-CHB-04, TC-CHB-05 | Lulus |
| FR-CHB-03 | Perbualan baharu dan padam perbualan milik sendiri | TC-CHB-08, TC-CHB-09 | Lulus |
| FR-CHB-04 | Tajuk automatik daripada mesej pertama | TC-CHB-01 | Lulus |
| FR-CHB-05 | Tetapan tanpa kod oleh pentadbir sistem | TC-CHB-10 hingga TC-CHB-14 | Lulus |
| FR-CHB-06 | Kadar had dan ralat mesra (dinyahaktif / tiada kunci / API gagal) | TC-CHB-06, TC-CHB-07, TC-CHB-14 | Lulus |

## 6. Kawalan Perubahan

Selepas dokumen ini disahkan, sebarang perubahan kepada keperluan mengikut proses berikut.

1. Permohonan perubahan dihantar menggunakan borang dalam lampiran, menyatakan keperluan yang terjejas dan sebab.
2. Pengurus projek menilai kesan kepada jadual, kos dan keperluan lain menggunakan matriks ini.
3. Perubahan yang menjejaskan objektif projek memerlukan kelulusan penaja projek. Perubahan lain diluluskan oleh pemilik proses.
4. Matriks ini dikemas kini bersama dokumen keperluan yang terjejas dalam satu kemas kini berversi.
5. Kes ujian yang terjejas dikemas kini sebelum pembangunan bermula.
