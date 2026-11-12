<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo">
</p>

<h1 align="center">
  🏛️ PRAJADIGITAL DESA LEMAHABANG
</h1>

<p align="center">
  <strong>Sistem Informasi & Pelayanan Publik Cerdas Desa Berbasis Laravel 13 dan Real-Time Google Gemini AI</strong>
</p>

<p align="center">
  <a href="#-status-pengujian--cicd"><img src="https://img.shields.io/badge/PHPUnit-253%20Passed%20(100%25)-success?style=for-the-badge&logo=php" alt="Tests"></a>
  <a href="#-status-pengujian--cicd"><img src="https://img.shields.io/badge/CI%2FCD-GitHub%20Actions%20Passed-brightgreen?style=for-the-badge&logo=githubactions" alt="CI/CD"></a>
  <a href="#-tech-stack"><img src="https://img.shields.io/badge/Laravel-13.x-red?style=for-the-badge&logo=laravel" alt="Laravel"></a>
  <a href="#-tech-stack"><img src="https://img.shields.io/badge/PHP-8.5-blue?style=for-the-badge&logo=php" alt="PHP"></a>
  <a href="#-tech-stack"><img src="https://img.shields.io/badge/Google%20Gemini-Active%20API-orange?style=for-the-badge&logo=google" alt="Gemini AI"></a>
  <a href="#-lisensi"><img src="https://img.shields.io/badge/License-MIT-purple?style=for-the-badge" alt="License"></a>
</p>

---

## 📌 INFORMASI MAHASISWA & PROYEK AKHIR

Dokumen ini disusun sebagai pemenuhan **Laporan Proyek Akhir Mandiri** Mata Kuliah **Rekayasa Perangkat Lunak (TIKB2503021)** pada Program Studi Sarjana, **STMIK IKMI Cirebon**.

