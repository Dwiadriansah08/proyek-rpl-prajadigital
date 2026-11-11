<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutoAccountCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $rtUser;

    private Desa $desa;

    private Rw $rw;

    private Rt $rt;

    private Rt $otherRt;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['name' => 'super_admin']);
        $roleRw = Role::create(['name' => 'rw']);
        $roleRt = Role::create(['name' => 'rt']);
        Role::create(['name' => 'warga']);

        $this->desa = Desa::create([
            'nama' => 'Desa Sukamaju',
            'kecamatan' => 'Kecamatan Makmur',
            'kabupaten_kota' => 'Kabupaten Sejahtera',
            'provinsi' => 'Provinsi Gemilang',
            'alamat' => 'Jl. Merdeka No. 1',
        ]);

        $this->rw = Rw::create([
            'desa_id' => $this->desa->id,
            'nomor_rw' => '001',
            'ketua_rw' => 'Ketua RW 001',
            'alamat' => 'Lingkungan RW 001',
            'status' => true,
        ]);

        $this->rt = Rt::create([
            'rw_id' => $this->rw->id,
            'nomor_rt' => '001',
            'ketua_rt' => 'Ketua RT 001',
            'alamat' => 'Lingkungan RT 001 RW 001',
            'status' => true,
        ]);

        $this->otherRt = Rt::create([
            'rw_id' => $this->rw->id,
            'nomor_rt' => '002',
            'ketua_rt' => 'Ketua RT 002',
            'alamat' => 'Lingkungan RT 002 RW 001',
            'status' => true,
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $roleAdmin->id,
            'username' => 'admin_utama',
            'desa_id' => $this->desa->id,
        ]);

        $this->rtUser = User::factory()->create([
            'role_id' => $roleRt->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
            'username' => 'rt001_leader',
            'desa_id' => $this->desa->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Payload warga valid berdasarkan kolom NOT NULL di tabel warga.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function wargaPayload(array $overrides = []): array
    {
        return array_merge([
            'nik' => '3201012345670001',
            'nomor_kk' => '3201012345670002',
            'nama_lengkap' => 'Budi Santoso',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Kawin',
            'pendidikan' => 'SMA',
            'pekerjaan' => 'Karyawan Swasta',
            'alamat' => 'Jl. Kenanga No. 10',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
            'nomor_telepon' => '081234567890',
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_kependudukan' => 'Tetap',
        ], $overrides);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Admin membuat warga dengan akun login
    // ─────────────────────────────────────────────────────────────────────

    public function test_admin_can_create_warga_with_login_account(): void
    {
        $payload = $this->wargaPayload([
            'email' => 'budi.santoso@example.com',
            'username' => 'budisantoso90',
            'password' => 'PasswordBudi123!',
            'password_confirmation' => 'PasswordBudi123!',
        ]);

        $response = $this->actingAs($this->admin)->post(route('warga.store'), $payload);
        $response->assertRedirect(route('warga.index'));

        $this->assertDatabaseHas('warga', [
            'nik' => '3201012345670001',
            'email' => 'budi.santoso@example.com',
        ]);

        $user = User::where('email', 'budi.santoso@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('budisantoso90', $user->username);
        $this->assertEquals('warga', $user->role->name);
        $this->assertEquals($this->rt->id, $user->rt_id);
        $this->assertEquals($this->rw->id, $user->rw_id);
        $this->assertTrue(Hash::check('PasswordBudi123!', $user->password));
    }

    public function test_can_create_warga_with_empty_or_null_pendidikan_and_optional_fields(): void
    {
        $payload = [
            'nik' => '3201019999990001',
            'nomor_kk' => '3201019999990002',
            'nama_lengkap' => 'Warga Tanpa Pendidikan',
            'jenis_kelamin' => 'Laki-laki',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
            'email' => 'tanpa.pendidikan@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'pendidikan' => null,
            'pekerjaan' => null,
            'tempat_lahir' => null,
            'tanggal_lahir' => null,
            'agama' => null,
            'status_perkawinan' => null,
            'alamat' => null,
            'status_dalam_keluarga' => null,
            'status_kependudukan' => null,
        ];

        $response = $this->actingAs($this->admin)->post(route('warga.store'), $payload);
        $response->assertRedirect(route('warga.index'));

        $this->assertDatabaseHas('warga', [
            'nik' => '3201019999990001',
            'nama_lengkap' => 'Warga Tanpa Pendidikan',
            'pendidikan' => null,
        ]);
    }

    public function test_can_create_warga_omitting_optional_fields_completely(): void
    {
        $payload = [
            'nik' => '3201019999990003',
            'nomor_kk' => '3201019999990004',
            'nama_lengkap' => 'Warga Minimal Field',
            'jenis_kelamin' => 'Perempuan',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ];

        $response = $this->actingAs($this->admin)->post(route('warga.store'), $payload);
        $response->assertRedirect(route('warga.index'));

        $this->assertDatabaseHas('warga', [
            'nik' => '3201019999990003',
            'nama_lengkap' => 'Warga Minimal Field',
            'pendidikan' => null,
        ]);
    }

    public function test_warga_account_can_login_with_email(): void
    {
        $payload = $this->wargaPayload([
            'nik' => '3201012345670010',
            'email' => 'budi2@example.com',
            'username' => 'budiu2',
            'password' => 'MyPass123!',
            'password_confirmation' => 'MyPass123!',
        ]);
        $this->actingAs($this->admin)->post(route('warga.store'), $payload)
            ->assertRedirect(route('warga.index'));

        $user = User::where('email', 'budi2@example.com')->firstOrFail();

        $this->post(route('logout'));
        $this->post(route('login'), [
            'email' => 'budi2@example.com',
            'password' => 'MyPass123!',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_warga_account_can_login_with_username(): void
    {
        $payload = $this->wargaPayload([
            'nik' => '3201012345670011',
            'email' => 'budi3@example.com',
            'username' => 'budiu3',
            'password' => 'MyPass456!',
            'password_confirmation' => 'MyPass456!',
        ]);
        $this->actingAs($this->admin)->post(route('warga.store'), $payload)
            ->assertRedirect(route('warga.index'));

        $user = User::where('email', 'budi3@example.com')->firstOrFail();

        $this->post(route('logout'));
        $this->post(route('login'), [
            'email' => 'budiu3',
            'password' => 'MyPass456!',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    // ─────────────────────────────────────────────────────────────────────
    // RT scope enforcement
    // ─────────────────────────────────────────────────────────────────────

    public function test_rt_can_create_warga_with_account_in_own_rt(): void
    {
        $payload = $this->wargaPayload([
            'nik' => '3201012345670003',
            'nama_lengkap' => 'Siti Aminah',
            'jenis_kelamin' => 'Perempuan',
            'email' => 'siti.aminah@example.com',
            'username' => 'sitiaminah95',
            'password' => 'SitiPassword123!',
            'password_confirmation' => 'SitiPassword123!',
        ]);

        $response = $this->actingAs($this->rtUser)->post(route('warga.store'), $payload);
        $response->assertRedirect(route('warga.index'));

        $user = User::where('email', 'siti.aminah@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('warga', $user->role->name);
        $this->assertEquals($this->rt->id, $user->rt_id);
    }

    public function test_rt_cannot_create_warga_in_another_rt(): void
    {
        $payload = $this->wargaPayload([
            'nik' => '3201012345670004',
            'nama_lengkap' => 'Warga Luar RT',
            'rt_id' => $this->otherRt->id, // bukan RT si rtUser
            'email' => 'luar.rt@example.com',
            'password' => 'PasswordLuar123!',
            'password_confirmation' => 'PasswordLuar123!',
        ]);

        $response = $this->actingAs($this->rtUser)->post(route('warga.store'), $payload);
        $response->assertForbidden();

        $this->assertDatabaseMissing('warga', ['nik' => '3201012345670004']);
        $this->assertDatabaseMissing('users', ['email' => 'luar.rt@example.com']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // RT creation
    // ─────────────────────────────────────────────────────────────────────

    public function test_admin_can_create_rt_with_account_and_login(): void
    {
        $payload = [
            'rw_id' => $this->rw->id,
            'nomor_rt' => '003',
            'ketua_rt' => 'Ahmad Subarjo',
            'alamat' => 'Jl. Kenari RT 003',
            'status' => '1',
            'email' => 'rt003@example.com',
            'username' => 'rt003_ketua',
            'password' => 'RtPassSecret123!',
            'password_confirmation' => 'RtPassSecret123!',
        ];

        $this->actingAs($this->admin)->post(route('rt.store'), $payload)
            ->assertRedirect(route('rt.index'));

        $rt = Rt::where('nomor_rt', '003')->where('rw_id', $this->rw->id)->first();
        $this->assertNotNull($rt);

        $user = User::where('email', 'rt003@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('rt', $user->role->name);
        $this->assertEquals($rt->id, $user->rt_id);
        $this->assertEquals($this->rw->id, $user->rw_id);
        $this->assertTrue(Hash::check('RtPassSecret123!', $user->password));

        // Login menggunakan username
        $this->post(route('logout'));
        $this->post(route('login'), [
            'email' => 'rt003_ketua',
            'password' => 'RtPassSecret123!',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    // ─────────────────────────────────────────────────────────────────────
    // RW creation
    // ─────────────────────────────────────────────────────────────────────

    public function test_admin_can_create_rw_with_account_and_login(): void
    {
        $payload = [
            'desa_id' => $this->desa->id,
            'nomor_rw' => '002',
            'ketua_rw' => 'Bambang Irawan',
            'alamat' => 'Jl. Cendrawasih RW 002',
            'status' => '1',
            'email' => 'rw002@example.com',
            'username' => 'rw002_ketua',
            'password' => 'RwPassSecret123!',
            'password_confirmation' => 'RwPassSecret123!',
        ];

        $this->actingAs($this->admin)->post(route('rw.store'), $payload)
            ->assertRedirect(route('rw.index'));

        $rw = Rw::where('nomor_rw', '002')->first();
        $this->assertNotNull($rw);

        $user = User::where('email', 'rw002@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('rw', $user->role->name);
        $this->assertEquals($rw->id, $user->rw_id);
        $this->assertTrue(Hash::check('RwPassSecret123!', $user->password));

        // Login menggunakan email
        $this->post(route('logout'));
        $this->post(route('login'), [
            'email' => 'rw002@example.com',
            'password' => 'RwPassSecret123!',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Validasi duplikasi
    // ─────────────────────────────────────────────────────────────────────

    public function test_duplicate_email_is_rejected_when_creating_warga(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $payload = $this->wargaPayload([
            'nik' => '3201012345670099',
            'email' => 'duplicate@example.com',
            'password' => 'PasswordDuplikat123!',
            'password_confirmation' => 'PasswordDuplikat123!',
        ]);

        $this->actingAs($this->admin)->post(route('warga.store'), $payload)
            ->assertSessionHasErrors(['email']);
    }

    public function test_duplicate_username_is_rejected_when_creating_warga(): void
    {
        User::factory()->create(['username' => 'existing_user']);

        $payload = $this->wargaPayload([
            'nik' => '3201012345670098',
            'email' => 'unique-email@example.com',
            'username' => 'existing_user',
            'password' => 'PasswordDuplikat123!',
            'password_confirmation' => 'PasswordDuplikat123!',
        ]);

        $this->actingAs($this->admin)->post(route('warga.store'), $payload)
            ->assertSessionHasErrors(['username']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Password hashing
    // ─────────────────────────────────────────────────────────────────────

    public function test_passwords_are_hashed_and_not_stored_as_plaintext(): void
    {
        $plain = 'SecurePassword999!';
        $payload = $this->wargaPayload([
            'nik' => '3201012345670050',
            'email' => 'hash.test@example.com',
            'password' => $plain,
            'password_confirmation' => $plain,
        ]);

        $this->actingAs($this->admin)->post(route('warga.store'), $payload)
            ->assertRedirect(route('warga.index'));

        $user = User::where('email', 'hash.test@example.com')->firstOrFail();
        $this->assertNotEquals($plain, $user->password);
        $this->assertTrue(Hash::check($plain, $user->password));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Toggle eye icon DOM check
    // ─────────────────────────────────────────────────────────────────────

    public function test_warga_create_form_contains_toggle_password_button(): void
    {
        $response = $this->actingAs($this->admin)->get(route('warga.create'));
        $response->assertOk();
        $response->assertSee('toggle-password');
        $response->assertSee('bi-eye', false);
    }

    public function test_rt_create_form_contains_toggle_password_button(): void
    {
        $response = $this->actingAs($this->admin)->get(route('rt.create'));
        $response->assertOk();
        $response->assertSee('toggle-password');
        $response->assertSee('bi-eye', false);
    }

    public function test_rw_create_form_contains_toggle_password_button(): void
    {
        $response = $this->actingAs($this->admin)->get(route('rw.create'));
        $response->assertOk();
        $response->assertSee('toggle-password');
        $response->assertSee('bi-eye', false);
    }

    public function test_login_form_contains_toggle_password_button(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk();
        $response->assertSee('toggle-password');
        $response->assertSee('bi-eye', false);
    }

    public function test_register_form_contains_toggle_password_buttons(): void
    {
        $response = $this->get(route('register'));
        $response->assertOk();
        $response->assertSee('toggle-password');
        $response->assertSee('bi-eye', false);
    }
}
