<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role = User::ROLE_USER): User
    {
        return User::create([
            'name' => ucfirst($role).' Test',
            'email' => $role.'@example.com',
            'password' => 'password',
            'role' => $role,
        ]);
    }

    public function test_login_returns_a_token_and_is_logged(): void
    {
        $user = $this->makeUser();

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);

        $this->assertDatabaseHas('activity_logs', ['action' => 'auth.login', 'user_id' => $user->id]);
    }

    public function test_wrong_password_is_rejected_and_logged(): void
    {
        $user = $this->makeUser();

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'nope'])
            ->assertStatus(422);

        $this->assertDatabaseHas('activity_logs', ['action' => 'auth.login_failed']);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = $this->makeUser();
        $user->update(['active' => false]);

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])
            ->assertStatus(422);
    }

    public function test_protected_routes_require_authentication(): void
    {
        $this->getJson('/api/participants')->assertUnauthorized();
    }

    public function test_regular_user_cannot_use_admin_routes(): void
    {
        $participant = Participant::create(['name' => 'Someone']);
        Sanctum::actingAs($this->makeUser(User::ROLE_USER));

        $this->getJson('/api/logs')->assertForbidden();
        $this->getJson('/api/users')->assertForbidden();
        $this->deleteJson('/api/participants/'.$participant->id)->assertForbidden();

        $this->assertDatabaseHas('participants', ['id' => $participant->id]);
    }

    public function test_admin_can_manage_users_and_changes_are_logged(): void
    {
        Sanctum::actingAs($admin = $this->makeUser(User::ROLE_ADMIN));

        $this->postJson('/api/users', [
            'name' => 'New Staff',
            'email' => 'staff@example.com',
            'password' => 'secret-123',
            'role' => 'user',
        ])->assertCreated();

        $this->assertDatabaseHas('activity_logs', ['action' => 'user.created', 'user_id' => $admin->id]);
        $this->getJson('/api/logs')->assertOk()->assertJsonPath('data.0.action', 'user.created');
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        Sanctum::actingAs($admin = $this->makeUser(User::ROLE_ADMIN));

        $this->deleteJson('/api/users/'.$admin->id)->assertStatus(422);
        $this->assertSame(0, ActivityLog::where('action', 'user.deleted')->count());
    }
}
