# Test Cases — Sistem Informasi Wilayah & Layanan Desa (RPL)

Dokumen ini memuat seluruh rancangan dan spesifikasi kasus uji (Test Cases) yang disusun berdasarkan materi Software Testing: Black-box (EP & BVA), Unit Testing, Integration Testing, White-box Testing, System Testing, Core/Auth Feature Suite, dan Acceptance Testing.

**Metode Penghitungan:**
- Setiap 1 test method pada PHPUnit dipetakan secara 1:1 menjadi 1 kasus uji otomatis (**61 Automated Test Cases**).
- 1 kasus uji penerimaan antarmuka visual manual (**1 Manual Acceptance Case**).
- Total seluruh kasus uji terdaftar: **62 Kasus Uji**.

---

## 1. Black-box Testing: Equivalence Partitioning (EP) & Boundary Value Analysis (BVA) (9 Kasus Uji)

| ID | Fitur | Jenis Pengujian | Skenario/Input | Langkah | Hasil yang Diharapkan | Hasil Aktual | Status | Bukti (Test Method) |
|---|---|---|---|---|---|---|---|---|
| **TC-BB-01** | Data Warga | BVA (Lower Bound) | Input NIK 15 digit angka (kurang dari batas wajib 16) | Submit form warga dengan NIK: `'320101123456789'` | Validasi gagal dengan error session pada field `nik` | Response session memiliki error `nik` | **PASS** | `BlackBoxValidationTest::test_bva_warga_nik_rejects_15_digits_boundary_below` |
| **TC-BB-02** | Data Warga | BVA (Exact Bound) | Input NIK tepat 16 digit angka valid | Submit form warga dengan NIK: `'3201011234567890'` | Validasi lolos, data warga tersimpan ke database | Data tersimpan di database `warga`, session tanpa error | **PASS** | `BlackBoxValidationTest::test_bva_warga_nik_accepts_valid_16_digits_exact_boundary` |
| **TC-BB-03** | Data Warga | BVA (Upper Bound) | Input NIK 17 digit angka (melebihi batas wajib 16) | Submit form warga dengan NIK: `'32010112345678901'` | Validasi gagal dengan error session pada field `nik` | Response session memiliki error `nik` | **PASS** | `BlackBoxValidationTest::test_bva_warga_nik_rejects_17_digits_boundary_above` |
| **TC-BB-04** | Data Warga | EP (Invalid - Duplicate) | Input NIK yang sudah terdaftar di database | Submit form warga dengan NIK yang identik dengan warga eksisting | Validasi gagal aturan `unique:warga` pada field `nik` | Response session memiliki error duplikat `nik` | **PASS** | `BlackBoxValidationTest::test_ep_warga_nik_rejects_duplicate_entry` |
| **TC-BB-05** | Data Warga | EP (Invalid - Enum) | Input `jenis_kelamin` bernilai selain `'Laki-laki'` atau `'Perempuan'` | Submit form warga dengan `jenis_kelamin: 'Pria'` | Validasi gagal aturan `in:Laki-laki,Perempuan` | Response session memiliki error `jenis_kelamin` | **PASS** | `BlackBoxValidationTest::test_ep_jenis_kelamin_rejects_unlisted_values` |
| **TC-BB-06** | Data Warga | BVA (Upper Bound String) | Input `nama_lengkap` sepanjang 256 karakter (batas maksimal 255) | Submit form warga dengan string 256 karakter | Validasi gagal aturan `max:255` pada field `nama_lengkap` | Response session memiliki error `nama_lengkap` | **PASS** | `BlackBoxValidationTest::test_bva_nama_lengkap_rejects_256_characters` |
| **TC-BB-07** | Pengajuan Surat | EP (Invalid - Required) | Submit surat tanpa mengisi `keperluan` (string kosong) | Submit `POST /surat` dengan field `keperluan: ''` | Validasi gagal aturan `required` pada field `keperluan` | Response session memiliki error `keperluan` | **PASS** | `BlackBoxValidationTest::test_ep_pengajuan_surat_rejects_missing_keperluan` |
| **TC-BB-08** | Pengajuan Surat | EP (Invalid - Foreign Key) | Submit surat dengan `template_id` tidak ada di database (`999999`) | Submit `POST /surat` dengan `template_id: 999999` | Validasi gagal aturan `exists:surat_templates,id` | Response session memiliki error `template_id` | **PASS** | `BlackBoxValidationTest::test_ep_pengajuan_surat_rejects_nonexistent_template_id` |
| **TC-BB-09** | Proses Surat | EP (Invalid - Action) | Proses surat dengan action ilegal (misal: `'pending'`) | Submit `POST /surat/{id}/process` dengan `action: 'pending'` | Validasi gagal aturan `in:approve,reject` | Response session memiliki error `action` | **PASS** | `BlackBoxValidationTest::test_ep_process_surat_rejects_invalid_action_value` |