| Komponen Identitas | Keterangan Rinci |
| :--- | :--- |
| **Nama Lengkap Mahasiswa** | **Mochammad Dwi Adriansah** |
| **Nomor Induk Mahasiswa (NIM)**| **41257117** |
| **Tema / Topik Kelompok** | **Sistem Informasi Desa (Smart Village / E-Government)** |
| **Mata Kuliah** | Rekayasa Perangkat Lunak (TIKB2503021) |
| **Dosen Pengampu** | **Yudhistira Arie Wijaya, M.Kom., CDS.** |
| **Institusi Perguruan Tinggi** | STMIK IKMI Cirebon |
| **Tahun Akademik** | 2025/2026 |
| **Repositori GitHub** | [https://github.com/Dwiadriansah08/proyek-rpl-prajadigital](https://github.com/Dwiadriansah08/proyek-rpl-prajadigital) |

---

## 📖 DESKRIPSI APLIKASI: PRAJADIGITAL

**PrajaDigital** adalah sistem informasi tata kelola pemerintahan dan pelayanan administrasi kependudukan terpadu yang dirancang khusus untuk **Desa Lemahabang**. Sistem ini mentransformasi alur birokrasi permohonan surat manual yang lambat, rentan pungli, dan membutuhkan kehadiran fisik menjadi ekosistem digital transparan dan efisien.

### 🌟 Fitur Utama Sistem:
1. **Alur Persetujuan Surat Berjenjang 3-Tier (Multi-Tier State Machine):**
   * **Warga:** Mengajukan permohonan surat secara online dan memantau status secara *real-time*.
   * **Ketua RT:** Memverifikasi berkas warga di wilayahnya (`MENUNGGU_RT` $\rightarrow$ `MENUNGGU_RW` atau `DITOLAK`).
   * **Ketua RW:** Memverifikasi pengantar dari RT (`MENUNGGU_RW` $\rightarrow$ `MENUNGGU_ADMIN_DESA` atau `DITOLAK`).
   * **Admin Desa (Kasi Pelayanan):** Melakukan persetujuan akhir dan penerbitan dokumen resmi desa (`MENUNGGU_ADMIN_DESA` $\rightarrow$ `SELESAI`).
2. **Auto-Generate 7 Dokumen Surat Resmi (DomPDF):**
   Penerbitan otomatis file PDF lengkap dengan format kop resmi desa, nomor surat otomatis format `470/{id}/RW.{rw}/{tahun}`, dan *QR-code verification*. Mencakup Surat Keterangan Domisili, SKU, SKTM, Kematian, Pengantar SKCK, Penghasilan, dan Pengantar Umum.
3. **Notifikasi Omnichannel Otomatis (Email SMTP & WhatsApp Gateway Fonnte):**
   Setiap transisi status mengirimkan teks notifikasi otomatis ke WhatsApp pemohon via Fonnte API dan melampirkan berkas PDF ke email warga melalui `SuratSelesaiMail`.
4. **Pengumuman Publik Bertarget (Targeted Broadcast):**
   Admin, RW, dan RT dapat menerbitkan pengumuman spesifik per desa, per RW, per RT, atau per role dengan integrasi siaran massal WhatsApp.
5. **Manajemen Kependudukan Terstruktur:**
   Pengelolaan master wilayah (Desa, RW, RT), data keluarga (Kartu Keluarga & anggota), master penduduk (NIK), dan akun pengguna dengan kontrol RBAC mandiri.

---

## 🤖 ARSITEKTUR & CARA KERJA FITUR AI (GOOGLE GEMINI REAL-TIME)

PrajaDigital mengintegrasikan model kecerdasan buatan (*Generative AI*) **Google AI Studio (Gemini API)** melalui service layer `GeminiService.php` dan `AiAssistantController.php` untuk melayani konsultasi warga seputar syarat administrasi desa 24/7.

```
+------------------+         +-------------------------------+         +-----------------------+
|  Warga / User    |  AJAX   |   PrajaDigital Backend        |  HTTPS  |  Google AI Studio     |
|  (Chat Widget)   | ------> |  - AiChatContextService       | ------> |  (Gemini API v1beta)  |
|                  | <------ |  - VillageStatistikService    | <------ |  - gemini-3.5-flash   |
+------------------+  JSON   |  - System Instruction Inject  |  JSON   +-----------------------+
                             +-------------------------------+                     |
                                             | (Fallback / Timeout > 35s)          |
                                             v                                     v
                             +-------------------------------+         +-----------------------+
                             |  Local Mock Response Engine   |         | Database rpl:         |
                             |  (Offline Rule-Based Safety)  |         | - ai_conversations    |
                             +-------------------------------+         | - ai_messages         |
                                                                       +-----------------------+
```

### Keunggulan & Spesifikasi Fitur AI:
* **Dynamic Context Injection:** Sebelum mengirim pertanyaan pengguna ke model Gemini, `AiChatContextService` menyuntikkan data kependudukan riil (nama desa, nama ketua RT/RW pengguna, statistik penduduk, dan daftar persyaratan 7 surat) ke dalam *System Instruction*.
* **5 Model Resmi Aktif & Hemat Kuota:** Mendukung pilihan model fleksibel: `gemini-3.5-flash-lite` (Default Rekomendasi), `gemini-3.1-flash-lite`, `gemini-flash-lite-latest`, `gemini-3.6-flash`, dan `gemini-3.8-flash`.
* **Zero-Crash Resilience & Fallback:** Dilengkapi batas waktu *timeout* 35 detik dan mekanisme *fallback* ke engine mock lokal apabila kuota API habis atau server eksternal tidak merespons, memastikan ketersediaan sistem tanpa henti.
* **Keamanan Kredensial (AES-256):** API Key disimpan pada tabel `system_settings` dalam keadaan terenkripsi (*AES-256-CBC*) oleh `SettingService` dan di-masking pada UI Super Admin.

---

## 💻 TECH STACK & ARSITEKTUR

| Komponen | Teknologi yang Digunakan | Peran dalam Sistem |
| :--- | :--- | :--- |
| **Backend Framework** | **Laravel Framework v13.17.x** | Core MVC Architecture & API Routing |
| **Runtime Environment** | **PHP 8.5.0** (CLI & FPM) | Server-side execution engine |
| **Database System** | **MySQL 8.0.x** (Database `rpl`) | RDBMS penyimpanan data relasional & audit logs |
| **Frontend UI** | **Blade Engine + Tailwind CSS v3** | Responsive Glassmorphism Design & Dashboard |
| **Client Interactivity** | **Alpine.js v3.4.2** | Komponen UI reaktif, modal dialog, & chat widget |
| **Document Engine** | **barryvdh/laravel-dompdf v3.1** | Rendering template surat A4 & rekapitulasi PDF |
| **External Integrations**| **Google Gemini API & Fonnte WA**| Generative AI Service & WhatsApp Notification Gateway |
| **Automated Testing** | **PHPUnit 12.5.12** | 253 Unit, Feature, & State Machine Tests (100% Pass) |
| **Code Style Linter** | **Laravel Pint v1.27** | Standar coding PSR-12 Clean Code |
| **CI/CD Pipeline** | **GitHub Actions** | Otomatisasi pengujian, linting, & build verifikasi |

---

## 📹 TAUTAN VIDEO PRESENTASI (3 TAHAPAN WAJIB RUBRIK OBE)

Berikut adalah tautan rekaman video presentasi pemenuhan tugas proyek mandiri sesuai standar rubrik:

| Tahapan Progres | Konten Presentasi | Durasi Target | Tautan Video (YouTube / G-Drive) |
| :--- | :--- | :---: | :--- |
| **Progres 1** | **PRD & Konsep Aplikasi:** Latar belakang masalah birokrasi desa, perumusan solusi PrajaDigital, matriks 5 aktor, dan spesifikasi fungsional SRS. | 3–5 Menit | **`[LINK_YOUTUBE_VIDEO_PROGRES_1]`** *(Contoh: https://youtu.be/xxxxx)* |
| **Progres 2** | **Pemodelan UML & UI/UX Figma:** Pembahasan mendalam 4 Diagram UML (Use Case, Activity, Class, Sequence) + ERD, serta visual antarmuka sistem. | 5–7 Menit | **`[LINK_YOUTUBE_VIDEO_PROGRES_2]`** *(Contoh: https://youtu.be/xxxxx)* |
| **Progres 3 (Final)** | **Live Demo Aplikasi, Fitur AI, Testing & CI/CD:** Demonstrasi live alur pengajuan surat 3-tier, interaksi real-time Gemini AI, hasil 253 testing, & CI/CD Actions. | 8–12 Menit | **`[LINK_YOUTUBE_VIDEO_FINAL_DEMO]`** *(Contoh: https://youtu.be/xxxxx)* |

> ⚠️ **Catatan Akses Video:** Seluruh tautan video disetel dalam status *Unlisted / Public* di YouTube atau *Anyone with the link can view* di Google Drive demi kelancaran evaluasi tim dosen penguji.

---

## 🚀 PANDUAN INSTALASI & MENJALANKAN SISTEM (SETUP INSTRUCTION)

Ikuti langkah-langkah berikut untuk menjalankan project PrajaDigital di komputer lokal:

### 1. Prasyarat Lingkungan (Prerequisites)
* PHP `>= 8.3` (Disarankan PHP 8.5) dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`.
* Composer `>= 2.7`
* Node.js `>= 20.x` & NPM
* Server MySQL aktif (disarankan menggunakan Laragon / XAMPP)

### 2. Kloning Repositori & Instalasi Dependensi
```bash
# 1. Clone repositori ke direktori web server lokal
git clone https://github.com/Dwiadriansah08/proyek-rpl-prajadigital.git
cd proyek-rpl-prajadigital

# 2. Instal seluruh paket dependensi backend (PHP Composer)
composer install

# 3. Instal paket dependensi frontend (Node.js NPM)
npm install
```

### 3. Konfigurasi Environment (`.env`)
Salin file konfigurasi lingkungan dan buat application key:
```bash
# Salin konfigurasi contoh
cp .env.example .env

# Generate encryption key
php artisan key:generate
```

Buka file `.env` dan sesuaikan pengaturan database serta integrasi API:
```ini
APP_NAME=PrajaDigital
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rpl
DB_USERNAME=root
DB_PASSWORD=

# Konfigurasi Google AI Studio (Gemini)
GEMINI_API_KEY=[ISI_DENGAN_GEMINI_API_KEY_ANDA]
GEMINI_MODEL=gemini-3.5-flash-lite

# Konfigurasi WhatsApp Gateway Fonnte (Opsional untuk testing riil)
FONNTE_TOKEN=[ISI_DENGAN_TOKEN_FONNTE_ANDA]
WA_SIMULATION=true
```

### 4. Migrasi & Seeding Basis Data
Jalankan migrasi skema tabel beserta seluruh akun demo dan master data:
```bash
php artisan migrate --seed
```

### 5. Kompilasi Frontend & Menjalankan Server
Jalankan dua terminal terpisah:
```bash
# Terminal 1: Menjalankan Vite development server
npm run dev

# Terminal 2: Menjalankan Laravel local development server
php artisan serve
```
Akses aplikasi melalui peramban: `http://localhost:8000` atau `http://127.0.0.1:8000`.

---

## 🔑 KREDENSIAL AKUN DEMO RESMI SISTEM

Setelah menjalankan `php artisan migrate --seed`, seluruh aktor dapat diuji menggunakan kredensial default berikut:

| Peran (Role) | Alamat Email | Kata Sandi (Password) | Hak Akses Utama |
| :--- | :--- | :---: | :--- |
| **Super Admin** | `superadmin@prajadigital.desa.id` | `password` | Master Desa, Konfigurasi AI & Token WA |
| **Admin Desa** | `admindesa@desa.com` | `password` | Final Approval Surat, Generate PDF, Laporan |
| **Ketua RW** | `rw01@desa.com` | `password` | Verifikasi Pengajuan Tingkat RW (001) |
| **Ketua RT** | `rt01@desa.com` | `password` | Verifikasi Pengajuan Tingkat RT (001) |
| **Warga** | `dwiadriansah4@gmail.com` | `password` | Pengajuan Surat Mandiri & Konsultasi AI |

---

## 🧪 PANDUAN PENGUJIAN OTOMATIS (AUTOMATED TESTING)

Sistem PrajaDigital mengimplementasikan *Test-Driven Development* (TDD) secara ketat dengan **253 kasus uji otomatis** (*automated unit & feature tests*) dan **1.094 asersi** tanpa satu pun kegagalan (*zero failure*).

Jalankan pengujian menggunakan perintah berikut:

```bash
# Menjalankan seluruh test suite otomatis dengan ringkasan status
php artisan test

# Atau menggunakan opsi compact
php artisan test --compact

# Menjalankan pengujian spesifik pada alur surat 3-tier
php artisan test tests/Feature/SuratWorkflowIntegrationTest.php

# Menjalankan pengujian spesifik integrasi Gemini AI Assistant
php artisan test tests/Feature/GeminiAiAssistantTest.php
```

### Hasil Eksekusi Uji Aktual:
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

---

## 📂 DOKUMENTASI LENGKAP PROYEK (FOLDER `docs/`)

Seluruh berkas laporan teknis, spesifikasi formal, diagram resolusi tinggi, dan PDF resmi telah tersedia di direktori `docs/`:

* **`docs/FINAL_REPORT_PRAJADIGITAL.md`**: Dokumen Laporan Akhir Utama Praktikum (Standar Akademik Bab I–V).
* **`docs/00_FINAL_LAPORAN_UTAMA_PRAJADIGITAL.pdf`**: Berkas PDF resmi siap cetak laporan akhir lengkap dengan tata letak A4 (Margin 4-4-3-3 cm) dan 5 diagram *full page*.
* **`docs/01_PRD_SRS_PRAJADIGITAL.md`** & **`.pdf`**: Dokumen Product Requirement Document / Software Requirements Specification (Standar IEEE 830 / ISO 29148).
* **`docs/02_SDD_UML_PRAJADIGITAL.md`** & **`.pdf`**: Dokumen Software Design Description & Pemodelan Lengkap UML 2.5.
* **`docs/02_UML_DIAGRAMS_FULLPAGE.pdf`**: Dokumen visual khusus berisikan 5 Diagram UML/ERD A4 Full Page resolusi tinggi.
* **`docs/03_TESTING_DEVOPS_PRAJADIGITAL.md`**: Laporan Audit Penjaminan Mutu Perangkat Lunak (QA), TDD, dan CI/CD GitHub Actions.
* **`docs/diagrams/`**: Berkas grafis diagram PNG mandiri (`01_usecase.png`, `02_activity.png`, `03_class.png`, `04_sequence.png`, `05_erd.png`).

---

## 📄 LISENSI

Proyek aplikasi PrajaDigital dilisensikan di bawah lisensi terbuka [MIT License](LICENSE).
Dikembangkan untuk keperluan akademik dan kemajuan tata kelola digital pedesaan Indonesia.
