# SPFA — Sistem Pengurusan Fasiliti & Aset ICT

Set dokumentasi keperluan bagi sebuah sistem dalaman yang menggabungkan **tempahan bilik mesyuarat** dan **pengurusan penyelenggaraan peralatan ICT**.

- **Nama sistem (cadangan):** SPFA (Sistem Pengurusan Fasiliti & Aset ICT)
- **Versi dokumen:** v1.0 (Draf untuk semakan)
- **Tarikh:** 7 September 2026
- **Disediakan oleh:** Claude Code
- **Status:** Draf — memerlukan pengesahan pemilik proses (business owner)

---

## Indeks Dokumen

| # | Dokumen | Kandungan | Audiens utama |
|---|---|---|---|
| 00 | [Project Charter & Vision](00-project-charter.md) | Latar belakang, objektif, skop, stakeholder, kekangan | Pengurusan, PMO |
| 01 | [Senarai & Peta Modul](01-modules.md) | 17 modul, sempadan modul, matriks peranan, fasa | Semua |
| 02 | [BRS — Business Requirements](02-BRS-business-requirements.md) | Keperluan perniagaan, KPI, kes perniagaan | Pengurusan atasan |
| 03 | [URS — User Requirements](03-URS-user-requirements.md) | Keperluan pengguna mengikut persona, user story | Pengguna, BA |
| 04 | [SRS — Software Requirements](04-SRS-software-requirements.md) | Keperluan fungsian terperinci (FR) setiap modul | Pembangun, QA |
| 05 | [DRD — Data Requirements](05-DRD-data-requirements.md) | Model data, ERD, kamus data, integriti, penyimpanan | Pentadbir data, DBA |
| 06 | [SDD — Reka Bentuk Sistem](06-SDD-system-design.md) | Seni bina, teknologi, komponen, penggunaan (deployment) | Arkitek, pembangun |
| 07 | [Use Case & Proses Perniagaan](07-use-cases.md) | UC terperinci, rajah aliran proses | BA, QA |
| 08 | [NFR, Keselamatan & Pematuhan](08-NFR-security-compliance.md) | Prestasi, keselamatan, PDPA, ketersediaan | Arkitek, keselamatan ICT |
| 09 | [Spesifikasi API](09-api-specification.md) | Endpoint REST, model permintaan/respons, kod ralat | Pembangun, integrator |
| 10 | [Spesifikasi UI/UX](10-ui-ux-spec.md) | Peta skrin, susun atur, wireframe teks, kebolehcapaian | Pereka, frontend |
| 11 | [RTM — Matriks Kebolehjejakan](11-RTM-traceability-matrix.md) | Jejak BR → UR → FR → UC → Ujian | QA, auditor |
| 12 | [Pelan Ujian & UAT](12-test-plan-uat.md) | Strategi ujian, kes ujian, skrip UAT, kriteria terima | QA, pengguna |
| 13 | [Pelan Pelaksanaan](13-implementation-plan.md) | Fasa, WBS, migrasi data, latihan, penyerahan | Pengurus projek |
| 14 | [Daftar Risiko](14-risk-register.md) | Risiko, kesan, mitigasi, pemilik | PMO |
| 15 | [Glosari](15-glossary.md) | Istilah & singkatan | Semua |
| A | [Borang Permohonan Perubahan](lampiran/A-borang-permohonan-perubahan.md) | Kawalan perubahan selepas baseline | PMO |
| B | [Templat Pengumpulan Data](lampiran/B-templat-pengumpulan-data.md) | Templat senarai bilik, aset, pelan penyelenggaraan | Pemilik data |
| C | [Senarai Semak Kesediaan](lampiran/C-senarai-semak-kesediaan.md) | Kesediaan pembangunan, UAT dan go-live | Pengurus projek |

---

## Cara Menggunakan Set Ini

1. **Sahkan dulu 00 dan 02.** Semua dokumen lain berpunca daripada skop dan keperluan perniagaan di situ. Perubahan di sini menjejaskan seluruh set.
2. **Bawa 03 kepada pengguna sebenar** untuk pengesahan. URS ditulis dalam bahasa pengguna, bukan bahasa teknikal.
3. **Serahkan 04, 05, 06 dan 09 kepada pasukan pembangunan.** Ini adalah kontrak teknikal.
4. **Guna 11 sebagai alat kawalan.** Setiap keperluan perniagaan mesti berakhir dengan sekurang-kurangnya satu kes ujian.
5. **Sebarang perubahan selepas baseline** hendaklah melalui borang permohonan perubahan dalam [lampiran/](lampiran/).

## Tafsiran Singkatan Dokumen

Istilah dokumentasi berbeza antara organisasi. Tafsiran yang digunakan di sini:

| Singkatan | Tafsiran dalam set ini |
|---|---|
| BRS | Business Requirements Specification — apa yang organisasi mahu capai |
| URS | User Requirements Specification — apa yang pengguna perlu buat |
| SRS | Software Requirements Specification — apa yang sistem mesti lakukan |
| DRD | Data Requirements Document — data apa yang perlu disimpan, hubungannya dan peraturannya |
| SDD | System Design Document — bagaimana sistem dibina |
| RTM | Requirements Traceability Matrix — jejak keperluan hingga ujian |

Jika organisasi anda mentafsir DRD sebagai *Detailed Requirements Document*, gabungkan dokumen 04 dan 05.