---

## 2. Unit Testing (13 Kasus Uji)

| ID | Fitur | Jenis Pengujian | Skenario/Input | Langkah | Hasil yang Diharapkan | Hasil Aktual | Status | Bukti (Test Method) |
|---|---|---|---|---|---|---|---|---|
| **TC-UT-01** | Model User | Unit Testing | Uji metode `hasRole('rt')` pada user ber-role RT | Panggil `$user->hasRole('rt')` | Mengembalikan boolean `true` | Mengembalikan `true` | **PASS** | `UserTest::test_user_has_role_returns_true_for_matching_single_role` |
| **TC-UT-02** | Model User | Unit Testing | Uji metode `hasRole(['super_admin', 'rw'])` dengan array role | Panggil `$user->hasRole(['super_admin', 'rw'])` pada user ber-role RW | Mengembalikan boolean `true` | Mengembalikan `true` | **PASS** | `UserTest::test_user_has_role_returns_true_for_matching_array_of_roles` |
| **TC-UT-03** | Model User | Unit Testing | Uji `hasRole()` pada user tanpa role (`role_id: null`) | Panggil `$user->hasRole('warga')` | Mengembalikan boolean `false` | Mengembalikan `false` | **PASS** | `UserTest::test_user_has_role_returns_false_when_user_has_no_role` |
| **TC-UT-04** | Model User | Unit Testing | Uji mutasi dan casting boolean atribut `is_active` | Ubah `is_active` dari `1` ke `0`, lalu periksa tipe data | Nilai berupa boolean murni (`true`/`false`) | Nilai konsisten boolean | **PASS** | `UserTest::test_user_is_active_attribute_casts_to_boolean` |
| **TC-UT-05** | Middleware CheckRole | Unit Testing | Permintaan dari guest (belum login) | Panggil middleware `CheckRole::handle` tanpa sesi auth | Redirect ke halaman `login` | Respons bertipe redirect ke `/login` | **PASS** | `CheckRoleMiddlewareTest::test_unauthenticated_user_is_redirected_to_login` |
| **TC-UT-06** | Middleware CheckRole | Unit Testing | Permintaan dari user nonaktif (`is_active: false`) | Panggil middleware dengan user status nonaktif | User di-logout otomatis dan diredirect ke login dengan pesan error | User di-logout (`assertGuest`) dan redirect | **PASS** | `CheckRoleMiddlewareTest::test_inactive_user_is_logged_out_and_redirected_with_error` |
| **TC-UT-07** | Middleware CheckRole | Unit Testing | Middleware tanpa parameter role | Panggil middleware tanpa argumen role | Request diteruskan ke `$next($request)` | Konten 'OK' diterima | **PASS** | `CheckRoleMiddlewareTest::test_request_proceeds_when_no_roles_are_required` |
| **TC-UT-08** | Middleware CheckRole | Unit Testing | Permintaan dari user dengan role sesuai | User RT mengakses rute bertag `role:super_admin,rw,rt` | Request diteruskan ke controller | Konten 'PROCEEDED' diterima | **PASS** | `CheckRoleMiddlewareTest::test_request_proceeds_when_user_has_matching_role` |
| **TC-UT-09** | Middleware CheckRole | Unit Testing | Permintaan dari user dengan role tidak diizinkan | User Warga mengakses rute bertag `role:super_admin` | Lempar `HttpException` dengan status code 403 | Tertangkap `HttpException` status 403 | **PASS** | `CheckRoleMiddlewareTest::test_request_aborts_with_403_when_user_role_does_not_match` |
| **TC-UT-10** | Model PengajuanSurat | Unit Testing | Uji casting array `data_tambahan` dan datetime `approved_at` | Simpan array JSON dan timestamp ke model | Array di-cast menjadi PHP array dan timestamp menjadi Carbon instance | `is_array()` dan `Carbon` terverifikasi | **PASS** | `PengajuanSuratModelTest::test_data_tambahan_casts_to_array_and_approved_at_casts_to_datetime` |
| **TC-UT-11** | Asisten AI | Unit Testing | Pengecekan guardrails larangan coding pada system prompt | Panggil `buildContext($user)` melalui Reflection | Mengandung teks larangan tegas coding, Python, dan pemrograman | String larangan terverifikasi ada | **PASS** | `AiChatContextTest::test_build_context_contains_strict_coding_prohibition` |
| **TC-UT-12** | Asisten AI | Unit Testing | Penyisipan konteks nomor RT dan RW user ke dalam prompt | Panggil `buildContext($user)` untuk user Ketua RT 5 RW 2 | System prompt memuat metadata `RW 002` dan `RT 005` | Metadata wilayah terverifikasi ada | **PASS** | `AiChatContextTest::test_build_context_includes_rt_rw_metadata_for_rt_role` |
| **TC-UT-13** | Assert Dasar | Unit Testing | Uji assertion dasar kerangka kerja pengujian | Panggil `assertTrue(true)` | Assertion bernilai benar | Lolos assertion dasar | **PASS** | `Tests\Unit\ExampleTest::test_that_true_is_true` |

