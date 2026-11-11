# Test Plan — Sistem Informasi Wilayah & Layanan Desa (RPL)

## 1. Pendahuluan & Tujuan Pengujian
Dokumen ini disusun sebagai panduan pengujian perangkat lunak sistematis untuk aplikasi web berbasis Laravel (**Sistem Informasi Wilayah & Layanan Desa**). Mengacu pada materi perkuliahan Rekayasa Perangkat Lunak (Software Testing), pengujian bertujuan untuk:
1. Memastikan seluruh fungsi sistem berjalan sesuai spesifikasi kebutuhan (Requirements Specification).
2. Menemukan dan mengisolasi cacat (bug/defect) sedini mungkin sebelum aplikasi masuk ke lingkungan produksi.
3. Meningkatkan stabilitas, kehandalan, serta keamanan sistem (khususnya perlindungan privasi data kependudukan dan kontrol hak akses bertingkat).
4. Mengurangi risiko kegagalan sistem operasional desa.

---

## 2. Ruang Lingkup Pengujian (Scope of Testing)

### 2.1 Fitur yang Diuji (In-Scope)
- **Modul Autentikasi & Akun**: Login, logout, registrasi warga (wajib NIK, KK, RT, RW), verifikasi status aktif (`is_active`), dan pengelolaan profil.
- **Modul Kontrol Akses & Otorisasi (RBAC)**: Middleware `CheckRole`, pembagian 4 tingkatan aktor (`super_admin`, `rw`, `rt`, `warga`).
- **Modul Data Kependudukan (Warga)**: Validasi NIK 16 digit, KK 16 digit, jenis kelamin, pembatasan jangkauan wilayah (scoping), dan pencarian data warga.
- **Modul Layanan Surat (Pengajuan Surat)**: Siklus hidup permohonan surat dari Warga (`MENUNGGU_RT`), verifikasi RT (`MENUNGGU_RW`), persetujuan RW (`SELESAI` + penomoran otomatis), penolakan (`DITOLAK` + catatan), dan proteksi privasi dokumen.
- **Modul Asisten AI (AiChat)**: Pembentukan system prompt berbasis wilayah & role (`buildContext`), penegakan guardrails (larangan coding), komunikasi AJAX JSON, serta penanganan kegagalan eksternal / mock API.
- **Modul Dashboard**: Agregasi statistik wilayah per role.

### 2.2 Batasan Pengujian (Out-of-Scope)
- Pengujian performa beban tinggi (stress/load testing skala ribuan user simultan).
- Pengujian penetration testing perangkat keras server hosting fisik.

---

## 3. Lingkungan Pengujian (Test Environment) & Strategi Keamanan Data

| Komponen | Spesifikasi / Konfigurasi |
|---|---|
| **Sistem Operasi** | Windows (Laragon Environment) |
| **Bahasa Pemrograman** | PHP 8.4.3 / 8.5.0 CLI |
| **Framework** | Laravel 13.32.0 |
| **Database Operasional Asli** | MySQL 8.4.3 (`127.0.0.1:3306`, Database: `rpl`) — **Wajib Dilindungi 100% dari Perubahan/Reset** |
| **Database Pengujian Utama** | SQLite In-Memory (`:memory:`) via `phpunit.xml` untuk eksekusi test suite cepat & terisolasi |
| **Database Uji Sintaks DDL** | MySQL Database Uji Terpisah (`rpl_migration_audit`) yang dibuat dan dihapus sementara khusus audit migrasi MySQL |
| **Test Runner** | PHPUnit 12.5.35 / 12.5.12 |
| **Mocking / Faking** | Laravel `Http::fake()`, Database Transactions/RefreshDatabase |

> [!IMPORTANT]
> **Aturan Keamanan Data:**
> Seluruh automated test suite dijalankan secara terisolasi pada SQLite in-memory database (`:memory:`). Tidak ada perintah `migrate:fresh`, `db:wipe`, atau operasi destruktif yang menyentuh database MySQL fisik (`rpl`). Validasi sintaks migrasi MySQL dijalankan di database sementara terpisah tanpa menyentuh database `rpl`.

---

## 4. Metodologi dan Terminologi Penghitungan (Counting Methodology)

