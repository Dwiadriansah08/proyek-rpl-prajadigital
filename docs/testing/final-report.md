# Laporan Akhir Pengujian Perangkat Lunak (Software Quality Assurance Report)
**Mata Kuliah**: Rekayasa Perangkat Lunak (RPL) / Pengujian Perangkat Lunak  
**Aplikasi**: Sistem Informasi Wilayah & Layanan Administrasi Desa  
**Framework**: Laravel 13.32.0 (PHP 8.5.0 / 8.4.3)  
**Database**: MySQL 8.4.3 (Laragon) & SQLite In-Memory Testing Engine  
**Tanggal Pengujian**: 13 Oktober 2026  

---

## 1. Deskripsi Singkat Aplikasi
Aplikasi **Sistem Informasi Wilayah & Layanan Administrasi Desa** adalah sistem informasi manajemen terpadu yang dirancang untuk mendigitalkan administrasi kependudukan dan pelayanan surat pengantar tingkat desa/kelurahan. Aplikasi ini memiliki empat tingkatan peran pengguna (**Role-Based Access Control / RBAC**):
1. **Super Admin**: Pengelolaan data master desa, manajemen user seluruh tingkatan, serta monitoring keseluruhan data.
2. **Ketua RW (Rukun Warga)**: Pemantauan kependudukan di wilayah RW terkait, verifikasi akhir dan penandatanganan/penerbitan surat pengantar resmi.
3. **Ketua RT (Rukun Tetangga)**: Pengelolaan data warga di lingkungan RT terkait, verifikasi berkas permohonan surat tahap pertama.
4. **Warga**: Pendaftaran mandiri, permohonan surat pengantar secara mandiri, pemantauan status permohonan, serta asisten AI interaktif untuk panduan layanan desa.

---

## 2. Ruang Lingkup dan Batasan Pengujian

### Ruang Lingkup
Pengujian difokuskan pada fungsionalitas inti, integritas data, dan kontrol keamanan akses:
- Alur autentikasi (login, register, logout, reset password).
- Validasi data input kependudukan (NIK, KK, biodata).
- Pembatasan wilayah (scoping data) per RT dan per RW.
- Siklus hidup surat pengantar (*Warga Submit -> Rekomendasi RT -> Pengesahan RW -> Selesai / Tolak*).
- Asisten AI desa (guardrails larangan coding dan penyisipan metadata wilayah).

### Batasan Pengujian
- Pengujian otomatis dijalankan pada lingkungan **SQLite in-memory** (`:memory:`) untuk menjaga 100% integritas data pada database MySQL asli di Laragon.
- Pengujian sintaks migrasi DDL MySQL diuji secara empiris pada database uji terpisah yang diisolasi, bukan database operasional.
- Pengujian visual UI/UX front-end memerlukan peninjauan langsung oleh pengguna nyata (*Human-in-the-loop Acceptance Testing*).

---

## 3. Lingkungan Pengujian (Test Environment) & Strategi Keamanan Data
- **OS**: Windows 11 / Windows Server
- **Server Stack**: Laragon Desktop
- **PHP Version**: 8.5.0 / 8.4.3 CLI
- **Framework**: Laravel 13.32.0
- **Database Engine Operasional**: MySQL 8.4.3 (Host: `127.0.0.1:3306`, DB: `rpl`) — **100% Utuh & Aman**
- **Database Engine Pengujian Otomatis**: SQLite 3 In-Memory (`:memory:`) via `phpunit.xml`
- **Database Uji Sintaks DDL**: MySQL Database Uji Terpisah (`rpl_migration_audit`) yang dibuat dan dihapus sementara khusus audit migrasi MySQL
- **Test Runner Framework**: PHPUnit 12.5.35

---

## 4. Metodologi Penghitungan dan Rekonsiliasi Metrik

Untuk menjaga akurasi pelaporan akademik, metrik pengujian dibedakan secara tegas:

1. **Test Method**: Jumlah fungsi pengujian berawalan `test_` pada suite PHPUnit (**61 test methods**).
2. **Assertions**: Jumlah titik verifikasi kondisi aktual vs ekspektasi (**210 assertions**).
3. **Automated Test Cases**: Skenario terprogram yang dieksekusi oleh mesin (**61 automated cases**, rasio 1:1 terhadap test methods).
4. **Manual Acceptance Case**: Skenario evaluasi antarmuka peramban oleh manusia (**1 manual case**, status: **NOT RUN**).

### Rekonsiliasi Jumlah Kasus Uji Otomatis:
Di laporan sebelumnya, penjumlahan 13 + 9 + 8 + 2 + 2 + 25 = 59 tampak menyisakan 2 tes dari total 61. Hal ini disebabkan **2 test methods pada `WargaSearchScopeTest`** awalnya dipisahkan sebagai kategori metodologi (*TDD Regression Tests*). Ketika dikelompokkan secara murni ke dalam tingkatan pengujian standar (*Testing Levels*), kedua tes tersebut merupakan bagian dari **Integration & Feature Security Testing**, sehingga subtotal Integrasi menjadi **10 test methods**:

$$\text{Unit (13)} + \text{Black-box (9)} + \text{Integrasi (10)} + \text{White-box (2)} + \text{System (2)} + \text{Core/Auth (25)} = \mathbf{61\text{ Automated Tests}}$$

$$\mathbf{61\text{ Automated Cases}} + \mathbf{1\text{ Manual Acceptance Case}} = \mathbf{62\text{ Total Kasus Uji Terdokumentasi}}$$

---

## 5. Ringkasan Hasil Pengujian Aktual

```
PHPUnit 12.5.35 by Sebastian Bergmann and contributors.
Configuration: D:\laragon\www\RPL\phpunit.xml

Tests:    61 passed (210 assertions)
Duration: 3.79s
Memory:   16.00 MB
Status:   100% PASS (Zero Failure, Zero Error, Zero Incomplete)
```

| Kategori Pengujian | Test Suite File | Test Methods / Cases | Assertions | Status |
|---|---|---|---|---|
| **Unit Testing** | `UserTest`, `CheckRoleMiddlewareTest`, `PengajuanSuratModelTest`, `AiChatContextTest`, `Unit\ExampleTest` | **13** | 30 | **13 PASS** |
| **Black-box Testing (EP & BVA)** | `BlackBoxValidationTest` | **9** | 18 | **9 PASS** |
| **Integration & Feature Security** | `SuratWorkflowIntegrationTest` (3), `RoleAccessIntegrationTest` (5), `WargaSearchScopeTest` (2 - TDD) | **10** | 57 | **10 PASS** |
| **White-box Testing** | `WhiteBoxBranchTest` | **2** | 16 | **2 PASS** |
| **System Testing** | `SystemEndToEndTest` | **2** | 27 | **2 PASS** |
| **Application Core & Auth Suite** | `ExampleTest` (2), `ProfileTest` (5), `AuthenticationTest` (4), `RegistrationTest` (2), `PasswordResetTest` (4), `PasswordConfirmationTest` (3), `PasswordUpdateTest` (2), `EmailVerificationTest` (3) | **25** | 62 | **25 PASS** |
| **Subtotal Automated Testing** | **19 File Pengujian** | **61** | **210** | **61 PASS (100%)** |
| **Manual Acceptance Testing** | UI/UX Visual Browser Verification (`TC-AT-01`) | **1** | — | **1 NOT RUN** |
| **TOTAL KESELURUHAN** | | **62** | **210** | **61 PASS, 1 NOT RUN** |

---

## 6. Daftar Bug yang Ditemukan dan Solusi Perbaikan

| No | Komponen | Deskripsi Bug | Severity | Tindakan Perbaikan |
|---|---|---|---|---|
| 1 | `WargaController::index` | Celah kebocoran data wilayah saat pencarian (`orWhere` tanpa closure membocorkan data warga RT lain ke Ketua RT) | **High** | Membungkus klausa pencarian ke dalam nested closure `$query->where(function($q) { ... })` melalui siklus TDD. |
| 2 | Migration DDL | Sintaks `ALTER TABLE ... MODIFY ENUM` khusus MySQL menyebabkan crash pada test runner SQLite in-memory | **Medium** | Menambahkan pengecekan driver `if (DB::getDriverName() === 'mysql')` dan sinkronisasi nilai enum. Diuji empiris pada MySQL terpisah dan terbukti valid. |
| 3 | `routes/web.php` | Rute pengelolaan profil belum terdaftar meskipun `ProfileController` sudah di-import | **Low** | Mendaftarkan rute resource profil (`profile.edit`, `update`, `destroy`) pada auth group. |
| 4 | `UserFactory.php` | Atribut `is_active` tidak disertakan di factory, memicu false auto-logout pada pengujian | **Low** | Menambahkan `'is_active' => true` pada `UserFactory::definition()`. |
| 5 | `ExampleTest.php` | Assertion mengasumsikan root URL mengembalikan HTTP 200, padahal desain sistem me-redirect ke login (HTTP 302) | **Low** | Memperbarui assertion agar memverifikasi redirect ke login dan ketersediaan form login (HTTP 200). |
| 6 | `RegistrationTest.php` | Pengujian registrasi tidak mengirimkan field kependudukan wajib (`nik`, `nomor_kk`, `rw_id`, `rt_id`) | **Low** | Melengkapi data form registrasi dengan relasi wilayah valid. |

---

## 7. Rincian Pelaksanaan Tiap Tingkatan Pengujian

### 7.1 Unit Testing (13 Tests - PASS)
- Model `User`: Pemetaan relasi role, fungsi pembantu `hasRole()` (string tunggal & array), casting boolean `is_active`.
- Model `PengajuanSurat`: Casting `data_tambahan` menjadi array dan `approved_at` menjadi datetime.
- Middleware `CheckRole`: Pengujian isolasi penanganan guest (redirect), user nonaktif (logout & redirect), role sesuai (lanjut), role ditolak (abort 403).
- Controller `AiChatController`: Pengujian pembentukan system prompt dan kepatuhan guardrail larangan coding desa.
- Pengujian dasar assert framework: `test_that_true_is_true`.