---

## 3. Integration & Feature Security Testing (10 Kasus Uji)

| ID | Fitur | Jenis Pengujian | Skenario/Input | Langkah | Hasil yang Diharapkan | Hasil Aktual | Status | Bukti (Test Method) |
|---|---|---|---|---|---|---|---|---|
| **TC-IT-01** | Alur Surat | Integration Testing | Siklus lengkap persetujuan berjenjang: Warga -> RT -> RW | 1. Warga submit surat (`POST /surat`)<br>2. RT approve (`POST /surat/{id}/process`)<br>3. RW approve (`POST /surat/{id}/process`) | Status surat bertahap dari `MENUNGGU_RT` -> `MENUNGGU_RW` -> `SELESAI`, nomor surat ter-generate | Status akhir `SELESAI`, format nomor surat `470/.../RW.001/...` terbentuk | **PASS** | `SuratWorkflowIntegrationTest::test_full_successful_surat_lifecycle_workflow` |
| **TC-IT-02** | Alur Surat | Integration Testing | Penolakan surat oleh RT disertai catatan revisi | RT memproses surat dengan `action: 'reject'` dan catatan: `'Berkas fotokopi KTP belum lengkap'` | Status berubah menjadi `DITOLAK`, catatan tersimpan, approved_by tercatat | Status `DITOLAK`, catatan sesuai input | **PASS** | `SuratWorkflowIntegrationTest::test_surat_rejection_workflow_sets_status_ditolak_with_catatan` |
| **TC-IT-03** | Privasi Surat | Integration Testing | Warga A mencoba melihat detail surat milik Warga B | Login sebagai Warga B, akses `GET /surat/{id_surat_warga_A}` | Akses ditolak oleh `PengajuanSuratPolicy` dengan HTTP 403 Forbidden | HTTP 403 Forbidden | **PASS** | `SuratWorkflowIntegrationTest::test_unauthorized_warga_cannot_view_another_wargas_surat` |
| **TC-IT-04** | Otorisasi RBAC | Integration Testing | Akses guest tanpa login ke rute-rute terproteksi | Request `GET` ke `/dashboard`, `/desa`, `/users`, `/rw`, `/rt`, `/warga`, `/surat` | Seluruh request diredirect ke `/login` (HTTP 302) | Seluruh rute redirect ke `/login` | **PASS** | `RoleAccessIntegrationTest::test_guest_is_redirected_to_login_for_protected_routes` |
| **TC-IT-05** | Otorisasi RBAC | Integration Testing | Akses penuh Super Admin ke modul administratif desa | Super Admin mengakses `/dashboard`, `/desa`, `/users`, `/rw`, `/rt`, `/warga` | Seluruh request mengembalikan HTTP 200 OK | HTTP 200 OK pada seluruh modul | **PASS** | `RoleAccessIntegrationTest::test_super_admin_has_full_access_to_administrative_modules` |
| **TC-IT-06** | Otorisasi RBAC | Integration Testing | Pembatasan hak akses Ketua RW | Ketua RW mengakses `/desa` dan `/users` | Ditolak dengan HTTP 403 Forbidden | HTTP 403 Forbidden | **PASS** | `RoleAccessIntegrationTest::test_rw_user_forbidden_from_desa_and_users_management` |
| **TC-IT-07** | Otorisasi RBAC | Integration Testing | Pembatasan hak akses Ketua RT | Ketua RT mengakses `/desa`, `/users`, dan `/rw` | Ditolak dengan HTTP 403 Forbidden | HTTP 403 Forbidden | **PASS** | `RoleAccessIntegrationTest::test_rt_user_forbidden_from_desa_users_and_rw_management` |
| **TC-IT-08** | Otorisasi RBAC | Integration Testing | Pembatasan hak akses Warga biasa | Warga mengakses modul wilayah (`/desa`, `/users`, `/rw`, `/rt`) | Ditolak dengan HTTP 403 Forbidden, hanya boleh buka `/warga` | HTTP 403 pada modul admin, 200 pada `/warga` | **PASS** | `RoleAccessIntegrationTest::test_warga_forbidden_from_admin_and_wilayah_management` |
| **TC-IT-09** | Scoping Wilayah | Feature Security (TDD) | Pencegahan kebocoran data warga RT lain saat Ketua RT melakukan pencarian | Ketua RT 1 mencari NIK milik warga yang tinggal di RT 2 | Hasil pencarian bernilai 0 (tidak membocorkan warga luar wilayah) | Data warga RT 2 tidak muncul di hasil pencarian Ketua RT 1 | **PASS** | `WargaSearchScopeTest::test_rt_user_search_cannot_leak_warga_from_other_rt` |
| **TC-IT-10** | Scoping Wilayah | Feature Security (TDD) | Pencarian warga valid yang berada di dalam wilayah RT sendiri | Ketua RT 1 mencari nama warga yang memang terdaftar di RT 1 | Menemukan tepat 1 record warga milik RT 1 | Mengembalikan 1 record warga yang sesuai | **PASS** | `WargaSearchScopeTest::test_rt_user_search_finds_warga_within_own_rt` |

