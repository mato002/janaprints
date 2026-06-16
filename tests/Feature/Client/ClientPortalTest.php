<?php

namespace Tests\Feature\Client;

use App\Enums\QuotationStatus;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function clientUser(?Customer $customer = null): User
    {
        $customer ??= Customer::factory()->create();

        return User::factory()->create([
            'company_id' => $customer->company_id,
            'default_branch_id' => $customer->branch_id,
            'customer_id' => $customer->id,
            'employee_id' => null,
        ]);
    }

    protected function actingAsClient(User $user)
    {
        return $this->withSession(['auth_context' => 'client'])->actingAs($user);
    }

    public function test_client_dashboard_renders_overview(): void
    {
        $user = $this->clientUser();

        $response = $this->actingAsClient($user)->get(route('client.dashboard'));

        $response->assertOk();
        $response->assertSee(__('Overview'), false);
        $response->assertSee($user->customer->company_name, false);
    }

    public function test_client_dashboard_request_quote_links_to_storefront_form(): void
    {
        $user = $this->clientUser();

        $this->actingAsClient($user)
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('href="'.route('storefront.quote').'#quote-form"', false);
    }

    public function test_client_can_update_contact_details(): void
    {
        $user = $this->clientUser();

        $this->actingAsClient($user)
            ->put(route('client.account.update'), [
                'name' => 'Updated Client Name',
                'phone' => '+254712345678',
                'alternative_phone' => '+254798765432',
                'city' => 'Mombasa',
                'physical_address' => '123 Client Street',
                'postal_address' => 'P.O. Box 456',
                'website' => 'https://demo-client.example',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $user->refresh();
        $customer = $user->customer->fresh();

        $this->assertSame('Updated Client Name', $user->name);
        $this->assertSame('Updated Client Name', $customer->contact_person);
        $this->assertSame('+254712345678', $customer->phone);
        $this->assertSame('+254798765432', $customer->alternative_phone);
        $this->assertSame('Mombasa', $customer->city);
        $this->assertSame('123 Client Street', $customer->physical_address);
        $this->assertSame('P.O. Box 456', $customer->postal_address);
        $this->assertSame('https://demo-client.example', $customer->website);
    }

    public function test_client_can_view_their_quotations(): void
    {
        $user = $this->clientUser();
        $quotation = Quotation::factory()->create([
            'company_id' => $user->company_id,
            'branch_id' => $user->default_branch_id,
            'customer_id' => $user->customer_id,
            'status' => QuotationStatus::Sent,
        ]);

        $other = Quotation::factory()->create([
            'status' => QuotationStatus::Sent,
        ]);

        $this->actingAsClient($user)
            ->get(route('client.quotations.index'))
            ->assertOk()
            ->assertSee($quotation->quotation_number, false)
            ->assertDontSee($other->quotation_number, false);

        $this->actingAsClient($user)
            ->get(route('client.quotations.show', $quotation))
            ->assertOk()
            ->assertSee($quotation->quotation_number, false);
    }

    public function test_client_cannot_view_another_customers_quotation(): void
    {
        $user = $this->clientUser();
        $quotation = Quotation::factory()->create([
            'status' => QuotationStatus::Sent,
        ]);

        $this->actingAsClient($user)
            ->get(route('client.quotations.show', $quotation))
            ->assertNotFound();
    }

    public function test_client_can_accept_quotation(): void
    {
        $user = $this->clientUser();
        $quotation = Quotation::factory()->create([
            'company_id' => $user->company_id,
            'branch_id' => $user->default_branch_id,
            'customer_id' => $user->customer_id,
            'status' => QuotationStatus::Sent,
        ]);

        $this->actingAsClient($user)
            ->post(route('client.quotations.accept', $quotation))
            ->assertRedirect(route('client.quotations.show', $quotation));

        $this->assertSame(QuotationStatus::Accepted, $quotation->fresh()->status);
    }

    public function test_staff_user_cannot_access_client_portal_routes(): void
    {
        $user = User::factory()->create();

        $this->withSession(['auth_context' => 'admin'])
            ->actingAs($user)
            ->get(route('client.dashboard'))
            ->assertRedirect(route('client.login'));

        $this->assertGuest();
    }
}
