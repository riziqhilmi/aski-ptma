<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_is_redirected_to_admin_dashboard_after_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_member_is_redirected_to_member_dashboard_after_login(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertRedirect(route('member.dashboard'));
    }

    public function test_member_login_ignores_stale_intended_admin_url(): void
    {
        $member = User::factory()->create([
            'role' => 'member',
            'password' => Hash::make('member-password'),
        ]);

        $this->withSession(['url.intended' => route('admin.dashboard')])
            ->post(route('login'), [
                'email' => $member->email,
                'password' => 'member-password',
            ])
            ->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))->assertRedirect(route('member.dashboard'));
        $this->get(route('member.dashboard'))->assertOk();
    }

    public function test_authenticated_user_can_log_out_with_post(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_stale_csrf_token_on_logout_returns_to_dashboard_with_recovery_message(): void
    {
        $request = Request::create(route('logout'), 'POST');
        $request->setLaravelSession($this->app['session.store']);
        $request->setRouteResolver(fn () => Route::getRoutes()->getByName('logout'));
        $this->assertTrue($request->routeIs('logout'));

        $response = $this->app[\App\Exceptions\Handler::class]
            ->render($request, new TokenMismatchException());

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('dashboard'), $response->headers->get('Location'));
        $this->assertSame(
            'Sesi keamanan berubah atau kedaluwarsa. Muat ulang halaman, lalu coba keluar kembali.',
            session('error'),
        );
    }

    public function test_member_cannot_access_admin_dashboard(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->get(route('admin.dashboard'))->assertForbidden();
    }
}