---

## 4. White-box Testing (2 Kasus Uji)

| ID | Fitur | Jenis Pengujian | Skenario/Input | Langkah | Hasil yang Diharapkan | Hasil Aktual | Status | Bukti (Test Method) |
|---|---|---|---|---|---|---|---|---|
| **TC-WB-01** | Controller Surat | Branch Coverage | RW menyetujui surat langsung dari status `MENUNGGU_RT` (Direct Bypass Path) | Submit approval oleh RW saat surat masih di tahap `MENUNGGU_RT` | Sistem mengeksekusi branch `elseif (($status === 'MENUNGGU_RW' || $status === 'MENUNGGU_RT') && $user->hasRole('rw'))` dan langsung menetapkan status `SELESAI` | Status langsung `SELESAI` dengan nomor surat resmi | **PASS** | `WhiteBoxBranchTest::test_branch_rw_direct_approval_from_menunggu_rt` |
| **TC-WB-02** | Dashboard | Decision Coverage | Eksekusi seluruh percabangan role (`super_admin`, `rw`, `rt`, `warga`) | Request `GET /dashboard` secara berurutan untuk keempat role | Setiap request memicu private method statistik yang tepat (`getSuperAdminStats`, `getRwStats`, `getRtStats`, `getWargaStats`) | Tiap role menerima array `$stats` dengan kunci data spesifiknya | **PASS** | `WhiteBoxBranchTest::test_branch_dashboard_renders_for_all_four_roles` |

