<?php

namespace Tests\Feature\Auth;

use App\Models\Crm\Customer;
use App\Models\User;
use Database\Seeders\ClientDemoUserSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/client/login');

        $response->assertStatus(200);
    }

    public function test_staff_user_can_view_client_login_without_admin_redirect(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['auth_context' => 'admin'])
            ->actingAs($user)
            ->get('/client/login');

        $response->assertOk();
        $response->assertSee(__('Client Login'), false);
        $this->assertFalse($response->isRedirect(route('admin.dashboard', absolute: false)));
    }

    public function test_staff_user_can_view_public_homepage_without_admin_redirect(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['auth_context' => 'admin'])
            ->actingAs($user)
            ->get('/');

        $response->assertOk();
        $this->assertFalse($response->isRedirect(route('admin.dashboard', absolute: false)));
    }

    public function test_staff_users_cannot_authenticate_through_client_login(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/client/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_client_users_can_authenticate_through_client_login(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create([
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'employee_id' => null,
        ]);

        $response = $this->post('/client/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('client.dashboard', absolute: false));
    }

    public function test_client_users_cannot_authenticate_through_admin_login(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create([
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'employee_id' => null,
        ]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_client_demo_seeder_creates_portal_login(): void
    {
        $this->seed([
            RolesAndPermissionsSeeder::class,
            OrganizationFoundationSeeder::class,
            ClientDemoUserSeeder::class,
        ]);

        $response = $this->post('/client/login', [
            'email' => 'client@janaprints.local',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('client.dashboard', absolute: false));
        $this->assertTrue(auth()->user()->isClientPortalAccount());
    }

    public function test_client_session_cannot_access_admin_routes(): void
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create([
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'employee_id' => null,
        ]);

        $response = $this->withSession(['auth_context' => 'client'])
            ->actingAs($user)
            ->get('/admin');

        $response->assertRedirect(route('admin.login', absolute: false));
        $this->assertGuest();
    }
}
