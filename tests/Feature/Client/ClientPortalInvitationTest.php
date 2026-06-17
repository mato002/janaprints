<?php

namespace Tests\Feature\Client;

use App\Models\Crm\Customer;
use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ClientPortalInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_invite_customer_to_client_portal(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->givePermissionTo('crm.customers.edit');

        $customer = Customer::factory()->create([
            'company_id' => $admin->company_id,
            'branch_id' => $admin->default_branch_id,
            'email' => 'portal-customer@example.com',
            'contact_person' => 'Portal Customer',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.crm.customers.portal-invite', $customer))
            ->assertRedirect(route('admin.crm.customers.show', $customer))
            ->assertSessionHas('status');

        $portalUser = User::query()->where('email', 'portal-customer@example.com')->first();

        $this->assertNotNull($portalUser);
        $this->assertTrue($portalUser->isClientPortalAccount());
        $this->assertSame($customer->id, $portalUser->customer_id);

        Notification::assertSentTo($portalUser, ResetPasswordNotification::class);
    }

    public function test_portal_invite_requires_customer_email(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('crm.customers.edit');

        $customer = Customer::factory()->create([
            'company_id' => $admin->company_id,
            'branch_id' => $admin->default_branch_id,
            'email' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.crm.customers.portal-invite', $customer))
            ->assertRedirect()
            ->assertSessionHasErrors('customer');
    }
}