---

## 5. System Testing (End-to-End User Journeys) (2 Kasus Uji)

| ID | Fitur | Jenis Pengujian | Skenario/Input | Langkah | Hasil yang Diharapkan | Hasil Aktual | Status | Bukti (Test Method) |
|---|---|---|---|---|---|---|---|---|
| **TC-ST-01** | End-to-End Auth | System Testing | Alur lengkap: Registrasi Warga Baru -> Auto Buat Profil -> Logout -> Login Kembali | 1. `POST /register` dengan data lengkap<br>2. Verifikasi sesi login & record tabel `warga`<br>3. `POST /logout`<br>4. `POST /login` dengan kredensial baru | Registrasi berhasil, profil warga terbentuk otomatis di database, user dapat logout dan login kembali tanpa kendala | Alur autentikasi dan pembuatan data kependudukan tuntas | **PASS** | `SystemEndToEndTest::test_system_journey_resident_registration_and_authentication` |
| **TC-ST-02** | End-to-End AI Chat | System Testing | Alur interaksi chat asisten AI desa dengan mock HTTP service | 1. User mengirim chat `POST /ai-chat`<br>2. Sistem memanggil Google Gemini API (disimulasikan via `Http::fake`)<br>3. Respons disimpan ke `ai_messages` | Respons JSON berstatus sukses, pesan user & assistant tersimpan di database | JSON mengembalikan `success: true` dan riwayat pesan tercatat | **PASS** | `SystemEndToEndTest::test_system_journey_ai_chat_interaction_flow` |

---

## 6. Application Core & Auth Feature Suite (25 Kasus Uji)

