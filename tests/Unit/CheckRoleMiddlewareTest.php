<?php

namespace Tests\Unit;

use App\Http\Middleware\CheckRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CheckRoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private CheckRole $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new CheckRole;
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $request = Request::create('/dashboard', 'GET');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'super_admin');

        $this->assertTrue($response->isRedirection());
        $this->assertStringContainsString('login', $response->headers->get('Location'));
    }

    public function test_inactive_user_is_logged_out_and_redirected_with_error(): void
    {
        $role = Role::create(['name' => 'warga']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => false,
        ]);

        $this->actingAs($user);
        $request = Request::create('/dashboard', 'GET');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertTrue($response->isRedirection());
        $this->assertGuest();
    }

    public function test_request_proceeds_when_no_roles_are_required(): void
    {
        $role = Role::create(['name' => 'warga']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $request = Request::create('/dashboard', 'GET');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_request_proceeds_when_user_has_matching_role(): void
    {
        $role = Role::create(['name' => 'rt']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $request = Request::create('/rt', 'GET');

        $response = $this->middleware->handle($request, function () {
            return new Response('PROCEEDED');
        }, 'super_admin', 'rw', 'rt');

        $this->assertEquals('PROCEEDED', $response->getContent());
    }

    public function test_request_aborts_with_403_when_user_role_does_not_match(): void
    {
        $role = Role::create(['name' => 'warga']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $request = Request::create('/desa', 'GET');

        try {
            $this->middleware->handle($request, function () {
                return new Response('SHOULD_NOT_REACH');
            }, 'super_admin');
            $this->fail('Expected HttpException was not thrown');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
            $this->assertStringContainsString('tidak memiliki akses', $e->getMessage());
        }
    }
}
