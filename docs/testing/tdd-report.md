# Laporan Test-Driven Development (TDD) — Siklus Red-Green-Refactor

## 1. Konsep & Metodologi TDD
Sesuai materi perkuliahan Rekayasa Perangkat Lunak, **Test-Driven Development (TDD)** adalah metodologi pengembangan perangkat lunak berbasis pengujian dengan siklus iteratif tiga langkah:
1. **RED**: Menulis automated test terlebih dahulu untuk mendefinisikan perilaku/fitur yang diinginkan atau membuktikan adanya bug. Tes harus gagal (*fail*) secara valid karena logika belum sesuai, bukan karena kesalahan konfigurasi atau database error.
2. **GREEN**: Mengimplementasikan kode program sesederhana mungkin (minimal code changes) agar tes berhasil lulus (*pass*).
3. **REFACTOR**: Membersihkan, menyempurnakan, dan merapikan struktur kode tanpa mengubah fungsionalitas dan perilaku yang sudah benar. Diakhiri dengan menjalankan ulang seluruh rangkaian tes regresi (*regression test*).

---

## 2. Studi Kasus TDD: Perbaikan Celah Otorisasi Pencarian Warga

### 2.1 Identifikasi Masalah Logika (The Problem)
Pada controller `app/Http/Controllers/WargaController.php`, fungsi `index()` bertugas membatasi daftar warga sesuai wilayah aktor:
- Ketua RW hanya boleh melihat warga di RW-nya (`where('rw_id', $user->rw_id)`).
- Ketua RT hanya boleh melihat warga di RT-nya (`where('rt_id', $user->rt_id)`).

Namun, kode awal untuk penanganan pencarian ditulis sebagai berikut:
```php
if ($request->search) {
    $query->where('nama_lengkap', 'like', '%' . $request->search . '%')
          ->orWhere('nik', 'like', '%' . $request->search . '%');
}
```

Dalam sintaks SQL yang dihasilkan:
```sql
SELECT * FROM warga 
WHERE rt_id = 1 
  AND nama_lengkap LIKE '%keyword%' 
   OR nik LIKE '%keyword%';
```
Karena operator logika `AND` dievaluasi lebih dulu daripada `OR`, query di atas setara dengan:
```sql
(rt_id = 1 AND nama_lengkap LIKE '%keyword%') OR (nik LIKE '%keyword%')
```
Akibatnya: jika seorang Ketua RT mencari NIK seorang warga yang tinggal di RT 2, kondisi kedua bernilai `TRUE` dan data warga RT 2 **bocor ke layar Ketua RT 1**!

---

## 3. Eksekusi Siklus TDD

### Tahap 1: FASE RED (Tulis Kasus Uji & Buktikan Kegagalan)
File pengujian dibuat di [tests/Feature/WargaSearchScopeTest.php](file:///D:/laragon/www/RPL/tests/Feature/WargaSearchScopeTest.php):
```php
public function test_rt_user_search_cannot_leak_warga_from_other_rt(): void
{
    // ... setup Ketua RT 1, Warga RT 1, dan Warga RT 2 ...

    // Ketua RT 1 mencari NIK milik Warga RT 2
    $response = $this->actingAs($userRt1)->get('/warga?search=3201012222220001');

    $response->assertStatus(200);
    $wargaList = $response->viewData('warga');

    // Warga dari RT 2 sama sekali tidak boleh muncul
    $this->assertCount(0, $wargaList, 'Warga dari RT 2 tidak boleh bocor ke Ketua RT 1 saat melakukan pencarian!');
}
```

**Bukti Eksekusi Terminal (RED):**
```
   FAIL  Tests\Feature\WargaSearchScopeTest
  ⨯ rt user search cannot leak warga from other rt                   0.36s  
  ─────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\WargaSearchScopeTest > rt user search cannot leak warga from other rt
  Warga dari RT 2 tidak boleh bocor ke Ketua RT 1 saat melakukan pencarian!
  Failed asserting that actual size 1 matches expected size 0.

  at tests/Feature/WargaSearchScopeTest.php:112
```
*Analisis:* Tes gagal secara valid karena sistem mengembalikan 1 data warga (bocor), membuktikan adanya bug logika query.

---

### Tahap 2: FASE GREEN (Implementasi Perubahan Minimum)
Perbaikan dilakukan pada [app/Http/Controllers/WargaController.php](file:///D:/laragon/www/RPL/app/Http/Controllers/WargaController.php#L39-L46) dengan membungkus kondisi `OR` ke dalam sub-closure SQL (parameter grouping):
```php
if ($request->search) {
    $query->where(function ($q) use ($request) {
        $q->where('nama_lengkap', 'like', '%' . $request->search . '%')
          ->orWhere('nik', 'like', '%' . $request->search . '%');
    });
}
```

SQL yang dihasilkan kini menggunakan tanda kurung yang aman:
```sql
SELECT * FROM warga 
WHERE rt_id = 1 
  AND (nama_lengkap LIKE '%keyword%' OR nik LIKE '%keyword%');
```

**Bukti Eksekusi Terminal (GREEN):**
```
   PASS  Tests\Feature\WargaSearchScopeTest
  ✓ rt user search cannot leak warga from other rt                   0.34s  

  Tests:    1 passed (2 assertions)
  Duration: 0.34s
```
*Analisis:* Logika query kini aman, data dari RT lain tidak lagi tembus ke hasil pencarian.

---

### Tahap 3: FASE REFACTOR (Penyempurnaan & Pengujian Komprehensif)
Pada tahap refactor:
1. Kode controller dirapikan dengan penulisan format closure standar Laravel.
2. Kasus uji positif ditambahkan (`test_rt_user_search_finds_warga_within_own_rt`) untuk memastikan pencarian warga di dalam RT sendiri tetap berfungsi normal tanpa false-negative.

**Bukti Eksekusi Terminal (REFACTOR):**
```
   PASS  Tests\Feature\WargaSearchScopeTest
  ✓ rt user search cannot leak warga from other rt                   0.35s  
  ✓ rt user search finds warga within own rt                         0.05s  

  Tests:    2 passed (5 assertions)
  Duration: 0.40s
```

---

### Tahap 4: REGRESSION TESTING (Uji Regresi Penuh)
Seluruh test suite proyek (61 pengujian) dijalankan kembali untuk memastikan perbaikan tidak menimbulkan efek samping (*side effect*) pada fitur lain:
```
Tests:    61 passed (210 assertions)
Duration: 3.10s
Status:   100% PASS
```

## 4. Kesimpulan TDD
Penerapan siklus Red-Green-Refactor berhasil membuktikan dan menuntaskan perbaikan celah keamanan data kependudukan secara terukur, terdokumentasi, dan terverifikasi 100% oleh sistem otomasi pengujian.