| ID | Modul | Jenis Pengujian | Skenario Uji | Hasil Aktual | Status | Bukti (Test Method) |
|---|---|---|---|---|---|---|
| **TC-AUTH-01** | Routing Dasar | Feature Test | Root URL `/` me-redirect user ke rute login | Redirect ke route `login` (HTTP 302) | **PASS** | `ExampleTest::test_root_redirects_to_login` |
| **TC-AUTH-02** | Routing Dasar | Feature Test | Halaman login dapat diakses dengan sukses | HTTP 200 OK | **PASS** | `ExampleTest::test_login_page_returns_successful_response` |
| **TC-AUTH-03** | Profil User | Feature Test | Form profil user dapat ditampilkan | HTTP 200 OK | **PASS** | `ProfileTest::test_profile_page_is_displayed` |
| **TC-AUTH-04** | Profil User | Feature Test | Informasi profil (nama, email) dapat diperbarui | Data terupdate di DB, redirect ke profil | **PASS** | `ProfileTest::test_profile_information_can_be_updated` |
| **TC-AUTH-05** | Profil User | Feature Test | Status email verification tidak berubah bila email tidak diganti | Timestamp verified tetap ada | **PASS** | `ProfileTest::test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged` |
| **TC-AUTH-06** | Profil User | Feature Test | User dapat menghapus akunnya dengan password benar | Akun terhapus dari DB, sesi logout | **PASS** | `ProfileTest::test_user_can_delete_their_account` |
| **TC-AUTH-07** | Profil User | Feature Test | Password salah menolak penghapusan akun | Error di session bag userDeletion | **PASS** | `ProfileTest::test_correct_password_must_be_provided_to_delete_account` |
| **TC-AUTH-08** | Autentikasi | Feature Test | Layar login dapat dirender | HTTP 200 OK | **PASS** | `AuthenticationTest::test_login_screen_can_be_rendered` |
| **TC-AUTH-09** | Autentikasi | Feature Test | User dapat login dengan kredensial valid | User terautentikasi | **PASS** | `AuthenticationTest::test_users_can_authenticate_using_the_login_screen` |
| **TC-AUTH-10** | Autentikasi | Feature Test | User ditolak login dengan password salah | User tetap berstatus guest | **PASS** | `AuthenticationTest::test_users_can_not_authenticate_with_invalid_password` |
| **TC-AUTH-11** | Autentikasi | Feature Test | User dapat logout dari sistem | Sesi auth berakhir | **PASS** | `AuthenticationTest::test_users_can_logout` |
| **TC-AUTH-12** | Registrasi | Feature Test | Layar form registrasi dapat ditampilkan | HTTP 200 OK | **PASS** | `RegistrationTest::test_registration_screen_can_be_rendered` |
| **TC-AUTH-13** | Registrasi | Feature Test | User baru berhasil register dengan data lengkap desa | User terdaftar dan login otomatis | **PASS** | `RegistrationTest::test_new_users_can_register` |
| **TC-AUTH-14** | Reset Password | Feature Test | Form permintaan reset password dapat dirender | HTTP 200 OK | **PASS** | `PasswordResetTest::test_reset_password_link_screen_can_be_rendered` |
| **TC-AUTH-15** | Reset Password | Feature Test | Link reset password dapat dikirimkan ke email user | Notifikasi terkirim | **PASS** | `PasswordResetTest::test_reset_password_link_can_be_requested` |
| **TC-AUTH-16** | Reset Password | Feature Test | Form pembuatan password baru dapat dirender | HTTP 200 OK | **PASS** | `PasswordResetTest::test_reset_password_screen_can_be_rendered` |
| **TC-AUTH-17** | Reset Password | Feature Test | Password dapat direset dengan token valid | Password berhasil diubah | **PASS** | `PasswordResetTest::test_password_can_be_reset_with_valid_token` |
| **TC-AUTH-18** | Konfirmasi Password | Feature Test | Layar konfirmasi password dapat dirender | HTTP 200 OK | **PASS** | `PasswordConfirmationTest::test_confirm_password_screen_can_be_rendered` |
| **TC-AUTH-19** | Konfirmasi Password | Feature Test | Konfirmasi password berhasil dengan password benar | Redirect ke intended URL | **PASS** | `PasswordConfirmationTest::test_password_can_be_confirmed` |
| **TC-AUTH-20** | Konfirmasi Password | Feature Test | Konfirmasi password gagal dengan password salah | Error session pada field password | **PASS** | `PasswordConfirmationTest::test_password_is_not_confirmed_with_invalid_password` |
| **TC-AUTH-21** | Ubah Password | Feature Test | Password berhasil diperbarui saat login | Password hash terupdate di DB | **PASS** | `PasswordUpdateTest::test_password_can_be_updated` |
| **TC-AUTH-22** | Ubah Password | Feature Test | Password saat ini (current_password) wajib benar | Error session pada current_password | **PASS** | `PasswordUpdateTest::test_correct_password_must_be_provided_to_update_password` |
| **TC-AUTH-23** | Verifikasi Email | Feature Test | Layar pemberitahuan verifikasi email dapat dirender | HTTP 200 OK | **PASS** | `EmailVerificationTest::test_email_verification_screen_can_be_rendered` |
| **TC-AUTH-24** | Verifikasi Email | Feature Test | Email berhasil diverifikasi dengan link bertanda tangan | Atribut email_verified_at terisi | **PASS** | `EmailVerificationTest::test_email_can_be_verified` |
| **TC-AUTH-25** | Verifikasi Email | Feature Test | Verifikasi email gagal bila hash tidak valid | Email tetap unverified | **PASS** | `EmailVerificationTest::test_email_is_not_verified_with_invalid_hash` |

