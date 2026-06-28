<?php

namespace Tests\Feature\Crm;

use App\Enums\CustomerStatus;
use App\Enums\LeadStatus;
use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadStage;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Platform\SystemSettingsService;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeadQuotationAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_quick_quote_auto_creates_customer_and_links_quotation(): void
    {
        [$company, $branch, $lead, $user] = $this->leadContext();

        app(SystemSettingsService::class)->set('auto_convert_lead_on_quote', true, $company->id, null, 'boolean');

        $response = $this->actingAs($user)
            ->post(route('admin.crm.leads.quotation.quick', $lead));

        $response->assertRedirect();

        $lead->refresh();
        $this->assertNotNull($lead->customer_id);

        $quotation = Quotation::query()->where('lead_id', $lead->id)->first();
        $this->assertNotNull($quotation);
        $this->assertSame($lead->customer_id, $quotation->customer_id);
        $this->assertSame(QuotationStatus::Sent, $quotation->status);
    }

    public function test_quick_quote_requires_customer_when_auto_convert_disabled(): void
    {
        [$company, $branch, $lead, $user] = $this->leadContext();

        app(SystemSettingsService::class)->set('auto_convert_lead_on_quote', false, $company->id, null, 'boolean');

        $this->actingAs($user)
            ->post(route('admin.crm.leads.quotation.quick', $lead))
            ->assertRedirect()
            ->assertSessionHasErrors('lead');

        $this->assertDatabaseCount('quotations', 0);
        $this->assertNull($lead->fresh()->customer_id);
    }

    public function test_quick_quote_uses_existing_customer_without_duplicating(): void
    {
        [$company, $branch, $lead, $user] = $this->leadContext();

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'CUST-00002',
            'company_name' => 'Linked Co',
            'status' => CustomerStatus::Active,
        ]);

        $lead->update(['customer_id' => $customer->id]);

        $this->actingAs($user)
            ->post(route('admin.crm.leads.quotation.quick', $lead))
            ->assertRedirect();

        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseHas('quotations', [
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_create_quotation_route_presets_quotation_form(): void
    {
        [$company, $branch, $lead, $user] = $this->leadContext([
            'crm.leads.view', 'crm.leads.create', 'crm.customers.create', 'crm.leads.edit', 'quotations.create', 'quotations.view',
        ]);

        app(SystemSettingsService::class)->set('auto_convert_lead_on_quote', true, $company->id, null, 'boolean');

        $this->actingAs($user)
            ->get(route('admin.crm.leads.quotation.create', $lead))
            ->assertRedirect(route('admin.quotations.create', [
                'customer_id' => $lead->fresh()->customer_id,
                'lead_id' => $lead->id,
            ]));
    }

    public function test_lead_360_shows_quotation_actions(): void
    {
        [$company, $branch, $lead, $user] = $this->leadContext();

        $this->actingAs($user)
            ->get(route('admin.crm.leads.show', $lead))
            ->assertOk()
            ->assertSee('Create Quotation', false)
            ->assertSee('Quick Quote', false)
            ->assertSee('Quotations', false);
    }

    public function test_viewer_cannot_create_quotation_from_lead(): void
    {
        [$company, $branch, $lead] = $this->leadContext(['crm.leads.view']);

        $viewer = $this->userWithRole('Viewer', $company, $branch, ['crm.leads.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($viewer)
            ->post(route('admin.crm.leads.quotation.quick', $lead))
            ->assertForbidden();
    }

    /**
     * @param  list<string>|null  $permissions
     * @return array{0: Company, 1: Branch, 2: Lead, 3: User}
     */
    protected function leadContext(?array $permissions = null): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        (new CrmFoundationSeeder)->run();

        $stage = LeadStage::query()->where('company_id', $company->id)->first();

        $lead = Lead::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'stage_id' => $stage->id,
            'lead_name' => 'Pipeline Prospect',
            'company_name' => 'Prospect Ltd',
            'phone' => '+254700000099',
            'email' => 'prospect@example.com',
            'status' => LeadStatus::Open,
            'estimated_value' => 25000,
        ]);

        $permissions ??= [
            'crm.leads.view',
            'crm.leads.create',
            'crm.leads.edit',
            'crm.customers.create',
            'quotations.create',
            'quotations.view',
        ];

        $user = $this->userWithRole('Sales', $company, $branch, $permissions);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $lead, $user];
    }

    protected function userWithRole(string $role, Company $company, Branch $branch, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $roleModel = Role::findByName($role, 'web');
        $roleModel->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
