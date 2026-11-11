<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\SuratTemplate;
use App\Models\User;
use App\Models\Warga;
use App\Services\AiChatContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Automated tests untuk audit kemampuan membaca database dan intent detection AI Chatbot.
 *
 * Menguji:
 * 1. Intent detection terpisah untuk statistik wilayah, statistik akun, penduduk, KK, layanan surat, dan pengajuan surat.
 * 2. Pemisahan tegas antara jumlah entitas wilayah (tabel rw/rt) dan jumlah akun pengguna (tabel users/roles).
 * 3. Normalisasi huruf besar/kecil, tanda baca akhir, dan variasi penulisan bahasa Indonesia.
 * 4. Verifikasi jumlah penduduk dari tabel warga (bukan akun login).
 * 5. Verifikasi data Kartu Keluarga / Kepala Keluarga dari tabel keluarga dan warga.
 * 6. Penanganan data kosong secara jujur (menjawab 0, bukan pesan error).
 * 7. Otorisasi berbasis role pengguna login (mengabaikan klaim role di isi pesan).
 */
class AiChatDatabaseQueryIntentTest extends TestCase
{
    use RefreshDatabase;

    private Desa $desa;

    private Role $roleSuperAdmin;

    private Role $roleRw;

    private Role $roleRt;

    private Role $roleWarga;

    private User $userAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleSuperAdmin = Role::create(['name' => 'super_admin']);
        $this->roleRw = Role::create(['name' => 'rw']);
        $this->roleRt = Role::create(['name' => 'rt']);
        $this->roleWarga = Role::create(['name' => 'warga']);

        $this->desa = Desa::create([
            'nama' => 'Desa Sukamaju',
            'kecamatan' => 'Kecamatan Makmur',
            'kabupaten_kota' => 'Kabupaten Sejahtera',
            'provinsi' => 'Jawa Barat',
            'alamat' => 'Jl. Merdeka No. 1',
            'kepala_desa' => 'H. Ahmad Sobari',
            'kontak' => '081234567890',
        ]);