---

## 7. Manual Acceptance Testing (UAT — Format Given-When-Then) (1 Kasus Uji)

| ID | Persona | Skenario | Kriteria Penerimaan (Given-When-Then) | Status | Pelaksana / Keterangan |
|---|---|---|---|---|---|
| **TC-AT-01** | Pengguna Nyata (Human Tester) | Pengujian Tampilan Antarmuka (UI/UX) pada Browser Nyata | **Given** pengguna membuka browser pada alamat `http://127.0.0.1:8000`<br>**When** pengguna bernavigasi antar halaman (Dashboard, Warga, Layanan Surat, Chat AI)<br>**Then** tampilan visual responsif, komponen Blade ter-render utuh, dan tidak terdapat error visual pada konsol peramban | **NOT RUN** | Menunggu pengujian manual langsung oleh pengguna |

---

## 8. Role Admin Desa & 3-Tier Approval Workflow Suite (6 Kasus Uji)

| ID | Modul | Jenis Pengujian | Skenario Uji | Hasil Aktual | Status | Bukti (Test Method) |
|---|---|---|---|---|---|---|
| **TC-AD-01** | Dashboard Desa | Feature Test | Akses dashboard Admin Desa dengan ringkasan 4 KPI dan grafik tren bulanan | HTTP 200 OK, widget Verifikasi Desa dan Tren Pengajuan Surat Bulanan ter-render | **PASS** | `AdminDesaAndLaporanFeatureTest::test_admin_desa_can_view_dashboard_with_stats` |
| **TC-AD-02** | Laporan Pelayanan | Feature Test | Akses halaman rekapitulasi pelayanan dengan filter status/bulan/tahun | HTTP 200 OK, ringkasan dan data pengajuan surat tampil | **PASS** | `AdminDesaAndLaporanFeatureTest::test_admin_desa_can_access_laporan_index_with_filters` |
| **TC-AD-03** | Laporan PDF | Feature Test | Unduh rekapitulasi pelayanan bulanan dalam format landscape PDF resmi desa | HTTP 200 OK, `Content-Type: application/pdf` valid | **PASS** | `AdminDesaAndLaporanFeatureTest::test_admin_desa_can_export_laporan_to_pdf` |
| **TC-AD-04** | Laporan Excel/CSV | Feature Test | Unduh rekapitulasi pelayanan bulanan dalam format CSV/Excel dengan BOM UTF-8 | HTTP 200 OK, `Content-Type: text/csv` dengan header kolom lengkap | **PASS** | `AdminDesaAndLaporanFeatureTest::test_admin_desa_can_export_laporan_to_excel_csv` |
| **TC-AD-05** | Restriksi Sistem | Security Test | Admin Desa dilarang mengakses modul Konfigurasi Sistem / API Key AI / Gateway WA | Ditolak dengan HTTP 403 Forbidden | **PASS** | `AdminDesaAndLaporanFeatureTest::test_admin_desa_cannot_access_konfigurasi_sistem` |
| **TC-AD-06** | Otorisasi Laporan | Security Test | Role non-otoritas (RW, RT, Warga) dilarang mengakses menu Laporan | Ditolak dengan HTTP 403 Forbidden pada seluruh role non-otoritas | **PASS** | `AdminDesaAndLaporanFeatureTest::test_unauthorized_roles_cannot_access_laporan` |