Untuk memastikan laporan memiliki integritas ilmiah dan dapat dipertanggungjawabkan, metrik pengujian didefinisikan secara tegas sebagai berikut:

1. **Test Method (Metode Uji PHPUnit)**:
   Setiap fungsi individual berawalan `test_` pada kelas turunan `Tests\TestCase` yang dieksekusi secara otomatis oleh runner PHPUnit.
2. **Assertions**:
   Setiap evaluasi kondisi individual (`assertTrue`, `assertEquals`, `assertStatus`, dll.) yang dieksekusi di dalam test methods.
3. **Automated Test Case (Kasus Uji Otomatis)**:
   Skenario pengujian yang diimplementasikan dalam bentuk kode terprogram dan dieksekusi oleh mesin. Setiap 1 test method merepresentasikan 1 kasus uji otomatis (rasio 1:1).
4. **Manual Acceptance Case**:
   Skenario pengujian penerimaan pengguna (UAT) manual yang membutuhkan interaksi manusia di browser web asli. Kasus ini berstatus **NOT RUN** sampai benar-benar diuji oleh penguji manusia.

### Rekonsiliasi Penghitungan Kasus Uji:
- **Total Automated Test Methods / Cases**: **61**
- **Total Assertions**: **210**
- **Total Manual Acceptance Cases**: **1** (TC-AT-04)
- **Total Seluruh Kasus Uji Terdokumentasi**: 61 + 1 = **62**

---

## 5. Pendekatan Tingkatan Pengujian (Four Testing Levels)

Mengacu pada materi standar Software Quality Assurance:

1. **Unit Testing (13 test methods)**:
   Pengujian independen metode model, casting atribut, guardrail AI, dan logika middleware tanpa ketergantungan eksternal.
2. **Black-box Testing: EP & BVA (9 test methods)**:
   - **Equivalence Partitioning (EP)**: Pengelompokan kelas input valid dan tidak valid pada field input.
   - **Boundary Value Analysis (BVA)**: Pengujian nilai batas kritis (misal: NIK 15 digit, 16 digit, 17 digit; nama 255 vs 256 karakter).
3. **Integration & Feature Security Testing (10 test methods)**:
   Pengujian interoperabilitas antara Routes, Middleware, Controllers, Request Validation, Policies, dan Database. Termasuk 2 tes scoping keamanan wilayah yang dihasilkan dari siklus TDD.
4. **White-box Testing (2 test methods)**:
   Pengujian percabangan logika (*Branch/Decision Coverage*) pada kontrol alur persetujuan surat dan middleware otorisasi.
5. **System Testing (2 test methods)**:
   Pengujian end-to-end skenario alur kerja lengkap (registrasi hingga approval surat dan chat AI).
6. **Application Core & Auth Feature Suite (25 test methods)**:
   Pengujian bawaan framework dan modul autentikasi Breeze (Login, Register, Password Reset, Password Update, Email Verification, Profile Management).
7. **Acceptance Testing (UAT Manual)**:
   Pengujian kriteria penerimaan pengguna berbasis user story (format *Given-When-Then*).

---

## 6. Manajemen Risiko & Kriteria Pengujian

### 6.1 Kriteria Masuk & Keluar (Entry & Exit Criteria)
- **Entry Criteria**: Lingkungan pengujian terisolasi (SQLite `:memory:`) dapat melakukan migrasi skema tanpa error sintaks.
- **Exit Criteria**: 100% automated test cases berstatus **PASS**, zero critical defect pada alur utama, dan seluruh skenario TDD terverifikasi hijau.

### 6.2 Manajemen Risiko
| Risiko | Dampak | Mitigasi |
|---|---|---|
| Database asli MySQL terhapus saat testing | Kehilangan data permanen | Gunakan isolasi SQLite `:memory:` pada `phpunit.xml`. |
| Pengujian AI Chat timeout karena kuota API eksternal | Eksekusi test macet / hanging | Gunakan `Http::fake()` untuk mensimulasikan respons model Google Gemini. |
| Ketidakcocokan sintaks DDL MySQL vs SQLite | Migrasi gagal dieksekusi | Tambahkan driver check `if (DB::getDriverName() === 'mysql')` pada DDL khusus MySQL, dan uji sintaks MySQL di DB sementara terpisah. |
