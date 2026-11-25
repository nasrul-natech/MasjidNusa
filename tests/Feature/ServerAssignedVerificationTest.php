<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactorVerified;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServerAssignedVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setDate(2026, 11, 25)->setTime(12, 0));
        Role::findOrCreate('jamaah', 'web');
    }

    public function test_registration_uses_server_time_instead_of_request_timestamp(): void
    {
        $this->mock(NotificationService::class)
            ->shouldReceive('sendWelcome')->once()->with(Mockery::type(User::class));

        $this->post('/register', [
            'name' => 'Registered User',
            'email' => 'registered@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'email_verified_at' => '2000-01-01 00:00:00',
        ])->assertSessionHasNoErrors()->assertRedirect('/');

        $user = User::where('email', 'registered@example.com')->sole();
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->email_verified_at->equalTo(now()));
        $this->assertTrue($user->hasRole('jamaah'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_new_google_user_gets_server_verification_time(): void
    {
        $this->mock(NotificationService::class)
            ->shouldReceive('sendWelcome')->once()->with(Mockery::type(User::class));
        $googleUser = (new GoogleUser)->map([
            'id' => 'google-new-user',
            'name' => 'Google User',
            'email' => 'google@example.com',
            'avatar' => null,
        ]);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn(
            Mockery::mock()->shouldReceive('user')->once()->andReturn($googleUser)->getMock()
        );

        $this->get(route('auth.google.callback', [
            'email_verified_at' => '2000-01-01 00:00:00',
        ]))->assertSessionHasNoErrors()->assertRedirect('/');

        $user = User::where('google_id', 'google-new-user')->sole();
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->email_verified_at->equalTo(now()));
        $this->assertTrue($user->hasRole('jamaah'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_created_user_gets_server_verification_time(): void
    {
        $role = Role::findOrCreate('super_admin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('manage_users', 'web'));
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole($role);
        // Isolate user creation from the separate 2FA challenge flow.
        $this->withoutMiddleware(EnsureTwoFactorVerified::class);

        $this->actingAs($admin)->post(route('super-admin.users.store'), [
            'name' => 'Admin Created User',
            'email' => 'admin-created@example.com',
            'password' => 'password123',
            'role' => 'jamaah',
            'email_verified_at' => '2000-01-01 00:00:00',
        ])->assertSessionHasNoErrors()->assertRedirect(route('super-admin.users.index'));

        $user = User::where('email', 'admin-created@example.com')->sole();
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->email_verified_at->equalTo(now()));
        $this->assertTrue($user->hasRole('jamaah'));
    }

    public function test_verification_timestamp_remains_guarded_against_mass_assignment(): void
    {
        $user = new User;

        $this->assertFalse($user->isFillable('email_verified_at'));
        $user->fill(['email_verified_at' => '2000-01-01 00:00:00']);
        $this->assertNull($user->email_verified_at);
    }
}
