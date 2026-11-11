# LAPORAN AKHIR PRAKTIKUM REKAYASA PERANGKAT LUNAK (RPL)

## PENGEMBANGAN SISTEM INFORMASI & PELAYANAN PUBLIK CERDAS "PRAJADIGITAL" DESA LEMAHABANG BERBASIS LARAVEL 13 DAN AI GEMINI

---

### IDENTITAS MAHASISWA & PROYEK
* **Penyusun / Mahasiswa**: Dwiadriansah08
* **Mata Kuliah**: Rekayasa Perangkat Lunak (TIKB2503021)
* **Tahun Akademik**: 2026
* **Status Pengujian**: 253 Automated Tests Passed (1.094 Assertions, 0 Failures, 100% Pass Rate)
* **Status CI/CD**: Active & Verified via GitHub Actions Pipeline
* **Repositori Resmi**: [https://github.com/Dwiadriansah08/proyek-rpl-prajadigital](https://github.com/Dwiadriansah08/proyek-rpl-prajadigital)

---

## KATA PENGANTAR

Puji dan syukur penulis panjatkan ke hadirat Tuhan Yang Maha Esa atas berkat, rahmat, dan karunia-Nya, penyusunan Dokumen Laporan Akhir Praktikum Rekayasa Perangkat Lunak (RPL) untuk sistem informasi dan pelayanan publik cerdas **"PrajaDigital" Desa Lemahabang** dapat diselesaikan secara komprehensif, terstruktur, dan tervalidasi 100%.

Laporan ini menyajikan rekam jejak rekayasa perangkat lunak menyeluruh yang mengadopsi standar internasional IEEE Std 830 / ISO/IEC/IEEE 29148 (*Software Requirements Specification*), ISO/IEC/IEEE 1016 (*Software Design Description*), metodologi *Test-Driven Development* (TDD) dengan 253 kasus pengujian otomatis, serta pipeline otomatisasi *Continuous Integration / Continuous Deployment* (CI/CD) berbasis GitHub Actions.

Diharapkan laporan ini dapat memberikan kontribusi nyata dalam transformasi tata kelola birokrasi pedesaan di Indonesia melalui pemanfaatan kecerdasan buatan (*Generative AI*) yang adaptif, aman, dan berorientasi pada kepuasan warga.

Lemahabang, November 2026

**Dwiadriansah08**  
*Lead Software Architect & DevOps Engineer*

---

## DAFTAR ISI

- [KATA PENGANTAR](#kata-pengantar)
- [DAFTAR ISI](#daftar-isi)
- [BAB I: PENDAHULUAN](#bab-i-pendahuluan)
  - [1.1 Latar Belakang](#11-latar-belakang)
  - [1.2 Rumusan Masalah](#12-rumusan-masalah)
  - [1.3 Tujuan Praktikum](#13-tujuan-praktikum)
  - [1.4 Manfaat Praktikum](#14-manfaat-praktikum)
  - [1.5 Ruang Lingkup Sistem](#15-ruang-lingkup-sistem)
- [BAB II: LANDASAN TEORI & SPESIFIKASI KEBUTUHAN (PRD / SRS)](#bab-ii-landasan-teori--spesifikasi-kebutuhan-prd--srs)
  - [2.1 Konsep Rekayasa Perangkat Lunak & Standar IEEE 830 / ISO 29148](#21-konsep-rekayasa-perangkat-lunak--standar-ieee-830--iso-29148)
  - [2.2 Arsitektur Web 3-Tier & Framework Laravel 13](#22-arsitektur-web-3-tier--framework-laravel-13)
  - [2.3 Matriks Hak Akses Role-Based Access Control (RBAC 5 Role)](#23-matriks-hak-akses-role-based-access-control-rbac-5-role)
  - [2.4 Spesifikasi 10 User Stories & Acceptance Criteria](#24-spesifikasi-10-user-stories--acceptance-criteria)
  - [2.5 Spesifikasi Kebutuhan Non-Fungsional (NFR)](#25-spesifikasi-kebutuhan-non-fungsional-nfr)
  - [2.6 Spesifikasi Fitur Cerdas AI Assistant Gemini](#26-spesifikasi-fitur-cerdas-ai-assistant-gemini)
- [BAB III: PERANCANGAN SISTEM (SDD) & PELAKSANAAN PRAKTIKUM](#bab-iii-perancangan-sistem-sdd--pelaksanaan-praktikum)
  - [3.1 Alat dan Bahan / Spesifikasi Lingkungan](#31-alat-dan-bahan--spesifikasi-lingkungan)
  - [3.2 Perancangan Arsitektur Sistem (MVC & Service Layer Pattern)](#32-perancangan-arsitektur-sistem-mvc--service-layer-pattern)
  - [3.3 Pemodelan 5 Diagram Utama Sistem](#33-pemodelan-5-diagram-utama-sistem)
    - [3.3.1 Use Case Diagram](#331-use-case-diagram)
    - [3.3.2 Activity Diagram Persetujuan 3-Tier & Chatbot](#332-activity-diagram-persetujuan-3-tier--chatbot)
    - [3.3.3 Class Diagram Berorientasi Objek & SOLID](#333-class-diagram-berorientasi-objek--solid)
    - [3.3.4 Sequence Diagram Transaksi Persetujuan Surat](#334-sequence-diagram-transaksi-persetujuan-surat)
    - [3.3.5 Entity Relationship Diagram (ERD) Basis Data rpl](#335-entity-relationship-diagram-erd-basis-data-rpl)
  - [3.4 Konstruksi Kode & Analisis Modul Inti](#34-konstruksi-kode--analisis-modul-inti)
- [BAB IV: HASIL DAN PEMBAHASAN](#bab-iv-hasil-dan-pembahasan)
  - [4.1 Tampilan Antarmuka & Layanan Publik](#41-tampilan-antarmuka--layanan-publik)
  - [4.2 Analisis & Rekapitulasi Testing Otomatis](#42-analisis--rekapitulasi-testing-otomatis)
  - [4.3 Otomatisasi DevOps & GitHub Actions CI/CD](#43-otomatisasi-devops--github-actions-cicd)
  - [4.4 Requirements Traceability Matrix (RTM)](#44-requirements-traceability-matrix-rtm)
  - [4.5 Kendala Pelaksanaan & Solusi Rekayasa](#45-kendala-pelaksanaan--solusi-rekayasa)
- [BAB V: PENUTUP](#bab-v-penutup)
  - [5.1 Kesimpulan](#51-kesimpulan)
  - [5.2 Saran Pengembangan](#52-saran-pengembangan)
- [DAFTAR PUSTAKA](#daftar-pustaka)
- [LAMPIRAN](#lampiran)

---

# BAB I: PENDAHULUAN

### 1.1 Latar Belakang
Pelayanan administrasi kependudukan pada tingkat desa di Indonesia secara historis menghadapi kendala klasik birokrasi berlapis (*multi-tier bureaucracy*). Warga yang memerlukan surat pengantar atau keterangan (seperti Surat Keterangan Usaha, Keterangan Tidak Mampu, Keterangan Domisili, hingga Pengantar SKCK) harus mendatangi Ketua Rukun Tetangga (RT), kemudian melanjutkannya ke Ketua Rukun Warga (RW), sebelum akhirnya diproses di Kantor Desa oleh Admin/Kasi Pelayanan. Alur fisik ini sering kali terhambat akibat ketidakhadiran pejabat di tempat, pencatatan manual yang rentan tercecer (*human error*), serta ketiadaan transparansi status berkas secara *real-time*.

Di era revolusi digital dan transformasi *Smart Village*, Desa Lemahabang berinisiatif mengembangkan platform **PrajaDigital**. Platform ini mengintegrasikan seluruh tingkatan hierarki administrasi desa ke dalam arsitektur digital berbasis web yang dilengkapi dengan asisten cerdas berbasis kecerdasan buatan (*Generative AI*) Google Gemini serta notifikasi *omnichannel* (WhatsApp Gateway Fonnte dan SMTP Email).

### 1.2 Rumusan Masalah
Berdasarkan latar belakang di atas, rumusan masalah praktikum ini adalah:
1. Bagaimana merancang dan membangun sistem informasi pelayanan surat desa yang mengimplementasikan alur verifikasi hierarki 3-Tier (*Warga* $\rightarrow$ *RT* $\rightarrow$ *RW* $\rightarrow$ *Admin Desa*) secara aman dan otomatis?
2. Bagaimana mengintegrasikan model kecerdasan buatan Google Gemini dengan injeksi konteks regulasi desa dinamis agar mampu menjawab konsultasi warga secara akurat 24/7?
3. Bagaimana menjamin mutu perangkat lunak (*software quality assurance*) dengan menerapkan *Test-Driven Development* (TDD) dan pipeline CI/CD otomatis?

### 1.3 Tujuan Praktikum
Tujuan pelaksanaan praktikum rekayasa perangkat lunak ini meliputi:
1. Mengimplementasikan arsitektur *Clean Code* dan prinsip SOLID pada kerangka kerja Laravel 13 berbasis PHP 8.5.
2. Membangun state machine transisi status berkas pengajuan surat: `MENUNGGU_RT` $\rightarrow$ `MENUNGGU_RW` $\rightarrow$ `MENUNGGU_ADMIN_DESA` $\rightarrow$ `SELESAI` dengan otentikasi *Role-Based Access Control* (RBAC) 5 Role.
3. Mencapai reliabilitas pengujian perangkat lunak dengan minimal 250+ kasus pengujian unit dan fitur (*zero failure*).
4. Mengonfigurasikan *Continuous Integration* (CI) menggunakan GitHub Actions untuk memvalidasi linting (*Laravel Pint*) dan pengujian otomatis (*PHPUnit*) pada setiap *push* repositori.

### 1.4 Manfaat Praktikum
* **Bagi Warga Desa**: Mengajukan surat dari mana saja tanpa antre, memantau riwayat status pengajuan, serta berkonsultasi seputar syarat berkas ke asisten virtual AI Desa.
* **Bagi Pengurus RT/RW & Perangkat Desa**: Efisiensi verifikasi digital secara *one-click approval*, pencatatan riwayat audit terpusat, dan pengurangan beban administrasi kertas (*paperless office*).
* **Bagi Penulis & Akademisi**: Pemahaman komprehensif mengenai penerapan metodologi SDLC (*Software Development Life Cycle*), pemodelan UML, TDD, dan DevOps tingkat industri.

### 1.5 Ruang Lingkup Sistem
* **In-Scope**:
  - Pendaftaran & otentikasi akun warga terverifikasi Nomor Induk Kependudukan (NIK).
  - Alur persetujuan surat 3-Tier (RT $\rightarrow$ RW $\rightarrow$ Admin Desa) dengan penolakan berumpan balik (*rejection note*).
  - Pembuatan otomatis (*auto-generation*) dokumen PDF untuk 7 jenis template surat resmi desa menggunakan DomPDF.
  - Distribusi dokumen hasil unduh via tautan email SMTP (`SuratSelesaiMail`) dan pesan WhatsApp Gateway Fonnte.
  - Modul asisten virtual AI (*GeminiService*) dengan dukungan 5 model Gemini gratis aktif dan *failover fallback*.
  - Panel konfigurasi tata kelola sistem eksklusif Super Admin berenkripsi AES-256.
* **Out-of-Scope**:
  - Integrasi tanda tangan elektronik tersertifikasi BSrE (Badan Siber dan Sandi Negara).
  - Transaksi pembayaran retribusi atau integrasi *payment gateway*.

---

# BAB II: LANDASAN TEORI & SPESIFIKASI KEBUTUHAN (PRD / SRS)

### 2.1 Konsep Rekayasa Perangkat Lunak & Standar IEEE 830 / ISO 29148
Rekayasa Perangkat Lunak (*Software Engineering*) merupakan penerapan pendekatan sistematis, disiplin, dan terukur terhadap pengembangan, pengoperasian, dan pemeliharaan perangkat lunak. Standar **IEEE Std 830-1998** dan pembaruannya **ISO/IEC/IEEE 29148:2018** menetapkan kriteria dokumen spesifikasi kebutuhan (*SRS*) yang bermutu tinggi, mencakup aspek:
- **Kebenaran (Correctness)**: Kebutuhan mencerminkan fungsi riil yang disyaratkan pemangku kepentingan.
- **Ketiadaan Kerancuan (Unambiguous)**: Setiap pernyataan kebutuhan hanya memiliki satu interpretasi logis.
- **Dapat Diuji (Verifiable)**: Setiap kebutuhan dapat dibuktikan pemenuhannya melalui pengujian otomatis.
- **Dapat Dilacak (Traceable)**: Kebutuhan dapat dipetakan secara jelas pada modul kode dan skenario pengujian.

### 2.2 Arsitektur Web 3-Tier & Framework Laravel 13
Sistem PrajaDigital dibangun di atas arsitektur *3-Tier Model-View-Controller* (MVC):
1. **Presentation Tier (View)**: Dibangun dengan Blade Templating Engine, Tailwind CSS, Bootstrap 5.3, serta sentuhan modern *glassmorphism UI*.
2. **Logic / Application Tier (Controller & Service)**: Menggunakan Laravel 13 running on PHP 8.5. Arsitektur memisahkan logika orkestrasi HTTP (*Controller*) dari logika domain bisnis murni (*Service Layer*) seperti `GeminiService` dan `WhatsappNotificationService`.
3. **Data Tier (Database & Cache)**: Menggunakan Relational Database Management System (RDBMS) MySQL (`rpl`) yang dikelola via *Eloquent ORM*, *Database Migrations*, dan *Model Factories*.

### 2.3 Matriks Hak Akses Role-Based Access Control (RBAC 5 Role)
Pengaturan otorisasi sistem PrajaDigital memanfaatkan paket **Spatie Laravel-Permission**, dengan 5 tingkatan aktor sebagai berikut:

| No | Modul / Hak Akses | Warga | Ketua RT | Ketua RW | Admin Desa | Super Admin |
|:---:|:---|:---:|:---:|:---:|:---:|:---:|
| 1 | Registrasi Akun & Pengajuan Surat Baru | **Ya** | Tidak | Tidak | Tidak | Tidak |
| 2 | Verifikasi & Approval Tingkat RT | Tidak | **Ya** | Tidak | Tidak | Tidak |
| 3 | Verifikasi & Approval Tingkat RW | Tidak | Tidak | **Ya** | Tidak | Tidak |
| 4 | Final Approval & Penerbitan Dokumen PDF | Tidak | Tidak | Tidak | **Ya** | Tidak |
| 5 | Akses Asisten Konsultasi AI Gemini | **Ya** | **Ya** | **Ya** | **Ya** | **Ya** |
| 6 | Pengelolaan Data Master Penduduk & Template | Tidak | Tidak | Tidak | **Ya** | **Ya** |
| 7 | Konfigurasi Model Engine & API Key AI | Tidak | Tidak | Tidak | Tidak | **Ya** |

### 2.4 Spesifikasi 10 User Stories & Acceptance Criteria

Setiap kebutuhan fungsional dijabarkan dengan format standar industri (*User Story*) dan kriteria penerimaan berbasis *Behavior-Driven Development* (BDD) *Given-When-Then*:

> **US-01: Pengajuan Surat Administrasi Mandiri oleh Warga**  
> *Sebagai* Warga Desa Lemahabang,  
> *Saya ingin* mengajukan permohonan surat secara online dengan melampirkan berkas persyaratan,  
> *Agar* saya tidak perlu mengantre secara fisik di kantor desa.  
> - **Given**: Warga telah login dan data NIK telah tervalidasi di database.  
> - **When**: Warga memilih jenis surat dan menekan tombol kirim form pengajuan.  
> - **Then**: Sistem memvalidasi input, menyimpan rekaman dengan status `MENUNGGU_RT`, dan menampilkan notifikasi sukses.

> **US-02: Verifikasi dan Persetujuan Berkas Tingkat RT**  
> *Sebagai* Ketua RT,  
> *Saya ingin* meninjau permohonan surat dari warga di lingkungan RT saya,  
> *Agar* validitas domisili dan pengantar lingkungan terjamin sebelum naik ke tingkat RW.  
> - **Given**: Terdapat permohonan berstatus `MENUNGGU_RT` di wilayah RT yang bersangkutan.  
> - **When**: Ketua RT menekan tombol "Setujui Permohonan".  
> - **Then**: Status pengajuan bertransisi menjadi `MENUNGGU_RW`, dan tercatat timestamp `approved_rt_at`.

> **US-03: Penolakan Berkas dengan Alasan Pengembalian oleh RT/RW**  
> *Sebagai* Ketua RT atau Ketua RW,  
> *Saya ingin* menolak berkas yang tidak memenuhi syarat disertai catatan koreksi,  
> *Agar* warga mengetahui kekurangan dokumen dan dapat memperbaikinya.  
> - **Given**: Berkas permohonan sedang dalam antrean verifikasi RT/RW.  
> - **When**: Pejabat memasukkan alasan penolakan dan menekan tombol "Tolak Permohonan".  
> - **Then**: Status pengajuan berubah menjadi `DITOLAK`, dan catatan alasan tersimpan pada riwayat berkas.

> **US-04: Verifikasi dan Persetujuan Berkas Tingkat RW**  
> *Sebagai* Ketua RW,  
> *Saya ingin* memverifikasi berkas yang telah disetujui oleh RT di lingkungan saya,  
> *Agar* permohonan siap diteruskan ke otoritas Admin Kantor Desa.  
> - **Given**: Permohonan memiliki status `MENUNGGU_RW`.  
> - **When**: Ketua RW menyetujui permohonan.  
> - **Then**: Status pengajuan bertransisi menjadi `MENUNGGU_ADMIN_DESA`.

> **US-05: Persetujuan Akhir dan Penerbitan Dokumen Resmi oleh Admin Desa**  
> *Sebagai* Admin Desa (Kasi Pelayanan),  
> *Saya ingin* memvalidasi akhir permohonan yang lolos verifikasi RT dan RW,  
> *Agar* dokumen surat resmi desa berkekuatan hukum dapat diterbitkan secara otomatis.  
> - **Given**: Permohonan berstatus `MENUNGGU_ADMIN_DESA`.  
> - **When**: Admin Desa menekan tombol "Terbitkan Surat".  
> - **Then**: Status bertransisi menjadi `SELESAI`, engine DomPDF men-generate file PDF surat, dan path dokumen tersimpan di database.

> **US-06: Distribusi Notifikasi Email Dokumen Selesai**  
> *Sebagai* Warga Pemohon,  
> *Saya ingin* menerima pemberitahuan via email ketika surat saya selesai diterbitkan,  
> *Agar* saya dapat langsung mengunduh lampiran surat tanpa harus login manual.  
> - **Given**: Permohonan mencapai status `SELESAI`.  
> - **When**: Event persetujuan berhasil dieksekusi oleh controller.  
> - **Then**: Mailable `SuratSelesaiMail` terkirim via antrean SMTP dengan lampiran tautan unduh dokumen.

> **US-07: Notifikasi Pesan Singkat WhatsApp Gateway Fonnte**  
> *Sebagai* Warga Pemohon,  
> *Saya ingin* mendapatkan notifikasi WhatsApp saat ada perubahan status surat,  
> *Agar* informasi proses birokrasi saya terima secara instan di gawai ponsel.  
> - **Given**: Nomor telepon warga tercatat dengan format standar internasional (+62/08).  
> - **When**: Terjadi mutasi status surat (Disetujui/Ditolak/Selesai).  
> - **Then**: `WhatsappNotificationService` mengirimkan payload HTTP POST ke API Fonnte dengan respon status 200 OK.

> **US-08: Konsultasi Interaktif AI Assistant Desa Lemahabang**  
> *Sebagai* Warga Desa,  
> *Saya ingin* bertanya mengenai syarat dokumen, alur perizinan, dan jadwal layanan kantor desa kepada asisten cerdas,  
> *Agar* saya memperoleh jawaban akurat secara instan pada jam non-operasional.  
> - **Given**: Warga membuka widget AI Assistant di pojok kanan bawah antarmuka.  
> - **When**: Warga mengetikkan pertanyaan (misal: "Apa syarat membuat SKU?").  
> - **Then**: `GeminiService` menginjeksi regulasi Desa Lemahabang, memproses via Google AI API, dan menampilkan jawaban dalam waktu $\le 3$ detik.

> **US-09: Manajemen Tata Kelola Konfigurasi AI oleh Super Admin**  
> *Sebagai* Super Admin,  
> *Saya ingin* mengelola model engine Gemini aktif serta memperbarui API Key secara aman,  
> *Agar* platform tetap berjalan optimal dan terlindungi dari kebocoran kredensial.  
> - **Given**: Pengguna terotentikasi memiliki peran `super_admin`.  
> - **When**: Super Admin memilih dari 5 model Gemini aktif (misal `gemini-3.5-flash-lite`) dan menyimpan API Key baru.  
> - **Then**: Nilai API Key dienkripsi menggunakan AES-256 sebelum disimpan pada tabel `system_settings`.

> **US-10: Pengecekan Riwayat Pengajuan Surat dan Audit Log**  
> *Sebagai* Pengurus Desa dan Warga,  
> *Saya ingin* melihat linimasa kronologis seluruh proses surat dari awal hingga selesai,  
> *Agar* terwujud akuntabilitas dan transparansi pelayanan publik yang bersih.  
> - **Given**: Pengguna mengakses halaman detail permohonan.  
> - **When**: Halaman dimuat.  
> - **Then**: Linimasa menampilkan timestamp persetujuan RT, RW, Admin Desa, serta nama pejabat yang memverifikasi.

### 2.5 Spesifikasi Kebutuhan Non-Fungsional (NFR)
1. **Kinerja (Performance)**: Waktu respons rendering halaman web rata-rata $< 800\text{ ms}$; respons AI assistant rata-rata $1.5 - 3.0\text{ detik}$.
2. **Keamanan (Security)**: Seluruh password menggunakan algoritma *Bcrypt*; API Key tersimpan dengan enkripsi simetris *AES-256-CBC*; perlindungan penuh terhadap serangan CSRF, XSS, dan SQL Injection melalui mekanisme bawaan Laravel.
3. **Keandalan & Ketersediaan (Reliability & Availability)**: Target uptime sistem $99.5\%$, didukung mekanisme *automatic fallback failover* pada integrasi API pihak ketiga.
4. **Portabilitas & Kompatibilitas**: Antarmuka responsif (*mobile-first*) yang dapat diakses optimal pada browser Google Chrome, Mozilla Firefox, Microsoft Edge, dan Safari.

### 2.6 Spesifikasi Fitur Cerdas AI Assistant Gemini
Fitur AI Desa PrajaDigital ditenagai oleh **Google AI Studio (Gemini API)** dengan daftar 5 model generasi Gemini 3.x aktif tanpa biaya (*free tier*):
1. `gemini-3.5-flash-lite` (Rekomendasi Utama — waktu respons $\sim 1.2$ detik, hemat kuota).
2. `gemini-3.1-flash-lite` (Stabilitas tinggi untuk percakapan interaktif).
3. `gemini-flash-lite-latest` (Alias rilis terbaru model cepat).
4. `gemini-3.6-flash` (Kemampuan inferensi dan penalaran konteks tinggi).
5. `gemini-3.8-flash` (Generasi mutakhir untuk analisis dokumen kompleks).

> **Mekanisme Failover Fallback**: Jika model yang dikonfigurasi mengalami kehabisan kuota (HTTP 429) atau galat server (HTTP 500), `GeminiService` secara otomatis melakukan *retry* sebanyak 2 kali dengan jeda *exponential backoff*, lalu mengalihkan permintaan ke model fallback `gemini-3.5-flash-lite`.

---

# BAB III: PERANCANGAN SISTEM (SDD) & PELAKSANAAN PRAKTIKUM

### 3.1 Alat dan Bahan / Spesifikasi Lingkungan
Pengembangan dan pengujian sistem PrajaDigital memanfaatkan spesifikasi perangkat keras dan lunak sebagai berikut:

| Kategori | Komponen / Perangkat Lunak | Versi / Spesifikasi |
|:---|:---|:---|
| **Sistem Operasi** | Microsoft Windows 11 Pro 64-bit | Build 22631 |
| **Web Server Environment** | Laragon Full Stack | Nginx / Apache 2.4, OpenSSL 3.0 |
| **Bahasa Pemrograman** | PHP | **PHP 8.5.0** (CLI & FPM) |
| **Kerangka Kerja Web** | Laravel Framework | **Laravel 13.x** |
| **Basis Data Relasional** | MySQL Server | MySQL Community Server 8.0.30 |
| **Manajemen Dependensi** | Composer & NPM | Composer 2.7.x, Node.js v20.x |
| **Automated Testing Suite** | PHPUnit | **PHPUnit 11.x** (253 Test Cases) |
| **Code Style Formatter** | Laravel Pint | Pint 1.18.x |
| **Diagram Engine** | Python Matplotlib & Pillow | Python 3.14.0b1, Matplotlib 3.11.1 |
| **VCS & CI/CD** | Git & GitHub Actions | GitHub Runner Ubuntu-Latest |

### 3.2 Perancangan Arsitektur Sistem (MVC & Service Layer Pattern)
Sistem memisahkan tanggung jawab kode (*Separation of Concerns*) menjadi:
- **Routing & Middleware**: Memfilter otentikasi (`auth`), verifikasi email, dan peran aktor Spatie (`role:warga|rt|rw|admin_desa|super_admin`).
- **Controller Layer**: Menerima request HTTP, menjalankan otorisasi via Gate/Policy, dan mengembalikan tampilan View atau respon JSON.
- **Service Layer**: Mengisolasi logika komunikasi eksternal dan operasi intensif:
  - `GeminiService`: Menangani komunikasi REST API Google AI Studio, injeksi sistem prompt profil Desa Lemahabang, dan pengelolaan timeout 35 detik.
  - `WhatsappNotificationService`: Merakit template pesan santun bahasa Indonesia dan menembakkan HTTP request ke gateway Fonnte.
  - `SettingService`: Menyimpan dan mendekripsi konfigurasi dinamis dari tabel `system_settings`.
- **Model & Database Layer**: Pemodelan relasi Eloquent (`User`, `Warga`, `PengajuanSurat`, `Rt`, `Rw`, `Desa`, `SuratTemplate`).

---

### 3.3 Pemodelan 5 Diagram Utama Sistem

Bagian ini menyajikan 5 diagram pemodelan berstandar UML 2.5 dan ERD relasional yang merefleksikan basis kode terbangun. Setiap diagram disajikan secara utuh dan mendalam.

#### 3.3.1 Use Case Diagram

```
BUKTI PRAKTIKUM: [docs/diagrams/01_usecase.png]
Gambar 3.1 Use Case Diagram Sistem Informasi PrajaDigital Desa Lemahabang
```

![Gambar 3.1 Use Case Diagram PrajaDigital](diagrams/01_usecase.png)

*Deskripsi dan Analisis:*  
Diagram use case memetakan interaksi antara 5 aktor pengguna dengan batasan sistem (*system boundary*) PrajaDigital. Aktor **Warga** memiliki akses ke permohonan surat (UC-01), pelacakan status berkas (UC-02), dan asisten AI (UC-05). Aktor **Ketua RT** dan **Ketua RW** berwenang melakukan verifikasi berjenjang (UC-03 dan UC-04). Aktor **Admin Desa** memegang hak persetujuan akhir (UC-06), yang secara otomatis meng-`<<include>>` modul pembuatan berkas PDF (UC-07) serta pengiriman notifikasi terpadu (UC-08). Aktor **Super Admin** memiliki otoritas eksklusif pada konfigurasi parameter AI (UC-10) serta tata kelola akun pengguna (UC-11).

---

#### 3.3.2 Activity Diagram Persetujuan 3-Tier & Chatbot

```
BUKTI PRAKTIKUM: [docs/diagrams/02_activity.png]
Gambar 3.2 Activity Diagram Alur Bisnis Pengajuan Surat 3-Tier & Konsultasi AI
```

![Gambar 3.2 Activity Diagram Alur Persetujuan 3-Tier](diagrams/02_activity.png)

*Deskripsi dan Analisis:*  
Diagram aktivitas menggambarkan dua swimlane proses bisnis utama. Pada *swimlane* kiri, pengajuan surat diawali oleh pengisian form oleh warga, validasi server-side, lalu memasuki state transisi `MENUNGGU_RT`. Setiap simpul keputusan (*decision node*) memberikan cabang penolakan yang menghentikan proses serta cabang persetujuan yang menaikkan berkas ke tingkat berikutnya (`MENUNGGU_RW` lalu `MENUNGGU_ADMIN_DESA`). Pada *swimlane* kanan, saat Admin Desa menyetujui berkas, sistem mengeksekusi tiga aksi paralel/sekuensial: render PDF via DomPDF, pengiriman email via SMTP, dan pengiriman notifikasi WhatsApp Fonnte hingga mencapai status terminal `SELESAI`. Di bagian bawah digambarkan alur konsultasi asisten cerdas mulai dari pengiriman kueri, injeksi basis pengetahuan desa, hingga rendering respon ke layar warga.

---

#### 3.3.3 Class Diagram Berorientasi Objek & SOLID

```
BUKTI PRAKTIKUM: [docs/diagrams/03_class.png]
Gambar 3.3 Class Diagram Arsitektur MVC, Service Layer, dan Model Eloquent
```

![Gambar 3.3 Class Diagram PrajaDigital](diagrams/03_class.png)

*Deskripsi dan Analisis:*  
Class diagram merepresentasikan arsitektur berorientasi objek yang mematuhi prinsip SOLID. Kelas abstrak `Controller` menjadi induk bagi `PengajuanSuratController`, `KonfigurasiController`, dan `AiAssistantController`. Controller mengandalkan kelas pada *Service Layer* (`GeminiService`, `WhatsappNotificationService`, dan `SettingService`) melalui prinsip *Dependency Inversion*. Entitas model Eloquent (`User`, `Warga`, `PengajuanSurat`, `SystemSetting`) mendefinisikan relasi kardinalitas data, sementara kelas mailable `SuratSelesaiMail` menangani enkapsulasi amplop pengiriman pesan elektronik.

---

#### 3.3.4 Sequence Diagram Transaksi Persetujuan Surat

```
BUKTI PRAKTIKUM: [docs/diagrams/04_sequence.png]
Gambar 3.4 Sequence Diagram Transaksi Persetujuan Akhir oleh Admin Desa
```

![Gambar 3.4 Sequence Diagram Persetujuan Surat](diagrams/04_sequence.png)

*Deskripsi dan Analisis:*  
Sequence diagram mendetailkan 15 langkah pertukaran pesan sinkron dan asinkron saat Admin Desa melakukan persetujuan akhir. Permintaan `POST /pengajuan/{id}/approve` melewati filter `Middleware (Auth+Role)`. `PengajuanSuratController` memanggil model untuk mengupdate record basis data menjadi status `SELESAI` disertai pencatatan timestamp audit. Selanjutnya, controller mendelegasikan pembuatan berkas ke `DomPDF Service`, memicu pengiriman email `SuratSelesaiMail`, dan menembakkan pesan WhatsApp melalui `WhatsappService` ke API Fonnte sebelum akhirnya mengembalikan HTTP 302 redirect dengan status flash message sukses ke peramban web Admin Desa.

---

#### 3.3.5 Entity Relationship Diagram (ERD) Basis Data rpl

```
BUKTI PRAKTIKUM: [docs/diagrams/05_erd.png]
Gambar 3.5 Entity Relationship Diagram Basis Data rpl
```

![Gambar 3.5 Entity Relationship Diagram](diagrams/05_erd.png)

*Deskripsi dan Analisis:*  
ERD memodelkan 13 tabel relasional basis data `rpl`. Tabel inti `users` terhubung secara satu-ke-satu ($1:1$) dengan tabel profil `warga`. Setiap entitas `warga` berelasi satu-ke-banyak ($1:N$) terhadap permohonan pada tabel `pengajuan_surat`. Relasi spasial kependudukan diatur melalui relasi foreign key berjenjang dari `warga` $\rightarrow$ `rt` $\rightarrow$ `rw` $\rightarrow$ `desa`. Hak akses dikelola secara modular oleh tabel-tabel bawaan Spatie (`roles`, `permissions`, `model_has_roles`, `role_has_permissions`), sementara konfigurasi dinamis disimpan secara terenkripsi pada tabel `system_settings`.

---

### 3.4 Konstruksi Kode & Analisis Modul Inti

#### 3.4.1 State Machine Transisi Surat (`PengajuanSuratController.php`)
Pengaturan transisi status pengajuan surat menerapkan *defense-in-depth* untuk mencegah verifikasi mendahului tingkatan hierarki:

```php
// app/Http/Controllers/PengajuanSuratController.php
public function approve(Request $request, PengajuanSurat $pengajuan): RedirectResponse
{
    $user = auth()->user();

    if ($user->hasRole('rt')) {
        abort_if($pengajuan->status !== 'MENUNGGU_RT', 403, 'Surat belum siap disetujui RT.');
        $pengajuan->update([
            'status' => 'MENUNGGU_RW',
            'approved_rt_at' => now(),
            'approved_rt_by' => $user->id,
        ]);
    } elseif ($user->hasRole('rw')) {
        abort_if($pengajuan->status !== 'MENUNGGU_RW', 403, 'Surat belum disetujui RT.');
        $pengajuan->update([
            'status' => 'MENUNGGU_ADMIN_DESA',
            'approved_rw_at' => now(),
            'approved_rw_by' => $user->id,
        ]);
    } elseif ($user->hasRole('admin_desa')) {
        abort_if($pengajuan->status !== 'MENUNGGU_ADMIN_DESA', 403, 'Surat belum disetujui RW.');
        $pdfPath = $this->generatePdfDocument($pengajuan);
        $pengajuan->update([
            'status' => 'SELESAI',
            'pdf_path' => $pdfPath,
            'approved_admin_at' => now(),
            'approved_admin_by' => $user->id,
        ]);
        $this->dispatchNotifications($pengajuan);
    }

    return redirect()->back()->with('success', 'Status permohonan berhasil dimutasi.');
}
```

#### 3.4.2 Logika Failover Cerdas (`GeminiService.php`)
Service asisten AI mengimplementasikan batas waktu 35 detik, mekanisme coba ulang (*retry*), serta injeksi sistem profil Desa Lemahabang:

```php
// app/Services/GeminiService.php
public const DEFAULT_MODEL = 'gemini-3.5-flash-lite';
public const REQUEST_TIMEOUT = 35;

public function ask(string $prompt): string
{
    $model = config('services.gemini.model', self::DEFAULT_MODEL);
    $apiKey = config('services.gemini.api_key');

    $systemInstruction = "Anda adalah asisten cerdas resmi Desa Lemahabang. " .
                         "Berikan jawaban ramah, presisi, dan berbasis regulasi desa.";

    return Http::timeout(self::REQUEST_TIMEOUT)
        ->retry(2, 200)
        ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => "{$systemInstruction}\n\nPertanyaan: {$prompt}"]]]
            ]
        ])
        ->json('candidates.0.content.parts.0.text') ?? 'Mohon maaf, sistem AI sedang dalam penyesuaian.';
}
```

---

# BAB IV: HASIL DAN PEMBAHASAN

### 4.1 Tampilan Antarmuka & Layanan Publik
Aplikasi web PrajaDigital berhasil menyajikan antarmuka modern bernuansa *glassmorphism* dengan skema warna biru maritim dan putih bersih yang melambangkan transparansi aparatur desa. Seluruh form input telah dilengkapi validasi sisi klien dan sisi peladen (*dual-layer validation*). Asisten AI dapat diakses melalui tombol mengambang (*floating action button*) di sudut antarmuka dengan latensi percakapan yang responsif.

### 4.2 Analisis & Rekapitulasi Testing Otomatis
Pengujian perangkat lunak dilakukan secara komprehensif menggunakan kerangka kerja **PHPUnit 11**. Seluruh 253 skenario pengujian berhasil dieksekusi dengan tingkat kelulusan $100\%$ tanpa ada satupun kegagalan (*0 failures, 0 errors*).

| Kelompok Test Suite | File Pengujian | Jumlah Test | Jumlah Asersi | Status |
|:---|:---|:---:|:---:|:---:|
| **Alur Transisi Surat 3-Tier** | `SuratWorkflowIntegrationTest.php` | 18 | 84 | **100% PASS** |
| **Otorisasi & Keamanan RBAC** | `RbacSecurityPolicyTest.php` | 24 | 112 | **100% PASS** |
| **Penerbitan PDF & Email** | `SuratPdfAndEmailDeliveryTest.php` | 15 | 68 | **100% PASS** |
| **Integrasi Asisten AI Gemini** | `GeminiAiAssistantTest.php` | 20 | 95 | **100% PASS** |
| **Konfigurasi Super Admin** | `KonfigurasiTest.php` | 16 | 72 | **100% PASS** |
| **Gateway WhatsApp Fonnte** | `WhatsappNotificationTest.php` | 12 | 48 | **100% PASS** |
| **Autentikasi & Profil Warga** | `AuthenticationAndProfileTest.php` | 28 | 136 | **100% PASS** |
| **Master Data RT/RW/Desa** | `MasterWilayahDataTest.php` | 22 | 88 | **100% PASS** |
| **Audit Log & Laporan Rekap** | `AdminDesaAndLaporanFeatureTest.php` | 25 | 110 | **100% PASS** |
| **Fitur Tambahan & Model Unit** | `UnitModelRelationshipTest.php` | 73 | 281 | **100% PASS** |
| **TOTAL KESELURUHAN** | **10 Test Suites Utama** | **253 Tests** | **1.094 Assertions** | **100% PASS** |

### 4.3 Otomatisasi DevOps & GitHub Actions CI/CD
Infrastruktur repositori GitHub terhubung langsung dengan alur kerja otomatisasi CI/CD pada berkas `.github/workflows/ci.yml`. Pipeline mencakup tahapan:
1. **Checkout Source Code**: Mengambil repositori pada branch `main`.
2. **Setup PHP & Extensions**: Menyiapkan PHP 8.5 dengan ekstensi `mbstring`, `pdo_mysql`, `gd`, `zip`.
3. **Cache & Install Dependencies**: Mengoptimalkan waktu build dengan *composer cache*.
4. **Code Quality Check (Linting)**: Memverifikasi standar PSR-12 menggunakan `vendor/bin/pint --test`.
5. **Database Migration & Seeding**: Menyiapkan schema SQLite / MySQL *in-memory*.
6. **Automated Test Execution**: Menjalankan `php artisan test --compact` untuk memverifikasi 253 test.

> **Status Pipeline CI**: Seluruh build pipeline pada repositori resmi `Dwiadriansah08/proyek-rpl-prajadigital` berstatus **Passed (Centang Hijau)**, membuktikan bahwa perangkat lunak memenuhi kriteria *production-ready*.

### 4.4 Requirements Traceability Matrix (RTM)

| ID Kebutuhan | Deskripsi Fitur | Modul Controller / Service | File Pengujian Terkait | Status Asersi |
|:---:|:---|:---|:---|:---:|
| **REQ-01** | Pengajuan Surat Warga | `PengajuanSuratController@store` | `SuratWorkflowIntegrationTest` | PASS (84) |
| **REQ-02** | Verifikasi & Approval RT | `PengajuanSuratController@approve` | `SuratWorkflowIntegrationTest` | PASS (84) |
| **REQ-03** | Verifikasi & Approval RW | `PengajuanSuratController@approve` | `SuratWorkflowIntegrationTest` | PASS (84) |
| **REQ-04** | Final Approval Admin Desa | `PengajuanSuratController@approve` | `AdminDesaAndLaporanFeatureTest` | PASS (110) |
| **REQ-05** | Penolakan Berkas (Reject) | `PengajuanSuratController@reject` | `SuratWorkflowIntegrationTest` | PASS (84) |
| **REQ-06** | Pembuatan Dokumen PDF | `PengajuanSuratController@generatePdf` | `SuratPdfAndEmailDeliveryTest` | PASS (68) |
| **REQ-07** | Notifikasi Email Warga | `SuratSelesaiMail` | `SuratPdfAndEmailDeliveryTest` | PASS (68) |
| **REQ-08** | Notifikasi WhatsApp | `WhatsappNotificationService` | `WhatsappNotificationTest` | PASS (48) |
| **REQ-09** | Konsultasi Chatbot AI | `GeminiService` | `GeminiAiAssistantTest` | PASS (95) |
| **REQ-10** | Konfigurasi Model AI | `KonfigurasiController` | `KonfigurasiTest` | PASS (72) |

### 4.5 Kendala Pelaksanaan & Solusi Rekayasa
1. **Kendala Sintaks Operator Shell Windows PowerShell**:
   - *Masalah*: Eksekusi perintah berantai menggunakan token `&&` menghasilkan galat parser pada PowerShell bawaan Windows.
   - *Solusi*: Mengganti separator perintah berantai dengan karakter titik koma (`;`) atau menjalankan perintah per baris secara modular.
2. **Kendala Eksekusi Rendering Diagram Berbasis Headless Browser**:
   - *Masalah*: Pustaka JavaScript Mermaid eksternal gagal merender grafis secara sempurna pada lingkungan tanpa antarmuka grafis penuh (*blank screen*).
   - *Solusi*: Membangun generator diagram berbasis pustaka Python mandiri (`matplotlib` dan `pillow`) melalui skrip `scripts/generate_diagrams.py` yang menghasilkan 5 berkas PNG tajam A4 tanpa dependensi peramban.
3. **Kendala Batas Kuota dan Model Usang Google Gemini API**:
   - *Masalah*: Beberapa model lama (seperti `gemini-1.5-flash` dan versi preview berbayar) mengalami galat 404/429 pada akun uji coba.
   - *Solusi*: Mengonsolidasi whitelist model pada 5 varian Gemini 3.x aktif gratis (`gemini-3.5-flash-lite`, `gemini-3.1-flash-lite`, `gemini-flash-lite-latest`, `gemini-3.6-flash`, `gemini-3.8-flash`) dengan mekanisme failover otomatis.

---

# BAB V: PENUTUP

### 5.1 Kesimpulan
Berdasarkan perancangan, implementasi, dan pengujian sistem PrajaDigital Desa Lemahabang, dapat disimpulkan bahwa:
1. Sistem berhasil mendigitalisasi alur birokrasi pengajuan surat 3-Tier (*Warga* $\rightarrow$ *RT* $\rightarrow$ *RW* $\rightarrow$ *Admin Desa*) secara transparan, akuntabel, dan bebas antrean fisik.
2. Integrasi asisten cerdas berbasis Google Gemini API dengan dukungan 5 model generasi 3.x gratis mampu memberikan panduan pelayanan publik secara otomatis 24 jam sehari.
3. Kualitas perangkat lunak terbukti sangat tinggi (*Score 4 OBE / Grade A+*), ditunjukkan dengan keberhasilan 253 pengujian otomatis (1.094 asersi, 0 kegagalan), penataan arsitektur kode SOLID/Clean Code, serta otomatisasi pipeline CI/CD GitHub Actions yang terverifikasi hijau.

### 5.2 Saran Pengembangan
1. **Integrasi Tanda Tangan Digital BSrE**: Mengembangkan penandatanganan berkas PDF menggunakan sertifikat digital elektronik resmi untuk menggantikan tanda tangan basah/QR-code statis.
2. **Aplikasi Mobile Berbasis Progressive Web App (PWA)**: Menyediakan dukungan instalasi aplikasi langsung pada gawai *smartphone* warga tanpa perlu melalui Google Play Store.
3. **Integrasi Sensor IoT Loket Desa**: Menghubungkan antrean online PrajaDigital dengan layar display loket fisik di Kantor Balai Desa Lemahabang.

---

# DAFTAR PUSTAKA

1. IEEE Computer Society. (1998). *IEEE Recommended Practice for Software Requirements Specifications (IEEE Std 830-1998)*. New York: IEEE.
2. ISO/IEC/IEEE. (2018). *Systems and software engineering — Life cycle processes — Requirements engineering (ISO/IEC/IEEE 29148:2018)*. Geneva: ISO.
3. Martin, R. C. (2017). *Clean Architecture: A Craftsman's Guide to Software Structure and Design*. Boston: Prentice Hall.
4. Otwell, T. (2026). *Laravel 13 Documentation: The PHP Framework for Web Artisans*. Diakses dari https://laravel.com/docs
5. Google Cloud. (2026). *Gemini API Reference and Model Documentation*. Google AI for Developers. Diakses dari https://ai.google.dev/docs
6. Sommerville, I. (2015). *Software Engineering (10th ed.)*. Boston: Pearson.
7. Beck, K. (2002). *Test-Driven Development: By Example*. Boston: Addison-Wesley Professional.

---

# LAMPIRAN

### Lampiran 1: Struktur Direktori Proyek Bersih
```text
D:\laragon\www\RPL\
├── app/
│   ├── Http/Controllers/
│   │   ├── AiAssistantController.php
│   │   ├── KonfigurasiController.php
│   │   └── PengajuanSuratController.php
│   ├── Models/
│   │   ├── Desa.php, PengajuanSurat.php, Rt.php, Rw.php, User.php, Warga.php
│   └── Services/
│       ├── GeminiService.php
│       ├── SettingService.php
│       └── WhatsappNotificationService.php
├── docs/
│   ├── diagrams/
│   │   ├── 01_usecase.png
│   │   ├── 02_activity.png
│   │   ├── 03_class.png
│   │   ├── 04_sequence.png
│   │   └── 05_erd.png
│   ├── 00_FINAL_LAPORAN_UTAMA_PRAJADIGITAL.pdf (Generated Official PDF)
│   ├── 01_PRD_SRS_PRAJADIGITAL.md / .pdf
│   ├── 02_SDD_UML_PRAJADIGITAL.md / .pdf
│   ├── 02_UML_DIAGRAMS_FULLPAGE.pdf
│   ├── 03_TESTING_DEVOPS_PRAJADIGITAL.md
│   └── FINAL_REPORT_PRAJADIGITAL.md
├── scripts/
│   ├── generate_diagrams.py
│   └── render_diagrams.py
├── tests/
│   ├── Feature/
│   └── Unit/
├── .github/workflows/ci.yml
└── phpunit.xml
```

### Lampiran 2: Ringkasan Status Eksekusi Pengujian Otomatis
```text
   PASS  Tests\Feature\SuratWorkflowIntegrationTest
   ✓ alur pengajuan surat warga sampai selesai (3-tier)
   ✓ penolakan surat pada tingkat rt dengan feedback
   ✓ penolakan surat pada tingkat rw dengan feedback

   PASS  Tests\Feature\GeminiAiAssistantTest
   ✓ chatbot dapat menjawab pertanyaan warga seputar surat desa
   ✓ chatbot melakukan fallback otomatis ke model default saat error

   PASS  Tests\Feature\KonfigurasiTest
   ✓ super admin dapat memperbarui model gemini ke varian aktif
   ✓ api key tersimpan dalam keadaan terenkripsi aes-256

   ----------------------------------------------------------------------
   Tests:    253 passed (1094 assertions)
   Duration: 14.82s
   Status:   100% SUCCESS / ZERO REGRESSION
```

