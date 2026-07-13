<?php

namespace Tests\Feature\Crm;

use App\Enums\ActivityType;
use App\Enums\CustomerStatus;
use App\Enums\FollowUpStatus;
use App\Enums\LeadStatus;
use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadFollowUp;
use App\Models\Crm\LeadStage;
use App\Models\Sales\Quotation;
use App\Models\User;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Lead360WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_lead_360_renders_all_tabs(): void
    {
        [$company, $branch, $user, $lead] = $this->leadContext([
            'crm.leads.view', 'commercial.activities.create', 'quotations.view',
            'quotations.create', 'crm.customers.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.crm.leads.show', $lead));

        $response->assertOk()
            ->assertSee(__('Lead 360'), false)
            ->assertSee(__('Overview'), false)
            ->assertSee(__('Activities'), false)
            ->assertSee(__('Follow Ups'), false)
            ->assertSee(__('Quotations'), false)
            ->assertSee(__('Timeline'), false)
            ->assertSee(__('Conversion History'), false)
            ->assertSee($lead->lead_name, false);
    }

    public function test_lead_360_overview_shows_opportunity_fields(): void
    {
        [$company, $branch, $user, $lead] = $this->leadContext(['crm.leads.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.crm.leads.show', $lead))
            ->assertOk()
            ->assertSee(__('Lead source'), false)
            ->assertSee(__('Assigned user'), false)
            ->assertSee(__('Expected close date'), false);
    }

    public function test_lead_activity_can_be_logged_from_workspace(): void
    {
        [$company, $branch, $user, $lead] = $this->leadContext([
            'crm.leads.view', 'commercial.activities.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.crm.leads.activities.store', $lead), [
                'activity_type' => ActivityType::Call->value,
                'subject' => 'Discovery call',
                'activity_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('admin.crm.leads.show', ['lead' => $lead, 'tab' => 'activities']));

        $this->assertDatabaseHas('customer_activities', [
            'lead_id' => $lead->id,
            'subject' => 'Discovery call',
        ]);
    }

    public function test_lead_360_displays_logged_activities(): void
    {
        [$company, $branch, $user, $lead] = $this->leadContext([
            'crm.leads.view', 'commercial.activities.create',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.crm.leads.activities.store', $lead), [
            'activity_type' => ActivityType::Meeting->value,
            'subject' => 'Site visit',
            'activity_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->actingAs($user)
            ->get(route('admin.crm.leads.show', $lead))
            ->assertOk()
            ->assertSee('Site visit', false);
    }

    public function test_follow_up_scheduling_redirects_to_follow_ups_tab(): void
    {
        [$company, $branch, $user, $lead] = $this->leadContext([
            'crm.leads.view', 'crm.leads.edit',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.crm.leads.follow-ups.store', $lead), [
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'notes' => 'Call back',
            ])
            ->assertRedirect(route('admin.crm.leads.show', ['lead' => $lead, 'tab' => 'follow-ups']));

        $this->assertDatabaseHas('lead_follow_ups', [
            'lead_id' => $lead->id,
            'notes' => 'Call back',
            'status' => FollowUpStatus::Pending->value,
        ]);
    }

    public function test_linked_quotations_appear_on_lead_360(): void
    {
        [$company, $branch, $user, $lead] = $this->leadContext([
            'crm.leads.view', 'quotations.view',
        ]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => CustomerStatus::Active,
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'lead_id' => $lead->id,
            'status' => QuotationStatus::Draft,
            'quotation_number' => 'QUO-LEAD-001',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.crm.leads.show', $lead))
            ->assertOk()
            ->assertSee('QUO-LEAD-001', false);
    }

    public function test_quotation_create_presets_lead_and_customer_from_lead(): void
    {
        [$company, $branch, $user, $lead] = $this->leadContext([
            'crm.leads.view', 'quotations.create', 'quotations.view', 'crm.customers.view',
        ]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);
        $lead->update(['customer_id' => $customer->id]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.quotations.create', ['lead_id' => $lead->id]))
            ->assertOk()
            ->assertSee('value="'.$lead->id.'" selected', false)
            ->assertSee('value="'.$customer->id.'" selected', false);
    }

    public function test_lead_conversion_creates_customer_and_marks_won(): void
    {
        [$company, $branch, $user, $lead] = $this->leadContext([
            'crm.leads.view', 'crm.leads.edit', 'crm.customers.create', 'crm.customers.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.crm.leads.convert', $lead))
            ->assertRedirect();

        $lead->refresh();

        $this->assertNotNull($lead->customer_id);
        $this->assertSame(LeadStatus::Won, $lead->status);
        $this->assertDatabaseHas('customers', [
            'id' => $lead->customer_id,
            'company_name' => 'Acme Printing',
        ]);
    }

    public function test_timeline_shows_lead_created_event(): void
    {
        [$company, $branch, $user, $lead] = $this->leadContext(['crm.leads.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.crm.leads.show', $lead))
            ->assertOk()
            ->assertSee(__('Lead created'), false)
            ->assertSee(__('Acquisition timeline'), false);
    }

    public function test_viewer_cannot_log_activities(): void
    {
        [$company, $branch, , $lead] = $this->leadContext([]);
        $viewer = $this->userWithRole('Viewer', $company, $branch, ['crm.leads.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($viewer)
            ->post(route('admin.crm.leads.activities.store', $lead), [
                'activity_type' => ActivityType::Call->value,
                'subject' => 'Blocked',
                'activity_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertForbidden();
    }

    public function test_company_isolation_for_lead_360(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $companyA->id]);
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);
        (new CrmFoundationSeeder)->run();

        $userA = $this->userWithRole('Sales', $companyA, $branchA, ['crm.leads.view']);
        $leadB = Lead::query()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'lead_name' => 'Foreign Lead',
            'status' => LeadStatus::Open,
        ]);

        session(['active_company_id' => $companyA->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($userA)
            ->get(route('admin.crm.leads.show', $leadB))
            ->assertNotFound();
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3: Lead}
     */
    protected function leadContext(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        (new CrmFoundationSeeder)->run();

        $stage = LeadStage::query()->where('company_id', $company->id)->first();
        $user = $this->userWithRole('Sales', $company, $branch, $permissions);

        $lead = Lead::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'stage_id' => $stage?->id,
            'lead_name' => 'Acme Prospect',
            'company_name' => 'Acme Printing',
            'phone' => '0700000000',
            'email' => 'prospect@acme.test',
            'estimated_value' => 250000,
            'probability' => 60,
            'expected_close_date' => now()->addMonth()->toDateString(),
            'status' => LeadStatus::Open,
        ]);

        LeadFollowUp::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'lead_id' => $lead->id,
            'created_by' => $user->id,
            'scheduled_at' => now()->subDay(),
            'status' => FollowUpStatus::Pending,
            'notes' => 'Overdue callback',
        ]);

        return [$company, $branch, $user, $lead];
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
