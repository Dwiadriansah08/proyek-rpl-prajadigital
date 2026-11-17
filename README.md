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

## 📄 LISENSI

Proyek aplikasi PrajaDigital dilisensikan di bawah lisensi terbuka [MIT License](LICENSE).
Dikembangkan untuk keperluan akademik dan kemajuan tata kelola digital pedesaan Indonesia.
