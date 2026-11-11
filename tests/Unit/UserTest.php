<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_role_returns_true_for_matching_single_role(): void
    {
        $role = Role::create(['name' => 'rt']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->hasRole('rt'));
        $this->assertFalse($user->hasRole('rw'));
        $this->assertFalse($user->hasRole('super_admin'));
    }

    public function test_user_has_role_returns_true_for_matching_array_of_roles(): void
    {
        $role = Role::create(['name' => 'rw']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->hasRole(['super_admin', 'rw']));
        $this->assertFalse($user->hasRole(['super_admin', 'rt']));
    }

    public function test_user_has_role_returns_false_when_user_has_no_role(): void
    {
        $user = User::factory()->create(['role_id' => null]);

        $this->assertFalse($user->hasRole('warga'));
        $this->assertFalse($user->hasRole(['super_admin', 'rt']));
    }

    public function test_user_is_active_attribute_casts_to_boolean(): void
    {
        $user = User::factory()->create(['is_active' => 1]);
        $this->assertIsBool($user->is_active);
        $this->assertTrue($user->is_active);

        $user->is_active = 0;
        $user->save();
        $user->refresh();

        $this->assertIsBool($user->is_active);
        $this->assertFalse($user->is_active);
    }
}
