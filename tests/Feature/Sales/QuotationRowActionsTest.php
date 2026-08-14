<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerStatus;
use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Sales\SalesDeskActionPresenter;
use App\Support\Sales\SalesDeskViews;
use App\Support\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuotationRowActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_draft_quote_offers_edit_submit_and_delete(): void
    {
        [$quotation, $user] = $this->quoteFor(QuotationStatus::Draft, [
            'quotations.view', 'quotations.edit', 'quotations.delete',
        ]);

        $this->actingAs($user);

        $this->assertSame(
            ['view', 'edit', 'submit_approval', 'document', 'pdf', 'delete'],
            $this->actionKeys($quotation),
        );
    }

    public function test_sent_quote_offers_follow_up_actions_not_edit(): void
    {
        [$quotation, $user] = $this->quoteFor(QuotationStatus::Sent, [
            'quotations.view', 'quotations.edit', 'quotations.send',
        ]);

        $this->actingAs($user);

        $keys = $this->actionKeys($quotation);

        $this->assertSame(
            ['view', 'mark_viewed', 'reject', 'expire', 'document', 'pdf'],
            $keys,
        );
        $this->assertNotContains('edit', $keys);
        $this->assertNotContains('convert', $keys);
    }

    public function test_viewed_quote_offers_accept_and_reject(): void
    {
        [$quotation, $user] = $this->quoteFor(QuotationStatus::Viewed, [
            'quotations.view', 'quotations.edit',
        ]);

        $this->actingAs($user);

        $this->assertSame(
            ['view', 'accept', 'reject', 'expire', 'document', 'pdf'],
            $this->actionKeys($quotation),
        );
    }

    public function test_accepted_quote_offers_convert_to_sales_order(): void
    {
        [$quotation, $user] = $this->quoteFor(QuotationStatus::Accepted, [
            'quotations.view', 'quotations.convert', 'sales_orders.create',
        ]);

        $this->actingAs($user);

        $this->assertSame(
            ['view', 'convert', 'quick_convert', 'document', 'pdf'],
            $this->actionKeys($quotation),
        );
    }

    public function test_pending_quote_offers_send_and_approve_when_permitted(): void
    {
        [$quotation, $user] = $this->quoteFor(QuotationStatus::PendingApproval, [
            'quotations.view', 'quotations.edit', 'quotations.send', 'quotations.approve',
        ]);

        $this->actingAs($user);

        $this->assertSame(
            ['view', 'edit', 'approve', 'send', 'document', 'pdf'],
            $this->actionKeys($quotation),
        );
    }

    public function test_converted_quote_links_to_sales_order(): void
    {
        [$quotation, $user] = $this->quoteFor(QuotationStatus::Converted, [
            'quotations.view', 'sales_orders.view',
        ]);

        SalesOrder::factory()->create([
            'company_id' => $quotation->company_id,
            'branch_id' => $quotation->branch_id,
            'customer_id' => $quotation->customer_id,
            'quotation_id' => $quotation->id,
            'artwork_request_id' => null,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        $this->assertSame(
            ['view', 'view_sales_order', 'document', 'pdf'],
            $this->actionKeys($quotation->fresh(['salesOrder'])),
        );
    }

    public function test_viewer_only_sees_view_and_documents(): void
    {
        [$quotation, $user] = $this->quoteFor(QuotationStatus::Sent, [
            'quotations.view',
        ]);

        $this->actingAs($user);

        $this->assertSame(
            ['view', 'document', 'pdf'],
            $this->actionKeys($quotation),
        );
    }

    public function test_sales_desk_quotes_register_renders_status_actions(): void
    {
        [$quotation, $user] = $this->quoteFor(QuotationStatus::Sent, [
            'quotations.view', 'quotations.edit',
            'crm.customers.create', 'sales_orders.create',
        ]);

        $this->actingAs($user)
            ->get(SalesDeskViews::quotesUrl())
            ->assertOk()
            ->assertSee(__('Mark viewed'), false)
            ->assertSee(__('Reject'), false)
            ->assertSee($quotation->quotation_number, false)
            ->assertDontSee('>'.__('Edit').'<', false);
    }

    /**
     * @return list<string>
     */
    protected function actionKeys(Quotation $quotation): array
    {
        return array_column(
            app(SalesDeskActionPresenter::class)->quotationRowActions($quotation),
            'key',
        );
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Quotation, 1: User}
     */
    protected function quoteFor(QuotationStatus $status, array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'CUST-00001',
            'company_name' => 'Test Customer',
            'status' => CustomerStatus::Active,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $user->id,
            'status' => $status,
        ]);

        return [$quotation, $user];
    }
}