### 7.2 Black-box Testing (EP & BVA) (9 Tests - PASS)
- **BVA NIK**: 15 digit (gagal), 16 digit (sukses), 17 digit (gagal).
- **EP NIK**: NIK duplikat (gagal).
- **EP Jenis Kelamin**: Pria/Wanita di luar enum (gagal), Laki-laki/Perempuan (sukses).
- **BVA Nama Lengkap**: 256 karakter melebihi max 255 (gagal).
- **EP Pengajuan Surat**: Keperluan kosong (gagal), template ID fiktif (gagal), action ilegal (gagal).

### 7.3 Integration & Security Testing (10 Tests - PASS)
- **Workflow Surat (3 tests)**: Warga mengajukan (`MENUNGGU_RT`) -> RT merekomendasikan (`MENUNGGU_RW`) -> RW mengesahkan (`SELESAI` + nomor register resmi). Penolakan surat (`DITOLAK` + catatan). Proteksi privasi surat warga lain (HTTP 403).
- **Matriks RBAC (5 tests)**: Verifikasi proteksi rute terhadap 4 tingkatan aktor (`super_admin`, `rw`, `rt`, `warga`).
- **Scoping Keamanan Wilayah (2 tests - TDD)**: Verifikasi pencegahan kebocoran data warga RT lain saat Ketua RT melakukan pencarian, serta verifikasi pencarian normal warga di dalam RT sendiri.

### 7.4 White-box Testing (2 Tests - PASS)
- **Branch Direct RW Approval**: Verifikasi jalur eksekusi ketika RW menyetujui surat langsung dari status `MENUNGGU_RT`.
- **Decision Role Dashboard**: Verifikasi eksekusi 4 percabangan kalkulasi statistik berdasarkan role yang login.

### 7.5 System Testing (2 Tests - PASS)
- **End-to-End Registrasi**: Registrasi warga baru -> pembuatan record profil otomatis di tabel `warga` -> logout -> login kembali.
- **End-to-End AI Chat**: Pengiriman pesan warga via JSON -> pemrosesan prompt kontekstual -> penerimaan jawaban terformat Markdown dan HTML.

### 7.6 Test-Driven Development (TDD) (2 Tests - PASS)
- Telah dibuktikan siklus **Red-Green-Refactor** secara nyata:
  - Fase RED: Tes `test_rt_user_search_cannot_leak_warga_from_other_rt` gagal dengan pesan kebocoran data.
  - Fase GREEN: Perbaikan query closure di `WargaController` membuat tes sukses dalam 0.34 detik.
  - Fase REFACTOR: Penambahan tes regresi pencarian positif dan pengujian menyeluruh.

---

## 8. Pemisahan Hasil Aktual, Harapan, dan Hal yang Belum Diuji

### Hasil yang Diharapkan
- Seluruh aturan validasi menolak data tidak valid dan menerima data valid.
- Hak akses antar RT/RW terlindungi secara ketat tanpa kebocoran data pada fungsi search.
- Tidak ada data asli MySQL yang terhapus saat pengujian.

### Hasil Aktual
- 61 automated tests lulus 100% tanpa kegagalan (210 assertions).
- Database operasional MySQL di `D:\laragon\data\mysql-8.4\rpl` terbukti tetap utuh dengan 4 users, 30 warga, dan 1 surat.
- Web server merespons normal (HTTP 200 pada `/login` dan `/up`).

### Hal yang Belum Diuji (Pending)
- **UAT Tampilan Visual (TC-AT-01)**: Tampilan visual CSS/Tailwind pada browser nyata berstatus **NOT RUN** sampai dievaluasi secara manual langsung oleh pengguna.

---

## 9. Kesimpulan dan Rekomendasi

### Kesimpulan
Berdasarkan serangkaian pengujian terstruktur dari tingkat Unit hingga System, aplikasi **Sistem Informasi Wilayah & Layanan Desa** terbukti memiliki fondasi arsitektur yang kuat, alur bisnis yang berfungsi dengan baik, dan kepatuhan validasi yang tinggi. Celah kebocoran data wilayah pada fitur pencarian telah berhasil diidentifikasi dan dituntaskan melalui metodologi TDD.

### Rekomendasi untuk Pengembang
1. **Pengujian Manual Visual UI**: Lakukan verifikasi manual antarmuka visual di browser untuk menuntaskan kasus UAT `TC-AT-01`.
2. **Ekstensi PHP Intl**: Aktifkan baris `extension=intl` pada file `php.ini` Laragon untuk mendukung fitur formatting angka pada command artisan Laravel 13.
3. **Continuous Testing**: Pertahankan eksekusi `php vendor/phpunit/phpunit/phpunit` secara rutin sebelum melakukan perubahan kode baru untuk mencegah regresi logika.