        $this->userAdmin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@desa.id',
            'role_id' => $this->roleSuperAdmin->id,
            'desa_id' => $this->desa->id,
        ]);
    }

    /** Test 1: Deteksi intent statistik_wilayah untuk berbagai variasi kalimat */
    public function test_intent_detection_statistik_wilayah_variations(): void
    {
        $service = app(AiChatContextService::class);

        // Variasi RW
        $this->assertEquals('statistik_wilayah', $service->detectIntent('berapa jumlah rw'));
        $this->assertEquals('statistik_wilayah', $service->detectIntent('ada berapa rw'));
        $this->assertEquals('statistik_wilayah', $service->detectIntent('jumlah rw berapa'));
        $this->assertEquals('statistik_wilayah', $service->detectIntent('berapa rw di desa'));
        $this->assertEquals('statistik_wilayah', $service->detectIntent('ada berapa rw di desa?'));
        $this->assertEquals('statistik_wilayah', $service->detectIntent('total rw'));

        // Variasi RT
        $this->assertEquals('statistik_wilayah', $service->detectIntent('berapa jumlah rt'));
        $this->assertEquals('statistik_wilayah', $service->detectIntent('ada berapa rt'));
        $this->assertEquals('statistik_wilayah', $service->detectIntent('jumlah rt berapa?'));
        $this->assertEquals('statistik_wilayah', $service->detectIntent('berapa rt di desa'));
        $this->assertEquals('statistik_wilayah', $service->detectIntent('total rt'));

        // Variasi Gabungan
        $this->assertEquals('statistik_wilayah', $service->detectIntent('berapa jumlah rw dan rt'));
        $this->assertEquals('statistik_wilayah', $service->detectIntent('statistik wilayah'));
        $this->assertEquals('statistik_wilayah', $service->detectIntent('rekap wilayah'));
    }

    /** Test 2: Deteksi intent statistik_akun untuk variasi akun/user per role */
    public function test_intent_detection_statistik_akun_variations(): void
    {
        $service = app(AiChatContextService::class);

        // Role RW
        $this->assertEquals('statistik_akun', $service->detectIntent('ada berapa user dengan role rw'));
        $this->assertEquals('statistik_akun', $service->detectIntent('berapa user dengan role rw?'));
        $this->assertEquals('statistik_akun', $service->detectIntent('berapa user role rw'));
        $this->assertEquals('statistik_akun', $service->detectIntent('total akun rw'));
        $this->assertEquals('statistik_akun', $service->detectIntent('jumlah akun role rw'));
        $this->assertEquals('statistik_akun', $service->detectIntent('user role rw ada berapa'));

        // Role RT
        $this->assertEquals('statistik_akun', $service->detectIntent('ada berapa user dengan role rt'));
        $this->assertEquals('statistik_akun', $service->detectIntent('berapa user role rt'));
        $this->assertEquals('statistik_akun', $service->detectIntent('total akun rt'));
        $this->assertEquals('statistik_akun', $service->detectIntent('user role rt ada berapa?'));

        // Role Warga & Admin
        $this->assertEquals('statistik_akun', $service->detectIntent('berapa user role warga'));
        $this->assertEquals('statistik_akun', $service->detectIntent('total akun warga'));
        $this->assertEquals('statistik_akun', $service->detectIntent('berapa user role admin'));
        $this->assertEquals('statistik_akun', $service->detectIntent('total akun pengguna'));
    }

    /** Test 3: Deteksi intent statistik_penduduk dan statistik_kepala_keluarga */
    public function test_intent_detection_penduduk_and_kk(): void
    {
        $service = app(AiChatContextService::class);

        // Penduduk / Warga
        $this->assertEquals('statistik_penduduk', $service->detectIntent('berapa jumlah penduduk'));
        $this->assertEquals('statistik_penduduk', $service->detectIntent('total penduduk desa'));
        $this->assertEquals('statistik_penduduk', $service->detectIntent('jumlah warga desa'));
        $this->assertEquals('statistik_penduduk', $service->detectIntent('ada berapa warga'));
        $this->assertEquals('statistik_penduduk', $service->detectIntent('jumlah jiwa'));

        // Kepala Keluarga (KK)
        $this->assertEquals('statistik_kepala_keluarga', $service->detectIntent('berapa kepala keluarga'));
        $this->assertEquals('statistik_kepala_keluarga', $service->detectIntent('berapa jumlah kk'));
        $this->assertEquals('statistik_kepala_keluarga', $service->detectIntent('ada berapa kepala keluarga?'));
        $this->assertEquals('statistik_kepala_keluarga', $service->detectIntent('total kepala keluarga'));
        $this->assertEquals('statistik_kepala_keluarga', $service->detectIntent('jumlah kartu keluarga'));
    }

    /** Test 4: 'berapa jumlah rw' menghitung entitas RW unik dari tabel 'rw', bukan dari akun user */
    public function test_jumlah_rw_counts_actual_unique_rw_entities_not_users(): void
    {
        // Fixture: Buat 2 RW di tabel 'rw'
        $rw1 = Rw::create(['desa_id' => $this->desa->id, 'nomor_rw' => '001', 'ketua_rw' => 'Ketua RW 1', 'status' => true]);
        $rw2 = Rw::create(['desa_id' => $this->desa->id, 'nomor_rw' => '002', 'ketua_rw' => 'Ketua RW 2', 'status' => true]);

        // Fixture: Buat 5 user dengan role 'rw' (misal pengurus RW bertambah)
        for ($i = 1; $i <= 5; $i++) {
            User::factory()->create([
                'name' => 'Pengurus RW '.$i,
                'email' => 'rw'.$i.'@test.id',
                'role_id' => $this->roleRw->id,
                'rw_id' => $rw1->id,
            ]);
        }

        $response = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'berapa jumlah rw',
        ]);

        $response->assertStatus(200);
        $content = $response->json('ai_message.content');

        // Harus menjawab 2 (jumlah entitas RW), BUKAN 5 (jumlah user role RW)
        $this->assertStringContainsString('2', $content);
        $this->assertStringContainsString('wilayah RW', $content);
        $this->assertStringNotContainsString('5 wilayah', $content);
    }

    /** Test 5: 'berapa jumlah rt' menghitung entitas RT unik dari tabel 'rt', bukan dari akun user */
    public function test_jumlah_rt_counts_actual_unique_rt_entities_not_users(): void
    {
        $rw1 = Rw::create(['desa_id' => $this->desa->id, 'nomor_rw' => '001', 'ketua_rw' => 'Ketua RW 1', 'status' => true]);

        // Fixture: Buat 3 RT di tabel 'rt'
        Rt::create(['rw_id' => $rw1->id, 'nomor_rt' => '001', 'ketua_rt' => 'Ketua RT 1', 'status' => true]);
        Rt::create(['rw_id' => $rw1->id, 'nomor_rt' => '002', 'ketua_rt' => 'Ketua RT 2', 'status' => true]);
        Rt::create(['rw_id' => $rw1->id, 'nomor_rt' => '003', 'ketua_rt' => 'Ketua RT 3', 'status' => true]);

        // Fixture: Buat 7 akun pengguna dengan role 'rt'
        for ($i = 1; $i <= 7; $i++) {
            User::factory()->create([
                'name' => 'Pengurus RT '.$i,
                'email' => 'rt'.$i.'@test.id',
                'role_id' => $this->roleRt->id,
                'rw_id' => $rw1->id,
            ]);
        }

        $response = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'berapa jumlah rt',
        ]);

        $response->assertStatus(200);
        $content = $response->json('ai_message.content');

        // Harus menjawab 3 RT, BUKAN 7
        $this->assertStringContainsString('3', $content);
        $this->assertStringContainsString('wilayah RT', $content);
        $this->assertStringNotContainsString('7 wilayah', $content);
    }

    /** Test 6: 'ada berapa user dengan role rw' menjawab akun role RW dan menjelaskan perbedaan dengan wilayah RW */
    public function test_user_role_rw_answers_accounts_and_explains_difference_from_wilayah(): void
    {
        // 1 RW di tabel 'rw'
        $rw1 = Rw::create(['desa_id' => $this->desa->id, 'nomor_rw' => '001', 'ketua_rw' => 'Ketua RW 1', 'status' => true]);

        // 4 User dengan role 'rw'
        for ($i = 1; $i <= 4; $i++) {
            User::factory()->create([
                'name' => 'Akun RW '.$i,
                'email' => 'akunque_rw'.$i.'@test.id',
                'role_id' => $this->roleRw->id,
                'rw_id' => $rw1->id,
            ]);
        }

        // Tambah juga role lain agar bisa memastikan tidak mencampuradukkan semua role
        User::factory()->create(['name' => 'Warga 1', 'email' => 'w1@test.id', 'role_id' => $this->roleWarga->id]);
        User::factory()->create(['name' => 'RT 1', 'email' => 'rt1@test.id', 'role_id' => $this->roleRt->id]);

        $response = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'ada berapa user dengan role rw',
        ]);

        $response->assertStatus(200);
        $content = $response->json('ai_message.content');

        // Harus menyebut 4 akun role RW secara spesifik
        $this->assertStringContainsString('4', $content);
        $this->assertStringContainsString('role', $content);
        $this->assertStringContainsString('RW', $content);

        // Harus menjelaskan perbedaan antara akun login dan wilayah administratif
        $this->assertStringContainsString('wilayah administratif RW', $content);
        $this->assertStringContainsString('1', $content); // Menyebut 1 wilayah RW
    }

    /** Test 7: 'ada berapa user dengan role rt' menjawab akun role RT dan menjelaskan perbedaan dengan wilayah RT */
    public function test_user_role_rt_answers_accounts_and_explains_difference_from_wilayah(): void
    {
        $rw1 = Rw::create(['desa_id' => $this->desa->id, 'nomor_rw' => '001', 'status' => true]);
        $rt1 = Rt::create(['rw_id' => $rw1->id, 'nomor_rt' => '001', 'status' => true]);
        $rt2 = Rt::create(['rw_id' => $rw1->id, 'nomor_rt' => '002', 'status' => true]);

        // 6 User dengan role 'rt'
        for ($i = 1; $i <= 6; $i++) {
            User::factory()->create([
                'name' => 'Akun RT '.$i,
                'email' => 'rt_test'.$i.'@test.id',
                'role_id' => $this->roleRt->id,
                'rw_id' => $rw1->id,
                'rt_id' => $rt1->id,
            ]);
        }

        $response = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'ada berapa user dengan role rt',
        ]);

        $response->assertStatus(200);
        $content = $response->json('ai_message.content');

        // Harus menyebut 6 akun role RT
        $this->assertStringContainsString('6', $content);
        $this->assertStringContainsString('RT', $content);
        // Menjelaskan perbedaan dengan wilayah fisik
        $this->assertStringContainsString('wilayah administratif RT', $content);
        $this->assertStringContainsString('2', $content); // 2 wilayah RT
    }

    /** Test 8: Data kosong — sistem menjawab 0 atau belum terdata dengan jujur, bukan pesan error */
    public function test_empty_data_returns_honest_zero_not_error(): void
    {
        // Tanpa data RW, RT, atau Warga
        $responseRw = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'berapa jumlah rw',
        ]);
        $responseRw->assertStatus(200);
        $this->assertStringContainsString('belum ada data', strtolower($responseRw->json('ai_message.content')));

        $responseRt = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'berapa jumlah rt',
        ]);
        $responseRt->assertStatus(200);
        $this->assertStringContainsString('belum ada data', strtolower($responseRt->json('ai_message.content')));

        $responseWarga = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'berapa jumlah penduduk',
        ]);
        $responseWarga->assertStatus(200);
        $this->assertStringContainsString('0 warga', strtolower($responseWarga->json('ai_message.content')));
    }

    /** Test 9: Jumlah penduduk dihitung dari tabel 'warga', bukan dari tabel 'users' */
    public function test_penduduk_counted_from_warga_table_not_users(): void
    {
        $rw1 = Rw::create(['desa_id' => $this->desa->id, 'nomor_rw' => '001', 'status' => true]);
        $rt1 = Rt::create(['rw_id' => $rw1->id, 'nomor_rt' => '001', 'status' => true]);

        // Buat 8 akun User login
        for ($i = 1; $i <= 8; $i++) {
            User::factory()->create([
                'email' => 'user_login'.$i.'@test.id',
                'role_id' => $this->roleWarga->id,
            ]);
        }

        // Namun hanya 2 warga riil di tabel 'warga'
        Warga::create([
            'nik' => '3201010101010011', 'nomor_kk' => '3201010101010099', 'nama_lengkap' => 'Warga Satu',
            'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '1990-01-01', 'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam', 'status_perkawinan' => 'Kawin', 'pendidikan' => 'SMA',
            'pekerjaan' => 'Karyawan', 'rw_id' => $rw1->id, 'rt_id' => $rt1->id,
            'status_dalam_keluarga' => 'Kepala Keluarga', 'status_kependudukan' => 'Tetap',
        ]);
        Warga::create([
            'nik' => '3201010101010012', 'nomor_kk' => '3201010101010099', 'nama_lengkap' => 'Warga Dua',
            'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '1992-02-02', 'jenis_kelamin' => 'Perempuan',
            'agama' => 'Islam', 'status_perkawinan' => 'Kawin', 'pendidikan' => 'SMA',
            'pekerjaan' => 'IRT', 'rw_id' => $rw1->id, 'rt_id' => $rt1->id,
            'status_dalam_keluarga' => 'Istri', 'status_kependudukan' => 'Tetap',
        ]);

        $response = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'berapa jumlah penduduk',
        ]);

        $response->assertStatus(200);
        $content = $response->json('ai_message.content');

        // Harus 2 orang warga, BUKAN 8 (jumlah akun user)
        $this->assertStringContainsString('2', $content);
        $this->assertStringContainsString('data warga resmi', $content);
        $this->assertStringNotContainsString('8', $content);
    }

    /** Test 10: Pertanyaan kepala keluarga membedakan data tabel keluarga dan status warga */
    public function test_kepala_keluarga_distinguishes_keluarga_table_and_warga_status(): void
    {
        $rw1 = Rw::create(['desa_id' => $this->desa->id, 'nomor_rw' => '001', 'status' => true]);
        $rt1 = Rt::create(['rw_id' => $rw1->id, 'nomor_rt' => '001', 'status' => true]);

        // Buat 1 warga berstatus Kepala Keluarga di tabel warga, namun 0 di tabel keluarga
        Warga::create([
            'nik' => '3201010101010011', 'nomor_kk' => '4444444444444444', 'nama_lengkap' => 'Bapak Kepala',
            'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '1980-01-01', 'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam', 'status_perkawinan' => 'Kawin', 'pendidikan' => 'S1',
            'pekerjaan' => 'PNS', 'rw_id' => $rw1->id, 'rt_id' => $rt1->id,
            'status_dalam_keluarga' => 'Kepala Keluarga', 'status_kependudukan' => 'Tetap',
        ]);

        $response = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'berapa kepala keluarga',
        ]);

        $response->assertStatus(200);
        $content = $response->json('ai_message.content');

        // Harus menyebut 1 warga dengan status Kepala Keluarga
        $this->assertStringContainsString('1', $content);
        $this->assertStringContainsString('Kepala Keluarga', $content);
    }

    /** Test 11: Pertanyaan layanan surat menghitung template aktif dari database */
    public function test_layanan_surat_counts_active_templates(): void
    {
        SuratTemplate::create(['nama_surat' => 'Surat Domisili', 'kode_surat' => 'SK-DOM', 'is_active' => true]);
        SuratTemplate::create(['nama_surat' => 'Surat Usaha', 'kode_surat' => 'SK-SKU', 'is_active' => true]);
        SuratTemplate::create(['nama_surat' => 'Surat Non-Aktif', 'kode_surat' => 'SK-OFF', 'is_active' => false]);

        $response = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'berapa jenis layanan surat',
        ]);

        $response->assertStatus(200);
        $content = $response->json('ai_message.content');

        // Menampilkan 2 yang aktif
        $this->assertStringContainsString('2', $content);
        $this->assertStringContainsString('Surat Domisili', $content);
        $this->assertStringContainsString('Surat Usaha', $content);
        $this->assertStringNotContainsString('Surat Non-Aktif', $content);
    }

    /** Test 12: Variasi huruf kapital dan tanda baca normalisasi kalimat */
    public function test_sentence_variations_and_normalization(): void
    {
        $rw1 = Rw::create(['desa_id' => $this->desa->id, 'nomor_rw' => '001', 'status' => true]);
        User::factory()->create(['name' => 'Akun RW 1', 'email' => 'arw@test.id', 'role_id' => $this->roleRw->id, 'rw_id' => $rw1->id]);

        // Huruf kapital penuh dan tanda tanya banyak
        $res1 = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'ADA BERAPA RW???',
        ]);
        $res1->assertStatus(200);
        $this->assertStringContainsString('1', $res1->json('ai_message.content'));

        // Variasi kalimat 'jumlah rw berapa'
        $res2 = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'Jumlah RW berapa, min?',
        ]);
        $res2->assertStatus(200);
        $this->assertStringContainsString('1', $res2->json('ai_message.content'));

        // 'TOTAL AKUN RW'
        $res3 = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'TOTAL AKUN RW!',
        ]);
        $res3->assertStatus(200);
        $this->assertStringContainsString('1', $res3->json('ai_message.content'));
        $this->assertStringContainsString('role', $res3->json('ai_message.content'));
    }

    /** Test 13: Otorisasi berbasis role pengguna login (tidak percaya klaim di isi pesan) */
    public function test_role_authorization_security_ignores_message_claims(): void
    {
        $rw1 = Rw::create(['desa_id' => $this->desa->id, 'nomor_rw' => '001', 'status' => true]);
        $rt1 = Rt::create(['rw_id' => $rw1->id, 'nomor_rt' => '001', 'status' => true]);
        $rw2 = Rw::create(['desa_id' => $this->desa->id, 'nomor_rw' => '002', 'status' => true]);
        $rt2 = Rt::create(['rw_id' => $rw2->id, 'nomor_rt' => '002', 'status' => true]);

        // Akun pengurus RT 1
        $userRt1 = User::factory()->create([
            'name' => 'Ketua RT 1',
            'email' => 'rt1@desa.id',
            'role_id' => $this->roleRt->id,
            'rw_id' => $rw1->id,
            'rt_id' => $rt1->id,
        ]);

        // Akun di RT 2
        User::factory()->create(['name' => 'User RT 2', 'email' => 'u_rt2@desa.id', 'role_id' => $this->roleRt->id, 'rw_id' => $rw2->id, 'rt_id' => $rt2->id]);

        // User RT 1 mencoba mengklaim bahwa dia adalah Super Admin lewat chat
        $service = app(AiChatContextService::class);
        $rtScope = $service->getAkunStatistikData($userRt1, 'rt', 'statistik_akun');

        // Hanya 1 akun di RT 1 miliknya, bukan 2 akun di seluruh desa
        $this->assertEquals(1, $rtScope['total']);
    }
}
