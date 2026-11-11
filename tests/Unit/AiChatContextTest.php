<?php

namespace Tests\Unit;

use App\Http\Controllers\AiChatController;
use App\Models\Desa;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class AiChatContextTest extends TestCase
{
    use RefreshDatabase;

    private function invokeBuildContext($user): string
    {
        $controller = new AiChatController;
        $method = new ReflectionMethod(AiChatController::class, 'buildContext');
        $method->setAccessible(true);

        return $method->invoke($controller, $user);
    }

    public function test_build_context_contains_strict_coding_prohibition(): void
    {
        $role = Role::create(['name' => 'warga']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $prompt = $this->invokeBuildContext($user);

        $this->assertStringContainsString('ANDA ADALAH ASISTEN AI RESMI SISTEM INFORMASI DESA', $prompt);
        $this->assertStringContainsString('LARANGAN CODING DAN SOURCE CODE', $prompt);
        $this->assertStringContainsString('JIKA USER MEMINTA PYTHON', $prompt);
    }

    public function test_build_context_includes_rt_rw_metadata_for_rt_role(): void
    {
        $role = Role::create(['name' => 'rt']);
        $desa = Desa::create([
            'nama' => 'Desa Sukamaju',
            'kecamatan' => 'Kecamatan Makmur',
            'kabupaten_kota' => 'Kabupaten Sejahtera',
            'provinsi' => 'Provinsi Gemilang',
            'alamat' => 'Jl. Merdeka No. 1',
        ]);
        $rw = Rw::create([
            'desa_id' => $desa->id,
            'nomor_rw' => '002',
            'ketua_rw' => 'Ketua RW 2',
            'alamat' => 'Lingkungan RW 2',
        ]);
        $rt = Rt::create([
            'rw_id' => $rw->id,
            'nomor_rt' => '005',
            'ketua_rt' => 'Ketua RT 5',
            'alamat' => 'Lingkungan RT 5 RW 2',
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'rw_id' => $rw->id,
            'rt_id' => $rt->id,
        ]);

        $prompt = $this->invokeBuildContext($user);

        $this->assertStringContainsString('rt', $prompt);
        $this->assertStringContainsString('RW 002', $prompt);
        $this->assertStringContainsString('RT 005', $prompt);
    }

    public function test_build_context_contains_natural_response_and_scope_rules(): void
    {
        $role = Role::create(['name' => 'warga']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $prompt = $this->invokeBuildContext($user);

        $this->assertStringContainsString('GAYA DAN FORMAT JAWABAN', $prompt);
        $this->assertStringContainsString('Jawab secara santai, ramah, natural, dan langsung ke intinya', $prompt);
        $this->assertStringContainsString('DILARANG mengarang wilayah Kecamatan', $prompt);
        $this->assertStringContainsString('Super Admin, RW, RT, dan Warga', $prompt);
    }
}
